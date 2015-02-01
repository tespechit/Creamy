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

require_once('CRMDefaults.php');
require_once('PassHash.php');
require_once('ImageHandler.php');
require_once('RandomStringGenerator.php');
require_once('LanguageHandler.php');

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

	// language handler
    private $conn;
	private $lh;
        
	/** Creation and class lifetime management */
    
    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new \creamy\DbConnect();
        $this->conn = $db->connect();
        $this->lh = \creamy\LanguageHandler::getInstance();
   		ini_set( 'date.timezone', CRM_TIMEZONE);
		date_default_timezone_set(CRM_TIMEZONE);
    }
    
    
    /** Administration of users */
    
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
            $password_hash = \creamy\PassHash::hash($password);
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
			$ih = new \creamy\ImageHandler();
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
		 	$ih = new \creamy\ImageHandler();
		 	$ih->removeUserAvatar($data["avatar"]);
	 	}
	 	// then remove the entry at the database
	 	$stmt = $this->conn->prepare("DELETE FROM users where id = ?");
	 	$stmt->bind_param("i", $userid);
	 	$result = $stmt->execute();
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
				if (\creamy\PassHash::check_password($password_hash, $password)) {
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
				if (\creamy\PassHash::check_password($password_hash, $oldpassword)) {
	                // oldpassword is correct, change password.
	                $newPasswordHash = \creamy\PassHash::hash($password1);
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
		$newPasswordHash = \creamy\PassHash::hash($password);
		
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
     * Returns an array containing all enabled users (those with status=1).
     * @return Array an array of objects containing the data of all users in the system.
	 */
	public function getAllEnabledUsers() {
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE status = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result == NULL) {
			return array();
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
     * Returns an array containing all users in the system (only relevant data).
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
	
	/** Password recovery */

	/** 
	 * Sends a recovery mail to the user. The user must have a valid email contained in the database.
	 * @param $email string string of the user.
	 * @return true if successful, false if email couldn't be sent.
	 */
	public function sendPasswordRecoveryEmail($email) {
		if ($this->userEmailAlreadyExists($email)) {
			$randomStringGenerator = new \creamy\RandomStringGenerator();
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
	        $password_hash = \creamy\PassHash::hash($password);
			return $this->conn->query("UPDATE users SET password_hash = '$password_hash' WHERE email = '$email'");
		}
		return false;
	}

	/** Customers */
	
	/**
	 * Gets all customers of certain type.
	 * @param $customerType the type of customer to retrieve.
	 * @return Array an array containing the objects with the users' data.
	 */
	public function getAllCustomersOfType($customerType) {
		if (!isset($customerType)) return array();
        $stmt = $this->conn->prepare("SELECT * FROM $customerType");
        if ($stmt === false) return array();
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
		if ($stmt->execute() === false) return $this->lh->translationFor("customer");
		else {
			$result = $stmt->get_result();
			if ($row = $result->fetch_assoc()) {
				return $row["description"];
			} else return "Customer";
		}
	}
	
	/** tasks */

	/**
	 * Gets all tasks belonging to a given user.
	 * @param $userid Int id of the user.
	 * @return Array an array containing all task objects as associative arrays, or NULL if user was not found or an error occurred.
	 */
	public function getCompletedTasks($userid) {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND completed = 100 ORDER BY creation_date");
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
		 $stmt = $this->conn->prepare("SELECT * from tasks where user_id = ? AND completed < 100 ORDER BY creation_date");
		 $stmt->bind_param("i", $userid);
		 if ($stmt->execute() === false) return 0;
		 $result = $stmt->get_result();
		 $stmt->close();

		 // analyse results
		 if ($result === false) return NULL;
		 else {
			 $tasks = array();
			 while ($task = $result->fetch_assoc()) {
				 array_push($tasks, $task);
			 }
			 $result->close();
			 return $tasks;
		}
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
		
		if ($taskInitialProgress == 100) { // already completed.
			$stmt = $this->conn->prepare("INSERT INTO tasks(user_id, description, completed, creation_date, completion_date) values(?, ?, ?, now(), now())");
			$stmt->bind_param("isi", $userid, $taskDescription, $taskInitialProgress);
		} else {
			$stmt = $this->conn->prepare("INSERT INTO tasks(user_id, description, completed, creation_date) values(?, ?, ?, now())");
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
	 * Sets the completed status of a task.
	 * @param $taskid Int identifier of the task
	 * @param $progress Int new completion status for the task (0-100).
	 * @param $userid Int id of the user the task belongs to.
	 * @return boolean true if modification was successful, false otherwise.
	 */
	public function setTaskCompletionStatus($taskid, $progress, $userid) {
		if (empty($taskid) || empty($progress) || empty($userid)) return false;
		$stmt = $this->conn->prepare("UPDATE tasks SET completed = ? WHERE id = ? AND user_id = ?");
		$stmt->bind_param("iii", $progress, $taskid, $userid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
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
		$stmt = $this->conn->prepare("UPDATE tasks SET description = ? WHERE id = ? AND user_id = ?");
		$stmt->bind_param("sii", $description, $taskid, $userid);
		$result = $stmt->execute();
		$stmt->close();
		return $result;
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
	public function getMessagesOfType($userid, $type) {
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
			$stmt = $this->conn->prepare("SELECT * FROM $tableName m, users u WHERE m.id = ? AND ((m.$useridfield = ? AND m.$useridfield = u.id) OR (m.$remoteuseridfield = ? AND m.$useridfield = u.id)) ");
			$stmt->bind_param("iii", $messageid, $userid, $userid);		
		} else {
			$stmt = $this->conn->prepare("SELECT * FROM $tableName m, users u WHERE m.$useridfield = ? AND m.$remoteuseridfield = u.id AND m.id = ?");
			$stmt->bind_param("ii", $userid, $messageid);
				
		}
		// execute the query
		if ($stmt->execute() === false) return NULL;
		$result = $stmt->get_result();
		$stmt->close();
		if (empty($result) || $result === false) { // sanity check
			return NULL;
		}
		
		// do we have a valid message? return it.
		if ($messageObj = $result->fetch_assoc()) { return $messageObj; }
		return NULL;
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

	/** Notificaciones */
	
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
	public function getTodayNotifications($userid) {
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
	public function getNotificationsForPastWeek($userid) {
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
	
	/**
	 * Gets the last $limit (default 10) customer statistics.
	 * @param $limit Int (default = 10) the number of statistics to retrieve, in descending order, ordered by timestamp.
	 * 
	 */	
	public function getLastCustomerStatistics($limit = 10) {
		$query = "SELECT * FROM `statistics` order by timestamp DESC limit 10";
		$result = $this->conn->query($query);
		if ($result === false) {
			return array();
		} else {
			$stats = array();
			while ($obj = $result->fetch_assoc()) {
				array_push($stats, $obj);
			}
			return $stats;
		}
	}
	
	/** Utility functions */
	
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
