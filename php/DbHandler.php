<?php

require_once('CRMDefaults.php');
require_once('PassHash.php');
require_once('ImageHandler.php');
require_once('RandomStringGenerator.php');

/**
 * Class to handle all db operations
 * This class is in charge of managing the database operations for Creamy. All DB managing should be done by calling this class
 * and performing operations. i.e:
 *
 * $db = new DbHandler();
 * $success = $db->deleteUser(123);
 *
 * @author Ignacio Nieto Carvajal
 * @link URL http://digitalleaves.com
 */
class DbHandler {

    private $conn;
    
    /* -------------- Variables, predefined text and code ----------------- */
    
	private $contactsTablePrefix = "<table id=\"contacts\" class=\"table table-bordered table-striped\">
	<thead>
		<tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>NIF / Pasaporte</th>
        </tr>
    </thead>
    <tbody>";
	private $contactsTableSuffix = "</tbody>
	<tfoot>
            <tr>
                <th>Id</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
				<th>NIF / Pasaporte</th>
            </tr>
        </tfoot>
    </table>";
	private $usersTablePrefix = "<table id=\"contacts\" class=\"table table-bordered table-striped\">
	<thead>
		<tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Fecha alta</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>";
	private $usersTableSuffix = "</tbody>
	<tfoot>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Fecha alta</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
        </tfoot>
    </table>";
    private $taskTablePrefix = "<table class=\"table table-condensed\"><thead><tr><th style=\"width: 10px\">#</th><th>Tarea</th><th>Progreso</th><th>Creada</th><th>Acción</th></tr></thead>";
    private $messageListPrefix = '<table class="table mailbox table-responsive" id="messagestable" name="messagestable"><thead><tr><td>Selección</td><td>Favorito</td><td>Usuario</td><td>subject</td><td>Fecha</td></tr></thead>';
    
        
    /* ---------------- Initializers -------------------- */
    
    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
   		ini_set( 'date.timezone', CRM_TIMEZONE);
		date_default_timezone_set(CRM_TIMEZONE);
    }
    
    /** -------------- Administración, usuarios ------------- */
    

    /**
     * Creating new user
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $password, $email, $phone, $role, $avatarURL) {
        $response = array();

        // First check if user already existed in db
        if (!$this->userAlreadyExists($name)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
            if (empty($avatarURL)) $avatarURL = CRM_DEFAULTS_USER_AVATAR;

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users (name, password_hash, email, phone, role, avatar, creation_date, status) values(?, ?, ?, ?, ?, ?, now(), 1)");
            $stmt->bind_param("ssssis", $name, $password_hash, $email, $phone, $role, $avatarURL);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
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
		if (!empty($avatar)) { // If we are modifying the user's avatar, make sure to delete the old one.
			$userdata = $this->getDataForUser($modifyid);
			$ih = new ImageHandler();
			$ih->removeUserAvatar($userdata["avatar"]);
			$stmt = $this->conn->prepare("UPDATE users set email = ?, phone = ?, avatar = ?, role = ? WHERE id = ?");
			$stmt->bind_param("sssii", $email, $phone, $avatar, $role, $modifyid);
		} else { // no avatar change required, just update the values.
	        $stmt = $this->conn->prepare("UPDATE users set email = ?, phone = ?, role = ? WHERE id = ?");
	        $stmt->bind_param("ssii", $email, $phone, $role, $modifyid);
		}
		
        // execute modification query
        $result = $stmt->execute();
        $stmt->close();

        // return true upon successful insertion
        return $result;
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
		 	$ih = new ImageHandler();
		 	$ih->removeUserAvatar($data["avatar"]);
	 	}
	 	// then remove the entry at the database
	 	$stmt = $this->conn->prepare("DELETE FROM users where id = ?");
	 	$stmt->bind_param("i", $userid);
	 	$result = $stmt->execut();
	 	$stmt->close();
        return $result;
	 }

    /**
     * Checking user login
     * @param String $name User login name
     * @param String $password User login password
     * @return object an associative array containing the user's data if credentials are valid and login succeed, NULL otherwise.
     */
    public function checkLogin($name, $password) {
        // fetching user by name and password
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        if ($stmt->execute() === false) return NULL;
        
        // check result and build response
        $result = $stmt->get_result();        
		if ($userobj = $result->fetch_assoc()) { // get first match.
			$password_hash = $userobj["password_hash"];
			$status = $userobj["status"];
			$result->close();
			if ($status == 1) { // user is active
				if (PassHash::check_password($password_hash, $password)) {
	                // User password is correct
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
			$result->close();
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
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
		$stmt->bind_param("i", $userid);
		if ($stmt->execute() === false) return false;
		
		// check if password change is valid
		$result = $stmt->get_result();
		if ($userobj = $result->fetch_assoc()) {
			$password_hash = $userobj["password_hash"];
			$status = $userobj["status"];
			$result->close();
			if ($status == 1) { // user is active, check old password.
				if (PassHash::check_password($password_hash, $oldpassword)) {
	                // oldpassword is correct, change password.
	                $newPasswordHash = PassHash::hash($password1);
	                $updateStmt = $this->conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
	                $updateStmt->bind_param("si", $newPasswordHash, $userid);
					$modifyResult = $updateStmt->execute();
					$updateStmt->close();
	                return $modifyResult;
	            } else {
	                // oldpassword is incorrect
	                return false;
	            }
			} else return false;
		} else {
			$result->close();
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
		$newPasswordHash = PassHash::hash($password);
		
		$stmt = $this->conn->prepare("UPDATE users SET password_hash = ? WHERE id = ? ");
		$stmt->bind_param("si", $newPasswordHash, $userid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
	}
    
    /**
     * Gets the data of a user.
     * @param String $userid id of the user to get data from.
     * @return object an associative array containing the user's relevant data if the user id valid, NULL otherwise.
     */
    public function getDataForUser($userid) {
	    $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
	    $stmt->bind_param("i", $userid);
		if ($stmt->execute() === false) return NULL;
		$result = $stmt->get_result();
		
		// extract relevant user's data.
		if ($obj = $result->fetch_assoc()) {
			$userobj = array();
			$userobj["name"] = $obj["name"];
			$userobj["email"] = $obj["email"];
			$userobj["phone"] = $obj["phone"];
			$userobj["role"] = $obj["role"];
			$userobj["avatar"] = $obj["avatar"];
	        $userobj["creation_date"] = $obj["creation_date"];
			
			$stmt->close();
			return $userobj;
		} else return NULL;
    }
    
    
    
    /**
     * Checking for duplicate user by name
     * @param String $name name to check in db
     * @return boolean
     */
    private function userAlreadyExists($name) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Checking for existing email for a user in the database
     * @param String $email email to check in db
     * @return boolean true if operation succeed.
     */
    private function userEmailAlreadyExists($name) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE email = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Returns an array containing all the user's in the system (only relevant data).
     * @return Array an array of objects containing the data of all users in the system.
     */
   	public function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT * FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result == NULL) {
			return NULL;
		} else {
			$response = array();
	        // looping through result and preparing channels array
	        while ($contact = $result->fetch_assoc()) {
	            $tmp = array();
	            $tmp["id"] = $contact["id"];
	            $tmp["name"] = $contact["name"];
	            $tmp["email"] = $contact["email"];
	            $tmp["phone"] = $contact["phone"];
	            $tmp["creation_date"] = $contact["creation_date"];
	            $tmp["role"] = $contact["role"];
	            $tmp["status"] = $contact["status"];
	            
	            array_push($response, $tmp);
	        }
			return $response;
		}
	}

    /**
     * Returns a HTML Table representation containing all the user's in the system (only relevant data).
     * @return String a HTML Table representation of the data of all users in the system.
     */
	public function getAllUsersAsTable() {
       $users = $this->getAllUsers();
       // is null?
       if (is_null($users)) { // error getting contacts
	       return $this->getErrorMessage("¡Vaya! Fue imposible obtener lista de usuarios. Por favor, inténtalo de nuevo más tarde. Si el problema persiste, contacta con el administrador.");
       } else if (empty($users)) { // no contacts found
	       return $this->getWarningMessage("¡Vaya! Parece que la lista de usuarios está vacía. ¿Por qué no creas uno nuevo?");
       } else { 
	       // we have some users, show a table
		   $result = $this->usersTablePrefix;
	       
	       // iterate through all contacts
	       foreach ($users as $userData) {
	       	   $status = $userData["status"] == 1 ? "Activo" : "Deshabilitado";
	       	   $userRole = $this->getRoleNameForRole($userData["role"]);	
	       	   $action = $this->getUserActionMenuForUser($userData["id"], $userData["name"], $userData["status"]);       
		       $result = $result."<tr>
	                    <td>".$userData["id"]."</td>
	                    <td><a class=\"edit-action\" href=\"".$userData["id"]."\">".$userData["name"]."</a></td>
	                    <td>".$userData["email"]."</td>
	                    <td>".$userData["creation_date"]."</td>
	                    <td>".$userRole."</td>
	                    <td>".$status."</td>
	                    <td>".$action."</td>
	                </tr>";
	       }
	       
	       // print suffix
	       $result = $result.$this->usersTableSuffix; 
	       return $result; 
       }
	}
	
	/**
	 * Retrieves the human friendly descriptive name for a role given its identifier number.
	 * @param $roleNumber Int number/identifier of the role.
	 * @return Human friendly descriptive name for the role.
	 */
	private function getRoleNameForRole($roleNumber) {
		switch ($roleNumber) {
			case CRM_DEFAULTS_USER_ROLE_ADMIN:
				return "administrator";
				break;
			case CRM_DEFAULTS_USER_ROLE_MANAGER:
				return "manager";
				break;
			case CRM_DEFAULTS_USER_ROLE_WRITER:
				return "writer";
				break;
			case CRM_DEFAULTS_USER_ROLE_READER:
				return "reader";
				break;
			case CRM_DEFAULTS_USER_ROLE_GUEST:
				return "guest";		
				break;
		}
	}

	/**
	 * Generates the HTML code for a select with the human friendly descriptive names for the user roles.
	 * @return String the HTML code for a select with the human friendly descriptive names for the user roles.
	 */
	public function getUserRolesAsFormSelect($selectedOption = CRM_DEFAULTS_USER_ROLE_MANAGER) {
		$selectedAdmin = $selectedOption == CRM_DEFAULTS_USER_ROLE_ADMIN ? " selected" : "";
		$selectedManager = $selectedOption == CRM_DEFAULTS_USER_ROLE_MANAGER ? " selected" : "";
		$selectedWriter = $selectedOption == CRM_DEFAULTS_USER_ROLE_WRITER ? " selected" : "";
		$selectedReader = $selectedOption == CRM_DEFAULTS_USER_ROLE_READER ? " selected" : "";
		$selectedGuest = $selectedOption == CRM_DEFAULTS_USER_ROLE_GUEST ? " selected" : "";
		
		return '<select id="role" name="role">
				   <option value="'.CRM_DEFAULTS_USER_ROLE_ADMIN.'"'.$selectedAdmin.'>'.$this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_ADMIN).'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_MANAGER.'"'.$selectedManager.'>'.$this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_MANAGER).'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_WRITER.'"'.$selectedWriter.'>'.$this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_WRITER).'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_READER.'"'.$selectedReader.'>'.$this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_READER).'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_GUEST.'"'.$selectedGuest.'>'.$this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_GUEST).'</option>				   
			    </select>';
	}

    /**
     * Returns a HTML representation of the action associated with a user in the admin panel.
     * @param $userid Int the id of the user
     * @param $username String the name of the user
     * @param $status Int the status of the user (enabled=1, disabled=0)
     * @return String a HTML representation of the action associated with a user in the admin panel.
     */
	private function getUserActionMenuForUser($userid, $username, $status) {
		$textForStatus = $status == 1 ? "Deshabilitar" : "Habilitar";
		$actionForStatus = $status == 1 ? "deactivate-user-action" : "activate-user-action";
		return '<div class="btn-group">
	                <button type="button" class="btn btn-danger dropdown-toggle"  data-toggle="dropdown">Elige una acción para '.$username.'</button>
	                <ul class="dropdown-menu" role="menu">
	                    <li><a class="edit-action" href="'.$userid.'">Editar datos</a></li>
	                    <li><a class="change-password-action" href="'.$userid.'">Cambiar contraseña</a></li>
	                    <li><a class="'.$actionForStatus.'" href="'.$userid.'">'.$textForStatus.'</a></li>
	                    <li class="divider"></li>
	                    <li><a class="delete-action" href="'.$userid.'">Eliminar usuario</a></li>
	                </ul>
	            </div>';
	}

	/**
	 * Changes the status for a user, from enabled (=1) to disabled (=0) or viceversa.
     * @param $userid Int the id of the user
     * @param $status Int the new status for the user
	 */
	public function setStatusOfUser($userid, $status) {
		$stmt = $this->conn->prepare("UPDATE users SET status = ? WHERE id = ?");
		$stmt->bind_param("ii", $status, $userid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
	}
	
	/* ------------------ Password recovery ------------------ */

	/** 
	 * Sends a recovery mail to the user. The user must have a valid email contained in the database.
	 * @param $email string string of the user.
	 * @return true if successful, false if email couldn't be sent.
	 */
	public function sendPasswordRecoveryEmail($email) {
		if ($this->userEmailAlreadyExists($email)) {
			$randomStringGenerator = new RandomStringGenerator();
			$nonce = $randomStringGenerator->generate(40);
			$dateAsString = date('Y-m-d-H-i-s');
			$htmlContent = file_get_contents(CRM_RECOVERY_EMAIL_FILE);
			if ($htmlContent !== false) {
				$subject = "Password reset link for your Creamy account.";
				$headers = "From: hello@creamycrm.com\r\n";
				$headers .= "Reply-To: hello@creamycrm.com\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
				
				$resetCode = $this->generatePasswordResetCode($email, $dateAsString);
				$htmlContent = str_replace("{email}", $email, $htmlContent);
				$htmlContent = str_replace("{date}", $dateAsString, $htmlContent);
				$htmlContent = str_replace("{host}", $_SERVER['SERVER_NAME'], $htmlContent);
				$htmlContent = str_replace("{code}", $resetCode, $htmlContent);
				$htmlContent = str_replace("{nonce}", $nonce, $htmlContent);
				return mail($email, $subject, $htmlContent, $headers);
			}
		}
		return false;
	}
	
	/** Generates a password reset code, a md5($email + $date + $nonce + CRM_SECURITY_TOKEN) */
	private function generatePasswordResetCode($email, $date, $nonce) {
		$baseString = $email.$date.$nonce.CRM_SECURITY_TOKEN;
		return md5($baseString);
	}
		
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

	/** 
	 * Changes the password of a user identified by an email. The user must have a valid email in the database.
	 * @param $email String the email of the user.
	 * @param $password the new password for the user.
	 */
	public function changePasswordForUserIdentifiedByEmail($email, $password) {
		if ($this->userExists($email)) {
	        // Generating password hash
	        $password_hash = PassHash::hash($password);
			return $this->conn->query("UPDATE users SET password_hash = '$password_hash' WHERE email = '$email'");
		}
		return false;
	}

	/* -------------- Warnings and messages --------------------- */
	
	/**
	 * Generates a info message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getInfoMessage($message) {
		return "<div class=\"callout callout-info\">\n\t<h4>message</h4>\n\t<p>$message</p>\n</div>\n";	
	}

	/**
	 * Generates a warning message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getWarningMessage($message) {
		return "<div class=\"callout callout-warning\">\n\t<h4>¡Atención!</h4>\n\t<p>$message</p>\n</div>\n";	
	}

	/**
	 * Generates a error message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getErrorMessage($message) {
		return "<div class=\"callout callout-danger\">\n\t<h4>¡Error!</h4>\n\t<p>$message</p>\n</div>\n";	
	}
	
	/**
	 * Generates a error modal message HTML dialog, with the given message.
	 * @param message String the message to show.
	 */
	function getErrorModalMessage($message) {
		$result = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Error recuperando message</h4>
		            </div><div class="modal-body">';
		$result = $result.$this->getErrorMessage($message);
		$result = $result.'</div><div class="modal-footer clearfix"><button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Salir</button></div></div></div>';
		return $result;
	}

	/* -------------- Notifications ------------------------ */

	/**
	 * Generates the HTML for the message notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	function getMessageNotifications($userid) {
        $list = $this->getMessagesOfType($userid, MESSAGES_GET_UNREAD_MESSAGES);
		$numMessages = count($list);
		
		$result = '<!-- Messages: style can be found in dropdown.less-->
            <li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-envelope"></i>
                    <span class="label label-success">'.$numMessages.'</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">Tienes '.$numMessages.' messages sin leer</li>
                    <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">';
        
        foreach ($list as $message) {
	        if (empty($message["remote_avatar"])) $remoteavatar = CRM_DEFAULTS_USER_AVATAR;
	        else $remoteavatar = $message["remote_avatar"];
	        $relativeTime = $this->relativeTime($message["date"], 1);
	        $shortText = $this->substringUpTo($message["message"], 40);
	        
	        $result = $result.'
	        <li><!-- start message -->
                <a href="messages.php">
                    <div class="pull-left">
                        <img src="'.$remoteavatar.'" class="img-circle" alt="User Image"/>
                    </div>
                    <h4>
                        '.$message["remote_user"].'
                        <small><i class="fa fa-clock-o"></i> '.$relativeTime.'</small>
                    </h4>
                    <p>'.$shortText.'</p>
                </a>
            </li><!-- end message -->';
        }
        $result = $result.'</ul></li><li class="footer"><a href="messages.php">Ver todos los messages</a></li></ul></li>';
        print $result;
	}
	
	/**
	 * Returns a random color for a notification, between green, red, blue and yellow.
	 */
	private function getRandomColorForNotification() {
		$number = rand(1,4);
		if ($number == 1) return "info";
		else if ($number == 2) return "danger";
		else if ($number == 3) return "warning";
		else return "success";
	}

	/**
	 * Generates the HTML for the alert notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	public function getAlertNotifications($userid) {
		$notifications = $this->getTodayNotifications($userid);
		if (empty($notifications)) $notificationNum = 0;
		else $notificationNum = count($notifications);
		
		$result = '<!-- Notifications: style can be found in dropdown.less -->
            <li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-warning"></i>
                    <span class="label label-warning">'.$notificationNum.'</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">Tienes '.$notificationNum.' notificaciones</li><li>
                        <!-- inner menu: contains the actual data -->
                        <ul class="menu">';
                        
        foreach ($notifications as $notification) {
	        $result = $result.'<li style="text-align: left; !important">
                                <a href="notifications.php">
                                     <i class="fa '.$this->notificationIconForNotificationType($notification["type"]).' '.$this->getRandomColorForNotification().'"></i> '.$this->substringUpTo($notification["texto"], 40).'
                                </a>
                            </li>';
        }                                        
        $result = $result.'</ul></li><li class="footer"><a href="notifications.php">Ver todas</a></li></ul></li>';
        return $result;
	}
	
	public function getTaskNotifications($userid) {
		$list = $this->getUnfinishedTasks($userid);
		$numTasks = count($list);
		
		$result = '<!-- Tasks: style can be found in dropdown.less -->
                        <li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-tasks"></i>
                                <span class="label label-danger">'.$numTasks.'</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">Tienes '.$numTasks.' tareas pendientes</li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">';
                                    
        foreach ($list as $task) {
	        $color = $this->getTaskColorForCompletion($task["completed"]);
	        $shortText = $this->substringUpTo($task["description"], 40);
	        $result = $result.'<li><!-- Task item -->
	            <a href="tasks.php">
	                <h3>'.$shortText.'</h3>
	                <div class="progress xs">
	                    <div class="progress-bar progress-bar-'.$color.'" style="width: '.$task["completed"].'%" role="progressbar" aria-valuenow="'.$task["completed"].'" aria-valuemin="0" aria-valuemax="100">
	                        <span class="sr-only">'.$task["completed"].'% Completado</span>
	                    </div>
	                </div>
	            </a>
	        </li><!-- end task item -->';
        }
                                    
        $result = $result.'</ul></li><li class="footer"><a href="tasks.php">Ver todas las tareas</a></li></ul></li>';
        return $result;

        return '';
    }

	/* -------------- User Account ------------------------- */
	/**
	 * Generates the HTML for the user's personal menu as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	public function getUserMenu($userid, $username, $avatar) {		
		print '<!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span>'.$username.' <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="'.$avatar.'" class="img-circle" alt="User Image" />
                                    <p>
                                        '.$username.'
                                        <small>Es un placer verte de nuevo.</small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
									<div class="text-center">
									    <a href="" data-toggle="modal" data-target="#change-password-dialog-modal">Cambiar mi contraseña</a>
									</div>
									<div class="text-center">
									    <a href="./messages.php">Mensajes</a>
									</div>
									<div class="text-center">
									        <a href="./notificationes.php">Notificaciones</a>
									    </div>
									<div class="text-center">
									        <a href="./tasks.php">Tareas</a>
								    </div>
								</li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="./edituser.php" class="btn btn-default btn-flat">Mis Datos</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="./logout.php" class="btn btn-default btn-flat">Salir</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
		';
	}

	/* --------------- Sidebar ----------------------------- */
	
	/**
	 * Generates the HTML for the sidebar of a user, given its role.
	 * @param $userid the id of the user.
	 */
	public function getSidebar($userid, $username, $userrole, $avatar) {
		$numMessages = $this->getUnreadMessagesNumber($userid);
		$numTasks = $this->getUnfinishedTasksNumber($userid);
		$numNotifications = $this->getNumberOfTodayNotifications($userid);
		
		$adminArea = "";
		if ($userrole == CRM_DEFAULTS_USER_ROLE_ADMIN) {
			$adminArea = '<li>
                            <a href="./admin.php">
                                <i class="fa fa-dashboard"></i> <span>Administración</span>
                            </a>
                        </li> ';
		}
		
		// get customer types
		$customerTypes = $this->getCustomerTypes();
		
		// prefix: structure and home link
		print '<!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <a href="edituser.php"><img src="'.$avatar.'" class="img-circle" alt="User Image" /></a>
                        </div>
                        <div class="pull-left info">
                            <p>Hola, '.$username.'</p>
                            <a href="edituser.php"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li>
                            <a href="./index.php">
                                <i class="fa fa-bar-chart-o"></i> <span>Inicio</span>
                            </a>
                        </li>';
        
        // include a link for every customer type
        foreach ($customerTypes as $customerType) {
	        if (isset($customerType["table_name"]) && isset($customerType["description"])) {
		        $customerTableName = $customerType["table_name"];
		        $customerFriendlyName = $customerType["description"];
		        print '<li>
                            <a href="./customerslist.php?customer_type='.$customerTableName.'&customer_name='.$customerFriendlyName.'">
                                <i class="fa fa-users"></i> <span>'.$customerFriendlyName.'</span> 
                            </a>
                       </li>
		        ';
	        }
        }

        // suffix: messages, notifications, tasks
		print '<li>
                            <a href="./messages.php">
                                <i class="fa fa-envelope"></i> <span>messages</span>
                                <small class="badge pull-right bg-green">'.$numMessages.'</small>
                            </a>
                        </li>
						<li>
                            <a href="./notifications.php">
                                <i class="fa fa-exclamation"></i> <span>Notificaciones</span>
                                <small class="badge pull-right bg-orange">'.$numNotifications.'</small>
                            </a>
                        </li>
						<li>
                            <a href="./tasks.php">
                                <i class="fa fa-tasks"></i> <span>Tareas</span>
                                <small class="badge pull-right bg-red">'.$numTasks.'</small>
                            </a>
                        </li>
                        '.$adminArea.'
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
		';
	}


	/* -------------- Customers ---------------------------- */
	
	/**
	 * Gets all customers of certain type.
	 * @param $customerType the type of customer to retrieve.
	 * @return Array an array containing the objects with the users' data.
	 */
	public function getAllCustomersOfType($customerType) {
		if (!isset($customerType)) return array();
        $stmt = $this->conn->prepare("SELECT * FROM $customerType");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result == NULL) {
			return NULL;
		} else {
			$response = array();
	        // looping through result and preparing channels array
	        while ($person = $result->fetch_assoc()) {
	            $tmp = array();
	            $tmp["id"] = $person["id"];
	            $tmp["name"] = $person["name"];
	            $tmp["email"] = $person["email"];
	            $tmp["phone"] = $person["phone"];
	            $tmp["id_number"] = $person["id_number"];
	            
	            array_push($response, $tmp);
	        }
			return $response;
		}
   	}
   	
   	/**
	 * Generates the HTML with the HTML table for an array of contacts or customers.
	 * @param $contacts Array the array with the objects representing the users.
	 * @param $customerType the type of customer
	 */
   	private function getCustomerListAsTable($customers, $customerType) {
	   	// print prefix
       $result = $this->contactsTablePrefix;
       
       foreach ($customers as $customer) {
	       $nameOrNonamed = $customer["name"];
	       if (empty ($nameOrNonamed) || strlen($nameOrNonamed) < 1) $nameOrNonamed = "(sin nombre)";
	       
	       $result = $result."<tr>
                    <td>".$customer["id"]."</td>
                    <td><a href=\"editcustomer.php?customerid=".$customer["id"]."&customer_type=".$customerType."\" >".$nameOrNonamed."</a></td>
                    <td>".$customer["email"]."</td>
                    <td>".$customer["phone"]."</td>
                    <td>".$customer["id_number"]."</td>
                </tr>";
       }
       
       // print suffix
       $result = $result.$this->contactsTableSuffix;
       return $result;
   	}

   	/**
	 * Generates the HTML with the HTML table for an array of contacts or customers.
	 * @param $contacts Array the array with the objects representing the users.
	 * @param $customerType the type of customer
	 */
	public function getAllCustomersOfTypeAsTable($customerType) {
       $customers = $this->getAllCustomersOfType($customerType);
       // is null?
       if (is_null($customers)) { // error getting customers
	       return $this->getErrorMessage("¡Vaya! Fue imposible obtener lista de clientes. Por favor, inténtalo de nuevo más tarde. Si el problema persiste, contacta con el administrador.");
       } else if (empty($customers)) { // no customers found
	       return $this->getWarningMessage("¡Vaya! Parece que la lista de clientes está vacía. ¿Por qué no creas uno nuevo?");
       } else { // we have some customers, show a table
		   return $this->getCustomerListAsTable($customers, $customerType);
       }
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
		$stmt = $this->conn->prepare("INSERT INTO $customerType (name, email, phone, mobile, id_number, address, city, state, zip_code, country, type, birthdate, marital_status, creation_date, created_by, do_not_send_email, gender) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?)");
		$stmt->bind_param("ssssssssssssiiii", $name, $email, $phone, $mobile, $id_number, $address, $city, $state, $zipcode, $country,  $productType, $correctDate, $maritalstatus,$createdByUser, $donotsendemail, $gender);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
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
		$stmt = $this->conn->prepare("UPDATE $customerType SET name = ?, email = ?, phone = ?, mobile = ?, id_number = ?, address = ?, city = ?, state = ?, zip_code = ?, country = ?, type = ?, birthdate = ?, marital_status = ?, do_not_send_email = ?, gender = ?, notes = ? WHERE id = ?");
		$stmt->bind_param("ssssssssssssiiisi", $name, $email, $phone, $mobile, $id_number, $address, $city, $state, $zipcode, $country, $productType, $correctDate, $maritalstatus, $donotsendemail, $gender, $notes, $customerid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
	}
		
	/**
     * Gets the data of a customer.
     * @param Int $userid id of the customer to get data from.
     * @param String $customerType type of the customer to get data from.
     * @return Array an array containing the customer data, or NULL if customer wasn't found.
     */
    public function getDataForCustomer($customerid, $customerType) {
		$stmt = $this->conn->prepare("SELECT * FROM $customerType WHERE id = ?");
		$stmt->bind_param("i", $customerid);
		if ($stmt->execute() === false) return NULL;
		
		// analyze results
		$result = $stmt->get_result();
		$stmt->close();
		if ($obj = $result->fetch_assoc()) {
			return $obj;
		} else return NULL;
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
        $stmt = $this->conn->prepare("DELETE FROM ? where id = ?");
        $stmt->bind_param("si", $customerType, $customerid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
	 }
	 
	/**
	 * Retrieves an array containing an array with all the customer types expressed as an associative array.
	 * @return Array the list of customer type structures.
	 */
	public function getCustomerTypes() {
		$result = $this->conn->query("SELECT * FROM customer_types");
		if ($result === false) return array();
		$customerTypes = array();
		while ($row = $result->fetch_assoc()) {
			array_push($customerTypes, $row);
		}
		return $customerTypes;
	}
	
	/**
	 * Retrieves the customer "human friendly" description name for a customer type.
	 * @param $customerType String customer type ( = table name).
	 * @return String a human friendly description of this customer type.
	 */
	public function getNameForCustomerType($customerType) {
		$stmt = $this->conn->prepare("SELECT * FROM customer_types WHERE table_name = ?");
		$stmt->bind_param("s", $customerType);
		if ($stmt->execute() === false) return "Customer";
		else {
			$result = $stmt->get_result();
			if ($row = $result->fetch_assoc()) {
				return $row["description"];
			} else return "Customer";
		}
	}
	
	/* ---------------- tareas --------------------------------- */

	/**
	 * Gets all tasks belonging to a given user.
	 * @param $userid Int id of the user.
	 * @return Array an array containing all task objects as associative arrays, or NULL if user was not found or an error occurred.
	 */
	private function getAllTasks($userid) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY creation_date");
        $stmt->bind_param("i", $userid);
        if ($stmt->execute() === false) return NULL;
        $tasks = $stmt->get_result();
        $stmt->close();
	    $result = array();
        if ($tasks->num_rows > 0) {
			while ($task = $tasks->fetch_assoc()) {
		        array_push($result, $task);
		    }
        }
		return $result;
	}
	
	/**
	 * Generates the HTML for a given task as a table row
	 * @param $task Array associative array representing the task object.
	 * @return String the HTML representation of the task as a row.
	 */
	private function getTaskAsTableRow($task) {
		// define progress and bar color
		$completed = $task["completed"];
		if ($completed < 0) $completed = 0;
		else if ($completed > 100) $completed = 100;
		$color = $this->getTaskColorForCompletion($completed); 
		$creationdate = $this->relativeTime($task["creation_date"]);
		
		return '<tr>
	            <td>'.$task["id"].'</td>
	            <td>'.$task["description"].'</td>
	            <td>
	                <div class="progress xs progress-striped active" title="'.$completed.'% Completado">
	                    <div class="progress-bar progress-bar-'.$color.'" style="width: '.$completed.'%"></div>
	                </div>
	            </td>
	            <td>'.$creationdate.'</td>
	            <td>'.$this->generateTaskActionButton($task["id"], $completed).'</td></tr>';	
	}

	/**
	 * Generates the HTML for a all tasks of a given user as a table row
	 * @param $userid Int id of the user to retrieve the tasks from.
	 * @return String the HTML representation of the user's tasks as a table.
	 */
	public function getAllMyTasksAsTable($userid) { 
		$tasks = $this->getAllTasks($userid);
		if (empty($tasks)) { return $this->getWarningMessage("¡Vaya! Parece que no tienes ninguna tarea en estos momentos. ¿Por qué no creas una?"); }
		else {
			$list = $this->taskTablePrefix;
			foreach ($tasks as $task) {
				// generate row
				$list = $list.$this->getTaskAsTableRow($task);
			}
			$list = $list."</table>";
	    	return $list;
		}
   	}
   	
	/**
	 * Generates the HTML for a the action button associated to a task listed in the user's task list.
	 * @param $userid Int id of the user to retrieve the tasks from.
	 * @param $finished Int completion percentage of the task (0-100).
	 * @return String the HTML representation of the action button associated to the task.
	 */
   	private function generateTaskActionButton($taskid, $finished) {
	   	$classFinished = "";
	   	if ($finished >= 100) $classFinished = " disabled-link";
	   	return '<div class="btn-group">
            <button type="button" class="btn btn-default btn-sm dropdown-toggle"  data-toggle="dropdown">Acción</button>
            <ul class="dropdown-menu" role="menu">
                <li><a class="info-task-action" href="'.$taskid.'" data-toggle="modal" data-target="#info-task-dialog-modal" >Información</a></li>
                <li><a class="complete-task-action'.$classFinished.'" data-toggle="modal" data-target="#complete-task-dialog-modal" href="'.$taskid.'">Completar</a></li>
                <li class="divider"></li>
                <li><a class="delete-task-action" href="'.$taskid.'">Eliminar</a></li>
            </ul>
        </div>';
   	}
	
	/**
	 * Creates a new task for a user.
	 * @param $userid Int id of the user creating the new task.
	 * @param $taskDescription String description of the new task.
	 * @param $taskInitialProgress Int initial completion percentage of the task that has been completed (0-100).
	 * @return boolean true if operation was successful, false otherwise.
	 */
	public function createTask($userid, $taskDescription, $taskInitialProgress) {
		// sanity checks
		if (empty($userid) || empty($taskDescription)) return false;
		else if (empty($taskInitialProgress)) $taskInitialProgress = 0;
		else if ($taskInitialProgress < 0) $taskInitialProgress = 0;
		else if ($taskInitialProgress > 100) $taskInitialProgress = 100;
		
		if ($taskInitialProgress == 100) { // already completed.
			$stmt = $this->conn->prepare("INSERT INTO tasks(user_id, description, completed, creation_date, completion_date) values(?, ?, ?, now(), now())");
			$stmt->bind_param("isi", $userid, $taskDescription, $taskInitialProgress);
		} else {
			$stmt = $this->conn->prepare("INSERT INTO tareas(user_id, description, completed, creation_date) values(?, ?, ?, now())");
			$stmt->bind_param("isi", $userid, $taskDescription, $taskInitialProgress);
		}
		$result = $stmt->execute();
		$stmt->close();
		return $result;
	}
	
	/**
	 * Deletes a task
	 * @param $taskid Int id of the task to be deleted.
	 * @return boolean true if operation was successful, false otherwise.
	 */
	public function deleteTask($taskid) {
	 	if (empty($taskid)) return false;
        $stmt = $this->conn->prepare("DELETE FROM tasks where id = ?");
        $stmt->bind_param("i", $taskid);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
	}

	/**
	 * Generates the HTML for an individual task as a HTML code, depending on a given format, controlled by the format variable.
	 * @param $task Array data for the task.
	 * @param $format Int Either TASK_GENERAL_INFO_FORMAT (generating a general task info table) or TASK_PROGRESS_FORMAT (generating a task progress info msg).
	 * @return String the HTML representation of the task, depending of a given format.
	 */
	private function getTaskAsIndividualTable($task, $format) {
		$completed = $task["completed"];
		if ($completed < 0) $completed = 0;
		else if ($completed > 100) $completed = 100;
		$color = $this->getTaskColorForCompletion($completed);

		if ($format == TASK_GENERAL_INFO_FORMAT) { // General task info
			$creationdate = $this->relativeTime($task["creation_date"]);
			$taskcompletion = "";
			if ($completed == 100) {
				$completiondate = $this->relativeTime($task["completion_date"]);
				$taskcompletion = "<tr><td>Fecha de finalización</td><td>".$completiondate."</td></tr>";
			}
			return '<table class="table table-bordered">
                    <tr>
                        <td>Descripción</td>
                        <td>'.$task["description"].'</td>
                    </tr>
                    <tr>
                        <td>Progreso</td>
                        <td>Completado '.$completed.'% de la tarea
                            <div class="progress xs">
                                <div class="progress-bar progress-bar-'.$color.'" style="width: '.$completed.'%"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Fecha de inicio</td>
                        <td>'.$creationdate.'</td>
                    </tr>'.$taskcompletion.'
                </table>';
		} else if ($format == TASK_PROGRESS_FORMAT) { // Task progress info.
			return '<div id="new-task-progress-label">Completado: ('.$completed.'%) </div>
					<input type="text required" value="" id="task-new-progress-slider" name="task-new-progress-slider" class="slider form-control" data-slider-min="'.$completed.'" data-slider-max="100" data-slider-step="5" data-slider-value="'.$completed.'" data-slider-orientation="horizontal">
';
		} else {
			return $this->getErrorMessage("Tipo de formato para mostrar la tarea desconocido. Consulte a su administrador.");
		}
	}

	/**
	 * Generates the HTML for a task as a HTML table.
	 * @param $taskid Int identifier for the task.
	 * @param $userid Int identifier for the user.
	 * @param $format Int Either TASK_GENERAL_INFO_FORMAT (generating a general task info table) or TASK_PROGRESS_FORMAT (generating a task progress info msg).
	 * @return String the HTML representation of the task, depending of a given format.
	 */
	public function getTaskInfoAsTable($taskid, $userid, $format) {
		// sanity check
		if (empty($taskid) || empty($userid)) {
			return $this->getErrorMessage("Ha sido imposible obtener los datos de la tarea. Por favor, inténtalo de nuevo más tarde.");
		} else if (empty($format)) { $format = TASK_GENERAL_INFO_FORMAT; }
		
		// get task data and execute query 
		$stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id = ?");
		$stmt->bind_param("i", $taskid);
		if ($stmt->execute() === false) return false;
		$result = $stmt->get_result();
		$stmt->close();
		
		// modify task if it actually belongs to the right user.
		if ($task = $result->fetch_assoc()) {
			if ($task["user_id"] == $userid) {
				return $this->getTaskAsIndividualTable($task, $format);
			} else return $this->getErrorMessage("Lo siento, no tiene permisos para acceder a tareas de otros usuarios.");
		} else { // return failed to get task error message.
			return $this->getErrorMessage("Ha sido imposible obtener los datos de la tarea. Por favor, inténtalo de nuevo más tarde.");
		}
	}
	
	/**
	 * Modifies a task progress status.
	 * @param $taskid Int identifier of the task
	 * @param $progress new progress for the task (0-100).
	 * @param $userid id of the user the task belongs to.
	 * @return boolean true if modification was successful, false otherwise.
	 */
	public function modifyTask($taskid, $progress, $userid) {
		if (empty($taskid) || empty($progress) || empty($userid)) return false;
		$stmt = $this->conn->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
		$stmt->bind_param("iii", $progress, $taskid, $userid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
	}
	
	/**
	 * Retrieves the number of unfinished tasks.
	 * @param Int $userid returns the number of unfinished tasks of the user.
	 */
	 public function getUnfinishedTasksNumber($userid) {
		 // prepare query.
		 $stmt = $this->conn->prepare("SELECT count(*) from tasks where user_id = ? AND completed < 100");
		 $stmt->bind_param("i", $userid);
		 if ($stmt->execute() === false) return 0;
		 $result = $stmt->get_result();
		 $stmt->close();
		 // analyse result
		 if ($result === false) return 0;
		 else {
			 $row = $result->fetch_row();
			 $numMessages = $row[0];
			 $result->close();
			 return $numMessages;
		}
	 }
	 
	/**
	 * Retrieves the unfinished tasks of a user as an array of tasks objects.
	 * @param Int $userid returns the unfinished tasks of the user.
	 */
	 public function getUnfinishedTasks($userid) {
		 // prepare query
		 $stmt = $this->conn->prepare("SELECT * from tasks where user_id = ? AND completed < 100");
		 $stmt->bind_param("i", $userid);
		 if ($stmt->execute() === false) return 0;
		 $result = $stmt->get_result();
		 $stmt->close();

		 // analyse results
		 if ($result === false) return NULL;
		 else {
			 $tasks = array();
			 while ($row = $result->fetch_assoc()) {
				 $tmp = array();
				 $tmp["id"] = $row["id"];
				 $tmp["user_id"] = $row["user_id"];
				 $tmp["target_customer_id"] = $row["target_customer_id"];
				 $tmp["description"] = $row["description"];
				 $tmp["completed"] = $row["completed"];
				 $tmp["creation_date"] = $row["creation_date"];
				 $tmp["completion_date"] = $row["completion_date"];
				 
				 array_push($tasks, $tmp);
			 }
			 $result->close();
			 return $tasks;
		}
	 }
	 	
	/* ------------------- messages --------------------------- */
	
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
		if (empty($subject)) $subject = "(Sin asunto)";
		if (empty($message)) $message = "(Sin mensaje)";
		
		// insert the new message in the inbox of the receiving user.
		$stmt = $this->conn->prepare("INSERT INTO messages_inbox (user_from, user_to, subject, message, date, message_read, favorite) VALUES (?, ?, ?, ?, now(), 0, 0)");
		$stmt->bind_param("iiss", $fromuserid, $touserid, $subject, $message);
		$insertInbox = $stmt->execute();
		$stmt->close();
		if ($insertInbox === false) return false;
				
		// insert the new message in the outbox of the sending user.
		$stmt = $this->conn->prepare("INSERT INTO messages_outbox (user_from, user_to, subject, message, date, message_read, favorite) VALUES (?, ?, ?, ?, now(), 1, 0)");
		$stmt->bind_param("iiss", $fromuserid, $touserid, $subject, $message);
		$insertOutbox = $stmt->execute();
		$stmt->close();
		if ($insertOutbox === false) return false;
		return true;
	}
	
	/**
	 * Generates the list of users $myuserid can send mail to (all except $myuserid) as a HTML form SELECT.
	 * @param Int $myuserid id of the user that wants to send messages, all other user's ids will be returned.
	 * @return the list of users $myuserid can send mail to (all except $myuserid) as a HTML form SELECT.
	 */
	public function generateMailToUserSelect($myuserid) {
		// perform query of users.
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE status = 1");
		if ($stmt->execute() === false) return $this->getErrorMessage("¡Vaya! Ha sido imposible obtener la lista de usuarios para enviar el message. Por favor, ponte en contacto con el administrador.");
		$result = $stmt->get_result();

		// iterate through all users and generate the select
		$response = '<select class="form-control" id="touserid" name="touserid">\n\t<option value="0">elige un destinatario</option>\n';
		while ($obj = $result->fetch_assoc()) {
			// don't include ourselves.
			if ($obj["id"] != $myuserid) $response = $response.'\t<option value="'.$obj["id"].'">'.$obj["name"].'</option>\n';			
		}
		$response = $response.'</select>';
		return $response;
	}
	
	/**
	 * Returns the table name associated with a mail folder id.
	 * @param $folder the identifier of the mail folder.
	 * @return the table name associated with a mail folder id.
	 */
	private function getTableNameForFolder($folder) {
		$tableName = NULL;
		if ($folder == MESSAGES_GET_INBOX_MESSAGES) { // all inbox messages.
			$tableName = "messages_inbox";
		} else if ($folder == MESSAGES_GET_UNREAD_MESSAGES) { // unread messages.
			$tableName = "messages_inbox";
		} else if ($folder == MESSAGES_GET_DELETED_MESSAGES) { // deleted messages.
			$tableName = "messages_junk";
		} else if ($folder == MESSAGES_GET_SENT_MESSAGES) { // sent messages.
			$tableName = "messages_outbox";
		} else if ($folder == MESSAGES_GET_FAVORITE_MESSAGES) { // favorite inbox messages
			$tableName = "messages_inbox";
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
	private function getMessagesOfType($userid, $type) {
		// initial sanity checks
		if (!is_numeric($userid) || !is_numeric($type)) return NULL;
		
		// determine type of messages to get.
		$whereClause = NULL;
		$tableName = $this->getTableNameForFolder($type);
		if ($type == MESSAGES_GET_INBOX_MESSAGES) { // all inbox messages.
			$whereClause = "WHERE m.user_to = $userid AND m.user_from = u.id";
		} else if ($type == MESSAGES_GET_UNREAD_MESSAGES) { // unread messages.
			$whereClause = "WHERE m.user_to = $userid AND m.message_read = 0 AND m.user_from = u.id";
		} else if ($type == MESSAGES_GET_DELETED_MESSAGES) { // deleted messages.
			$whereClause = "WHERE (m.user_to = $userid AND m.user_from = u.id) OR (m.user_from = $userid AND m.user_to = u.id)";
		} else if ($type == MESSAGES_GET_SENT_MESSAGES) { // sent messages.
			$whereClause = "WHERE m.user_from = $userid AND m.user_to = u.id";
		} else if ($type == MESSAGES_GET_FAVORITE_MESSAGES) { // favorite inbox messages
			$whereClause = "WHERE m.user_to = $userid AND favorite = 1 AND m.user_from = u.id";
		}
		
		// safety check
		if (empty($whereClause) || empty($tableName)) {
			return NULL;
		}
		// return the messages.
		$result = $this->conn->query("SELECT u.name, u.avatar, m.id, m.user_from, m.user_to, m.subject, m.message, m.date, m.message_read, m.favorite FROM ".$tableName." m, users u ".$whereClause." ORDER BY m.date DESC");
		if (empty($result) || $result === false) { // sanity check
			return NULL;
		}
		// iterate through all users and generate the select
		$response = array();
		while ($obj = $result->fetch_assoc()) {
			$tmp = array();
			$tmp["id"] = $obj["id"]; 
			$tmp["user_from"] = $obj["user_from"]; 
			$tmp["user_to"] = $obj["user_to"]; 
			$tmp["subject"] = $obj["subject"]; 
			$tmp["message"] = $obj["message"]; 
			$tmp["date"] = $obj["date"]; 
			$tmp["message_read"] = $obj["message_read"]; 
			$tmp["favorite"] = $obj["favorite"];
			$tmp["remote_user"] = $obj["name"];
			$tmp["remote_avatar"] = $obj["avatar"]; 
			array_push($response, $tmp);
		}
		
		return $response;
	}
	
	/**
	 * Generates the HTML of the given messages as a HTML table, from a table array
	 * @param Array $messages the list of messages.
	 * @return the HTML code with the list of messages as a HTML table. 
	 */
	private function getMessageListAsTable($messages) {
		// generate the table.
		$result = $this->messageListPrefix;
		foreach ($messages as $message) {
			if ($message["message_read"] == 0) $result = $result.'<tr class="unread">';
			else $result = $result.'<tr>';
						
			// variables and html text depending on the message
			$favouriteHTML = "-o"; if ($message["favorite"] == 1) $favouriteHTML = "";
			$messageLink = '<a href="'.$message["id"].'" class="show-message-link">';
			
			$result = $result.'<td class="small-col"><input type="checkbox" class="message-selection-checkbox" value="'.$message["id"].'"/></td>';
			$result = $result.'<td class="small-col"><i class="fa fa-star'.$favouriteHTML.'" id="'.$message["id"].'"></i></td>';
			$result = $result.'<td class="name">'.$messageLink.$message["remote_user"].'</a></td>';
			$result = $result.'<td class="subject">'.$messageLink.$message["subject"].'</a></td>';
			$result = $result.'<td class="time">'.$this->relativeTime($message["date"]).'</td>';
			
			$result = $result."</tr>";
		}
		$result = $result."</table>";
		return $result;		
	}	
	
	/**
	 * Generates a HTML table with all inbox messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getInboxMessagesAsTable($userid) {
		$messages = $this->getMessagesOfType($userid, MESSAGES_GET_INBOX_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage("¡Vaya! Ha sido imposible obtener los mensajes. Inténtalo más tarde o consulta con el administrador");
		else return $this->getMessageListAsTable($messages);
	}
	
	/**
	 * Generates a HTML table with the unread messages of the user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getUnreadMessagesAsTable($userid) {
		$messages = $this->getMessagesOfType($userid, MESSAGES_GET_UNREAD_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage("No hay messages en esta carpeta.");
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with with the junk messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getJunkMessagesAsTable($userid) {
		$messages = $this->getMessagesOfType($userid, MESSAGES_GET_DELETED_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage("No hay messages en esta carpeta.");
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the sent messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getSentMessagesAsTable($userid) {
		$messages = $this->getMessagesOfType($userid, MESSAGES_GET_SENT_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage("No hay messages en esta carpeta.");
		else return $this->getMessageListAsTable($messages);
	}
				
	/**
	 * Generates a HTML table with the favourite messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getFavoriteMessagesAsTable($userid) {
		$messages = $this->getMessagesOfType($userid, MESSAGES_GET_FAVORITE_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage("No hay messages en esta carpeta.");
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the messages from given folder for a user.
	 * @param Int $userid user to retrieve the messages from
	 * @param Int $folder folder to retrieve the messages from
	 */
	public function getMessagesFromFolderAsTable($userid, $folder) {
		$messages = $this->getMessagesOfType($userid, $folder);
		if ($messages == NULL) return $this->getInfoMessage("No hay messages en esta carpeta.");
		else return $this->getMessageListAsTable($messages);
	}

	/**
	 * Returns the number of unread messages for a user.
	 * @param Int $userid id of the user to get the unread messages from.
	 */
	 public function getUnreadMessagesNumber($userid) {
		 if (empty($userid)) return 0;
		 // prepare query.
		 $stmt = $this->conn->prepare("SELECT count(*) FROM messages_inbox WHERE user_to = ? AND message_read = 0");
		 $stmt->bind_param("i", $userid);
		 if ($stmt->execute() === false) return 0;

		 // analyse results
		 $result = $stmt->get_result();
		 if ($result === false) return 0;
		 else {
			 $row = $result->fetch_row();
			 $numMessages = $row[0];
			 $result->close();
			 return $numMessages;
		}
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
		
		// return result of update 
		$result = $this->conn->query("UPDATE ".$tableName." SET message_read = 1 WHERE ".$useridfield." = $userid AND id IN(".implode(',',$messageids).")");
		return $result;
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

		// return result of update 
		$result = $this->conn->query("UPDATE ".$tableName." SET message_read = 0 WHERE ".$useridfield." = $userid AND id IN(".implode(',',$messageids).")");
		return $result;
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
		$result = $this->conn->query("UPDATE ".$tableName." SET favorite = ".$favorite." WHERE ".$useridfield." = $userid AND id IN(".implode(',',$messageids).")");
		return $result;
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

		// return result of update 
		$result = $this->conn->query("DELETE FROM ".$tableName." WHERE ".$useridfield." = $userid AND id IN(".implode(',',$messageids).")");
		return $result;
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
			$copyresult = $this->conn->query("INSERT INTO messages_junk (user_from, user_to, subject, message, date, message_read, favorite, origin_folder) SELECT user_from, user_to, subject, message, date, message_read, favorite, '".$tableName."' as origin_folder FROM ".$tableName." WHERE ".$useridfield." = $userid AND id = ".$messageid);
			if ($copyresult) {
				$deleteOriginal = $this->conn->query("DELETE FROM ".$tableName." WHERE ".$useridfield." = $userid AND id = ".$messageid);
				if ($deleteOriginal) { 
					$messagesJunked = $messagesJunked + 1;
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
			$selectData = $this->conn->query("SELECT * FROM messages_junk WHERE id = $messageid");
			if ($selectData) {
				if ($junkedObj = $selectData->fetch_assoc()) {
					$tableName = $junkedObj["origin_folder"];
					$fromuserid = $junkedObj["user_from"];
					$touserid = $junkedObj["user_to"];
					$subject = $junkedObj["subject"];
					$text = $junkedObj["message"];
					$messagedate = $junkedObj["date"];
					$readmail = $junkedObj["message_read"];
					$favorite = $junkedObj["favorite"];
					if (!empty($tableName)) {
						$restore = $this->conn->query("INSERT INTO ".$tableName." (user_from, user_to, subject, message, date, message_read, favorite) VALUES ($fromuserid, $touserid, '$subject', '$text', '$messagedate', $readmail, $favorite)");
						if ($restore) {
							$deleteOriginal = $this->conn->query("DELETE FROM messages_junk WHERE id = $messageid");
							if ($deleteOriginal) {
								$messagesUnjunked = $messagesUnjunked + 1;
							}
						}
					}
				}
			}
		}
		
		return $messagesUnjunked;	
	}

	/**
	 * Generates a modal dialog HTML code for a given message of a given id. If the message is not found or an error occurrs, a error modal message is generated.
	 * @param $userid Int the user identifier the message belongs to
	 * @param $messageid Int the identifier for the message.
	 * @param $folder Int identifier of the mail folder the message is contained in.
	 * @return the modal dialog HTML code. 
	 */
	public function getMessageModalDialogAsHTML($userid, $messageid, $folder) {
		// sanity checks
		$tableName = $this->getTableNameForFolder($folder);
		if ($tableName == NULL || $userid == NULL || $messageid == NULL) {
			return $this->getErrorModalMessage("Ha sido imposible obtener el message. Por favor, inténtelo de nuevo más tarde. Si el problema persiste, consulte con el administrador.");
		}
		$remoteuseridfield = "user_from";
		$useridfield = "user_to";
		if ($folder == MESSAGES_GET_SENT_MESSAGES) {
			 $useridfield = "user_from";
			 $remoteuseridfield = "user_to";
		}
		
		// calculate query message.
		if ($folder == MESSAGES_GET_DELETED_MESSAGES) {
			$stmt = $this->conn->prepare("SELECT * FROM $tableName m, usuarios u WHERE m.id = ? AND ((m.? = ? AND m.? = u.id) OR (m.? = ? AND m.? = u.id)) ");
			$stmt->bind_param("isissis", $messageid, $useridfield, $userid, $remoteuseridfield, $remoteuseridfield, $userid, $useridfield);		
		} else {
			$stmt = $this->conn->prepare("SELECT * FROM $tableName m, usuarios u WHERE m.? = ? AND m.? = u.id AND m.id = ?");
			$stmt->bind_param("sisi", $useridfield, $userid, $remoteuseridfield, $messageid);
				
		}
		// execute the query
		if ($stmt->execute() === false) return NULL;
		$result = $stmt->get_result();
		if (empty($result) || $result === false) { // sanity check
			return NULL;
		}
		
		// generate the message modal dialog to show the message.
		if ($obj = $result->fetch_assoc()) {
			$messageid = $obj["id"]; 
			$fromuserid = $obj["user_from"]; 
			$touserid = $obj["user_to"]; 
			$subject = $obj["subject"]; 
			$text = $obj["message"]; 
			$messagedate = $obj["date"]; 
			$remoteusername = $obj["name"]; 
			$fromortodestination = ($fromuserid == $userid)? " para $remoteusername." : " de $remoteusername.";
			$relativeTime = $this->relativeTime($messagedate);
		
			return '
			<div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> message'.$fromortodestination.'</h4>
		            </div>
		            <form action="#" method="post" id="show-message-form" name="show-message-form">
		                <div class="modal-body">
		                    <div class="form-group">
		                        <div class="input-group">
		                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
		                            <input name="fromuserid" id="fromuserid" type="text" class="form-control" value="'.$remoteusername.'" readonly>
		                        </div>
		                    </div>
		                    <div class="form-group">
		                        <div class="input-group">
		                            <span class="input-group-addon"><i class="fa fa-comment"></i></span>
		                            <input name="subject" id="subject" type="text" class="form-control" value="'.$subject.'" readonly>
		                        </div>
		                    </div>                    
		                    <div class="form-group">
		                        <div class="input-group">
		                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                            <input name="messagedate" id="messagedate" type="text" class="form-control" value="'.$relativeTime.'" readonly>
		                        </div>
		                    </div> 
							<div class="form-group">
		                        <textarea name="message" id="message" class="form-control" placeholder="Message" style="height: 120px;" readonly>'.$text.'
		                        </textarea>
		                    </div>
		                </div>
		                <input type="hidden" id="messageid" name="messageid" value="'.$messageid.'">
		                <div class="modal-footer clearfix">
		                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Salir</button>
		                </div>
		            </form>
		        </div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->';
    	} else {
	    	
			return $this->getErrorModalMessage("Ha sido imposible obtener el message. Por favor, inténtelo de nuevo más tarde. Si el problema persiste, consulte con el administrador.");
    	}

	} // end function

	/* ---------------- Notificaciones -------------------------- */
	
	/**
	 * Gets the number of notifications for today for the user.
	 * @param $userid Int the identifier for the user.
	 * @return Int the number of notifications. 
	 */
	public function getNumberOfTodayNotifications($userid) {
		// prepare query
		if (empty($userid)) return NULL;
		$stmt = $this->conn->prepare("SELECT count(*) FROM notifications WHERE DATE(date) = CURDATE() AND (target_user = 0 OR target_user = ?)");
		$stmt->bind_param("i", $userid);
		if ($stmt->execute() === false) return 0;
		
		// execute query and return results
		$result = $stmt->get_result();
		$stmt->close();
		if ($result === false) return 0;
		else {
			 $row = $result->fetch_row();
			 $numMessages = $row[0];
			 $result->close();
			 return $numMessages;
		}
	}
	
	/**
	 * Gets the notifications for today for the user as an array.
	 * @param $userid Int the identifier for the user.
	 * @return Array the notifications as an associative array. 
	 */
	private function getTodayNotifications($userid) {
		// prepare query
		if (empty($userid)) return NULL;
		$stmt = $this->conn->prepare("SELECT * FROM notifications WHERE DATE(date) = CURDATE() AND (target_user = 0 OR target_user = ?)");
		$stmt->bind_param("i", $userid);
		if ($stmt->execute() === false) return NULL;
		
		// execute query and return results.
		$result = $stmt->get_result();
		$stmt->close();		
		$notifications = array();
		while ($obj = $result->fetch_assoc()) {
			array_push($notifications, $obj);
		}
		return $notifications;
	}
	
	/**
	 * Get notifications for past week for the user.
	 * @param $userid Int the identifier for the user.
	 * @return Array the notifications as an associative array. 
	 */
	private function getNotificationsForPastWeek($userid) {
		// prepare query
		if (empty($userid)) return NULL;
		$stmt = $this->conn->prepare("SELECT * FROM notifications WHERE (DATE(date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY) AND (target_user = 0 OR target_user = ?)");
		$stmt->bind_param("i", $userid);
		if ($stmt->execute() === false) return NULL;
		
		// execute query and return results.
		$result = $stmt->get_result();
		$stmt->close();				
		$notifications = array();
		while ($obj = $result->fetch_assoc()) {
			array_push($notifications, $obj);
		}
		return $notifications;
	}
	
	/**
	 * Returns the HTML font-awesome icon for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the font-awesome icon for this notification type.
	 */
	private function notificationIconForNotificationType($type) {
		if ($type == "contact") return "fa-user";
		else if ($type == "message") return "fa-envelope";
		else return "fa-calendar-o";
	}
	
	/**
	 * Returns the HTML UI color for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the UI color for this notification type.
	 */
	private function notificationColorForNotificationType($type) {
		if ($type == "contact") return "bg-aqua";
		else if ($type == "message") return "bg-blue";
		else return "bg-yellow";
	}
	
	/**
	 * Returns the HTML action button text for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the action button text for this notification type.
	 */
	private function actionButtonTextForNotificationType($type) {
		if ($type == "contact") return "Ver cliente";
		else if ($type == "message") return "Leer message";
		else return "Saber más";
	}
	
	/**
	 * Returns the HTML header text for notifications of certain type associated to certain action.
	 * @param $type String the type of notification.
	 * @param $action String a URL with the action to perform for this notification.
	 * @return String the string with the header text for this notification type.
	 */
	private function headerTextForNotificationType($type, $action) {
		if ($type == "contact") return empty($action) ? "Hay un nuevo Contacto" : "Hay un nuevo <a href=".$action.">Contacto</a>";
		else if ($type == "message") return empty($action) ? "Tienes un nuevo message" : "Tienes un nuevo <a href=".$action.">message</a>";
		else return empty($action) ? "Nuevo Evento" : "Nuevo <a href=".$action.">Evento</a>";
	}
	
	/**
	 * Generates the HTML code for the given notification.
	 * @param $notification Array an associative array object containing the notification data.
	 * @return String a HTML representation of the notification.
	 */
	private function timelineItemForNotification($notification) {
		$type = $notification["type"];
		$action = isset($notification["action"]) ? $notification["action"]: NULL;
		$date = $notification["date"];
		$texto = $notification["text"];
		$actionHTML = "";
		
		if (!empty($action)) $actionHTML = '<div class="timeline-footer"><a class="btn btn-success btn-xs" href="'.$action.'">'.$this->actionButtonTextForNotificationType($type).'</a></div>';
		
		$color = $this->notificationColorForNotificationType($type);
		$icon = $this->notificationIconForNotificationType($type);
		$relativetime = $this->relativeTime($date, 1);

		return '<!-- timeline item -->
                <li>
                    <i class="fa '.$icon.' '.$color.'"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> '.$relativetime.'</span>
                        <h3 class="timeline-header no-border">'.$this->headerTextForNotificationType($type, $action).'</h3>
						<div class="timeline-body">
						'.$texto.'
						</div>
                        '.$actionHTML.'
                    </div>
                </li>
                <!-- END timeline item -->';
	}
	
	/**
	 * Generates the HTML code for the given notification.
	 * @param $notification Array an associative array object containing the notification data.
	 * @return String a HTML representation of the notification.
	 */
	public function getNotificationsAsTimeLine($userid) {
		$days = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
		$months = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$todayAsText = $days[date('w')].", ".date('d')." de ".$months[date('n')-1]. " del ".date('Y') ;
		
		
		// today
		$timeline = '<ul class="timeline">
	                    <!-- timeline time label -->
	                    <li class="time-label">
	                        <span class="bg-green">
	                            '.$todayAsText.'
	                        </span>
	                    </li>';
		
		$notifications = $this->getTodayNotifications($userid);
		if (empty($notifications)) {
			$timeline = $timeline.'<li><div class="timeline-item">'.$this->getInfoMessage("No hay notificaciones para hoy.").'</div></li>';
		} else {
			foreach ($notifications as $notification) {
				$timeline = $timeline.$this->timelineItemForNotification($notification);
			}
		}
		
        // past week
		$timeline = $timeline.'<!-- timeline time label -->
	                    <li class="time-label">
	                        <span class="bg-yellow">
	                            La semana pasada
	                        </span>
	                    </li>';
        		
        $notifications = $this->getNotificationsForPastWeek($userid);
		if (empty($notifications)) {
			$timeline = $timeline.'<li><div class="timeline-item">'.$this->getInfoMessage("No hay notificaciones para la semana pasada.").'</li></div>';
		} else {
			foreach ($notifications as $notification) {
				$timeline = $timeline.$this->timelineItemForNotification($notification);
			}
		}

		// end timeline
		$timeline = $timeline.'<li>
							      <i class="fa fa-clock-o"></i>
						       </li>
						      </ul>';
        
        return $timeline;
	}
	
	/* ---------------- Statistics --------------------------------- */
	
	/**
	 * Inserts a new entry in the statistics table with the current number of customers in every table.
	 * @return boolean true if the operation was successful, false otherwise.
	 */
	public function generateStatisticsForToday() {
		// get customer tables
		$customerTypes = $this->getCustomerTypes();
		if (empty($customerTypes)) return true;

		// build the query by adding customer types
		$queryPrefix = "INSERT INTO statistics (date ";
		$querySuffix = ") VALUES (now() ";
		
		foreach ($customerTypes as $customerType) {
			$numCustomers = $this->getNumberOfClientsFromTable($customerType["table_name"]);
			$queryPrefix = $queryPrefix.", ".$customerType["table_name"];
			$querySuffix = $querySuffix.", ".$numCustomers;
		}
		$query = $queryPrefix.$querySuffix.")";

		// execute query and return results.
		return $this->conn->query($query);
	}

	/**
	 * Gets the number of customers of a given type (= tablename).
	 * @param $tableName String the table of customers to get the count from.
	 * @return the number (count(*)) of entries in the given customer table.
	 */
	private function getNumberOfClientsFromTable($tableName) {
		if (empty($tableName)) return 0;
		$tableName = $this->escape_string($tableName);

		$result = $this->conn->query("SELECT count(*) FROM $tableName");
		if ($result === false) return 0;
		 else {
			 $row = $result->fetch_row();
			 $numClients = $row[0];
			 $result->close();
			 return $numClients;
		}
	}
	
	/**
	 * Gets the number of new contacts (last week).
	 * @return the number of contact entries that were created in the last week.
	 */
	public function getNumberOfNewContacts() {
		$result = $this->conn->query("SELECT count(*) FROM ".CRM_CONTACTS_TABLE_NAME." WHERE (DATE(creation_date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE())");
		if ($result === false) return 0;
		 else {
			 $row = $result->fetch_row();
			 $numClients = $row[0];
			 $result->close();
			 return $numClients;
		}
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
			$result = $this->conn->query("SELECT count(*) FROM ".$customerType["table_name"]." WHERE (DATE(creation_date) BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE())");
			if ($result !== false) {
				 $row = $result->fetch_row();
				 $numClients += $row[0];
				 $result->close();
			}
		}
		return $numClients;
	}
		
	
	/* ---------------- Utility functions -------------------------- */
	
	/**
	 * Generates a relative time string for a given date, relative to the current time.
	 * @param $mysqltime String a string containing the time extracted from MySQL.
	 * @param $maxdepth Int the max depth to dig when representing the time, 
	 *        i.e: 3 days, 4 hours, 1 minute and 20 seconds with $maxdepth=2 would be 3 days, 4 hours.
	 * @return String the string representation of the time relative to the current date.
	 */
	private function relativeTime($mysqltime, $maxdepth = 2) {
		$time = strtotime(str_replace('/','-', $mysqltime));
	    $d[0] = array(1,"segundo");
	    $d[1] = array(60,"minuto");
	    $d[2] = array(3600,"hora");
	    $d[3] = array(86400,"día");
	    $d[4] = array(604800,"semana");
	    $d[5] = array(2592000,"mes");
	    $d[6] = array(31104000,"año");
	
	    $w = array();
	
		$depth = 0;
	    $return = "";
	    $now = time();
	    $diff = ($now-$time);
	    $secondsLeft = $diff;
	
	    for($i=6;$i>-1;$i--)
	    {
	         $w[$i] = intval($secondsLeft/$d[$i][0]);
	         $secondsLeft -= ($w[$i]*$d[$i][0]);
	         if($w[$i]!=0)
	         {
	            $return.= abs($w[$i]) . " " . $d[$i][1] . (($w[$i]>1)?'s':'') ." ";
	            $depth += 1;
	            if ($depth >= $maxdepth) break;
	         }
	
	    }
	
	    $verb = ($diff>0)?"hace ":"quedan ";
	    $return = $verb.$return;
	    return $return;
	}
	
	private function substringUpTo($string, $maxCharacters) {
		if (empty($maxCharacters)) $maxCharacters = 4;
		else if ($maxCharacters < 1) $maxCharacters = 4;
		return (strlen($string) > $maxCharacters) ? substr($string, 0, $maxCharacters-3).'...' : $string;
	}
	
	/**
	 * Generates the HTML color for tasks given their completion status.
	 * @param $completion Int a number between 0 and 100 representing the completion status of the task
	 * @return String the color for the task HTML code.
	 */
	private function getTaskColorForCompletion($completion) {
		$color = "red";
		if ($completion > 30) $color = "yellow";
		if ($completion > 60) $color = "blue";
		if ($completion > 90) $color = "green";
		return $color;
	}
	
	/**
	 * Escapes a string for a safer inclusion in a MySQL statement. Please note that this method alone is not enough for preventing SQL injections.
	 * @param $string String the string to be escaped.
	 * @return String the string escaped with a call to mysqli::real_escape_string();
	 */
	public function escape_string($string) {
		return $this->conn->real_escape_string($string);
	}
	
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
