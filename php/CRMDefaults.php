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
define ('CRM_INSTALL_VERSION', '1.0');
define ('CRM_INSTALLED_FILE', 'installed.txt');
define ('CRM_SKEL_DIRECTORY', 'skel');
define ('CRM_RECOVERY_EMAIL_FILE', 'creamyEmail.html');
define ('CRM_PHP_CONFIG_FILE', 'php'.DIRECTORY_SEPARATOR.'Config.php');
define ('CRM_PHP_BEGIN_TAG', '<?php');
define ('CRM_PHP_END_TAG', '?>');
define ('CRM_DEFAULT_HEADER_LOGO', 'img/logoWhite.png');
define ('CRM_DEFAULT_COMPANY_LOGO', 'img/customCompanyLogo.png');

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
define ('AVATAR_IMAGE_FILEDIR', 'img'.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR);
define ('AVATAR_IMAGE_DEFAULT_FILEDIR', 'img'.DIRECTORY_SEPARATOR.'avatars'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR);

// file constants
define ('CRM_UPLOADS_DIRNAME', 'uploads');
define ('CRM_UPLOAD_FILENAME_LENGTH', 40);
define ('CRM_MODULES_BASEDIR', 'modules');

// task constants
define ('TASK_GENERAL_INFO_FORMAT', 'task-general-info');
define ('TASK_PROGRESS_FORMAT', 'task-progress-info');

// Database constants
define ('CRM_DEFAULTS_CUSTOMERS_SCHEMA_DEFAULT', 'default');
define ('CRM_DEFAULTS_CUSTOMERS_SCHEMA_CUSTOM', 'custom');
define ('CRM_DEFAULT_DB_PORT', 3306);
define ('CRM_CONTACTS_TABLE_NAME', 'clients_1');

// User constants
define ('USER_CREATED_SUCCESSFULLY', 0);
define ('USER_CREATE_FAILED', 1);
define ('USER_ALREADY_EXISTED', 2);
define ('WRONG_AUTHENTICATION_TYPE', 3);
define ('CRM_DEFAULTS_USER_ROLE_ADMIN', 0);
define ('CRM_DEFAULTS_USER_ROLE_MANAGER', 1);
define ('CRM_DEFAULTS_USER_ROLE_WRITER', 2);
define ('CRM_DEFAULTS_USER_ROLE_READER', 3);
define ('CRM_DEFAULTS_USER_ROLE_GUEST', 4);
define ('CRM_DEFAULTS_USER_DISABLED', 0);
define ('CRM_DEFAULTS_USER_ENABLED', 1);
define ('CRM_DEFAULTS_USER_AVATAR_IMAGE_NAME', 'defaultAvatar.png');
define ('CRM_DEFAULTS_USER_AVATAR', './img/avatars/default/'.CRM_DEFAULTS_USER_AVATAR_IMAGE_NAME);

// User interface
define ('CRM_UI_STYLE_DEFAULT', 'default');
define ('CRM_UI_STYLE_PRIMARY', 'primary');
define ('CRM_UI_STYLE_SUCCESS', 'success');
define ('CRM_UI_STYLE_DANGER', 'danger');
define ('CRM_UI_STYLE_INFO', 'info');
define ('CRM_UI_STYLE_WARNING', 'warning');
define ('CRM_UI_COLOR_DEFAULT_NAME', 'aqua');
define ('CRM_UI_COLOR_DEFAULT_HEX', '#00c0ef');

define ('CRM_UI_TOPBAR_MENU_STYLE_SIMPLE', 'notifications');
define ('CRM_UI_TOPBAR_MENU_STYLE_DATE', 'tasks');
define ('CRM_UI_TOPBAR_MENU_STYLE_COMPLEX', 'messages');

// timeline notification time
define ('CRM_NOTIFICATION_PERIOD', 'period');
define ('CRM_NOTIFICATION_PERIOD_TODAY', 'today');
define ('CRM_NOTIFICATION_PERIOD_PASTWEEK', 'past_week');

// installation constants.
define ('CRM_INSTALL_STATE_SUCCESS', 1);
define ('CRM_INSTALL_STATE_ERROR', 0);
define ('CRM_INSTALL_STATE_DATABASE_ERROR', 'Database error');
define ('CRM_INSTALL_STATE_FILE_ERROR', 'Filesystem error');

// Table names
define('CRM_CUSTOMER_TYPES_TABLE_NAME', "customer_types");
define('CRM_MARITAL_STATUS_TABLE_NAME', "marital_status");
define('CRM_MESSAGES_INBOX_TABLE_NAME', "messages_inbox");
define('CRM_MESSAGES_OUTBOX_TABLE_NAME', "messages_outbox");
define('CRM_MESSAGES_JUNK_TABLE_NAME', "messages_junk");
define('CRM_NOTIFICATIONS_TABLE_NAME', "notifications");
define('CRM_SETTINGS_TABLE_NAME', "settings");
define('CRM_STATISTICS_TABLE_NAME', "statistics");
define('CRM_TASKS_TABLE_NAME', "tasks");
define('CRM_USERS_TABLE_NAME', "users");
define('CRM_ATTACHMENTS_TABLE_NAME', "attachments");
define('CRM_EVENTS_TABLE_NAME', "events");

// File constants
define('CRM_MAX_ATTACHMENT_FILESIZE', 2);
define('CRM_FILETYPE_PDF', "file-pdf-o");
define('CRM_FILETYPE_IMAGE', "file-image-o");
define('CRM_FILETYPE_ZIP', "file-zip-o");
define('CRM_FILETYPE_TXT', "file-text-o");
define('CRM_FILETYPE_VIDEO', "file-video-o");
define('CRM_FILETYPE_HTML', "file-code-o");
define('CRM_FILETYPE_UNKNOWN', "file-o");

// Job scheduling
define ('CRM_JOB_SCHEDULING_HOURLY', 0);
define ('CRM_JOB_SCHEDULING_DAILY', 1);
define ('CRM_JOB_SCHEDULING_WEEKLY', 2);
define ('CRM_JOB_SCHEDULING_MONTHLY', 3);


// settings constants
define ('CRM_SETTING_CONTEXT_CREAMY', "creamy");
define ('CRM_SETTING_CRM_VERSION', "crm_version");
define ('CRM_SETTING_CRM_BASE_URL', "base_url");
define ('CRM_SETTING_ADMIN_USER', "admin_user");
define ('CRM_SETTING_INSTALLATION_DATE', "installation_date");
define ('CRM_SETTING_ACTIVE_MODULES', "active_modules");
define ('CRM_SETTING_MODULE_SYSTEM_ENABLED', "module_system_enabled");
define ('CRM_SETTING_STATISTICS_SYSTEM_ENABLED', "statistics_system_enabled");
define ('CRM_SETTING_CUSTOMER_LIST_FIELDS', "customer_list_fields");
define ('CRM_SETTING_TIMEZONE', "timezone");
define ('CRM_SETTING_LOCALE', "locale");
define ('CRM_SETTING_SECURITY_TOKEN', "security_token");
define ('CRM_SETTING_CONFIRMATION_EMAIL', "confirmation_email");
define ('CRM_SETTING_THEME', "theme");
define ('CRM_SETTING_COMPANY_LOGO', "company_logo");
define ('CRM_SETTING_COMPANY_NAME', "company_name");
define ('CRM_SETTING_EVENTS_EMAIL', "notification_email_events");
define ('CRM_SETTING_JOB_SCHEDULING_MIN_FREQ', 'job_scheduling_min_freq');
define ('CRM_SETTING_JOB_LAST_DAY', 'job_scheduling_last_day');
define ('CRM_SETTING_JOB_LAST_WEEK', 'job_scheduling_last_week');
define ('CRM_SETTING_JOB_LAST_MONTH', 'job_scheduling_last_month');


define ('CRM_SETTING_DEFAULT_CUSTOMER_LIST_FIELDS', 'id,name,email,phone,id_number');
define ('CRM_SETTING_DEFAULT_THEME', "blue");

define ('CRM_SETTING_TYPE_STRING', "string");
define ('CRM_SETTING_TYPE_INT', "int");
define ('CRM_SETTING_TYPE_FLOAT', "float");
define ('CRM_SETTING_TYPE_BOOL', "bool");
define ('CRM_SETTING_TYPE_DATE', "date");


// misc constants
define ('CRM_DEFAULT_SUCCESS_RESPONSE', "success");

?>