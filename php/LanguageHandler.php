<?php
/**
 * Class to handle language and translations. LanguageHandler uses the Singleton pattern, thus gets instanciated by the LanguageHandler::getInstante().
 * This class is in charge of returning the right texts for the user's language.
 *
 * $lh = \creamy\LanguageHandler::getInstance();
 * $lh->translationFor("cancel"); --> returns "cancel" for en_US, "cancelar" for es_ES, etc...
 *
 * @author Ignacio Nieto Carvajal
 * @link URL http://digitalleaves.com
 */
namespace creamy;

require_once('CRMDefaults.php');
require_once('Config.php');

define('CRM_LANGUAGE_BASE_DIR', '/../lang/');

class LanguageHandler {
	
	/** Variables and constants */
	private $texts = array();
	private $locale;
	
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
		// initialize language and user locale
		$this->locale = CRM_LOCALE;
		if (!isset($this->locale)) $this->locale = "en_US";
		
		// initialize map of language texts.
		$filepath = dirname(__FILE__).CRM_LANGUAGE_BASE_DIR.$this->locale;
		$this->texts = parse_ini_file($filepath) or array();
		
		// log
		error_log("Creamy: Singleton instance initialised.");
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
    	
	/** Translation methods */
	
	/**
	 * Return the direct translation for the string term given as parameter, depending on the configured locale.
	 * @param $string String the string to search for in the translation table
	 * @return String the translated text.
	 */
	public function translationFor($string) {
		if (isset($this->texts[$string])) return $this->texts[$string];
		return $string;
	}
	
	/**
	 * Prints the direct translation for the string term given as parameter, depending on the configured locale.
	 * @param $string String the string to search for in the translation table
	 */
	public function translateText($string) {
		if (isset($this->texts[$string])) { 
			print $this->texts[$string]; 
		}
		else print $string;
	}
	

	/**
	 * Translates a text, substituting all appearances of the terms passed in the "terms" parameter with their proper values in the translation table.
	 * @param $string String the text to translate.
	 * @param $terms Array an array of strings containing the terms to find and replace in the String $string.
	 */
	public function translationForTerms($string, $terms) {
		$translatedString = $string;
		// iterate through all the terms.
		foreach ($terms as $term) {
			$translation = $this->texts[$term];
			if (!empty($translation)) {
				$translatedString = str_replace($term, $translation, $translatedString);
			}
		}
		return $translatedString;
	}

	/**
	 * prints the translated text consisting on substituting all appearances of the terms passed in the "terms" parameter with their 
	 * proper values in the translation table.
	 * @param $string String the text to translate.
	 * @param $terms Array an array of strings containing the terms to find and replace in the String $string.
	 */
	public function translateTerms($string, $terms) {
		print $this->translationForTerms($string, $terms);
	}
	
}

?>