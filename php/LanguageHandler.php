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
@include_once('Config.php');

define('CRM_LANGUAGE_BASE_DIR', '/../lang/');

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
    public static function getInstance($locale = "en_US")
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static($locale);
        }

        return $instance;
    }
	
    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct($locale = "en_US")
    {
		// initialize language and user locale
		if (isset($locale)) { $this->locale = $locale; }
		else $this->locale = defined('CRM_LOCALE') ? CRM_LOCALE : "en_US";
		if (!isset($this->locale)) {
			 $this->locale = "en_US";
		}
		
		// initialize map of language texts.
		$filepath = dirname(__FILE__).CRM_LANGUAGE_BASE_DIR.$this->locale;
		if (!file_exists($filepath)) {
			// fallback to en_US installation (everybody knows english, don't they?)
			$filepath = dirname(__FILE__).CRM_LANGUAGE_BASE_DIR."en_US";
			$this->locale = "en_US";
		}
		$this->texts = parse_ini_file($filepath) or array();
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
	 * Sets the locale of the LanguageHandler locale
	 * $locale String locale to set. If a language file for the specified language does not exists, the language will default to en_US.
	 */
	public function setLanguageHandlerLocale($locale) {
		$filepath = dirname(__FILE__).CRM_LANGUAGE_BASE_DIR.$locale;
		if (!file_exists($filepath)) {
			// fallback to en_US installation (everybody knows english, don't they?)
			$filepath = dirname(__FILE__).CRM_LANGUAGE_BASE_DIR."en_US";
			$this->locale = "en_US";
		} else {
			$this->locale = $locale;
		}
		$this->texts = parse_ini_file($filepath) or array();
	}
	
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