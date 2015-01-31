<?php

require_once('CRMDefaults.php');
require_once('PassHash.php');
require_once('LanguageHandler.php');

/**
 * Class to handle DB Installation
 *
 * @author Ignacio Nieto Carvajal
 * @link URL http://digitalleaves.com
 */
class DBInstaller {

    private $conn;
    private $state;
    private $error;
    private $lh;
    
    /* ---------------- Initializers -------------------- */
    
    public function __construct($dbhost, $dbname, $dbuser, $dbpass, $dbport = CRM_DEFAULT_DB_PORT) {
        $this->lh = LanguageHandler::getInstance();
        $this->conn = @ new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);
		
        // Check for database connection error
        if ($this->conn->connect_error) {
            $this->state = CRM_INSTALL_STATE_ERROR;
            $this->error = CRM_INSTALL_STATE_DATABASE_ERROR . ". " .  $this->conn->connect_error;
        } else {
			$this->conn->set_charset('utf8');
	        $this->state = CRM_INSTALL_STATE_SUCCESS;
	    }
        
    }
    
    public function __destruct() {
	    $this->closeDatabaseConnection();
    }
    
    public function getState() {
	    return $this->state;
    }
    
    public function getLastErrorMessage() {
	    return $this->error;
    }
    
    public function closeDatabaseConnection() {
	    if (isset($this->conn)) { @ $this->conn->close(); }
    }
    
    /* ---------------- Setup of database -------------------------- */
    
    /**
	 * Setups the database without the client models, just the standard tables.
	 * It also creates the default admin user.
	 * @param $adminUserName String the name of the admin user.
	 * @param $adminUserPassword String 
	 */
    public function setupBasicDatabase($adminUserName, $adminUserPassword, $adminUserEmail) {
	    // drop previous tables if any
	    if ($this->dropPreviousTables()) { return false; }
	    
	    // create the basic tables
	    if ($this->setupUsersTable($adminUserName, $adminUserPassword, $adminUserEmail) == false) { return false; }
	    if ($this->setupTasksTable() == false) { return false; }
	    if ($this->setupNotificationsTable() == false) { return false; }
	    if ($this->setupMaritalStatusTable() == false) { return false; }
	    if ($this->setupMessagesTables() == false) { return false; }
	    
	    return true;
    }
    
	/* ----------------------- Table creation, deletion and population -------------------------- */

	private function dropPreviousTables() {
		$dropTableQuery = "DROP TABLE IF EXISTS `users` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `users`"; return false; } // failed to drop table.

		$dropTableQuery = "DROP TABLE IF EXISTS `tasks` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `tasks`"; return false; } // failed to drop table.

		$dropTableQuery = "DROP TABLE IF EXISTS `notifications` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `notifications`"; return false; } // failed to drop table.
		
		$dropTableQuery = "DROP TABLE IF EXISTS `marital_status` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `marital_status`"; return false; } // failed to drop table.

		$dropTableQuery = "DROP TABLE IF EXISTS `messages_inbox` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `messages_inbox`"; return false; } // failed to drop table.
		
		$dropTableQuery = "DROP TABLE IF EXISTS `messages_outbox` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `messages_outbox`"; return false; } // failed to drop table.
		
		$dropTableQuery = "DROP TABLE IF EXISTS `messages_junk` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `messages_junk`"; return false; } // failed to drop table.
		
		$dropTableQuery = "DROP TABLE IF EXISTS `statistics` CASCADE";
		if (!$this->conn->query($dropTableQuery)) { $this->error = "CRM: Failed to drop table `statistics`"; return false; } // failed to drop table.
	}

	private function setupUsersTable($initialUser, $initialPass, $initialEmail) {
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `users` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `password_hash` varchar(255) NOT NULL,
			  `phone` varchar(40) DEFAULT NULL,
			  `email` varchar(120) DEFAULT NULL,
			  `avatar` varchar(255) DEFAULT NULL,
			  `creation_date` date NOT NULL,
			  `role` int(4) NOT NULL,
			  `status` int(1) NOT NULL COMMENT '1=enabled, 0=disabled',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `users`."; return false; } // failed to create table
		
		$password_hash = PassHash::hash($initialPass);
		$initializeTableQuery = "INSERT INTO `users` (`name`, `password_hash`, `email`, `avatar`, `creation_date`, `role`, `status`) VALUES
('$initialUser', '$password_hash', '$initialEmail', '".CRM_DEFAULTS_USER_AVATAR."', now(), 0, 1) ON DUPLICATE KEY UPDATE password_hash = '$password_hash'";
		if (!$this->conn->query($initializeTableQuery)) { $this->error = "CRM: Failed to insert the initial admin user."; return false; } 
		
		return true;
	}
	
	private function setupTasksTable() {
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `tasks` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `description` varchar(512) NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `target_customer_id` int(11),
		  `creation_date` datetime NOT NULL,
		  `completion_date` datetime DEFAULT NULL,
		  `completed` int(3) NOT NULL COMMENT 'from 0 to 100, 0=not started, 100=completed',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `tasks`"; return false; } // failed to create table
		return true;
	}

	private function setupNotificationsTable() {
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `notifications` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `target_user` int(11) DEFAULT NULL COMMENT '0=all users, otherwise, the user id of the target user.',
		  `text` varchar(512) NOT NULL,
		  `date` datetime NOT NULL,
		  `action` varchar(255) DEFAULT NULL COMMENT 'if not null, a link to the target of the action',
		  `type` varchar(255) NOT NULL DEFAULT 'event',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `notifications`"; return false; } // failed to create table
		return true;
	}

	private function setupMaritalStatusTable() {
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `marital_status` (
		  `id` int(1) NOT NULL,
		  `name` varchar(80) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `marital_status`"; return false; } // failed to create table
		
		$initializeTableQuery = "INSERT INTO `marital_status` (`id`, `name`) VALUES
			(1, 'single'), (2, 'married'), (3, 'divorced'), (4, 'separated'), (5, 'widow/er')";
		if (!$this->conn->query($initializeTableQuery)) { $this->error = "CRM: Failed to initialize `marital_status`"; return false; } // failed to initialize table
		
		return true;
	}

	private function setupMessagesTables() {
		// inbox
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `messages_inbox` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_from` int(11) NOT NULL,
		  `user_to` int(11) NOT NULL,
		  `subject` varchar(255) NOT NULL,
		  `message` varchar(1024) DEFAULT NULL,
		  `date` datetime NOT NULL,
		  `message_read` int(1) NOT NULL,
		  `favorite` int(1) NOT NULL DEFAULT '0' COMMENT '0=not-favorite, 1=favorite',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `messages_inbox`"; return false; } // failed to create table
		
		// outbox
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `messages_junk` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_from` int(11) NOT NULL,
		  `user_to` int(11) NOT NULL,
		  `subject` varchar(255) NOT NULL,
		  `message` varchar(1024) DEFAULT NULL,
		  `date` datetime NOT NULL,
		  `message_read` int(1) NOT NULL,
		  `favorite` int(1) NOT NULL DEFAULT '0' COMMENT '0=not-favorite, 1=favorite',
		  `origin_folder` varchar(120) NOT NULL COMMENT 'origin folder of the message (for restore purposes)',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `messages_junk`"; return false; } // failed to create table
		
		// junk
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `messages_outbox` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_from` int(11) NOT NULL,
		  `user_to` int(11) NOT NULL,
		  `subject` varchar(255) NOT NULL,
		  `message` varchar(1024) DEFAULT NULL,
		  `date` datetime NOT NULL,
		  `message_read` int(1) NOT NULL,
		  `favorite` int(1) NOT NULL DEFAULT '0' COMMENT '0=not-favorite, 1=favorite',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createTableQuery)) { $this->error = "CRM: Failed to create table `messages_outbox`"; return false; } // failed to create table
		
		return true;		
	}

	private function generateIdentifiersForCustomers($schema, $customCustomers) {
		$customerIdentifiers = array();
		
		if ($schema == CRM_DEFAULTS_CUSTOMERS_SCHEMA_DEFAULT) { // default schema: clients_1 (contacts) and clients_2 (normal clients).
			array_push($customerIdentifiers, "clients_1");
			array_push($customerIdentifiers, "clients_2");			
		} else if ($schema == CRM_DEFAULTS_CUSTOMERS_SCHEMA_CUSTOM) {
			$index = 1;
			foreach ($customCustomers as $description) {
				array_push($customerIdentifiers, "clients_$index");
			}
		}
		return $customerIdentifiers;
	}

	public function setupCustomerTables($schema, $customCustomers) {
		// first create the types table
		if (!$this->createCustomerTypesTable()) return false;
		$customerIdentifiers = $this->generateIdentifiersForCustomers($schema, $customCustomers);
		
		if ($schema == CRM_DEFAULTS_CUSTOMERS_SCHEMA_DEFAULT) { // default schema: clients_1 (contacts) and clients_2 (normal clients).
			if (!$this->createCustomersTableWithNameAndDescription("clients_1", "Contacts")) return false;
			if (!$this->createCustomersTableWithNameAndDescription("clients_2", "Customers")) return false;
		} else if ($schema == CRM_DEFAULTS_CUSTOMERS_SCHEMA_CUSTOM) {
			$index = 1;
			foreach ($customCustomers as $description) {
				if (!$this->createCustomersTableWithNameAndDescription("clients_$index", $description)) return false;
				$index++;
			}
		}
		
		// if all operations succeed, return true
		return true;
	}
	
	public function setupCustomersStatistics($schema, $customCustomers) {
	    // create the statistics table for tracking evolution in number of customers.
		$customerIdentifiers = $this->generateIdentifiersForCustomers($schema, $customCustomers);
	    $createStatisticsQuery = "CREATE TABLE IF NOT EXISTS `statistics` (
			`id` int(11) NOT NULL AUTO_INCREMENT,";
		foreach ($customerIdentifiers as $customerId) {
			$createStatisticsQuery = $createStatisticsQuery . "`$customerId` int(11) NOT NULL,\n";
		}
		$createStatisticsQuery = $createStatisticsQuery."`timestamp` date NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if (!$this->conn->query($createStatisticsQuery)) return false;

		// create the event for scheduling the statistics retrieval. The event scheduler must be turned on.
	    $customerFieldsString = "";
	    $customerCountsString = "";
	    $index = 1;
	    foreach ($customerIdentifiers as $ci) { 
		    $customerFieldsString = $customerFieldsString . ", $ci";
		    $customerCountsString = $customerCountsString . ", (select count(*) from $ci) as t$index";
		}

	    // the event scheduler will take care of running the event.
		$eventQuery = "CREATE EVENT retrieve_statistics 
			ON SCHEDULE EVERY 1 WEEK 
			DO BEGIN
				INSERT INTO statistics ( timestamp $customerFieldsString ) 
				SELECT now() $customerCountsString ;							    
			END;";
		if (!$this->conn->query($eventQuery)) return false;

		// Start event scheduler. Requires SUPER admin privileges.
		$startEventSchedulerQuery = "SET GLOBAL event_scheduler = 1;";
		if (!$this->conn->query($startEventSchedulerQuery)) return false;
		// if all operations succeed, return true
		return true;
	}
	
	private function createCustomersTableWithNameAndDescription($name, $description) {
		$createContactsQuery = "CREATE TABLE IF NOT EXISTS `$name` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `company` int(1) NOT NULL DEFAULT '0',
		  `name` varchar(50) NOT NULL,
		  `id_number` varchar(50) DEFAULT NULL COMMENT 'passport, dni, nif or identifier of the person',
		  `address` text,
		  `city` varchar(50) DEFAULT NULL,
		  `state` varchar(50) DEFAULT NULL,
		  `zip_code` varchar(50) DEFAULT NULL,
		  `country` varchar(50) DEFAULT NULL,
		  `phone` text,
		  `mobile` text,
		  `email` varchar(255) DEFAULT NULL,
		  `avatar` varchar(255) DEFAULT NULL,
		  `type` text,
		  `notes` text,
		  `birthdate` datetime DEFAULT NULL,
		  `marital_status` int(11) DEFAULT NULL,
		  `creation_date` datetime DEFAULT NULL,
		  `created_by` int(11) NOT NULL COMMENT 'id of the user that created the contact or client',
		  `do_not_send_email` char(1) DEFAULT NULL,
		  `gender` int(1) DEFAULT NULL COMMENT '0=female, 1=male',
		  PRIMARY KEY (`id`),
		  KEY `Unique id` (`id`),
		  KEY `Unique name` (`name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$success = $this->conn->query($createContactsQuery);
		
		if ($success === true) {
			$addCustomerLinkQuery = "INSERT INTO customer_types (table_name, description) VALUES ('$name', '$description')";
			$success = $this->conn->query($addCustomerLinkQuery);
		}
		
		return $success;
	}
	
	private function createCustomerTypesTable() {
		$createTableQuery = "CREATE TABLE IF NOT EXISTS `customer_types` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `table_name` varchar(255) NOT NULL,
		  `description` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		
		return $this->conn->query($createTableQuery);
	}
}

?>