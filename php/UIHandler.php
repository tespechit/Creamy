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
require_once('LanguageHandler.php');

/**
 *  UIHandler.
 *  This class is in charge of generating the dynamic HTML code for the basic functionality of the system. 
 *  Every time a page view has to generate dynamic contact, it should do so by calling some of this class methods.
 *  UIHandler uses the Singleton pattern, thus gets instanciated by the UIHandler::getInstante().
 *  This class is supposed to work as a ViewController, stablishing the link between the view (PHP/HTML view pages) and the Controller (DbHandler).
 */
 class UIHandler {
	
	// language handler
	private $lh;
	// Database handler
	private $db;
	
	/** Predefined skel text and code */
    
	private $contactsTablePrefix = "<table id=\"contacts\" class=\"table table-bordered table-striped\">
	<thead>
		<tr>
            <th>Id</th>
            <th>name</th>
            <th>email</th>
            <th>phone</th>
            <th>id_number</th>
        </tr>
    </thead>
    <tbody>";
	private $contactsTableSuffix = "</tbody>
	<tfoot>
            <tr>
	            <th>Id</th>
	            <th>name</th>
	            <th>email</th>
	            <th>phone</th>
	            <th>id_number</th>
            </tr>
        </tfoot>
    </table>";
    
	private $usersTablePrefix = "<table id=\"contacts\" class=\"table table-bordered table-striped\">
	<thead>
		<tr>
            <th>Id</th>
            <th>name</th>
            <th>email</th>
            <th>creation_date</th>
            <th>role</th>
            <th>status</th>
            <th>action</th>
        </tr>
    </thead>
    <tbody>";
	private $usersTableSuffix = "</tbody>
	<tfoot>
        <tr>
            <th>Id</th>
            <th>name</th>
            <th>email</th>
            <th>creation_date</th>
            <th>role</th>
            <th>status</th>
            <th>action</th>
        </tr>
        </tfoot>
    </table>";
    private $taskTablePrefix = "<ul class=\"todo-list ui-sortable\">";
    
    private $taskTableSuffix = "</ul>";
    
    private $messageListPrefix = '<table class="table mailbox table-responsive" id="messagestable" name="messagestable"><thead><tr><td>selection</td><td>favorite</td><td>user</td><td>subject</td><td>date</td></tr></thead>';
    
	/** Creation and class lifetime management */

	/**
     * Returns the singleton instance of LanguageHandler.
     * @staticvar LanguageHandler $instance The LanguageHandler instance of this class.
     * @return LanguageHandler The singleton instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

	
    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        require_once dirname(__FILE__) . '/DbHandler.php';
        // opening db connection
        $this->db = new \creamy\DbHandler();
        $this->lh = \creamy\LanguageHandler::getInstance();
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
    
    
    /* Administration & user management */
    
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
		
		$adminName = $this->lh->translationFor($this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_ADMIN));
		$managerName = $this->lh->translationFor($this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_MANAGER));
		$writerName = $this->lh->translationFor($this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_WRITER));
		$readerName = $this->lh->translationFor($this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_READER));
		$guestName = $this->lh->translationFor($this->getRoleNameForRole(CRM_DEFAULTS_USER_ROLE_GUEST));
		
		return '<select id="role" name="role">
				   <option value="'.CRM_DEFAULTS_USER_ROLE_ADMIN.'"'.$selectedAdmin.'>'.$adminName.'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_MANAGER.'"'.$selectedManager.'>'.$managerName.'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_WRITER.'"'.$selectedWriter.'>'.$writerName.'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_READER.'"'.$selectedReader.'>'.$readerName.'</option>
				   <option value="'.CRM_DEFAULTS_USER_ROLE_GUEST.'"'.$selectedGuest.'>'.$guestName.'</option>				   
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
		$textForStatus = $status == 1 ? $this->lh->translationFor("disable") : $this->lh->translationFor("enable");
		$actionForStatus = $status == 1 ? "deactivate-user-action" : "activate-user-action";
		return '<div class="btn-group">
	                <button type="button" class="btn btn-danger dropdown-toggle"  data-toggle="dropdown">'.$this->lh->translationFor("choose_action_user").' '.$username.'</button>
	                <ul class="dropdown-menu" role="menu">
	                    <li><a class="edit-action" href="'.$userid.'">'.$this->lh->translationFor("edit_data").'</a></li>
	                    <li><a class="change-password-action" href="'.$userid.'">'.$this->lh->translationFor("change_password").'</a></li>
	                    <li><a class="'.$actionForStatus.'" href="'.$userid.'">'.$textForStatus.'</a></li>
	                    <li class="divider"></li>
	                    <li><a class="delete-action" href="'.$userid.'">'.$this->lh->translationFor("delete_user").'</a></li>
	                </ul>
	            </div>';
	}

    /**
     * Returns a HTML Table representation containing all the user's in the system (only relevant data).
     * @return String a HTML Table representation of the data of all users in the system.
     */
	public function getAllUsersAsTable() {
       $users = $this->db->getAllUsers();
       // is null?
       if (is_null($users)) { // error getting contacts
	       return $this->getErrorMessage($this->lh->translationFor("unable_get_user_list"));
       } else if (empty($users)) { // no contacts found
	       return $this->getWarningMessage($this->lh->translationFor("no_users_in_list"));
       } else { 
	       // we have some users, show a table
		   $result = $this->lh->translationForTerms($this->usersTablePrefix, array("name", "email", "creation_date", "role", "status", "action"));
	       
	       // iterate through all contacts
	       foreach ($users as $userData) {
	       	   $status = $userData["status"] == 1 ? $this->lh->translationFor("enabled") : $this->lh->translationFor("disabled");
	       	   $userRole = $this->lh->translationFor($this->getRoleNameForRole($userData["role"]));	
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
	       $result = $result.$this->lh->translationForTerms($this->usersTableSuffix, array("name", "email", "creation_date", "role", "status", "action")); 
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
	 * Generates the HTML with a unauthorized access. It must be included inside a <section> section.
	 */
	public function getUnauthotizedAccessMessage() {
		print '<div class="box box-danger">
				<div class="box-header">
	                <i class="fa fa-lock"></i>
	                <h3 class="box-title">'.$this->lh->translationFor("access_denied").'</h3>
	            </div>
				<div class="box-body" id="graph-box">
					<div class="callout callout-danger">
						<p>'.$this->lh->translationFor("you_dont_have_permission").'</p>
					</div>
				</div>
			   </div>';
	}
	
	/**
	 * Generates the HTML code for editing the profile of a user as  an HTML form. Depends on the user having the right permissions.
	 * @param $usertoeditid ID of the user to edit
	 * @param $requestinguserid ID of the user requesting the edit form for the user $usertoeditid
	 * @param $requestinguserrole user role (permissions) for the user requesting the for, identified by $requestinguserid
	 * @return A HTML containing the edit user form if permissions are correct, an error message otherwise.
	 */
	public function getEditUserForm($usertoeditid, $requestinguserid, $requestinguserrole) {
		$userobj = NULL;
		$errormessage = NULL;
		
		if (!empty($usertoeditid)) {
			if (($requestinguserid == $usertoeditid) || (userHasAdminPermission($requestinguserrole))) { 
    			// if it's the same user or we have admin privileges.
    			$userobj = $this->db->getDataForUser($usertoeditid);
			} else {
    			$errormessage = $this->lh->translationFor("not_permission_edit_user_information");
			}
		} else {
    		$errormessage = $this->lh->translationFor("unknown_error");
		}
		
		if (!empty($userobj)) {
			// current user avatar
			$currentUserAvatar = empty($userobj["avatar"]) ? "" : 
				"<img src=\"".$userobj["avatar"]."\" class=\"img-circle\" width=\"100\" height=\"100\" alt=\"User Image\" /><br>";
			// if requesting user is admin, we can change the user role
			$setUserRoleCode = "";
			if (userHasAdminPermission($requestinguserrole)) {
				$userRolesAsFormSelect = $this->getUserRolesAsFormSelect($userobj["role"]);
				$setUserRoleCode = '<div class="form-group"><label for="role">'.$this->lh->translationFor("user_role").'</label>'.$userRolesAsFormSelect.'</div>';
			}	
						
			$result = '<div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">'.$this->lh->translationFor("insert_new_data").'</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" id="modifyuser" name="modifyuser" method="post" action=""  enctype="multipart/form-data">
                                	<input type="hidden" id="modifyid" name="modifyid" value="'.$usertoeditid.'">
                                    <div class="box-body">
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                        <input type="text" id="name" name="name" class="form-control required" placeholder="'.
	                                        $this->lh->translationFor("name").'" value="'.$userobj["name"].'" disabled>
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                        <input type="text" id="email" name="email" class="form-control"placeholder="'.
	                                        $this->lh->translationFor("email").' ('.$this->lh->translationFor("optional").')'.'" value="'.$userobj["email"].'">
	                                    </div>
	                                    <br>
	                                    <div class="input-group">
	                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                        <input type="text" id="phone" name="phone" class="form-control" placeholder="'.
	                                        $this->lh->translationFor("phone").' ('.$this->lh->translationFor("optional").')'.'" value="'.$userobj["phone"].'">
	                                    </div>
	                                    <br>
                                        <div class="form-group">
                                            <label for="exampleInputFile">'.
                                            $this->lh->translationFor("user_avatar").' ('.$this->lh->translationFor("optional").')'.'
                                            </label><br>
                                            '.$currentUserAvatar.'
                                            <br>
                                            <input type="file" id="avatar" name="avatar">
                                            <p class="help-block">'.$this->lh->translationFor("choose_image").'</p>
                                        </div>
										'.$setUserRoleCode.'
	                                    <br>
	                                    <div  id="resultmessage" name="resultmessage" style="display:none">
	                                    </div>
                                    </div><!-- /.box-body -->
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">'.$this->lh->translationFor("edit_user").'</button>
                                    </div>
                                </form>
                            </div><!-- /.box -->';
                return $result;
		} else {
			return $this->getErrorMessage($errormessage);
		}
	}

	/** Warnings and messages */
	
	/**
	 * Generates a info message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getInfoMessage($message) {
		return "<div class=\"callout callout-info\">\n\t<h4>".$this->lh->translationFor("message")."</h4>\n\t<p>$message</p>\n</div>\n";	
	}

	/**
	 * Generates a warning message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getWarningMessage($message) {
		return "<div class=\"callout callout-warning\">\n\t<h4>".$this->lh->translationFor("warning")."</h4>\n\t<p>$message</p>\n</div>\n";	
	}

	/**
	 * Generates a error message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	function getErrorMessage($message) {
		return "<div class=\"callout callout-danger\">\n\t<h4>".$this->lh->translationFor("error")."</h4>\n\t<p>$message</p>\n</div>\n";	
	}
	
	/**
	 * Generates a error modal message HTML dialog, with the given message.
	 * @param message String the message to show.
	 */
	function getErrorModalMessage($message, $header) {
		$result = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> '.$header.'</h4>
		            </div><div class="modal-body">';
		$result = $result.$this->getErrorMessage($message);
		$result = $result.'</div><div class="modal-footer clearfix"><button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> '.
		$this->lh->translationFor("exit").'</button></div></div></div>';
		return $result;
	}

	/** Notifications */

	/**
	 * Generates the HTML for the message notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	function getMessageNotifications($userid, $userrole) {
		if (!userHasBasicPermission($userrole)) return '';

        $list = $this->db->getMessagesOfType($userid, MESSAGES_GET_UNREAD_MESSAGES);
		$numMessages = count($list);
		
		$result = '<li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-envelope"></i>
                    <span class="label label-success">'.$numMessages.'</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">'.$this->lh->translationFor("you_have").' '.$numMessages.' '.$this->lh->translationFor("unread_messages").'</li>
                    <li>
                            <ul class="menu">';
        
        foreach ($list as $message) {
	        if (empty($message["remote_avatar"])) $remoteavatar = CRM_DEFAULTS_USER_AVATAR;
	        else $remoteavatar = $message["remote_avatar"];
	        $relativeTime = $this->relativeTime($message["date"], 1);
	        $shortText = $this->substringUpTo($message["message"], 40);
	        
	        $result = $result.'
	        <li><a href="messages.php">
                    <div class="pull-left">
                        <img src="'.$remoteavatar.'" class="img-circle" alt="User Image"/>
                    </div>
                    <h4>
                    <small class="label"> <i class="fa fa-clock-o"></i> '.$relativeTime.'</small>
                        '.$message["remote_user"].' 
                    </h4>
                    <p>'.$shortText.'</p>
                </a>
            </li>';
        }
        $result = $result.'</ul></li><li class="footer"><a href="messages.php">'.$this->lh->translationFor("see_all_messages").'</a></li></ul></li>';
        print $result;
	}
	
	/**
	 * Generates the HTML containing the label for new notifications, i.e: "7 new", to show in the index box.
	 * @param $userid Int the id of the user to retrieve the new notifications from.
	 */
	public function generateLabelForTodayNotifications($userid) {
		return $this->db->getNumberOfTodayNotifications($userid)." ".$this->lh->translationFor("new");
	}

	/**
	 * Generates the HTML containing the label for new contacts, i.e: "7 new", to show in the index box.
	 */
	public function generateLabelForNewContacts() {
		return $this->db->getNumberOfNewContacts()." ".$this->lh->translationFor("new");
	}

	/**
	 * Generates the HTML containing the label for new customers, i.e: "7 new", to show in the index box.
	 */
	public function generateLabelForNewCustomers() {
		return $this->db->getNumberOfNewCustomers()." ".$this->lh->translationFor("new");
	}

	/**
	 * Generates the HTML for the alert notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	public function getAlertNotifications($userid, $userrole) {
		if (!userHasBasicPermission($userrole)) return '';
		
		$notifications = $this->db->getTodayNotifications($userid);
		if (empty($notifications)) $notificationNum = 0;
		else $notificationNum = count($notifications);
		
		$result = '<li class="dropdown notifications-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-warning"></i>
                    <span class="label label-warning">'.$notificationNum.'</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="header">'.$this->lh->translationFor("you_have").' '.$notificationNum.' '.strtolower($this->lh->translationFor("notifications")).'</li><li>
                        <ul class="menu">';
                        
        foreach ($notifications as $notification) {
	        $result = $result.'<li style="text-align: left; !important">
                                <a href="notifications.php">
                                     <i class="fa '.$this->notificationIconForNotificationType($notification["type"]).' '.$this->getRandomColorForNotification().'"></i> '.$this->substringUpTo($notification["text"], 40).'
                                </a>
                            </li>';
        }                                        
        $result = $result.'</ul></li><li class="footer"><a href="notifications.php">'.$this->lh->translationFor("see_all_notifications").'</a></li></ul></li>';
        return $result;
	}
	
	public function getTaskNotifications($userid, $userrole) {
		if (!userHasBasicPermission($userrole)) return '';

		$list = $this->db->getUnfinishedTasks($userid);
		$numTasks = count($list);
		
		$result = '<li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-tasks"></i>
                                <span class="label label-danger">'.$numTasks.'</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">'.$this->lh->translationFor("you_have").' '.$numTasks.' '.$this->lh->translationFor("pending_tasks").'</li>
                                <li>
                                    <ul class="menu">';
                                    
        foreach ($list as $task) {
	        $shortText = $this->substringUpTo($task["description"], 35);
	        $relativeTime = $this->relativeTime($task["creation_date"], 1);
	        
	        
	        $result = $result.'<li><!-- Task item -->
	            <a href="tasks.php">
                    <h3>
                        <p class="pull-left">'.$shortText.'</p>
                        <small class="label label-warning"><i class="fa fa-clock-o"></i> '.$relativeTime.'</small>
                    </h3>
	            </a>
	        </li><!-- end task item -->';
        }
                                    
        $result = $result.'</ul></li><li class="footer"><a href="tasks.php">'.$this->lh->translationFor("see_all_tasks").'</a></li></ul></li>';
        return $result;

        return '';
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

	/** Basic UI Elements */
	
	/**
	 * Generates the HTML for the user's topbar menu.
	 * @param $userid the id of the user.
	 */
	public function getTopbarItems($userid, $username, $avatar, $userrole) {
		// menu actions (only for users with permissions).
		$menuActions = '';
		if (userHasBasicPermission($userrole)) $menuActions = '<li class="user-body">
									<div class="text-center">
									    <a href="" data-toggle="modal" data-target="#change-password-dialog-modal">'.$this->lh->translationFor("change_password").'</a>
									</div>
									<div class="text-center">
									    <a href="./messages.php">'.$this->lh->translationFor("messages").'</a>
									</div>
									<div class="text-center">
									        <a href="./notificationes.php">'.$this->lh->translationFor("notifications").'</a>
									    </div>
									<div class="text-center">
									        <a href="./tasks.php">'.$this->lh->translationFor("tasks").'</a>
								    </div>
								</li>';
		
		// change my data (only for users with permissions).
		$changeMyData = '';
		if (userHasBasicPermission($userrole)) 
			$changeMyData = '<div class="pull-left"><a href="./edituser.php" class="btn btn-default btn-flat">'.$this->lh->translationFor("my_profile").'</a></div>';
		
		return '<li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span>'.$username.' <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="'.$avatar.'" class="img-circle" alt="User Image" />
                                    <p>
                                        '.$username.'
                                        <small>'.$this->lh->translationFor("nice_to_see_you_again").'</small>
                                    </p>
                                </li>'.$menuActions.'
                                <li class="user-footer">
                                    '.$changeMyData.'
                                    <div class="pull-right">
                                        <a href="./logout.php" class="btn btn-default btn-flat">'.$this->lh->translationFor("exit").'</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
		';
	}

	/** Sidebar */
	
	/**
	 * Generates the HTML for the sidebar of a user, given its role.
	 * @param $userid the id of the user.
	 */
	public function getSidebar($userid, $username, $userrole, $avatar) {
		$numMessages = $this->db->getUnreadMessagesNumber($userid);
		$numTasks = $this->db->getUnfinishedTasksNumber($userid);
		$numNotifications = $this->db->getNumberOfTodayNotifications($userid);
		
		$adminArea = "";
		if ($userrole == CRM_DEFAULTS_USER_ROLE_ADMIN) {
			$adminArea = '
				<li class="treeview">
                    <a href="#">
                        <i class="fa fa-dashboard"></i> <span>'.$this->lh->translationFor("administration").'</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="./adminusers.php"><i class="fa fa-users"></i> '.$this->lh->translationFor("users").'</a></li>
                    </ul>
                </li>';
		}
		
		// get customer types
		$customerTypes = $this->db->getCustomerTypes();
		
		// prefix: structure and home link
		print '<aside class="left-side sidebar-offcanvas">
                <section class="sidebar">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <a href="edituser.php"><img src="'.$avatar.'" class="img-circle" alt="User Image" /></a>
                        </div>
                        <div class="pull-left info">
                            <p>'.$this->lh->translationFor("hello").', '.$username.'</p>
                            <a href="edituser.php"><i class="fa fa-circle text-success"></i> '.$this->lh->translationFor("online").'</a>
                        </div>
                    </div>
                    <ul class="sidebar-menu">
                        <li>
                            <a href="./index.php">
                                <i class="fa fa-bar-chart-o"></i> <span>'.$this->lh->translationFor("home").'</span>
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
                                <i class="fa fa-envelope"></i> <span>'.$this->lh->translationFor("messages").'</span>
                                <small class="badge pull-right bg-green">'.$numMessages.'</small>
                            </a>
                        </li>
						<li>
                            <a href="./notifications.php">
                                <i class="fa fa-exclamation"></i> <span>'.$this->lh->translationFor("notifications").'</span>
                                <small class="badge pull-right bg-orange">'.$numNotifications.'</small>
                            </a>
                        </li>
						<li>
                            <a href="./tasks.php">
                                <i class="fa fa-tasks"></i> <span>'.$this->lh->translationFor("tasks").'</span>
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

	/** Customers */
   	
   	/**
	 * Generates the HTML code for a table containing an array of contacts or customers.
	 * @param $contacts Array the array with the objects representing the users.
	 * @param $customerType the type of customer
	 */
   	private function getCustomerListAsTable($customers, $customerType) {
	   	// print prefix
       $result = $this->lh->translationForTerms($this->contactsTablePrefix, array("name", "email", "phone", "id_number"));
       
       foreach ($customers as $customer) {
	       $nameOrNonamed = $customer["name"];
	       if (empty ($nameOrNonamed) || strlen($nameOrNonamed) < 1) $nameOrNonamed = "(".$this->lh->translationFor("no_name").")";
	       
	       $result = $result."<tr>
                    <td>".$customer["id"]."</td>
                    <td><a href=\"editcustomer.php?customerid=".$customer["id"]."&customer_type=".$customerType."\" >".$nameOrNonamed."</a></td>
                    <td>".$customer["email"]."</td>
                    <td>".$customer["phone"]."</td>
                    <td>".$customer["id_number"]."</td>
                </tr>";
       }
       
       // print suffix
       $result = $result.$this->lh->translationForTerms($this->contactsTableSuffix, array("name", "email", "phone", "id_number"));
       return $result;
   	}

  	/**
	 * Generates the HTML with the HTML table for an array of contacts or customers.
	 * @param $contacts Array the array with the objects representing the users.
	 * @param $customerType the type of customer
	 */
	public function getAllCustomersOfTypeAsTable($customerType) {
       $customers = $this->db->getAllCustomersOfType($customerType);
       // is null?
       if (is_null($customers)) { // error getting customers
	       return $this->getErrorMessage($this->lh->translationFor("unable_get_customer_list"));
       } else if (empty($customers)) { // no customers found
	       return $this->getWarningMessage($this->lh->translationFor("no_customers_in_list"));
       } else { // we have some customers, show a table
		   return $this->getCustomerListAsTable($customers, $customerType);
       }
	}	

   	
   	/**
	 * Generates the HTML code for the editing customer form.
	 * @param customerId Int the id of the customer to edit
	 * @param customerType String the table name (= customer type identifier) of the customer to edit. 
	 */
	public function generateCustomerEditionForm($customerid, $customerType, $userrole) {
		$customerobj = NULL;
		$errormessage = NULL;
		
		if (isset($customerid) && isset($customerType)) {
			$customerobj = $this->db->getDataForCustomer($customerid, $customerType);
		} else {
    		$errormessage = $this->lh->translationFor("some_fields_missing");
		}
		
		if (!empty($customerobj)) {

			// marital status
            $currentMS = 0;
            if (!empty($customerobj["marital_status"])) {
                $currentMS = $customerobj["marital_status"];
                if ($currentMS < 1) $currentMS = 0;
                if ($currentMS > 5) $currentMS = 0;
            }
            $msSelected0 = $currentMS == 0 ?  "selected" : "";
            $msSelected1 = $currentMS == 1 ?  "selected" : "";
            $msSelected2 = $currentMS == 2 ?  "selected" : "";
            $msSelected3 = $currentMS == 3 ?  "selected" : "";
            $msSelected4 = $currentMS == 4 ?  "selected" : "";
			$msSelected5 = $currentMS == 5 ?  "selected" : "";

			// gender
	        $currentGender = -1;
	        if (!empty($customerobj["gender"])) {
	            $currentGender = $customerobj["gender"];
	            if ($currentGender < 0) $currentGender = -1;
	            if ($currentGender > 1) $currentGender = -1;
	        }
	        $cgSelectedDefault = $currentGender == -1 ? "selected" : "";
	        $cgSelected0 = $currentGender == 0 ? "selected" : "";
	        $cgSelected1 = $currentGender == 1 ? "selected" : "";
	        
	        // date as dd/mm/yyyy
			$dateAsDMY = "";
	        if (!empty($customerobj["birthdate"])) { 
	            $time = strtotime($customerobj["birthdate"]);
	            $dateAsDMY = date('d/m/Y', $time); 
	        }

			// buttons at bottom (only for writing+ permissions)
			$buttons = "";
			if (userHasWritePermission($userrole)) {
				$buttons = '<div class="modal-footer clearfix">
	                        <button type="button" class="btn btn-danger" data-dismiss="modal" id="modifyCustomerDeleteButton" href="'.
	                        $customerid.'"><i class="fa fa-times"></i> '.$this->lh->translationFor("delete").'</button>
	                        <button type="submit" class="btn btn-primary pull-left" id="modifyCustomerOkButton"><i class="fa fa-check-circle"></i> '.
	                        $this->lh->translationFor("modify").'</button>
	                    </div>';
			}

			// do not send email
			$doNotSendEmail = empty($customerobj["do_not_send_email"]) ? "" : "checked";

            return '<div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Introduzca los nuevos datos</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
	                <form action="" method="post" name="modifycustomerform" id="modifycustomerform">
	                    <div class="modal-body">
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
	                                <input name="name" id="name" type="text" class="form-control" value="'.
	                                $customerobj["name"].'" placeholder="'.$this->lh->translationFor("name").' ('.$this->lh->translationFor("mandatory").')'.'">
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-medkit"></i></span>
	                                <input name="productType" id="productType" value="'.
	                                $customerobj["type"].'" type="text" class="form-control" placeholder="'.$this->lh->translationFor("customer_or_service_type").'">
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
	                                <input name="id_number" id="id_number" type="text" class="form-control" placeholder="'.
	                                $this->lh->translationFor("id_number").'" value="'.$customerobj["id_number"].'">
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                <input name="email" id="email" type="text" class="form-control" placeholder="'.
	                                $this->lh->translationFor("email").'" value="'.$customerobj["email"].'">
	                            </div>                  
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
	                                <input name="phone" id="phone" type="text" class="form-control" placeholder="'.
	                                $this->lh->translationFor("home_phone").'" value="'.$customerobj["phone"].'">
	                            </div>                  
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-mobile"></i></span>
	                                <input name="mobile" id="mobile" type="text" class="form-control" placeholder="'.
	                                $this->lh->translationFor("mobile_phone").'" value="'.$customerobj["mobile"].'">
	                            </div>                  
	                        </div>
	                        <div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
	                                <input name="address" id="address" type="text" class="form-control" placeholder="'.
	                                $this->lh->translationFor("address").'" value="'.$customerobj["address"].'">
	                            </div>                  
	                        </div>
	                        <div class="form-group">
	                            <div class="row">
								<div class="col-lg-6">
		                            <div class="input-group">
		                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
		                                <input name="city" id="city" type="text" class="form-control" placeholder="'.
		                                $this->lh->translationFor("city").'" value="'.$customerobj["city"].'">
		                            </div>
		                        </div><!-- /.col-lg-6 -->
		                        <div class="col-lg-6">
		                            <div class="input-group">
		                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
		                                <input name="state" id="state" type="text" class="form-control" placeholder="'.
		                                $this->lh->translationFor("estate").'" value="'.$customerobj["state"].'">
		                            </div>                        
		                        </div><!-- /.col-lg-6 -->
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="row">
								<div class="col-lg-6">
		                            <div class="input-group">
		                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
		                                <input name="zipcode" id="zipcode" type="text" class="form-control" placeholder="'.
		                                $this->lh->translationFor("zip_code").'" value="'.$customerobj["zip_code"].'">
		                            </div>
		                        </div><!-- /.col-lg-6 -->
		                        <div class="col-lg-6">
		                            <div class="input-group">
		                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
		                                <input name="country" id="country" type="text" class="form-control" placeholder="'.
		                                $this->lh->translationFor("country").'" value="'.$customerobj["country"].'">
		                            </div>                        
		                        </div><!-- /.col-lg-6 -->
	                            </div>
	                        </div>
							<div class="form-group">
	                            <div class="input-group">
	                                <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
	                                <textarea id="notes" name="notes" placeholder="'.
	                                $this->lh->translationFor("notes").'" class="form-control">'.$customerobj["notes"].'</textarea>
	                            </div>                  
	                        </div>
	                        <div class="form-group">
	                            <label>'.$this->lh->translationFor("marital_status").'</label>
	                            <select class="form-control" id="maritalstatus" name="maritalstatus">
									<option value="0" '.$msSelected0.'>'.$this->lh->translationFor("choose_an_option").'</option>
	                                <option value="1" '.$msSelected1.'>'.$this->lh->translationFor("single").'</option>
	                                <option value="2" '.$msSelected2.'>'.$this->lh->translationFor("married").'</option>
	                                <option value="3" '.$msSelected3.'>'.$this->lh->translationFor("divorced").'</option>
	                                <option value="4" '.$msSelected4.'>'.$this->lh->translationFor("separated").'</option>
	                                <option value="5" '.$msSelected5.'>'.$this->lh->translationFor("widow").'</option>
	                            </select>
	                        </div>
							<div class="form-group">
	                            <label>Sexo</label>
	                            <select class="form-control" id="gender" name="gender">
									<option value="-1" '.$cgSelectedDefault.'><'.$this->lh->translationFor("choose_an_option").'</option>
	                                <option value="0" '.$cgSelected0.'>'.$this->lh->translationFor("female").'</option>
	                                <option value="1" '.$cgSelected1.'>'.$this->lh->translationFor("male").'</option>
	                            </select>
	                        </div>
	                        <div class="form-group">
	                            <label>'.$this->lh->translationFor("birthdate").':</label>
	                            <div class="input-group">
	                                <div class="input-group-addon">
	                                    <i class="fa fa-calendar"></i>
	                                </div>
	                                <input name="birthdate" id="birthdate" type="text" class="form-control" data-inputmask="\'alias\': \'dd/mm/yyyy\'" data-mask value="'.$dateAsDMY.'" placeholder="dd/mm/yyyy"/>
	                            </div><!-- /.input group -->
	                        </div><!-- /.form group -->                        
	                        <div class="form-group">
	                            <div class="checkbox">
	                                <label><input name="donotsendemail" id="donotsendemail" type="checkbox" '.$doNotSendEmail.'/> 
	                                '.$this->lh->translationFor("do_not_send_email").'</label>
	                            </div>
	                        </div>
							<input type="hidden" id="customer_type" name="customer_type" value="'.$customerType.'">
							<input type="hidden" id="customerid" name="customerid" value="'.$customerid.'">
							<div id="modifycustomerresult" name="modifycustomerresult"></div>
	                    </div>
	                    '.$buttons.'
	                </form>
                </div><!-- /.box -->';

		} else {
			print $this->getErrorMessage($errormessage);
		}
		
	}
   	
	/** Tasks */

	/**
	 * Generates the HTML for a given task as a table row
	 * @param $task Array associative array representing the task object.
	 * @return String the HTML representation of the task as a row.
	 */
	private function getTaskAsIndividualRow($task) {
		// define progress and bar color
		$completed = $task["completed"];
		if ($completed < 0) $completed = 0;
		else if ($completed > 100) $completed = 100;
		$creationdate = $this->relativeTime($task["creation_date"]);
		// values dependent on completion of the task.
		$doneOrNot = $completed == 100 ? 'class="done"' : '';
		$completeActionCheckbox = $completed == 100 ? '' : '<input type="checkbox" value="" name="" style="position: absolute; opacity: 0;">';
		
		return '<li id="'.$task["id"].'" '.$doneOrNot.'>
			  	  '.$completeActionCheckbox.'
				  <span class="text">'.$task["description"].'</span>
				  <small class="label label-warning pull-right"><i class="fa fa-clock-o"></i> '.$creationdate.'</small>
				  <div class="tools">
						<a class="edit-task-action" href="'.$task["id"].'" data-toggle="modal" data-target="#edit-task-dialog-modal">
						<i class="fa fa-edit task-item"></i>
						</a>
						<a class="delete-task-action" href="'.$task["id"].'">
							<i class="fa fa-trash-o"></i>
						</a>
				  </div>
			 </li>';
	}

	/**
	 * Generates the HTML for a all tasks of a given user as a table row
	 * @param $userid Int id of the user to retrieve the tasks from.
	 * @return String the HTML representation of the user's tasks as a table.
	 */
	public function getCompletedTasksAsTable($userid, $userrole) { 
		$tasks = $this->db->getCompletedTasks($userid);
		if (empty($tasks)) { return $this->getInfoMessage($this->lh->translationFor("you_dont_have_completed_tasks")); }
		else {
			$list = $this->taskTablePrefix;
			foreach ($tasks as $task) {
				// generate row
				$taskHTML = $this->getTaskAsIndividualRow($task);
				$list = $list.$taskHTML;
			}
			
			$list = $list.$this->taskTableSuffix;
	    	return $list;
		}
   	}

	/**
	 * Generates the HTML for a all tasks of a given user as a table row
	 * @param $userid Int id of the user to retrieve the tasks from.
	 * @return String the HTML representation of the user's tasks as a table.
	 */
	public function getUnfinishedTasksAsTable($userid, $userrole) { 
		$tasks = $this->db->getUnfinishedTasks($userid);
		if (empty($tasks)) { return $this->getInfoMessage($this->lh->translationFor("you_dont_have_pending_tasks")); }
		else {
			$list = $this->taskTablePrefix;
			foreach ($tasks as $task) {
				// generate row
				$taskHTML = $this->getTaskAsIndividualRow($task);
				$list = $list.$taskHTML;
			}
			
			$list = $list.$this->taskTableSuffix;
	    	return $list;
		}
   	}
	
	/** Messages */

	/**
	 * Generates the list of users $myuserid can send message to or assign a task to as a HTML form SELECT.
	 * @param Int $myuserid id of the user that wants to send messages, all other user's ids will be returned.
	 * @param Boolean $includeSelf if true, $myuserid will appear listed in the options. If false (default), $myuserid will not be included in the options. If this parameter is set to true, the default option will be the $myuserid
	 * @param String $customMessage The custome message to ask for a selection in the SELECT, default is "send this message to...".
	 * @return the list of users $myuserid can send mail to (all valid users except $myuserid unless $includeSelf==true) as a HTML form SELECT.
	 */
	public function generateSendToUserSelect($myuserid, $includeSelf = false, $customMessage = NULL) {
		// perform query of users.
		if (empty($customMessage)) $customMessage = $this->lh->translationFor("send_this_message_to");
		$usersarray = $this->db->getAllEnabledUsers();

		// iterate through all users and generate the select
		$response = '<select class="form-control" id="touserid" name="touserid">\n\t<option value="0">'.$customMessage.'</option>\n';
		foreach ($usersarray as $userobj) {
			// don't include ourselves.
			if ($userobj["id"] != $myuserid) {
				$response = $response.'\t<option value="'.$userobj["id"].'">'.$userobj["name"].'</option>\n';
			} else if ($includeSelf === true) { // assign to myself by default
				$response = $response.'\t<option value="'.$userobj["id"].'" selected="true">myself</option>\n';
			}	
		}
		$response = $response.'</select>';
		return $response;
	}
	
	/**
	 * Generates the HTML of the given messages as a HTML table, from a table array
	 * @param Array $messages the list of messages.
	 * @return the HTML code with the list of messages as a HTML table. 
	 */
	private function getMessageListAsTable($messages) {
		// generate the table.
		$result = $this->lh->translationForTerms($this->messageListPrefix, array("selection", "favorite", "user", "date", "subject"));
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
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_INBOX_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("unable_get_messages"));
		else return $this->getMessageListAsTable($messages);
	}
	
	/**
	 * Generates a HTML table with the unread messages of the user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getUnreadMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_UNREAD_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with with the junk messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getJunkMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_DELETED_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the sent messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getSentMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_SENT_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
				
	/**
	 * Generates a HTML table with the favourite messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getFavoriteMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_FAVORITE_MESSAGES);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the messages from given folder for a user.
	 * @param Int $userid user to retrieve the messages from
	 * @param Int $folder folder to retrieve the messages from
	 */
	public function getMessagesFromFolderAsTable($userid, $folder) {
		$messages = $this->db->getMessagesOfType($userid, $folder);
		if ($messages == NULL) return $this->getInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
	
	/**
	 * Generates the HTML with the list of message folders as <li> items.
	 * @param $activefolder String current active folder the user is in.
	 * @return String the HTML with the list of message folders as <li> items.
	 */
	public function getMessageFoldersAsList($activefolder) {
        $unreadMessages = $this->db->getUnreadMessagesNumber($_SESSION["userid"]);
        $activeInbox = $activefolder == MESSAGES_GET_INBOX_MESSAGES ? 'class="active"' : '';
        $activeSent = $activefolder == MESSAGES_GET_SENT_MESSAGES ? 'class="active"' : '';
        $activeFavorite = $activefolder == MESSAGES_GET_FAVORITE_MESSAGES ? 'class="active"' : '';
        $activeDeleted = $activefolder == MESSAGES_GET_DELETED_MESSAGES ? 'class="active"' : '';
        
		return '<li class="header">'.$this->lh->translationFor("folders").'</li>
        <li '.$activeInbox.'><a href="messages.php?folder=0"><i class="fa fa-inbox"></i> '.$this->lh->translationFor("inbox").' ('.$unreadMessages.')</a></li>
        <li '.$activeSent.'><a href="messages.php?folder=3"><i class="fa fa-mail-forward"></i> '.$this->lh->translationFor("sent").'</a></li>
        <li '.$activeFavorite.'><a href="messages.php?folder=4"><i class="fa fa-star"></i> '.$this->lh->translationFor("favorites").'</a></li>
        <li '.$activeDeleted.'><a href="messages.php?folder=2"><i class="fa fa-folder"></i> '.$this->lh->translationFor("trash").'</a></li>';
	}

	/**
	 * Generates a modal dialog HTML code for a given message of a given id. If the message is not found or an error occurrs, a error modal message is generated.
	 * @param $userid Int the user identifier the message belongs to
	 * @param $messageid Int the identifier for the message.
	 * @param $folder Int identifier of the mail folder the message is contained in.
	 * @return the modal dialog HTML code. 
	 */
	public function getMessageModalDialogAsHTML($userid, $messageid, $folder) {
		$obj = $this->db->getSpecificMessage($userid, $messageid, $folder); // get message object
	
		// generate the message modal dialog to show the message.
		if (isset($obj)) {
			$messageid = $obj["id"]; 
			$fromuserid = $obj["user_from"]; 
			$touserid = $obj["user_to"]; 
			$subject = $obj["subject"]; 
			$text = $obj["message"]; 
			$messagedate = $obj["date"]; 
			$remoteusername = $obj["name"]; 
			$fromortodestination = ($fromuserid == $userid)? $this->lh->translationFor("to")." $remoteusername." : $this->lh->translationFor("from")." $remoteusername.";
			$relativeTime = $this->relativeTime($messagedate);
		
			return '
			<div class="modal-dialog">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> '.$this->lh->translationFor("message").' '.$fromortodestination.'</h4>
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
		                        <textarea name="message" id="message" class="form-control" placeholder="'.$this->lh->translationFor("message").'" style="height: 120px;" readonly>'.$text.'
		                        </textarea>
		                    </div>
		                </div>
		                <input type="hidden" id="messageid" name="messageid" value="'.$messageid.'">
		                <div class="modal-footer clearfix">
		                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> '.$this->lh->translationFor("exit").'</button>
		                </div>
		            </form>
		        </div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->';
    	} else {
			return $this->getErrorModalMessage($this->lh->translationFor("unable_get_message"), $this->lh->translationFor("error_getting_message"));
    	}

	} // end function

	/** Notifications */

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
		if ($type == "contact") return $this->lh->translationFor("see_customer");
		else if ($type == "message") return $this->lh->translationFor("read_message");
		else return $this->lh->translationFor("see_more");
	}
	
	/**
	 * Returns the HTML header text for notifications of certain type associated to certain action.
	 * @param $type String the type of notification.
	 * @param $action String a URL with the action to perform for this notification.
	 * @return String the string with the header text for this notification type.
	 */
	private function headerTextForNotificationType($type, $action) {
		if ($type == "contact") 
		return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("contact") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("contact")."</a>";
		else if ($type == "message") 
			return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("message") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("message")."</a>";

		return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("event") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("event")."</a>";
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

		return '<li>
                    <i class="fa '.$icon.' '.$color.'"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> '.$relativetime.'</span>
                        <h3 class="timeline-header no-border">'.$this->headerTextForNotificationType($type, $action).'</h3>
						<div class="timeline-body">
						'.$texto.'
						</div>
                        '.$actionHTML.'
                    </div>
                </li>';
	}
	
	/**
	 * Generates the HTML code for the given notification.
	 * @param $notification Array an associative array object containing the notification data.
	 * @return String a HTML representation of the notification.
	 */
	public function getNotificationsAsTimeLine($userid) {
		setlocale(LC_ALL, CRM_LOCALE);
		$todayAsDate = strftime("%x");
		$todayAsText = $this->lh->translationFor("today")." ($todayAsDate)";
		
		// today
		$timeline = '<ul class="timeline">
	                    <li class="time-label">
	                        <span class="bg-green">
	                            '.$todayAsText.'
	                        </span>
	                    </li>';
		
		$notifications = $this->db->getTodayNotifications($userid);
		if (empty($notifications)) {
			$timeline = $timeline.'<li><div class="timeline-item">'.$this->getInfoMessage($this->lh->translationFor("no_notifications_today")).'</div></li>';
		} else {
			foreach ($notifications as $notification) {
				$timeline = $timeline.$this->timelineItemForNotification($notification);
			}
		}
		
        // past week
		$timeline = $timeline.'<li class="time-label">
	                        <span class="bg-yellow">
	                            '.$this->lh->translationFor("past_week").'
	                        </span>
	                    </li>';

        $notifications = $this->db->getNotificationsForPastWeek($userid);
		if (empty($notifications)) {
			$timeline = $timeline.'<li><div class="timeline-item">'.$this->getInfoMessage($this->lh->translationFor("no_notifications_past_week")).'</div></li>';
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

	/** Statistics */

	/** 
	 * Generates the HTML to be used as input for the statistics table generated by JS in index.php
	*/
	public function getStatisticsData() {
		// format: {y: '2011 Q1', item1: 2666, item2: 2666},
		$statistics = "";
		$statsArray = $this->db->getLastCustomerStatistics();
		foreach ($statsArray as $obj) {
			$formattedDate = date("Y-m",strtotime($obj['timestamp']));
			$numContacts = $obj["clients_1"];
			$numCustomers = 0;
			$customerTypes = $this->db->getCustomerTypes();
			foreach ($customerTypes as $customerType) {
				$customerTableName = $customerType["table_name"];
				if ($customerTableName !== "clients_1") { // do not include contacts in the customers row
					if (isset($obj[$customerTableName])) $numCustomers += $obj[$customerTableName];
				}
			}
			// add the statistics line
			$statistics = $statistics . "{y: '$formattedDate', item1: $numContacts, item2: $numCustomers}, ";
		}
		return $statistics;
	}
	
	/** Utility functions */

	/**
	 * Generates a relative time string for a given date, relative to the current time.
	 * @param $mysqltime String a string containing the time extracted from MySQL.
	 * @param $maxdepth Int the max depth to dig when representing the time, 
	 *        i.e: 3 days, 4 hours, 1 minute and 20 seconds with $maxdepth=2 would be 3 days, 4 hours.
	 * @return String the string representation of the time relative to the current date.
	 */
	private function relativeTime($mysqltime, $maxdepth = 1) {
		$time = strtotime(str_replace('/','-', $mysqltime));
	    $d[0] = array(1,$this->lh->translationFor("second"));
	    $d[1] = array(60,$this->lh->translationFor("minute"));
	    $d[2] = array(3600,$this->lh->translationFor("hour"));
	    $d[3] = array(86400,$this->lh->translationFor("day"));
	    $d[4] = array(604800,$this->lh->translationFor("week"));
	    $d[5] = array(2592000,$this->lh->translationFor("month"));
	    $d[6] = array(31104000,$this->lh->translationFor("year"));
	
	    $w = array();
	
		$depth = 0;
	    $return = "";
	    $now = time();
	    $diff = ($now-$time);
	    $secondsLeft = $diff;
	
		if ($secondsLeft == 0) return "now";
	
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
	
	    $verb = ($diff>0)?"":"in ";
	    $return = $verb.$return;
	    return $return;
	}
	
	private function substringUpTo($string, $maxCharacters) {
		if (empty($maxCharacters)) $maxCharacters = 4;
		else if ($maxCharacters < 1) $maxCharacters = 4;
		return (strlen($string) > $maxCharacters) ? substr($string, 0, $maxCharacters-3).'...' : $string;
	}
	


}	
	
?>