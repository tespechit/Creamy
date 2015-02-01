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
 
/**
 * CRMDefaults.php
 * 
 * This is the main definitions file for the CRM, it carries all global
 * definitions that must be accessed by the rest of files of the CRM.
 * @author Ignacio Nieto Carvajal
 * @link URL http://digitalleaves.com
 */

// global constants
define('CRM_SKEL_CONFIG_FILE', 'skel/Config.php');
define('CRM_INSTALLED_FILE', './installed.txt');
define('CRM_GETTING_STARTED_FILE', './help/gettingstarted.html');
define('CRM_PHP_CONFIG_FILE', 'php/Config.php');
define('CRM_PHP_END_TAG', '?>');

// messages constants
define ('MESSAGES_GET_INBOX_MESSAGES', 0);
define ('MESSAGES_GET_UNREAD_MESSAGES', 1);
define ('MESSAGES_GET_DELETED_MESSAGES', 2);
define ('MESSAGES_GET_SENT_MESSAGES', 3);
define ('MESSAGES_GET_FAVORITE_MESSAGES', 4);
define ('MESSAGES_MAX_FOLDER', 4);

// user images constants
define ('AVATAR_IMAGE_DEFAULT_SIZE', 300);
define ('AVATAR_IMAGE_FILENAME_LENGTH', 16);
define ('AVATAR_IMAGE_FILENAME_PREFIX', 'avatar');
define ('AVATAR_IMAGE_FILENAME_EXTENSION', 'jpg');
define ('AVATAR_IMAGE_FILEDIR', '../img/avatars/');
define ('AVATAR_IMAGE_DEFAULT_FILEDIR', '../img/avatars/default/');

// task constants
define ('TASK_GENERAL_INFO_FORMAT', 'task-general-info');
define ('TASK_PROGRESS_FORMAT', 'task-progress-info');

// Database constants
define ('CRM_DEFAULTS_DATABASE_CONTACTS', 'people_contacts');
define ('CRM_DEFAULTS_DATABASE_CUSTOMERS', 'people_customers');
define ('CRM_DEFAULTS_CUSTOMERS_SCHEMA_DEFAULT', 'default');
define ('CRM_DEFAULTS_CUSTOMERS_SCHEMA_CUSTOM', 'custom');
define ('CRM_RECOVERY_EMAIL_FILE', '../skel/recoveryEmail.html');
define ('CRM_DEFAULT_DB_PORT', 3306);
define ('CRM_CONTACTS_TABLE_NAME', 'clients_1');

// User constants
define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);
define('WRONG_AUTHENTICATION_TYPE', 3);

define ('CRM_DEFAULTS_USER_ROLE_ADMIN', 0);
define ('CRM_DEFAULTS_USER_ROLE_MANAGER', 1);
define ('CRM_DEFAULTS_USER_ROLE_WRITER', 2);
define ('CRM_DEFAULTS_USER_ROLE_READER', 3);
define ('CRM_DEFAULTS_USER_ROLE_GUEST', 4);

define ('CRM_DEFAULTS_USER_DISABLED', 0);
define ('CRM_DEFAULTS_USER_ENABLED', 1);

define ('CRM_DEFAULTS_USER_AVATAR', './img/avatars/default/defaultAvatar.png');

// user access functions
function userHasAdminPermission($userrole) {
	if (!isset($userrole)) return false;
	if ($userrole === CRM_DEFAULTS_USER_ROLE_ADMIN) return true;
	return false;
}

function userHasManagerPermission($userrole) {
	if (!isset($userrole)) return false;
	if (($userrole === CRM_DEFAULTS_USER_ROLE_ADMIN) || ($userrole === CRM_DEFAULTS_USER_ROLE_MANAGER)) return true;
	return false;
}

function userHasWritePermission($userrole) {
	if (!isset($userrole)) return false;
	if (($userrole === CRM_DEFAULTS_USER_ROLE_ADMIN) || ($userrole === CRM_DEFAULTS_USER_ROLE_MANAGER) || ($userrole === CRM_DEFAULTS_USER_ROLE_WRITER)) return true;
	return false;
}

function userHasBasicPermission($userrole) {
	if (!isset($userrole)) return false;
	if (($userrole === CRM_DEFAULTS_USER_ROLE_ADMIN) || ($userrole === CRM_DEFAULTS_USER_ROLE_MANAGER) || ($userrole === CRM_DEFAULTS_USER_ROLE_WRITER) || ($userrole === CRM_DEFAULTS_USER_ROLE_READER)) return true;
	return false;
}

// installation constants.
define ('CRM_INSTALL_STATE_SUCCESS', 1);
define ('CRM_INSTALL_STATE_ERROR', 0);
define ('CRM_INSTALL_STATE_DATABASE_ERROR', 'Database error');
define ('CRM_INSTALL_STATE_FILE_ERROR', 'Filesystem error');


?>