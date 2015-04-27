<?php
/**
	The MIT License (MIT)
	
	Copyright (c) 2015 Ignacio Nieto Carvajal
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

namespace creamy;

// dependencies
require_once('CRMDefaults.php');
require_once('PassHash.php');
require_once('ImageHandler.php');
require_once('RandomStringGenerator.php');
require_once('LanguageHandler.php');
require_once('DatabaseConnectorFactory.php');

/**
 * DbHandler class.
 * Class to handle all db operations
 * This class is in charge of managing the database operations for Creamy. All DB managing should be done by means of instances of this class, i.e:
 *
 * $db = new \creamy\DbHandler();
 * $success = $db->deleteUser(123);
 *
 * @author Ignacio Nieto Carvajal
 * @link URL http://digitalleaves.com
 */
class DbHandler {
    /** Database connector */
    private $dbConnector;
	/** Language handler */
	private $lh;
        
	/** Creation and class lifetime management */
    
    function __construct($dbConnectorType = CRM_DB_CONNECTOR_TYPE_MYSQL) {
		// Database connector
		$this->dbConnector = \creamy\DatabaseConnectorFactory::getInstance()->getDatabaseConnectorOfType($dbConnectorType);
		$locale = $this->getLocaleSetting();
		// language handler
		$this->lh = \creamy\LanguageHandler::getInstance($locale, $dbConnectorType);
    
    }
    
    function __destruct() {
	    if (isset($this->dbConnector)) { unset($this->dbConnector); }
    }    
    
    /** Administration of users */
    
    /**
     * Creating new user
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $password, $email, $phone, $role, $avatarURL) {
        // First check if user already existed in db
        if (!$this->userExistsIdentifiedByName($name)) {
            // Generating password hash
            $password_hash = \creamy\PassHash::hash($password);
            if (empty($avatarURL)) $avatarURL = CRM_DEFAULTS_USER_AVATAR;

            // insert query
            $data = Array(
	            "name" => $name,
	            "password_hash" => $password_hash,
	            "email" => $email,
	            "phone" => $phone,
	            "role" => $role,
	            "avatar" => $avatarURL,
	            "creation_date" => $this->dbConnector->now(),
	            "status" => "1"
            );
            $id = $this->dbConnector->insert(CRM_USERS_TABLE_NAME, $data);
            // Check for successful insertion
            if ($id) { // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else { // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
    }

	/**
	 * Modifies user's data.
	 * @param Int $modifyid id of the user to be modified.
	 * @param String $email new email for the user.
	 * @param String $phone new phone for the user.
	 * @param String $role new role for the user.
	 * @param String $avatar new avatar URI for the user. Old avatar will be deleted from disk.
	 * return boolean true if user was successfully modified, false otherwise.
	 */
	public function modifyUser($modifyid, $email, $phone, $role, $avatar) {
		// prepare query depending on parameters.
		$this->dbConnector->where("id", $modifyid);
		if (!empty($avatar)) { // If we are modifying the user's avatar, make sure to delete the old one.
			// get user data and remove previous avatar.
			$userdata = $this->getDataForUser($modifyid);
			$ih = new \creamy\ImageHandler();
			$ih->removeUserAvatar($userdata["avatar"]);
			
			// update with new avatar
			$data = Array("email" => $email, "phone" => $phone, "avatar" => $avatar, "role" => $role);
		} else { // no avatar change required, just update the values.
			$data = Array("email" => $email, "phone" => $phone, "role" => $role);
		}

		// execute and return results
		return ( $this->dbConnector->update(CRM_USERS_TABLE_NAME, $data) );

   	}

	/**
	 * Deletes a user from the database.
	 * @param Int $userid id of the user to be deleted.
	 * return boolean true if user was successfully deleted, false otherwise.
	 */
	 public function deleteUser($userid) {
	 	if (empty($userid)) return false;
	 	// first check if we need to remove the avatar.
	 	$data = $this->getDataForUser($userid);
	 	if (isset($data["avatar"])) {
		 	$ih = new \creamy\ImageHandler();
		 	$ih->removeUserAvatar($data["avatar"]);
	 	}
	 	// then remove the entry at the database
	 	$this->dbConnector->where("id", $userid);
	 	return $this->dbConnector->delete(CRM_USERS_TABLE_NAME);
	 }

    /**
     * Checking user login
     * @param String $name User login name
     * @param String $password User login password
     * @return object an associative array containing the user's data if credentials are valid and login succeed, NULL otherwise.
     */
    public function checkLogin($name, $password) {
        // fetching user by name and password
        $this->dbConnector->where("name", $name);
        $userobj = $this->dbConnector->getOne(CRM_USERS_TABLE_NAME);

		if ($userobj) { // first match valid?
			$password_hash = $userobj["password_hash"];
			$status = $userobj["status"];
			if ($status == 1) { // user is active
				if (\creamy\PassHash::check_password($password_hash, $password)) {
	                // User password is correct. return some interesting fields...
	                $arr = array();
	                $arr["id"] = $userobj["id"];
	                $arr["name"] = $userobj["name"];
	                $arr["email"] = $userobj["email"];
	                $arr["role"] = $userobj["role"];
	                $arr["avatar"] = $userobj["avatar"];
	                
	                return $arr;
	            } else {
	                // user password is incorrect
	                return NULL;
	            }
			} else return NULL;
		} else {
			return NULL;
		}
    }
    
    /**
	 * Changes the user password to $password1 (= $password2) if $oldpassword matches current password.
	 * This function is supposed to be called by a user changing its own password.
	 * @param String $userid ID of the user to change the password to.
	 * @param String $oldpassword old password to change.
	 * @param String $password1 new password
	 * @param String $password2 new password (must be = to $password1).
	 * @return boolean true if password was successfully changed, false otherwise.
	 */
	public function changePassword($userid, $oldpassword, $password1, $password2) {
		// safety check
		if ($password1 != $password2) return false;
		// get old password hash to check both.
		$this->dbConnector->where("id", $userid);
		$userobj = $this->dbConnector->getOne(CRM_USERS_TABLE_NAME);
		// check if password change is valid
		if ($userobj) {
			$password_hash = $userobj["password_hash"];
			$status = $userobj["status"];
			if ($status == 1) { // user is active, check old password.
				if (\creamy\PassHash::check_password($password_hash, $oldpassword)) {
	                // oldpassword is correct, change password.
	                $newPasswordHash = \creamy\PassHash::hash($password1);
					$this->dbConnector->where("id", $userid);
					$data = Array("password_hash" => $newPasswordHash);
					return $this->dbConnector->update(CRM_USERS_TABLE_NAME, $data);
	            } else {
	                // oldpassword is incorrect
	                return false;
	            }
			} else return false;
		} else {
			return false;
		}
	}
	
    /**
	 * Changes the user password to $password, without checking for valid old password.
	 * This function is intended to be called only by superuser or a CRM administrator, with admin role.
	 * @param String $userid ID of the user to change the password to.
	 * @param String $password new password
     * @return boolean true if operation succeed.
	 */
	public function changePasswordAdmin($userid, $password) {
		$newPasswordHash = \creamy\PassHash::hash($password);
		$this->dbConnector->where("id", $userid);
		$data = Array("password_hash" => $newPasswordHash);
		return $this->dbConnector->update(CRM_USERS_TABLE_NAME, $data);
	}
    
    /**
     * Gets the data of a user.
     * @param String $userid id of the user to get data from.
     * @return object an associative array containing the user's relevant data if the user id valid, NULL otherwise.
     */
    public function getDataForUser($userid) {
	    $this->dbConnector->where("id", $userid);
	    $cols = array("id", "name", "email", "phone", "role", "avatar", "creation_date");
	    return $this->dbConnector->getOne(CRM_USERS_TABLE_NAME, null, $cols);
    }
    
    /**
     * Returns an array containing all enabled users (those with status=1).
     * @return Array an array of objects containing the data of all users in the system.
	 */
	public function getAllEnabledUsers() {
		$this->dbConnector->where("status", "1");
		$cols = array("id", "name", "email", "phone", "role", "avatar", "creation_date", "status");
		return $this->dbConnector->get(CRM_USERS_TABLE_NAME, null, $cols);
	}
    
    /**
     * Checking for duplicate user by name
     * @param String $name name to check in db
     * @return boolean
     */
    public function userExistsIdentifiedByName($name) {
	    $this->dbConnector->where("name", $name);
	    $this->dbConnector->get(CRM_USERS_TABLE_NAME);
	    return ($this->dbConnector->count > 0);
    }

    /**
     * Checking for existing email for a user in the database
     * @param String $email email to check in db
     * @return boolean true if operation succeed.
     */
    public function userExistsIdentifiedByEmail($email) {
	    $this->dbConnector->where("email", $email);
	    $this->dbConnector->get(CRM_USERS_TABLE_NAME);
	    return ($this->dbConnector->count > 0);
    }

    /**
     * Returns an array containing all users in the system (only relevant data).
     * @return Array an array of objects containing the data of all users in the system.
     */
   	public function getAllUsers() {
	   	$cols = array("id", "name", "email", "phone", "creation_date", "role", "avatar", "status");
	   	return $this->dbConnector->get(CRM_USERS_TABLE_NAME, null, $cols);
	}
	
	/**
	 * Changes the status for a user, from enabled (=1) to disabled (=0) or viceversa.
     * @param $userid Int the id of the user
     * @param $status Int the new status for the user
	 */
	public function setStatusOfUser($userid, $status) {
		$this->dbConnector->where("id", $userid);
		$data = array("status" => $status);
		return $this->dbConnector->update(CRM_USERS_TABLE_NAME, $data);
	}
	
	/** Password recovery */

	/** Checks link validity for a password reset code */
	public function checkPasswordResetValidity($email, $date, $nonce, $code) {
		$checkCode = $this->generatePasswordResetCode($email, $date, $nonce);
		if ($checkCode == $code) { // if codes match (not tainted data)
			$parsed = date_parse_from_format('Y-m-d-H-i-s', $date);
			$requestTimestamp = mktime(
		        $parsed['hour'], 
		        $parsed['minute'], 
		        $parsed['second'], 
		        $parsed['month'], 
		        $parsed['day'], 
		        $parsed['year']
			);
			$currentTimestamp = time();
			// check if no more than 24h have passed.
			$diff = $currentTimestamp - $requestTimestamp;
			if ($diff > 0 && $diff < (60*60*24)) { return true; }
		}
		return false;
	}

	/** Generates a password reset code, a md5($email + $date + $nonce + CRM_SECURITY_TOKEN) */
	public function generatePasswordResetCode($email, $date, $nonce) {
		$baseString = $email.$date.$nonce.CRM_SECURITY_TOKEN;
		return md5($baseString);
	}
	
	/** 
	 * Changes the password of a user identified by an email. The user must have a valid email in the database.
	 * @param $email String the email of the user.
	 * @param $password the new password for the user.
	 */
	public function changePasswordForUserIdentifiedByEmail($email, $password) {
		if ($this->userExistsIdentifiedByEmail($email)) {
	        // Generating password hash
	        $password_hash = \creamy\PassHash::hash($password);
	        $this->dbConnector->where("email", $email);
	        $data = array("password_hash" => $password_hash);
	        return $this->dbConnector->update(CRM_USERS_TABLE_NAME, $data);
		}
		return false;
	}
	
	/** Settings */

	/** Returns the value for a setting with a given key */
	public function getSettingValueForKey($key) {
		$this->dbConnector->where("setting", $key);
		if ($result = $this->dbConnector->getOne(CRM_SETTINGS_TABLE_NAME)) {
			return $result["value"];
		} 
		return NULL;
	}
	
	public function setSettingValueForKey($key, $value) {
		$this->dbConnector->where("setting", $key);
		$data = array("value" => $value);
		if ($this->dbConnector->update(CRM_SETTINGS_TABLE_NAME, $data)) {
			// update succeed.
			return true;
		} else { // unable to upload. Perhaps the key didn't exist?
			// try to insert instead.
			$this->dbConnector->where("setting", $key);
			$data = array("setting" => $key, "value" => $value);
			return $this->dbConnector->insert(CRM_SETTINGS_TABLE_NAME, $data);
		}
	}
	
	public function setSettings($data) {
		$this->dbConnector->startTransaction();
		if (is_array($data) && !empty($data)) {
			foreach ($data as $key => $value) {
				// locale
				if ($key == CRM_SETTING_LOCALE) { $result = $this->setLocaleSetting($value); }
				// timezone
				else if ($key == CRM_SETTING_TIMEZONE) { $result = $this->setTimezoneSetting($value); }
				// other settings.
				else { $result = $this->setSettingValueForKey($key, $value); }
				
				// failure ?
				if ($result === false) {
					$this->dbConnector->rollback();
					return false;
				}
				
			}
		}
		$this->dbConnector->commit();
		return true;
	}
	
	public function getMainAdminUserData() {
		$adminUserId = $this->getSettingValueForKey(CRM_SETTING_ADMIN_USER);
		if (!empty($adminUserId)) {
			return $this->getDataForUser($adminUserId);
		}
	}
	
	public function getMainAdminEmail() {
		$adminUserData = $this->getMainAdminUserData();
		if (isset($adminUserData)) { return $adminUserData["email"]; }
		else { return null; }
	}
	
	// special settings that need some extra work.
	
	public function getLocaleSetting() { return $this->getSettingValueForKey(CRM_SETTING_LOCALE); }

	public function getTimezoneSetting() { return $this->getSettingValueForKey(CRM_SETTING_TIMEZONE); }

	public function setLocaleSetting($newLocale) {
		if ($this->setSettingValueForKey(CRM_SETTING_LOCALE, $newLocale)) {
			// update Language handler.
			\creamy\LanguageHandler::getInstance()->setLanguageHandlerLocale($newLocale);
			return true;
		}
		return false;
	}
	
	public function setTimezoneSetting($newTimezone) {
		if ($this->setSettingValueForKey(CRM_SETTING_TIMEZONE, $newTimezone)) {
			// update timezone information.
	        ini_set('date.timezone', $newTimezone);
			date_default_timezone_set($newTimezone);
			return true;
		}
		return false;
	}

	/** Customers */
	
	/**
	 * Gets all customers of certain type.
	 * @param $customerType the type of customer to retrieve.
	 * @return Array an array containing the objects with the users' data.
	 */
	public function getAllCustomersOfType($customerType, $numRows = null, $sorting = null, $filtering = null) {
		// safety check
		if (!isset($customerType)) return array();
		
		// columns
		$cols = $this->getCustomerColumnsToBeShownInCustomerList($customerType);
		
		// sorting
		if (isset($sorting) && count($sorting) > 0) {
			foreach ($sorting as $columnToSort => $sortType) { $this->dbConnector->orderBy($columnToSort, $sortType); }
		}
		
		// filtering
		if (isset($filtering) && count($filtering) > 0) {
			$i = 0;
			foreach ($filtering as $columnToSearch => $wordToSearch) {
				if ($i == 0) { $this->dbConnector->where($columnToSearch, '%'.$wordToSearch.'%', "LIKE"); }
				else { $this->dbConnector->orWhere($columnToSearch, '%'.$wordToSearch.'%', 'LIKE'); }
				$i++;
			}
		}
		
		// perform query and execute results.
		return $this->dbConnector->get($customerType, $numRows, $cols);
   	}
	
	/**
	 * Gets the customer columns to be shown in the customer list.
	 * @param $customerType the type of customer to retrieve.
	 * @return Array an array containing the columns to be shown in the customer list.
	 */	
	public function getCustomerColumnsToBeShownInCustomerList($customerType) {
		return array("id", "name", "email", "phone", "id_number");
	}
	
	/**
	 * Creates a new customer
	 * @param $customerType String type of customer (= table where to insert the new customer).
	 * @param $name String name for the new customer
	 * @param $phone String (home) phone for the new customer
	 * @param $mobile String mobile phone for the new customer.
	 * @param $id_number String passport, dni, nif, VAT number or identifier for the customer
	 * @param $address String physical address for that customer
	 * @param $city String City of the customer
	 * @param $state String state for the customer
	 * @param $zipcode String ZIP code for the customer
	 * @param $country String Country for the customer  
	 * @param $birthdate String Birthdate of the customer, expressed in the proper locale format, with month, days and years separated by '/' or '-'.  
	 * @param $maritalstatus String Marital status of the customer (single=1, married=2, divorced=3, separated=4, widow/er=5)  
	 * @param $productType String Product type or definition of the product/service sold to the customer or in which the customer is interested in.
	 * @param $donotsendemail Int a integer/boolean to indicate whether the customer doesn't want to receive email (=1) or is just fine receiving them (=0).
	 * @param $createdByUser Int id of the user that inserted the customer in the system.  
	 * @param $gender Int gender of the customer (female=0, male=1).  
	 * @return boolean true if insert was successful, false otherwise.
	 */
	public function createCustomer($customerType, $name, $email, $phone, $mobile, $id_number, $address, $city, $state, $zipcode, $country, $birthdate, $maritalstatus, $productType, $donotsendemail, $createdByUser, $gender) {
		// sanity checks
		if (empty($customerType)) return false;
		
		// generate correct, mysql-ready date.
		$correctDate = NULL;
		if (!empty($birthdate)) $correctDate = date('Y-m-d',strtotime(str_replace('/','-', $birthdate)));
		
		// prepare and execute query.
		$data = array(
			"name" => $name,
			"email" => $email,
			"phone" => $phone,
			"mobile" => $mobile,
			"id_number" => $id_number,
			"address" => $address,
			"city" => $city,
			"state" => $state,
			"zip_code" => $zipcode,
			"country" => $country,
			"type" => $productType,
			"birthdate" => $correctDate,
			"marital_status" => $maritalstatus,
			"creation_date" => $this->dbConnector->now(),
			"created_by" => $createdByUser,
			"do_not_send_email" => $donotsendemail,
			"gender" => $gender,
		);
		
		if ($this->dbConnector->insert($customerType, $data)) { return true; }
		else { error_log($this->dbConnector->getLastError()); return false; }
	}

	/**
	 * Modifies the data of an existing customer
	 * @param $customerType String type of customer (= table where to insert the new customer).
	 * @param $customerType Int id of the customer in the database
	 * @param $name String name for the new customer
	 * @param $phone String (home) phone for the new customer
	 * @param $mobile String mobile phone for the new customer.
	 * @param $id_number String passport, dni, nif, VAT number or identifier for the customer
	 * @param $address String physical address for that customer
	 * @param $city String City of the customer
	 * @param $state String state for the customer
	 * @param $zipcode String ZIP code for the customer
	 * @param $country String Country for the customer  
	 * @param $birthdate String Birthdate of the customer, expressed in the proper locale format, with month, days and years separated by '/' or '-'.  
	 * @param $maritalstatus String Marital status of the customer (single=1, married=2, divorced=3, separated=4, widow/er=5)  
	 * @param $productType String Product type or definition of the product/service sold to the customer or in which the customer is interested in.
	 * @param $donotsendemail Int a integer/boolean to indicate whether the customer doesn't want to receive email (=1) or is just fine receiving them (=0).
	 * @param $createdByUser Int id of the user that inserted the customer in the system.  
	 * @param $gender Int gender of the customer (female=0, male=1).  
	 * @param $notes String notes for the customer 
	 * @return boolean true if insert was successful, false otherwise.
	 */
	public function modifyCustomer($customerType, $customerid, $name, $email, $phone, $mobile, $id_number, $address, $city, $state, $zipcode, $country, $birthdate, $maritalstatus, $productType, $donotsendemail, $createdByUser, $gender, $notes) {
		// determine customer type (target table) and sanity checks.
		$correctDate = NULL;
		if (!empty($birthdate)) $correctDate = date('Y-m-d',strtotime(str_replace('/','-', $birthdate)));
		
		// prepare and execute query
				// prepare and execute query.
		$data = array(
			"name" => $name,
			"email" => $email,
			"phone" => $phone,
			"mobile" => $mobile,
			"id_number" => $id_number,
			"address" => $address,
			"city" => $city,
			"state" => $state,
			"zip_code" => $zipcode,
			"country" => $country,
			"type" => $productType,
			"birthdate" => $correctDate,
			"marital_status" => $maritalstatus,
			"do_not_send_email" => $donotsendemail,
			"gender" => $gender,
			"notes" => $notes,
		);
		$this->dbConnector->where("id", $customerid);
		return $this->dbConnector->update($customerType, $data);
	}
		
	/**
     * Gets the data of a customer.
     * @param Int $userid id of the customer to get data from.
     * @param String $customerType type of the customer to get data from.
     * @return Array an array containing the customer data, or NULL if customer wasn't found.
     */
    public function getDataForCustomer($customerid, $customerType) {
	    $this->dbConnector->where("id", $customerid);
	    return $this->dbConnector->getOne($customerType);
    }
    
    /**
	 * Deletes a customer from his/her database.
	 * @param $customerid Int id of the customer to delete.
	 * @param $customerType String type (=table) of the customer.
	 */
	 public function deleteCustomer($customerid, $customerType) {
		 // sanity checks
	 	if (empty($customerid) || empty($customerType)) return false;
	 	// then remove the entry at the database
	 	$this->dbConnector->where("id", $customerid);
	 	return $this->dbConnector->delete($customerType);
	 }
	
	/**
	 * Deletes a customer type. This function will delete the table and all associated registers.
	 * @param Int customerType the identifier of the customer type to delete (= id in table customer_types).
	 */
	public function deleteCustomerType($customerTypeId) {
		// safety checks && get the table name for the customer type.
		if ($customerTypeId == 1) { return false; } // we don't want to delete the basic contacts table.
		$this->dbConnector->where("id", $customerTypeId);
		if ($result = $this->dbConnector->getOne(CRM_CUSTOMER_TYPES_TABLE_NAME)) { 
			$tableName = $result["table_name"]; 
		}
		if (!isset($tableName)) { return false; }
		
		// We will set a transaction to make sure we delete everything at once.
		$this->dbConnector->startTransaction();
		// try to delete the association first.
		$this->dbConnector->where("id", $customerTypeId);
		if ($this->dbConnector->delete(CRM_CUSTOMER_TYPES_TABLE_NAME)) {
			// now try to delete all the customer table.
			if ($this->dbConnector->dropTable($tableName)) { // success
				// now try to delete the statistics column.
				if ($this->dbConnector->dropColumnFromTable(CRM_STATISTICS_TABLE_NAME, $tableName)) {
					// success!
					$this->dbConnector->commit();
					return true;
				}
			}
			// TODO: Warn the modules about the deletion, in case they need to modify something.
		}
		$this->dbConnector->rollback();
		return false;
	}
	
	private function createNewCustomersTable($tablename) {
		$fields = array(
			"company" => "int(1) NOT NULL DEFAULT 0",
			"name" => "varchar(255) NOT NULL",
			"id_number" => " varchar(255) DEFAULT NULL",
			"address" => "text",
			"city" => "varchar(255) DEFAULT NULL",
			"state" => "varchar(255) DEFAULT NULL",
			"zip_code" => "varchar(255) DEFAULT NULL",
			"country" => "varchar(255) DEFAULT NULL",
			"phone" => "text",
			"mobile" => "text",
			"email" => "varchar(255) DEFAULT NULL",
			"avatar" => "varchar(255) DEFAULT NULL",
			"type" => "text",
			"webpage" => "varchar(255) DEFAULT NULL",
			"company_name" => "varchar(255) DEFAULT NULL",
			"notes" => "text",
			"birthdate" => "datetime DEFAULT NULL",
			"marital_status" => "int(11) DEFAULT NULL",
			"creation_date" => "datetime DEFAULT NULL",
			"created_by" => "int(11) NOT NULL",
			"do_not_send_email" => "char(1) DEFAULT NULL",
			"gender" => "int(1) DEFAULT NULL");
		
		$unique_keys = array("name", "");
		
		return $this->dbConnector->createTable($tablename, $fields, $unique_keys);
	}
	
	/**
	 * Adds a new customer type, creating the new customer tables and updating customer_types and statistics tables.
	 */
	public function addNewCustomerType($description) {
		// we generate a random temporal name for the table.
		$rsg = new \creamy\RandomStringGenerator();
		$tempName = "temp".$rsg->generate(20);

		$this->dbConnector->startTransaction();
		// first we need to insert the customer_type register, because we don't have a table_name yet.
		$data = array("table_name" => $tempName, "description" => $description);
		$id = $this->dbConnector->insert(CRM_CUSTOMER_TYPES_TABLE_NAME, $data);
		if ($id) { // if insertion was successful, use the generated auto_increment id to set the name of the table_name.
			$tableName = "clients_$id";
			$this->dbConnector->where("id", $id);
			$finalData = array("table_name" => $tableName);
			if ($this->dbConnector->update(CRM_CUSTOMER_TYPES_TABLE_NAME, $finalData)) { // success!
				// now we try to add the new customers table.
				if ($this->createNewCustomersTable($tableName)) { // success!
					// now try to add to statistics.
					if ($this->dbConnector->addColumnToTable(CRM_STATISTICS_TABLE_NAME, $tableName, "INT(11) DEFAULT 0", "0")) {
						// success!
						$this->dbConnector->commit();
						return true;
					}
				}
			}
		}
		$this->dbConnector->rollback();
		return false;
	}
	
	/**
	 * Modifies the description for a type of customer.
	 */
	public function modifyCustomerDescription($customerTypeId, $newDescription) {
		// update description
		$this->dbConnector->where("id", $customerTypeId);
		$data = array("description" => $newDescription);
		return $this->dbConnector->update(CRM_CUSTOMER_TYPES_TABLE_NAME, $data);
	}
	
	/**
	 * Retrieves an array containing an array with all the customer types expressed as an associative array.
	 * @return Array the list of customer type structures.
	 */
	public function getCustomerTypes() {
		return $this->dbConnector->get(CRM_CUSTOMER_TYPES_TABLE_NAME);
	}
	
	/**
	 * Retrieves the customer "human friendly" description name for a customer type.
	 * @param $customerType String customer type ( = table name).
	 * @return String a human friendly description of this customer type.
	 */
	public function getNameForCustomerType($customerType) {
		$this->dbConnector->where("table_name", $customerType);
		return $this->dbConnector->getValue(CRM_CUSTOMER_TYPES_TABLE_NAME, "description");
	}
	
	/** tasks */

	/**
	 * Gets all tasks belonging to a given user.
	 * @param $userid Int id of the user.
	 * @return Array an array containing all task objects as associative arrays, or NULL if user was not found or an error occurred.
	 */
	public function getCompletedTasks($userid) {
		$this->dbConnector->where("user_id", $userid);
		$this->dbConnector->where("completed", 100);
		$this->dbConnector->orderBy("creation_date", "Desc");
		return $this->dbConnector->get(CRM_TASKS_TABLE_NAME);
	}

	
	/**
	 * Retrieves the number of unfinished tasks.
	 * @param Int $userid returns the number of unfinished tasks of the user.
	 */
	 public function getUnfinishedTasksNumber($userid) {
		$this->dbConnector->where("user_id", $userid);
		$this->dbConnector->where("completed", 100, "<");
		if ($this->dbConnector->get(CRM_TASKS_TABLE_NAME)) {
			return $this->dbConnector->count;
		} else { return 0; }
	 }
	 
	/**
	 * Retrieves the unfinished tasks of a user as an array of tasks objects.
	 * @param Int $userid returns the unfinished tasks of the user.
	 */
	 public function getUnfinishedTasks($userid) {
		$this->dbConnector->where("user_id", $userid);
		$this->dbConnector->where("completed", 100, "<");
		$this->dbConnector->orderBy("creation_date", "Desc");
		return $this->dbConnector->get(CRM_TASKS_TABLE_NAME);
	 }
	 
	/**
	 * Creates a new task for a user.
	 * @param $userid Int id of the user creating the new task.
	 * @param $taskDescription String description of the new task.
	 * @param $taskInitialProgress Int initial completion percentage of the task that has been completed (0-100).
	 * @return boolean true if operation was successful, false otherwise.
	 */
	public function createTask($userid, $taskDescription, $taskInitialProgress = 0) {
		// sanity checks
		if (empty($userid) || empty($taskDescription)) return false;
		else if (empty($taskInitialProgress)) $taskInitialProgress = 0;
		else if ($taskInitialProgress < 0) $taskInitialProgress = 0;
		else if ($taskInitialProgress > 100) $taskInitialProgress = 100;
		
		$data = array(
			"user_id" => $userid, 
			"description" => $taskDescription, 
			"completed" => $taskInitialProgress, 
			"creation_date" => $this->dbConnector->now()
		);
		if ($taskInitialProgress == 100) { $data["completion_date"] = $this->dbConnector->now(); }
		if ($this->dbConnector->insert(CRM_TASKS_TABLE_NAME, $data)) { return true; }
		else { return $false; }
	}
	
	/**
	 * Deletes a task
	 * @param $taskid Int id of the task to be deleted.
	 * @return boolean true if operation was successful, false otherwise.
	 */
	public function deleteTask($taskid) {
	 	// safety check
	 	if (empty($taskid)) return false;
	 	
	 	$this->dbConnector->where("id", $taskid);
	 	return $this->dbConnector->delete(CRM_TASKS_TABLE_NAME);
	}
	
	/**
	 * Sets the completed status of a task.
	 * @param $taskid Int identifier of the task
	 * @param $progress Int new completion status for the task (0-100).
	 * @param $userid Int id of the user the task belongs to.
	 * @return boolean true if modification was successful, false otherwise.
	 */
	public function setTaskCompletionStatus($taskid, $progress, $userid) {
		if (empty($taskid) || empty($progress) || empty($userid)) return false;
		
		$this->dbConnector->where("id", $taskid);
		$this->dbConnector->where("user_id", $userid);
		$data = array("completed" => $progress);
		return $this->dbConnector->update(CRM_TASKS_TABLE_NAME, $data);
	}
	
	/**
	 * Edits the description of the task
	 * @param $taskid Int identifier of the task
	 * @param $description String new progress for the task (0-100).
	 * @param $userid Int id of the user the task belongs to.
	 * @return boolean true if modification was successful, false otherwise.
	 */
	public function editTaskDescription($taskid, $description, $userid) {
		if (empty($taskid) || empty($description) || empty($userid)) return false;
		$this->dbConnector->where("id", $taskid);
		$this->dbConnector->where("user_id", $userid);
		$data = array("description" => $description);
		return $this->dbConnector->update(CRM_TASKS_TABLE_NAME, $data);
	}
	
	
	/** Messages */
	
	/**
	 * Sends a message from one user to another.
	 * @param Int $fromuserid id of the user sending the message.
	 * @param Int $touserid id of the user to send the message to.
	 * @param String $subject subject of the message to send.
	 * @param String $message body of the message to send (text/rich html).
	 * @return boolean true if successful, false otherwise
	 */
	public function sendMessage($fromuserid, $touserid, $subject, $message) {
		// sanity checks
		if (empty($fromuserid) || empty($touserid)) return false;
		if (empty($subject)) $subject = "(".$this->lh->translationFor("no_subject").")";
		if (empty($message)) $message = "(".$this->lh->translationFor("no_message").")";
		
		// insert the new message in the inbox of the receiving user.
		$data = array(
			"user_from" => $fromuserid,
			"user_to" => $touserid,
			"subject" => $subject,
			"message" => $message,
			"date" => $this->dbConnector->now(),
			"message_read" => 0,
			"favorite" => 0
		);
		if (!$this->dbConnector->insert(CRM_MESSAGES_INBOX_TABLE_NAME, $data)) { return false; }
				
		// insert the new message in the outbox of the sending user.
		$data["message_read"] = 1;
		return $this->dbConnector->insert(CRM_MESSAGES_OUTBOX_TABLE_NAME, $data);
	}
	
	/**
	 * Returns the table name associated with a mail folder id.
	 * @param $folder the identifier of the mail folder.
	 * @return the table name associated with a mail folder id.
	 */
	private function getTableNameForFolder($folder) {
		$tableName = NULL;
		if ($folder == MESSAGES_GET_INBOX_MESSAGES) { // all inbox messages.
			$tableName = CRM_MESSAGES_INBOX_TABLE_NAME;
		} else if ($folder == MESSAGES_GET_UNREAD_MESSAGES) { // unread messages.
			$tableName = CRM_MESSAGES_INBOX_TABLE_NAME;
		} else if ($folder == MESSAGES_GET_DELETED_MESSAGES) { // deleted messages.
			$tableName = CRM_MESSAGES_JUNK_TABLE_NAME;
		} else if ($folder == MESSAGES_GET_SENT_MESSAGES) { // sent messages.
			$tableName = CRM_MESSAGES_OUTBOX_TABLE_NAME;
		} else if ($folder == MESSAGES_GET_FAVORITE_MESSAGES) { // favorite inbox messages
			$tableName = CRM_MESSAGES_INBOX_TABLE_NAME;
		}
		return $tableName;
	}
	
	/**
	 * Returns the messages of the user
	 * @param Int $userid id of the user of the messages to retrieve
	 * @param Int $type type of messages to retrieve:
	 * - MESSAGES_GET_INBOX_MESSAGES (0): inbox messages 
	 * - MESSAGES_GET_UNREAD_MESSAGES (1): unread messages 
	 * - MESSAGES_GET_DELETED_MESSAGES (2): deleted messages  
	 * - MESSAGES_GET_SENT_MESSAGES (3): sent messages 	 
	 */
	public function getMessagesOfType($userid, $type) {
		// initial sanity checks
		if (!is_numeric($userid) || !is_numeric($type)) return NULL;
		
		// determine type of messages to get.
		$tableName = $this->getTableNameForFolder($type);
		if (empty($tableName)) { return NULL; }

		if ($type == MESSAGES_GET_INBOX_MESSAGES) { // all inbox messages.
			$this->dbConnector->where("$tableName.user_to", $userid);
			$this->dbConnector->where("$tableName.user_from = ".CRM_USERS_TABLE_NAME.".id");
		} else if ($type == MESSAGES_GET_UNREAD_MESSAGES) { // unread messages.
			$this->dbConnector->where("$tableName.user_to", $userid);
			$this->dbConnector->where("$tableName.user_from = ".CRM_USERS_TABLE_NAME.".id");
			$this->dbConnector->where("$tableName.message_read", 0);
		} else if ($type == MESSAGES_GET_DELETED_MESSAGES) { // deleted messages.
			$this->dbConnector->where("($tableName.user_to = $userid AND $tableName.user_from = users.id) OR ($tableName.user_from = $userid AND $tableName.user_to = users.id)");
		} else if ($type == MESSAGES_GET_SENT_MESSAGES) { // sent messages.
			$this->dbConnector->where("$tableName.user_from", $userid);
			$this->dbConnector->where("$tableName.user_to = users.id");
		} else if ($type == MESSAGES_GET_FAVORITE_MESSAGES) { // favorite inbox messages
			$this->dbConnector->where("$tableName.user_to", $userid);
			$this->dbConnector->where("$tableName.user_from = users.id");
			$this->dbConnector->where("$tableName.favorite", 1);
		} else { return NULL; }
		
		// return the messages.
		$cols = array("$tableName.id", "$tableName.user_from", "$tableName.user_to", "$tableName.subject", "$tableName.message", "$tableName.date", "$tableName.message_read", "$tableName.favorite", "users.name as remote_user", "users.avatar as remote_avatar");
		return $this->dbConnector->get("$tableName, users", null, $cols);
	}
	
	/**
	 * Gets a specific message from one folder, taking into account the sender and receiver of the message.
	 */
	public function getSpecificMessage($userid, $messageid, $folder) {
		// sanity checks.
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL || $userid == NULL || $messageid == NULL) { return NULL; }

		// determine to/from of the message.
		$remoteuseridfield = "user_from";
		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) {
			 $useridfield = "user_from";
			 $remoteuseridfield = "user_to";
		}
		
		// calculate query message.
		if ($folder == MESSAGES_GET_DELETED_MESSAGES) {
			$params = arrat($messageid, $userid, $userid);		
			$query = "SELECT * FROM $tableName m, users u WHERE m.id = ? AND ((m.$useridfield = ? AND m.$useridfield = u.id) OR (m.$remoteuseridfield = ? AND m.$useridfield = u.id))";
		} else {
			$params = array($userid, $messageid);
			$query = "SELECT * FROM $tableName m, users u WHERE m.$useridfield = ? AND m.$remoteuseridfield = u.id AND m.id = ?";
				
		}
		// execute the query
		if ($result = $this->dbConnector->rawQuery($query, $params)) { return $result[0]; }
		else { return NULL; }
	}
	
	/**
	 * Returns the number of unread messages for a user.
	 * @param Int $userid id of the user to get the unread messages from.
	 */
	 public function getUnreadMessagesNumber($userid) {
		 if (empty($userid)) return 0;
		 // prepare query.
		 $this->dbConnector->where("user_to", $userid);
		 $this->dbConnector->where("message_read", "0");
		 return $this->dbConnector->getValue(CRM_MESSAGES_INBOX_TABLE_NAME, "count(*)");
	 }
	 
	/**
	 * Marks a set of messages as read.
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function markMessagesAsRead($userid, $messageids, $folder) {
		// sanity checks
		if (!is_numeric($userid)) return false;
		if (!is_numeric($folder)) return false;
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;
		
		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) $useridfield = "user_from";
		
		$this->dbConnector->where($useridfield, $userid);
		$this->dbConnector->where("id IN (".implode(',',$messageids).")");
		$data = array("message_read" => "1");
		return $this->dbConnector->update($tableName, $data);
	}
		 
	/**
	 * Marks a set messages as unread.
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function markMessagesAsUnread($userid, $messageids, $folder) {
		// sanity checks
		if (!is_numeric($userid)) return false;
		if (!is_numeric($folder)) return false;
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;

		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) $useridfield = "user_from";

		$this->dbConnector->where($useridfield, $userid);
		$this->dbConnector->where("id IN (".implode(',',$messageids).")");
		$data = array("message_read" => "0");
		return $this->dbConnector->update($tableName, $data);
	}

	/**
	 * Marks a set of messages as favorites or un-favorites.
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function markMessagesAsFavorite($userid, $messageids, $folder, $favorite) {
		// sanity check
		if (!is_numeric($userid)) return false;
		if (!is_numeric($folder)) return false;
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL) return false;
		if ($favorite < 0 || $favorite > 1) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;

		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) $useridfield = "user_from";
		
		// return result of update 
		$this->dbConnector->where($useridfield, $userid);
		$this->dbConnector->where("id IN (".implode(',',$messageids).")");
		$data = array("favorite" => $favorite);
		return $this->dbConnector->update($tableName, $data);
	}

	/**
	 * Deletes a set of messages permanently
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function deleteMessages($userid, $messageids, $folder) {
		// sanity check
		if (!is_numeric($userid)) return false;
		if (!is_numeric($folder)) return false;
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;

		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) $useridfield = "user_from";

		$this->dbConnector->where($useridfield, $userid);
		$this->dbConnector->where("id IN (".implode(',',$messageids).")");
		return $this->dbConnector->delete($tableName);
	}

	/**
	 * Moves a set of messages to the junk folder
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function junkMessages($userid, $messageids, $folder) {
		// sanity check
		if (!is_numeric($userid)) return false;
		if (!is_numeric($folder)) return false;
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;

		// initial values
		$messagesToJunk = count($messageids);
		$messagesJunked = 0;
		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) $useridfield = "user_from";
		
		foreach ($messageids as $messageid) {
			// get the data from the old messages box first
			$this->dbConnector->where($useridfield, $userid);
			$this->dbConnector->where("id", $messageid);
			$oldData = $this->dbConnector->getOne($tableName, array("user_from", "user_to", "subject", "message", "date", "message_read", "favorite"));
			if ($oldData) {
				// add origin folder
				$oldData["origin_folder"] = $tableName;
				// insert old data in messages_junk
				$newJunkId = $this->dbConnector->insert(CRM_MESSAGES_JUNK_TABLE_NAME, $oldData);
				if ($newJunkId) {
					$this->dbConnector->where($useridfield, $userid);
					$this->dbConnector->where("id", $messageid);
					if ($deleteOriginal = $this->dbConnector->delete($tableName)) {
					$messagesJunked = $messagesJunked + 1;
					}

				}
			}
		}
		
		return $messagesJunked;
	}

	/**
	 * Gets a set of messages out of the jumk folder back to their original folder.
	 * @param $userid Int the id of the user the messages belong to.
	 * @param $messageids Array a set of Int values containing the ids of the messages.
	 * @param $folder Int folder id the messages belong to.
	 * @return true if operation was successful, false otherwise.
	 */
	public function unjunkMessages($userid, $messageids) {
		// sanity check
		if (!is_numeric($userid)) return false;
		if (!$this->array_contains_only_numeric_values($messageids)) return false;

		// initial values
		$messagesToUnjunk = count($messageids);
		$messagesUnjunked = 0;
		$useridfield = "user_to";
		
		foreach ($messageids as $messageid) {
			$this->dbConnector->where("id", $messageid);
			$junkedObj = $this->dbConnector->getOne(CRM_MESSAGES_JUNK_TABLE_NAME, array("user_from", "user_to", "subject", "message", "date", "message_read", "favorite", "origin_folder"));
			if ($junkedObj) {
				$tableName = $junkedObj["origin_folder"];
				unset($junkedObj["origin_folder"]); // origin_folder doesn't exist in $tableName to insert, so we remove it.
				if (!empty($tableName)) {
					if ($this->dbConnector->insert($tableName, $junkedObj)) { // insert into origin_folder succeed!
						// now try to delete the message from the junk folder.
						$this->dbConnector->where("id", $messageid);
						if ($this->dbConnector->delete(CRM_MESSAGES_JUNK_TABLE_NAME)) { $messagesUnjunked = $messagesUnjunked + 1; }
					}
				}
			}
		}
		
		return $messagesUnjunked;	
	}

	/** Notificaciones */
	
	/**
	 * Gets the number of notifications for today for the user.
	 * @param $userid Int the identifier for the user.
	 * @return Int the number of notifications. 
	 */
	public function getNumberOfTodayNotifications($userid) {
		$this->dbConnector->where("DATE(date) = CURDATE() AND (target_user = 0 OR target_user = ?)", array($userid));
		return $this->dbConnector->getValue(CRM_NOTIFICATIONS_TABLE_NAME, "count(*)");
	}
	
	/**
	 * Gets the notifications for today for the user as an array.
	 * @param $userid Int the identifier for the user.
	 * @return Array the notifications as an associative array. 
	 */
	public function getTodayNotifications($userid) {
		// prepare query
		if (empty($userid)) return NULL;
		$this->dbConnector->where("DATE(date) = CURDATE() AND (target_user = 0 OR target_user = ?)", array($userid));
		return $this->dbConnector->get(CRM_NOTIFICATIONS_TABLE_NAME);
	}
	
	/**
	 * Get notifications for past week for the user.
	 * @param $userid Int the identifier for the user.
	 * @return Array the notifications as an associative array. 
	 */
	public function getNotificationsForPastWeek($userid) {
		// prepare query
		if (empty($userid)) return NULL;
		$this->dbConnector->where("(DATE(date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY) AND (target_user = 0 OR target_user = ?)", array($userid));
		return $this->dbConnector->get(CRM_NOTIFICATIONS_TABLE_NAME);
	}

	/** Statistics */
	
	/**
	 * Inserts a new entry in the statistics table with the current number of customers in every table.
	 * @return boolean true if the operation was successful, false otherwise.
	 */
	public function generateStatisticsForToday() {
		// get customer tables
		$customerTypes = $this->getCustomerTypes();
		if (empty($customerTypes)) return true;

		// build the query by adding customer types
		$data = array("date" => $this->dbConnector->now());
		foreach ($customerTypes as $customerType) {
			$numCustomers = $this->getNumberOfClientsFromTable($customerType["table_name"]);
			$customerKey = $customerType["table_name"];
			$data[$customerKey] = $numCustomers;
		}
		return $this->dbConnector->insert(CRM_STATISTICS_TABLE_NAME, $data);
	}

	/**
	 * Gets the number of customers of a given customerType (= tablename).
	 * @param $tableName String the table of customers to get the count from.
	 * @return the number (count(*)) of entries in the given customer table.
	 */
	public function getNumberOfClientsFromTable($tableName) {
		if (empty($tableName)) return 0;
		$tableName = $this->escape_string($tableName);
		return $this->dbConnector->getValue($tableName, "count(*)");
	}

	/**
	 * Gets the number of new contacts (last week).
	 * @return the number of contact entries that were created in the last week.
	 */
	public function getNumberOfNewContacts() {
		$this->dbConnector->where("DATE(creation_date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()");
		return $this->dbConnector->getValue(CRM_CONTACTS_TABLE_NAME, "count(*)");
	}
	
	/**
	 * Gets the number of new customers (last week), not including contacts.
	 * @return the number of customer entries that were created in the last week from all customer tables but not including contacts.
	 */
	public function getNumberOfNewCustomers() {
		$customerTypes = $this->getCustomerTypes();
		if (empty($customerTypes)) return 0;
		
		$numClients = 0;
		foreach ($customerTypes as $customerType) {
			if ($customerType["table_name"] == CRM_CONTACTS_TABLE_NAME) continue;
			$this->dbConnector->where("DATE(creation_date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()");
			$numClients += $this->dbConnector->getValue($customerType["table_name"], "count(*)");
		}
		return $numClients;
	}
	
	/**
	 * Gets the last $limit (default 10) customer statistics.
	 * @param $limit Int (default = 10) the number of statistics to retrieve, in descending order, ordered by timestamp.
	 * 
	 */	
	public function getLastCustomerStatistics($limit = 10) {
		$this->dbConnector->orderBy("timestamp", "Desc");
		return $this->dbConnector->get(CRM_STATISTICS_TABLE_NAME, $limit);
	}
	
	/** Modules */
	
	/**
	 * Retrieves the list of active modules.
	 * @return Array an array with the list of active modules.
	 */
	public function getActiveModules() {
		$this->dbConnector->where("setting", CRM_SETTING_ACTIVE_MODULES);
		$modulesRow = $this->dbConnector->getOne(CRM_SETTINGS_TABLE_NAME);
		if (is_string($modulesRow["value"]) && !empty($modulesRow["value"])) { return explode(",", $modulesRow["value"]); } 
		return array();
	}
	
	/**
	 * Sets the list of active modules.
	 * @param Array $modules an array containing the short names of the modules to enable.
	 * @return Bool true if successful, false otherwise.
	 */
	public function setActiveModules($modules) {
		if (is_array($modules)) {
			// generate module string.
			$modulesString = implode(",", $modules);
			$moduleData = array("value" => $modulesString);
			// update settings.
			$this->dbConnector->where("setting", CRM_SETTING_ACTIVE_MODULES);
			return $this->dbConnector->update(CRM_SETTINGS_TABLE_NAME, $moduleData);
		} else { return false; }
	}
	
	/** 
	 * Returns true if the module system is enabled. 
	 * @return true if the module system is enabled. False otherwise.
	 */
	public function moduleSystemEnabled() {
		$this->dbConnector->where("setting", CRM_SETTING_MODULE_SYSTEM_ENABLED);
		return $this->dbConnector->getOne(CRM_SETTINGS_TABLE_NAME);
	}
	
	/**
	 * Modify the status (enabled/disabled) of a module.
	 * @param String $moduleName the name of the module to enable/disable.
	 * @param String/Bool $status 1/true if module should be enabled, 0/false otherwise.
	 * @return Bool true if active modules changed, false otherwise.
	 */
	public function changeModuleStatus($moduleName, $status) {
		$modules = $this->getActiveModules();
		$modulesChanged = false;
		// check status
		if ($status == "1" || $status == true) {
			if (!in_array($moduleName, $modules, true)) { $modules[] = $moduleName; $modulesChanged = true; }
		} else if ($status == "0" || $status == false) {
			if ( ($key = array_search($moduleName, $modules)) !== false) { unset($modules[$key]); $modulesChanged = true; } 
		}
		
		// change status and return success.
		if ($modulesChanged) {
			return $this->setActiveModules($modules);
		}
		return false;
	}
	
	/** Utility functions */
	
	/**
	 * Escapes a string for a safer inclusion in a MySQL statement. Please note that this method alone is not enough for preventing SQL injections.
	 * @param $string String the string to be escaped.
	 * @return String the string escaped with a call to mysqli::real_escape_string();
	 */
	public function escape_string($string) {
		return $this->dbConnector->escape($string);
	}
	
	/**
	 * Returns the number of affected/selected rows from the last query.
	 */
	public function rowCount() { return $this->dbConnector->count; }
	
	/**
	 * Checks if a given array only contains numeric values.
	 * @param $array ? (supposed to be an array) input parameter, to check if its an array with only numeric values.
	 * @return boolean true if and only if $array is an array which contains only numeric values (those whose call to is_numeric returns true).
	 */
	private function array_contains_only_numeric_values($array) {
		if (!is_array($array)) return false;
		foreach ($array as $element) {
			if (!is_numeric($element)) return false;
		}
		return true;
	}
	
}

?>
