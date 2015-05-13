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

define ('CRM_NO_REPLY_ADMIN_EMAIL_ADDRESS', 'no-reply@creamycrm.com');

require_once('CRMDefaults.php');
require_once('LanguageHandler.php');

/**
 *  MailHandler.
 *  This class is in charge of sending emails and communicating system information to users. 
 *  MailHandler uses the Singleton pattern, thus gets instanciated by the MailHandler::getInstante().
 */
class MailHandler {
	/** Variables && data */
	private $db; // database handler

	/** Creation and class lifetime management */

	/**
     * Returns the singleton instance of UIHandler.
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
    
	/** Mailing methods */
	
	/** 
	 * Sends a recovery mail to the user. The user must have a valid email contained in the database.
	 * @param $email string string of the user.
	 * @return true if successful, false if email couldn't be sent.
	 */
	public function sendPasswordRecoveryEmail($email) {
		// safety check.
		if (!$this->db->userExistsIdentifiedByEmail($email)) { return false; }
		
		// get file contents and prepare values.
		$dateAsString = date('Y-m-d-H-i-s');
		$htmlContent = file_get_contents(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.CRM_SKEL_DIRECTORY.DIRECTORY_SEPARATOR.CRM_RECOVERY_EMAIL_FILE);
		$baseURL = \creamy\CRMUtils::creamyBaseURL();
		
		if ($htmlContent !== false) {
			// create subject and headers
			$replyEmailAddress = $this->getSystemAdminReplyToEmailAddress();
			$subject = "Password reset link for your Creamy account.";
			$headers = "From: ".$replyEmailAddress."\r\n";
			$headers .= "Reply-To: ".$replyEmailAddress."\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			
			// generate a nonce to avoid replay attacks && the password reset code.
			$randomStringGenerator = new \creamy\RandomStringGenerator();
			$nonce = $randomStringGenerator->generate(40);
			
			// build the message.
			$resetCode = $this->db->generatePasswordResetCode($email, $dateAsString, $nonce);
			$htmlContent = str_replace("{email}", $email, $htmlContent);
			$htmlContent = str_replace("{date}", $dateAsString, $htmlContent);
			$htmlContent = str_replace("{host}", $baseURL, $htmlContent);
			$htmlContent = str_replace("{code}", $resetCode, $htmlContent);
			$htmlContent = str_replace("{nonce}", $nonce, $htmlContent);
			return mail($email, $subject, $htmlContent, $headers);
		}
		return false;
	}
	
	private function getSystemAdminReplyToEmailAddress() {
		$dbAdminData = $this->db->getMainAdminUserData();
		// try to return the main admin email address.
		if (is_array($dbAdminData) && (array_key_exists("email", $dbAdminData))) { return $dbAdminData["email"]; }
		else { // fallback to no-reply.
			return CRM_NO_REPLY_ADMIN_EMAIL_ADDRESS;
		}	
	}
	
	/** 
	 * Sends a mail to the given recipients.
	 * @param String $recipients	A valid RFC 2822 recipients address set. See http://www.faqs.org/rfcs/rfc2822
	 * @param String $subject 		A valid RFC 2047 subject. See http://www.faqs.org/rfcs/rfc2047
	 * @param String $message		Message in HTML or plain text.
	 * @param Array  $attachements	Array of files as received by $_FILES.
	 * @return true if successful, false if email couldn't be sent.
	 */
	public function sendMailWithAttachements($recipients, $subject, $message, $attachements, $attachementTag = "attachment") {
		// safety checks.		
		require_once('Session.php');
		if (empty($user)) { return false; }
		// boundary for this email.
		$boundaryId = md5(uniqid(time()));
		// generate a valid header including the attachements.
		$header = $this->generateMultipartHeaderAndMessageContent($recipients, $message, $boundaryId);
		$header .= $this->generateAttachementMultipartFromFiles($attachements, $boundaryId, $attachementTag);
		// send email
		return @mail($recipients, $subject, null, $header);
	}
	
	/**
	 * Generates a multipart message header, appends the recipients, and the basic message in
	 * HTML format. This header is ready to be appended different attachements by invoking the function
	 * generateAttachementMultipartFromFiles().
	 * @return String the multipart header and message content
	 */
	protected function generateMultipartHeaderAndMessageContent($from, $message, $boundaryId) {
		$strHeader = "";
		$strHeader .= "From: $from\r\n" . "MIME-Version: 1.0\r\n" . "Content-Type: multipart/mixed;\r\n"; 
		
		$strHeader .= "MIME-Version: 1.0\n";
		$strHeader .= "Content-Type: multipart/mixed; boundary=\"".$boundaryId."\"\n\n";
		$strHeader .= "This is a multi-part message in MIME format.\n";
		
		$strHeader .= "--".$boundaryId."\n";
		$strHeader .= 'Content-Type: text/html; charset=UTF-8'.PHP_EOL;
		$strHeader .= "Content-Transfer-Encoding: 8bit\n\n";
		$strHeader .= $message."\n\n";
		
		return $strHeader;
	}
	
	/**
	 * Generates a valid form submit multipart with the given files (from $_FILES).
	 * @return String the multipart submit file upload multipart.
	 */
	protected function generateAttachementMultipartFromFiles($files, $boundaryId, $attachementTag = "attachment") {
		// no files, empty files.
		if (!is_array($files)) { return ""; }
	
		
		// process attachements.
		$strHeader = "";
		for($i = 0; $i < count($files[$attachementTag]["tmp_name"]); $i++) {
	    // Check $files['<nameofinputfile>']['error'] value.
		if ($files[$attachementTag]['error'] != UPLOAD_ERR_OK) { return ""; }
			if (($files[$attachementTag]["tmp_name"][$i] != "") && ($files[$attachementTag]['error'] == UPLOAD_ERR_OK)) {
				$strFilesName = sha1_file($files[$attachementTag]['tmp_name'][$i]);
				$strContent = chunk_split(base64_encode(file_get_contents($files[$attachementTag]["tmp_name"][$i])));
				$strHeader .= "--".$boundaryId."\n";
				$strHeader .= "Content-Type: application/octet-stream; name=\"".$strFilesName."\"\n";
				$strHeader .= "Content-Transfer-Encoding: base64\n";
				$strHeader .= "Content-Disposition: attachment; filename=\"".$strFilesName."\"\n\n";
				$strHeader .= $strContent."\n\n";
			}
		}
		return $strHeader;
	}
}

?>