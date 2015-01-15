<?php
	
// global constants
define ('TASK_GENERAL_INFO_FORMAT', 'task-general-info');
define ('TASK_PROGRESS_FORMAT', 'task-progress-info');

define ('MESSAGES_GET_INBOX_MESSAGES', 0);
define ('MESSAGES_GET_UNREAD_MESSAGES', 1);
define ('MESSAGES_GET_DELETED_MESSAGES', 2);
define ('MESSAGES_GET_SENT_MESSAGES', 3);
define ('MESSAGES_GET_FAVORITE_MESSAGES', 4);
define ('MESSAGES_MAX_FOLDER', 4);

define ('AVATAR_IMAGE_DEFAULT_SIZE', 300);
define ('AVATAR_IMAGE_FILENAME_LENGTH', 16);
define ('AVATAR_IMAGE_FILENAME_PREFIX', 'avatar');
define ('AVATAR_IMAGE_FILENAME_EXTENSION', '.jpg');
define ('AVATAR_IMAGE_FILEDIR', '../img/avatars/');
define ('AVATAR_IMAGE_DEFAULT_FILEDIR', '../img/avatars/default/');

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USER_ALREADY_EXISTED', 2);
define('WRONG_AUTHENTICATION_TYPE', 3);

// specific install configuration


// database configuration
define('DB_USERNAME', 'ejemplocrm');
define('DB_PASSWORD', 'ejemplocrm');
define('DB_HOST', 'localhost');
define('DB_NAME', 'ejemplocrm');
define('DB_PORT', '3306');
		
// General configuration
define('CRM_TIMEZONE', 'Europe/Madrid');
define('CRM_SECURITY_TOKEN', 'ZyBkLAEXdYFwqhlzpeBJaSTg1wuP5NDIDsZO2PlY');

define('CRM_ADMIN_EMAIL', 'contact@digitalleaves.com');
?>
