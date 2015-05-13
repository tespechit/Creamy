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
require_once('LanguageHandler.php');
require_once('CRMUtils.php');
require_once('ModuleHandler.php');

// constants
define ('CRM_UI_DEFAULT_RESULT_MESSAGE_TAG', "resultmessage");

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
	
	/** Creation and class lifetime management */

	/**
     * Returns the singleton instance of UIHandler.
     * @staticvar UIHandler $instance The UIHandler instance of this class.
     * @return UIHandler The singleton instance.
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
    
    /** Generic HTML structure */

	public function fullRowWithContent($content, $colSize = "xs") {
		return '<div class="row"><div class="col-'.$colSize.'-12">'.$content.'</div></div>';
	}

    public function boxWithContent($header_title, $body_content, $footer_content = NULL, $icon = NULL, $style = NULL, $body_id = NULL, $additional_body_classes = "") {
	    // if icon is present, generate an icon item.
	    $iconItem = (empty($icon)) ? "" : '<i class="fa fa-'.$icon.'"></i>';
	    $bodyIdCode = (empty($body_id)) ? "" : 'id="'.$body_id.'"';
	    $boxStyleCode = empty($style) ? "" : "box-$style";
	    $footerDiv = empty($footer_content) ? "" : '<div class="box-footer">'.$footer_content.'</div>';
	    
	    return '<div class="box '.$boxStyleCode.'">
					<div class="box-header">'.$iconItem.'
				        <h3 class="box-title">'.$header_title.'</h3>
				    </div>
					<div class="box-body '.$additional_body_classes.'" '.$bodyIdCode.'>'.$body_content.'</div>
					'.$footerDiv.'
				</div>';
    }
    
    public function responsibleTableBox($header_title, $table_content, $icon = NULL, $style = NULL, $body_id = NULL) {
	    return $this->boxWithContent($header_title, $table_content, NULL, $icon, $style, $body_id, "table-responsive");
    }
    
    public function boxWithMessage($header_title, $message, $icon = NULL, $style = "PRIMARY") {
	    $body_content = '<div class="callout callout-'.$style.'"><p>'.$message.'</p></div>';
	    return $this->boxWithContent($header_title, $body_content, NULL, $icon, $style);
    }
    
    public function boxWithForm($id, $header_title, $content, $submit_text = null, $style = CRM_UI_STYLE_PRIMARY, $messagetag = CRM_UI_DEFAULT_RESULT_MESSAGE_TAG) {
	    if (empty($submit_text)) { $submit_text = $this->lh->translationFor("accept"); }
	    return '<div class="box box-primary"><div class="box-header"><h3 class="box-title">'.$header_title.'</h3></div>
	    	   '.$this->formWithContent($id, $content, $submit_text, $style, $messagetag).'</div>';
    }
    
    public function boxWithQuote($title, $quote, $author, $icon = "quote-left", $style = null, $body_id = null, $additional_body_classes = "") {
	    $body_content = '<blockquote><p>'.$quote.'</p>'.(empty($author) ? "" : '<small>'.$author.'</small>').'</blockquote>';
	    return $this->boxWithContent($title, $body_content, null, $icon, $style, $body_id, $additional_body_classes);
    }
    
    public function infoBox($title, $subtitle, $url, $icon, $color, $colsize = 3) {
	    return '<div class="col-md-'.$colsize.'"><div class="info-box"><a href="'.$url.'"><span class="info-box-icon bg-'.$color.'"><i class="fa fa-'.$icon.'"></i></span></a>
	    	<div class="info-box-content"><span class="info-box-text">'.$title.'</span>
			<span class="info-box-number">'.$subtitle.'</span></div></div></div>';
    }
    
    /** Tables */

    public function generateTableHeaderWithItems($items, $id, $styles = "", $needsTranslation = true, $hideHeading = false) {
	    $theadStyle = $hideHeading ? 'style="display: none;"' : '';
	    $table = "<table id=\"$id\" class=\"table $styles\"><thead $theadStyle><tr>";
	    if (is_array($items)) {
		    foreach ($items as $item) {
			    $table .= "<th>".($needsTranslation ? $this->lh->translationFor($item) : $item)."</th>";
		    }
	    }
		$table .= "</tr></thead><tbody>";
		return $table;
    }
    
    public function generateTableFooterWithItems($items, $needsTranslation = true, $hideHeading = false) {
	    $theadStyle = $hideHeading ? 'style="display: none;"' : '';
	    $table = "</tbody><tfoot $theadStyle><tr>";
	    if (is_array($items)) {
		    foreach ($items as $item) {
			    $table .= "<th>".($needsTranslation ? $this->lh->translationFor($item) : $item)."</th>";
		    }
	    }
		$table .= "</tr></tfoot></table>";
		return $table;
	}
    
    /** Style and color */
    	
	/**
	 * Returns a random UI style to use for a notification, button, background element or such.
	 */
	public function getRandomUIStyle() {
		$number = rand(1,5);
		if ($number == 1) return CRM_UI_STYLE_INFO;
		else if ($number == 2) return CRM_UI_STYLE_DANGER;
		else if ($number == 3) return CRM_UI_STYLE_WARNING;
		else if ($number == 4) return CRM_UI_STYLE_SUCCESS;
		else return CRM_UI_STYLE_DEFAULT;
	}
		
    /** Messages */
    
    public function dismissableAlertWithMessage($message, $success, $includeResultData = false) {
	    $icon = $success ? "check" : "ban";
	    $color = $success ? "success" : "danger";
	    $title = $success ? $this->lh->translationFor("success") : $this->lh->translationFor("error");
	    $plusData = $includeResultData ? "'+ data+'" : "";
	    return '<div class="alert alert-dismissable alert-'.$color.'"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><h4><i class="fa fa-'.$icon.'"></i> '.$title.'</h4><p>'.$message.' '.$plusData.'</p></div>';
    }
    
    public function emptyMessageDivWithTag($tagname) {
	    return '<div  id="'.$tagname.'" name="'.$tagname.'" style="display:none"></div>';
    } 
    
    /**
	 * Generates a generic callout message with the given title, message and style.
	 * @param title String the title of the callout message.
	 * @param message String the message to show.
	 * @param style String a string containing the style (danger, success, primary...) or NULL if no style.
	 */
	public function calloutMessageWithTitle($title, $message, $style = NULL) {
		$styleCode = empty($style) ? "" : "callout-$style";
		return "<div class=\"callout $styleCode\"><h4>$title</h4><p>$message</p></div>";	
	}
    
	/**
	 * Generates a generic message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	public function calloutInfoMessage($message) { 
		return $this->calloutMessageWithTitle($this->lh->translationFor("message"), $message, "info"); 
	}

	/**
	 * Generates a warning message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	public function calloutWarningMessage($message) { 
		return $this->calloutMessageWithTitle($this->lh->translationFor("warning"), $message, "warning");
	}

	/**
	 * Generates a error message HTML box, with the given message.
	 * @param message String the message to show.
	 */
	public function calloutErrorMessage($message) {
		return $this->calloutMessageWithTitle($this->lh->translationFor("error"), $message, "danger");
	}
	
	/**
	 * Generates a error modal message HTML dialog, with the given message.
	 * @param message String the message to show.
	 */
	public function modalErrorMessage($message, $header) {
		$result = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> '.$header.'</h4>
		            </div><div class="modal-body">';
		$result = $result.$this->calloutErrorMessage($message);
		$result = $result.'</div><div class="modal-footer clearfix"><button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> '.
		$this->lh->translationFor("exit").'</button></div></div></div>';
		return $result;
	}
	
	/** Forms */
	
	public function formWithContent($id, $content, $submit_text = null, $submitStyle = CRM_UI_STYLE_PRIMARY, $messagetag = CRM_UI_DEFAULT_RESULT_MESSAGE_TAG, $action = "") {
		return '<form role="form" id="'.$id.'" name="'.$id.'" method="post" action="'.$action.'" enctype="multipart/form-data">
            <div class="box-body">
            	'.$content.'
            </div>
            <div class="box-footer" id="form-footer">
            	'.$this->emptyMessageDivWithTag($messagetag).'
                <button type="submit" class="btn btn-'.$submitStyle.'">'.$submit_text.'</button>
            </div>
        </form>';
	}
	
	public function formForCustomHook($id, $modulename, $hookname, $content, $submit_text = null, $messagetag = CRM_UI_DEFAULT_RESULT_MESSAGE_TAG, $action = "") {
		$hiddenFields = $this->hiddenFormField("module_name", $modulename).$this->hiddenFormField("hook_name", $hookname);
		return $this->formWithContent($id, $hiddenFields.$content, $submit_text, CRM_UI_STYLE_PRIMARY, $messagetag, $action);
	}
	
	public function modalFormStructure($modalid, $formid, $title, $subtitle, $body, $footer, $icon = null) {
		$iconCode = empty($icon) ? '' : '<i class="fa fa-'.$icon.'"></i> ';
		$subtitleCode = empty($subtitle) ? '' : '<p>'.$subtitle.'</p>';
		
		return '<div class="modal fade" id="'.$modalid.'" name="'.$modalid.'" tabindex="-1" role="dialog" aria-hidden="true">
	        	<div class="modal-dialog"><div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                    <h4 class="modal-title">'.$iconCode.$title.'</h4>
	                    '.$subtitleCode.'
	                </div>
	                <form action="" method="post" name="'.$formid.'" id="'.$formid.'">
	                    <div class="modal-body">
	                        '.$body.'
	                    </div>
	                    <div class="modal-footer clearfix">
							'.$footer.'
	                    </div>
	                </form>
				</div></div>
				</div>';
	}
	
    public function checkboxInputWithLabel($label, $id, $name, $enabled) {
	    return '<div class="checkbox"><label for="'.$id.'">
	    <input type="checkbox" id="'.$id.'" name="'.$name.'"'.($enabled ? "checked": "").'/> '.$label.'</label></div>';
    }
    
    public function selectWithLabel($label, $id, $name, $options, $selectedOption, $needsTranslation = false) {
	    $selectCode = '<div class="form-group"><label>'.$label.'</label><select id="'.$id.'" name="'.$name.'" class="form-control">';
	    foreach ($options as $key => $value) {
		    $isSelected = ($selectedOption == $key) ? " selected" : "";
		    $selectCode .= '<option value="'.$key.'" '.$isSelected.'>'.($needsTranslation ? $this->lh->translationFor($value) : $value).'</option>';
	    }
		$selectCode .= '</select></div>';
		return $selectCode;
    }
    
    public function singleFormInputElement($id, $name, $type, $placeholder, $value = null, $icon = null) {
	    $iconCode = empty($icon) ? '' : '<span class="input-group-addon"><i class="fa fa-'.$icon.'"></i></span>';
	    $valueCode = empty($value) ? '' : ' value="'.$value.'"';
	    return $iconCode.'<input name="'.$name.'" id="'.$id.'" type="'.$type.'" class="form-control" placeholder="'.$placeholder.'"'.$valueCode.'>';
    }

	public function maskedDateInputElement($id, $name, $dateFormat = "dd/mm/yyyy", $value = null, $icon = null) {
		// date value
		$dateAsDMY = "";
        if (isset($value)) { 
            $time = strtotime($value);
            $phpFormat = str_replace("dd", "d", $dateFormat);
            $phpFormat = str_replace("mm", "m", $phpFormat);
            $phpFormat = str_replace("yyyy", "Y", $phpFormat);
            $dateAsDMY = date($phpFormat, $time); 
        }
        // icon and label
		$iconCode = empty($icon) ? '' : '<span class="input-group-addon"><i class="fa fa-'.$icon.'"></i></span>';

		// bild html code
		$htmlCode = '<input name="'.$name.'" id="'.$id.'" type="text" class="form-control" data-inputmask="\'alias\': \''.$dateFormat.'\'" data-mask value="'.$dateAsDMY.'" placeholder="'.$dateFormat.'"/>';
		// build JS code to turn an input text into a dateformat.
		$jsIncludes = '<script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
	    <script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
	    <script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>';
	    $jsActivation = $this->wrapOnDocumentReadyJS('$("#'.$id.'").inputmask("'.$dateFormat.'", {"placeholder": "'.$dateFormat.'"});');
		
		return $iconCode.$htmlCode."\n".$jsIncludes."\n".$jsActivation;
	}

	public function hiddenFormField($id, $value = "") {
		return '<input type="hidden" id="'.$id.'" name="'.$id.'" value="'.$value.'">';
	}

    public function singleFormInputGroup($inputElement, $label = null) {
	    $labelCode = isset($label) ? "<label>$label</label>" : "";
	    return '<div class="form-group">'.$labelCode.'<div class="input-group">'.$inputElement.'</div></div>';
    }
    
    public function doubleFormInputGroup($firstInputElement, $secondInputElement, $sizeClass = "lg") {
	    return '<div class="form-group"><div class="row"><div class="col-'.$sizeClass.'-6"><div class="input-group">'.$firstInputElement.'</div></div>
                <div class="col-'.$sizeClass.'-6">'.$secondInputElement.'</div></div></div>';
    }
    
    public function modalDismissButton($id, $message = null, $position = "right", $dismiss = true) {
	    if (empty($message)) { $message = $this->lh->translationFor("cancel"); }
	    $dismissCode = $dismiss ? 'data-dismiss="modal"' : '';
	    return '<button type="button" class="btn btn-danger pull-'.$position.'" '.$dismissCode.' id="'.$id.'">
	    		<i class="fa fa-times"></i> '.$message.'</button>';
    }
    
    public function modalSubmitButton($id, $message = null, $position = "left", $dismiss = false) {
	    if (empty($message)) { $message = $this->lh->translationFor("accept"); }
	    $dismissCode = $dismiss ? 'data-dismiss="modal"' : '';
	    return '<button type="submit" class="btn btn-primary pull-'.$position.'" '.$dismissCode.' id="'.$id.'"><i class="fa fa-check-circle"></i> '.$message.'</button>';
    }
    
    /** Task list buttons */
    
    /**
	 * Creates a hover action button to be put in a a task list, todo-list or similar.
	 * If modaltarget is specified, the button will open a custom dialog with the given id.
	 * @param String $classname name for the class.
	 * @param Array $parameters An associative array of parameters to include (i.e: "user_id" => "1231").
	 * @param String icon the font-awesome identifier for the icon.
	 * @param String modaltarget if specified, id of the destination modal dialog to open.
	 * @param String $linkClasses Additional classes for the HTML a link
	 * @param String $iconClasses Additional classes for the font awesome icon i.
	 */
    public function hoverActionButton($classname, $icon, $hrefValue = "", $modaltarget = null, $linkClasses = "", $iconClasses = "", $otherParameters = null) {
	    // build parameters and additional code
	    $paramCode = "";
	    if (isset($otherParameters)) foreach ($otherParameters as $key => $value) { $paramCode = "$key=\"$value\" "; }
	    $modalCode = isset($modaltarget) ? "data-toggle=\"modal\" data-target=\"#$modaltarget\"" : "";
	    // return the button action link
	    return '<a class="'.$classname.' '.$linkClasses.'" href="'.$hrefValue.'" '.$paramCode.' '.$modalCode.'>
	    		<i class="fa fa-'.$icon.' '.$iconClasses.'"></i></a>';
    }
    
    /** Pop-Up Action buttons */
    
    public function popupActionButton($title, $options, $style = CRM_UI_STYLE_DEFAULT) {
	    // style code
	    if (is_string($style)) { $styleCode = "btn btn-$style"; }
	    else if (is_array($style)) {
		    $styleCode = "btn";
		    foreach ($style as $class) { $styleCode .= " btn-$class"; }
	    } else { $styleCode = "btn btn-default"; }
	    // popup prefix code
	    $popup = '<div class="btn-group"><button type="button" class="'.$styleCode.' dropdown-toggle" data-toggle="dropdown">'.$title.'</button><ul class="dropdown-menu" role="menu">';
	    // options
	    foreach ($options as $option) { $popup .= $option; }
	    // popup suffix code.
	    $popup .= '</ul></div>';
	    return $popup;
    }
    
    public function actionForPopupButtonWithClass($class, $text, $parameter_value, $parameter_name = "href") {
	    return '<li><a class="'.$class.'" '.$parameter_name.'="'.$parameter_value.'">'.$text.'</a></li>';
    }
    
    public function actionForPopupButtonWithLink($url, $text, $class = null, $parameter_value = null, $parameter_name = null) {
	    // do we need to specify a class?
	    if (isset($class)) { $classCode = 'class="'.$class.'"'; } else { $classCode = ""; }
	    // do we have an optional parameter?
	    if (isset($parameter_value) && isset($parameter_name)) { $parameterCode = $parameter_name.'="'.$parameter_value.'"'; }
	    else { $parameterCode = ""; }
	    return '<li><a '.$classCode.' href="'.$url.'" '.$parameterCode.'>'.$text.'</a></li>';
    }
    
    public function actionForPopupButtonWithOnClickCode($text, $jsFunction, $parameters = null, $class = null) {
	    // do we need to specify a class?
	    if (isset($class)) { $classCode = 'class="'.$class.'"'; } else { $classCode = ""; }
	    // do we have an parameters?
	    if (isset($parameters) && is_array($parameters)) { 
		    $parameterCode = "";
		    foreach ($parameters as $parameter) { $parameterCode .= "'$parameter',"; }
		    $parameterCode = rtrim($parameterCode, ",");
		} else { $parameterCode = ""; }
	    return '<li><a href="#" '.$classCode.' onclick="'.$jsFunction.'('.$parameterCode.');">'.$text.'</a></li>';
    } 
    
    public function separatorForPopupButton() {
	    return '<li class="divider"></li>';
    }
    
    public function simpleLinkButton($id, $title, $url, $icon = null, $style = CRM_UI_STYLE_DEFAULT, $additionalClasses = null) {
	    // style code.
	    $styleCode = "";
	    if (!empty($style)) {
		    if (is_array($style)) { foreach ($style as $st) { $styleCode .= "btn-$style "; } }
		    else if (is_string($style)) { $styleCode = "btn-$style"; }
	    }
	    return '<a id="'.$id.'" class="btn '.$styleCode.'" href="'.$url.'">'.$title.'</a>';


    }
    
    /** Javascript HTML code generation */
    
    public function wrapOnDocumentReadyJS($content) {
	    return '<script type="text/javascript">$(document).ready(function() {
		    '.$content.'
		    });</script>';
    }
    
    public function formPostJS($formid, $phpfile, $successJS, $failureJS, $preambleJS = "", $successResult=CRM_DEFAULT_SUCCESS_RESPONSE) {
	    return $this->wrapOnDocumentReadyJS('$("#'.$formid.'").validate({
			submitHandler: function(e) {
				'.$preambleJS.'
				$.post("'.$phpfile.'", $("#'.$formid.'").serialize(), function(data) {
					if (data == "'.$successResult.'") {
						'.$successJS.'
					} else {
						'.$failureJS.'
					}
				}).fail(function(){ 
					'.$failureJS.'
  				});
			}
		 });');
    }
    
    public function fadingInMessageJS($message, $tagname = CRM_UI_DEFAULT_RESULT_MESSAGE_TAG) {
	    return '$("#'.$tagname.'").html(\''.$message.'\');
				$("#'.$tagname.'").fadeIn();';
    }
    
    public function fadingOutMessageJS($animated = false, $tagname = CRM_UI_DEFAULT_RESULT_MESSAGE_TAG) {
	    if ($animated) { return '$("#'.$tagname.'").fadeOut();'; }
	    else { return '$("#'.$tagname.'").hide();'; }
    }
    
    public function reloadLocationJS() { return 'location.reload();'; }
    
    public function newLocationJS($url) { return 'window.location.href = "'.$url.'";'; }
    
    public function showRetrievedErrorMessageAlertJS() { return 'alert(data);'; }
    
    public function showCustomErrorMessageAlertJS($msg) { return 'alert("'.$msg.'");'; }
    
    public function clickableClassActionJS($className, $parameter, $container, $phpfile, $successJS, $failureJS, $confirmation = false, $successResult = "success", $additionalParameters = null, $parentContainer = null) {
	    // build the confirmation code if needed.
	    $confirmPrefix = $confirmation ? 'var r = confirm("'.$this->lh->translationFor("are_you_sure").'"); if (r == true) {' : '';
	    $confirmSuffix = $confirmation ? '}' : '';
	    $paramCode = empty($parentContainer) ? 'var paramValue = $(this).attr("'.$container.'");' : 
	    			'var ele = $(this).parents("'.$parentContainer.'").first(); var paramValue = ele.attr("'.$container.'");';
	    // additional parameters
	    $additionalString = "";
	    if (is_array($additionalParameters) && count($additionalParameters) > 0) {
		    foreach ($additionalParameters as $apKey => $apValue) { $additionalString .= ", \"$apKey\": $apValue ";  }
	    }
	    
	    // return the JS code
	    return $this->wrapOnDocumentReadyJS(
	    '$(".'.$className.'").click(function(e) {
			e.preventDefault();
			'.$confirmPrefix.'
				'.$paramCode.'
				$.post("'.$phpfile.'", { "'.$parameter.'": paramValue '.$additionalString.'} ,function(data){
					if (data == "'.$successResult.'") { '.$successJS.' }
					else { '.$failureJS.' }
				}).fail(function(){ 
					'.$failureJS.'
  				});
			'.$confirmSuffix.'
		 });');
    }
    
    /**
	 * Creates a javascript javascript "reload with message" code, that will
	 * reload the current page passing a custom message tag. 
	 */
	public function reloadWithMessageFunctionJS($messageVarName = "message") {
		return 'function reloadWithMessage(message) {
		    var url = window.location.href;
			if (url.indexOf("?") > -1) { url += "&'.$messageVarName.'="+message;
			} else{ url += "?'.$messageVarName.'="+message; }
			window.location.href = url; 
		}'."\n";
	}
	
	/**
	 * Generates an javascript calling to ReloadWithMessage function, generated
	 * by reloadWithMessageFunctionJS() to reload the custom page sending a
	 * custom message parameter.
	 * Note: Message is not quoted inside call, you must do it yourself.
	 */
	public function reloadWithMessageCallJS($message) { return 'reloadWithMessage('.$message.');'; }
    
    /**
	 * This function generates the javascript for the messages mailbox actions.
	 * You must pass a class name for the button that triggers the action, a php
	 * url for the Ajax request, a completion resultJS code and, optionally, if
	 * you want to discern the failure from the success, a failureJS.
	 * The function does the following assumptions:
	 * - The result message div for showing the results has id messages-message-box
	 * - The parameters to send to any function invoked by the php ajax script are
	 *   messageids (containing a comma separated string array of message ids to act upon)
	 *   and folder (containing the current folder identifier).
	 * @param String $classname the name of the mailbox action class.
	 * @param String $url the URL for the PHP that will receive the Ajax POST request.
	 * @param String $resultJS The default javascript to execute (or the successful one if
	 *        failureJS is also specified).
	 * @param String $failureJS The failure javascript to execute, if left null, only the
	 *        resultJS will be applied, without taking into account the result data.
	 * @param Array $customParameters Associative array with custom parameters to add to the request.
	 * @param Bool $confirmation If true, confirmation will be asked before applying the action.
	 * @param Bool $checkSelectedMessages if true, no action will be taken if no messages are selected.
	 */ 
	public function mailboxAction($classname, $url, $resultJS, $failureJS = null, $customParameters = null, $confirmation = false, 
								  $checkSelectedMessages = true) {
		// check selected messages count?
		$checkSelectedMessagesCode = $checkSelectedMessages ? 'if (selectedMessages.length < 1) { return; }' : '';
		// needs confirmation?
		$confirmPrefix = $confirmation ? 'var r = confirm("'.$this->lh->translationFor("are_you_sure").'"); if (r == true) {' : '';
		$confirmSuffix = $confirmation ? '}' : '';
		// success+failure or just result ?
		if (empty($failureJS)) { $content = $resultJS; } 
		else { $content = 'if (data == "'.CRM_DEFAULT_SUCCESS_RESPONSE.'") { '.$resultJS.' } else { '.$failureJS.' }'; }
		// custom parameters
		$paramCode = "";
		if (is_array($customParameters) && count($customParameters)) {
			foreach ($customParameters as $key => $value) { $paramCode .= ", \"$key\": \"$value\" "; }
		}
		
		$result = '$(".'.$classname.'").click(function (e) {
				    '.$checkSelectedMessagesCode.'
					e.preventDefault();
					'.$confirmPrefix.'
					$("#messages-message-box").hide();
					$.post("'.$url.'", { "messageids": selectedMessages, "folder": folder '.$paramCode.'}, function(data) {
						'.$content.'
					});
					'.$confirmSuffix.'
			    });';
		return $result;
	}
    
    // Assignment to variables from one place to a form destination.
    
    private function javascriptVarFromName($name, $prefix = "var") {
	    $result = str_replace("-", "", $prefix.$name);
	    $result = str_replace("_", "", $result);
	    return trim($result);
    }
    
    public function selfValueAssignmentJS($attr, $destination) {
	    $varName = $this->javascriptVarFromName($destination);
	    return 'var '.$varName.' = $(this).attr("'.$attr.'"); 
	    		$("#'.$destination.'").val('.$varName.');';
    }
    
    public function directValueAssignmentJS($source, $attr, $destination) {
	    $varName = $this->javascriptVarFromName($destination);
	    return 'var '.$varName.' = $("#'.$source.'").attr("'.$attr.'"); 
	    		$("#'.$destination.'").val('.$varName.');';
    }
    
    public function classValueFromParentAssignmentJS($classname, $parentContainer, $destination) {
	    $elementName = $this->javascriptVarFromName($destination, "ele");
	    $varName = $this->javascriptVarFromName($destination);
	    return 'var '.$elementName.' = $(this).parents("'.$parentContainer.'").first();
				var '.$varName.' = $(".'.$classname.'", '.$elementName.');
				$("#'.$destination.'").val('.$varName.'.text().trim());';
    }
        
    public function clickableFillValuesActionJS($classname, $assignments) {
	    $js = '$(".'.$classname.'").click(function(e) {'."\n".'e.preventDefault();';
		foreach ($assignments as $assignment) { $js .= "\n".$assignment; }
		$js .= '});'."\n";
		return $this->wrapOnDocumentReadyJS($js);
    }
    
    /** Hooks */
    
    /**
	 * Returns the hooks for the dashboard.
	 */
    public function hooksForDashboard() {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_DASHBOARD, null, CRM_MODULE_MERGING_STRATEGY_APPEND);
    }
    
	/**
	 * Generates the footer for the customer list screen, by invoking the different modules
	 * CRM_MODULE_HOOK_CUSTOMER_LIST_FOOTER hook.
	 */
	public function getCustomerListFooter($customer_type) {
		$mh = \creamy\ModuleHandler::getInstance();
		$footer = $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_CUSTOMER_LIST_FOOTER, array(CRM_MODULE_HOOK_PARAMETER_CUSTOMER_LIST_TYPE => $customer_type), CRM_MODULE_MERGING_STRATEGY_APPEND);
		$js = $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_CUSTOMER_LIST_ACTION, array(CRM_MODULE_HOOK_PARAMETER_CUSTOMER_LIST_TYPE => $customer_type), CRM_MODULE_MERGING_STRATEGY_APPEND);
		return $footer.$js;
	}	 

	/**
	 * Generates the footer for the messages list screen, by invoking the different modules
	 * CRM_MODULE_HOOK_MESSAGE_LIST_FOOTER & CRM_MODULE_HOOK_MESSAGE_LIST_ACTION hooks.
	 */
	public function getMessagesListActionJS($folder) {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_LIST_ACTION, array(CRM_MODULE_HOOK_PARAMETER_MESSAGES_FOLDER => $folder), CRM_MODULE_MERGING_STRATEGY_APPEND);
	}	 
	 
	public function getComposeMessageFooter() {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_COMPOSE_FOOTER, null, CRM_MODULE_MERGING_STRATEGY_APPEND);
	}
	 
	public function getComposeMessageActionJS() {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_COMPOSE_ACTION, null, CRM_MODULE_MERGING_STRATEGY_APPEND);
	}
	 
	public function getMessageDetailFooter($messageid, $folder) {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_DETAIL_FOOTER, array(CRM_MODULE_HOOK_PARAMETER_MESSAGE_ID => $messageid, CRM_MODULE_HOOK_PARAMETER_MESSAGES_FOLDER => $folder), CRM_MODULE_MERGING_STRATEGY_APPEND);
	}
	 
	public function getMessageDetailActionJS($messageid, $folder) {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_DETAIL_ACTION, array(CRM_MODULE_HOOK_PARAMETER_MESSAGE_ID => $messageid, CRM_MODULE_HOOK_PARAMETER_MESSAGES_FOLDER => $folder), CRM_MODULE_MERGING_STRATEGY_APPEND);
	}

    /**
	 * Returns the hooks for the customer detail/edition screen.
	 */
	public function customerDetailModuleHooks($customerid, $customerType) {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_CUSTOMER_DETAIL, array(CRM_MODULE_HOOK_PARAMETER_CUSTOMER_LIST_ID => $customerid, CRM_MODULE_HOOK_PARAMETER_CUSTOMER_LIST_TYPE => $customerType),
		 CRM_MODULE_MERGING_STRATEGY_APPEND);
	}
    
    /* Administration & user management */
    
    /** Returns the HTML form for modyfing the system settings */
    public function getGeneralSettingsForm() {
		// current settings values
	    $modulesEnabled = $this->db->getSettingValueForKey(CRM_SETTING_MODULE_SYSTEM_ENABLED) == true ? " checked" : "";
	    $statsEnabled = $this->db->getSettingValueForKey(CRM_SETTING_STATISTICS_SYSTEM_ENABLED) ? " checked" : "";
	    $baseURL = $this->db->getSettingValueForKey(CRM_SETTING_CRM_BASE_URL);
	    $tz = $this->db->getSettingValueForKey(CRM_SETTING_TIMEZONE);
	    $lo = $this->db->getSettingValueForKey(CRM_SETTING_LOCALE);
	    
	    // translation.
	    $em_text = $this->lh->translationFor("enable_modules");
	    $es_text = $this->lh->translationFor("enable_statistics");
	    $tz_text = $this->lh->translationFor("detected_timezone");
	    $lo_text = $this->lh->translationFor("choose_language");
	    $ok_text = $this->lh->translationFor("settings_successfully_changed");
	    $ko_text = $this->lh->translationFor("error_changing_settings");
	    $bu_text = $this->lh->translationFor("base_url");
	    
	    // form
	    $form = '<div class="form-group"><form role="form" id="adminsettings" name="adminsettings" class="form">
			  '.$this->singleFormInputGroup($this->singleFormInputElement("base_url", "base_url", "text", $bu_text, $baseURL, "globe"), $bu_text).'
	    	  <label>'.$this->lh->translationFor("general_settings").'</label>
			  '.$this->checkboxInputWithLabel($em_text, "enableModules", "enableModules", $modulesEnabled).'
			  '.$this->checkboxInputWithLabel($es_text, "enableStatistics", "enableStatistics", $statsEnabled).'
			  '.$this->selectWithLabel($tz_text, "timezone", "timezone", \creamy\CRMUtils::getTimezonesAsArray(), $tz).'
			  '.$this->selectWithLabel($lo_text, "locale", "locale", \creamy\LanguageHandler::getAvailableLanguages(), $lo).'
			  <div class="box-footer">
			  '.$this->emptyMessageDivWithTag(CRM_UI_DEFAULT_RESULT_MESSAGE_TAG).'
			  <button type="submit" class="btn btn-primary">'.$this->lh->translationFor("modify").'</button></form></div></div>';
		
		// javascript
		$successJS = $this->reloadLocationJS();
		$failureJS = $this->fadingInMessageJS($this->dismissableAlertWithMessage($ko_text, false, true));
		$preambleJS = $this->fadingOutMessageJS(false);
		$javascript = $this->formPostJS("adminsettings", "./php/ModifySettings.php", $successJS, $failureJS, $preambleJS);
		
		return $form."</br>".$javascript;
    }
    
    /** Returns the HTML code for the input field associated with a module setting data type */
    public function inputFieldForModuleSettingOfType($setting, $type, $currentValue) {
	    if (is_array($type)) { // select type
		    return $this->selectWithLabel($this->lh->translationFor($setting), $setting, $setting, $type, $currentValue);
	    } else { // single input type: text, number, bool, date...
		    switch ($type) {
			    case CRM_SETTING_TYPE_STRING:
				    return $this->singleFormInputGroup($this->singleFormInputElement($setting, $setting, "text", $this->lh->translationFor($setting), $currentValue), $this->lh->translationFor($setting));
					break;
				case CRM_SETTING_TYPE_INT:
				case CRM_SETTING_TYPE_FLOAT:
				    return $this->singleFormInputGroup($this->singleFormInputElement($setting, $setting, "number", $this->lh->translationFor($setting), $currentValue), $this->lh->translationFor($setting));
					break;
				case CRM_SETTING_TYPE_BOOL:
					return $this->singleFormInputGroup($this->checkboxInputWithLabel($this->lh->translationFor($setting), $setting, $setting, (bool) $currentValue));
					break;
				case CRM_SETTING_TYPE_DATE:
					$dateFormat = $this->lh->getDateFormatForCurrentLocale();
				    return $this->singleFormInputGroup($this->maskedDateInputElement($setting, $setting, $dateFormat, $currentValue), $this->lh->translationFor($setting));
					break;
		    }
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
	                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">'.$this->lh->translationFor("choose_action").'</button>
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
	       return $this->calloutErrorMessage($this->lh->translationFor("unable_get_user_list"));
       } else if (empty($users)) { // no contacts found
	       return $this->calloutWarningMessage($this->lh->translationFor("no_users_in_list"));
       } else { 
	       // we have some users, show a table
	       $columns = array("id", "name", "email", "creation_date", "role", "status", "action");
		   $result = $this->generateTableHeaderWithItems($columns, "users", "table-bordered table-striped", true);
	       
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
	       $result .= $this->generateTableFooterWithItems($columns, true);
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
		return $this->boxWithMessage($this->lh->translationFor("access_denied"), $this->lh->translationFor("you_dont_have_permission"), "lock", "danger");
	}
	
	/**
	 * Generates the HTML code for editing the profile of a user as  an HTML form. Depends on the user having the right permissions.
	 * @param $usertoeditid ID of the user to edit
	 * @param $requestinguserid ID of the user requesting the edit form for the user $usertoeditid
	 * @param $hasAdminPermissions true if the user has admin permissions.
	 * @return A HTML containing the edit user form if permissions are correct, an error message otherwise.
	 */
	public function getEditUserForm($usertoeditid, $requestinguserid, $hasAdminPermissions) {
		$userobj = NULL;
		$errormessage = NULL;
		
		if (!empty($usertoeditid)) {
			if (($requestinguserid == $usertoeditid) || ($hasAdminPermissions)) { 
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
			if ($hasAdminPermissions) {
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
	                                    <div  id="'.CRM_UI_DEFAULT_RESULT_MESSAGE_TAG.'" name="'.CRM_UI_DEFAULT_RESULT_MESSAGE_TAG.'" style="display:none">
	                                    </div>
                                    </div><!-- /.box-body -->
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">'.$this->lh->translationFor("edit_user").'</button>
                                    </div>
                                </form>
                            </div><!-- /.box -->';
                return $result;
		} else {
			return $this->calloutErrorMessage($errormessage);
		}
	}

	/** Modules */
	
	public function getModulesAsList() {
		// get all modules.
		$mh = \creamy\ModuleHandler::getInstance();
		$allModules = $mh->listOfAllModules();
		
		// generate a table with all elements.
		$items = array("name", "description", "enabled", "action");
		$table = $this->generateTableHeaderWithItems($items, "moduleslist", "table-striped");
		// fill table
		foreach ($allModules as $moduleClass => $moduleDefinition) {
			// module data
			if ($mh->moduleIsEnabled($moduleClass)) { // module is enabled.
				$status = "<i class='fa fa-check-square-o'></i>";
				$enabled = true;
			} else { // module is disabled.
				$status = "<i class='fa fa-times-circle-o'></i>";
				$enabled = false;
			}
			$moduleName = $moduleDefinition->getModuleName();
			$moduleVersion = $moduleDefinition->getModuleVersion();
			$moduleDescription = $moduleDefinition->getModuleDescription();
			// module action
			$moduleShortName = $moduleDefinition->getModuleShortName();
			$action = $this->getActionButtonForModule($moduleShortName, $enabled);
			// add module row
			$table .= "<tr><td><b>$moduleName</b><br/><div class='small'>$moduleDescription</div></td><td>$moduleVersion</td><td>$status</td><td>$action</td></tr>";
		}

		// close table
		$table .= $this->generateTableFooterWithItems($items);
		
		// add javascript code.
		$enableJS = $this->clickableClassActionJS("enable_module", "module_name", "href", "./php/ModifyModule.php", $this->reloadLocationJS(), $this->showRetrievedErrorMessageAlertJS(), false, CRM_DEFAULT_SUCCESS_RESPONSE, array("enabled"=>"1"), null);
		$disableJS = $this->clickableClassActionJS("disable_module", "module_name", "href", "./php/ModifyModule.php", $this->reloadLocationJS(), $this->showRetrievedErrorMessageAlertJS(), false, CRM_DEFAULT_SUCCESS_RESPONSE, array("enabled"=>"0"), null);
		$deleteJS = $this->clickableClassActionJS("uninstall_module", "module_name", "href", "./php/DeleteModule.php", $this->reloadLocationJS(), $this->showRetrievedErrorMessageAlertJS(), true);
		$table .= $enableJS.$disableJS.$deleteJS;
		
		return $table;
	}
	
	public function getModuleHandlerLog() {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->getModuleHandlerLog();
	}
	
	private function getActionButtonForModule($moduleShortName, $enabled) {
		// build the options.
		$ed_option = $enabled ? $this->actionForPopupButtonWithClass("disable_module", $this->lh->translationFor("disable"), $moduleShortName) : $this->actionForPopupButtonWithClass("enable_module", $this->lh->translationFor("enable"), $moduleShortName);
		//$up_option = $this->actionForPopupButtonWithClass("update_module", $this->lh->translationFor("update"), $moduleShortName);
		$un_option = $this->actionForPopupButtonWithClass("uninstall_module", $this->lh->translationFor("uninstall"), $moduleShortName);
		$options = array($ed_option, $un_option);
		// build and return the popup action button.
		return $this->popupActionButton($this->lh->translationFor("choose_action"), $options);
	}

	/** Header */
	
	/**
	 * Returns the default creamy header for all pages.
	 */
	public function creamyHeader($user) {
		// module topbar elements
		$mh = \creamy\ModuleHandler::getInstance();
		$moduleTopbarElements = $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_TOPBAR, null, CRM_MODULE_MERGING_STRATEGY_APPEND);
		// return header
		return '<header class="main-header">
	            <a href="./index.php" class="logo"><img src="img/logoWhite.png" width="32" height="32"> Creamy</a>
	            <nav class="navbar navbar-static-top" role="navigation">
	                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
	                    <span class="sr-only">Toggle navigation</span>
	                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
	                </a>
	                <div class="navbar-custom-menu">
	                    <ul class="nav navbar-nav">
	                    		'.$moduleTopbarElements.'
	                    		'.$this->getTopbarMessagesMenu($user).'  
		                    	'.$this->getTopbarNotificationsMenu($user).'
		                    	'.$this->getTopbarTasksMenu($user).'
		                    	'.$this->getTopbarUserMenu($user).'
	                    </ul>
	                </div>
	            </nav>
	        </header>';
	}
	
	/**
	 * Returns the default creamy footer for all pages.
	 */
	public function creamyFooter() {
		$version = $this->db->getSettingValueForKey(CRM_SETTING_CRM_VERSION);
		if (empty($version)) { $version = "unknown"; }
		return '<footer class="main-footer"><div class="pull-right hidden-xs"><b>Version</b> '.$version.'</div><strong>Copyright &copy; 2014 <a href="http://digitalleaves.com">Digital Leaves</a> - <a href="http://woloweb.com">Woloweb</a>.</strong> All rights reserved.</footer>';
	}
	
	/** Topbar Menu elements */

	/**
	 * Generates the HTML for the message notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	protected function getTopbarMessagesMenu($user) {
		if (!$user->userHasBasicPermission()) return '';

        $list = $this->db->getMessagesOfType($user->getUserId(), MESSAGES_GET_UNREAD_MESSAGES);
		$numMessages = count($list);
		
		$headerText = $this->lh->translationFor("you_have").' '.$numMessages.' '.$this->lh->translationFor("unread_messages");
		$result = $this->getTopbarMenuHeader("envelope", $numMessages, CRM_UI_TOPBAR_MENU_STYLE_COMPLEX, $headerText, null, CRM_UI_STYLE_SUCCESS);
        
        foreach ($list as $message) {
	        if (empty($message["remote_avatar"])) $remoteavatar = CRM_DEFAULTS_USER_AVATAR;
	        else $remoteavatar = $message["remote_avatar"];
	        $result .= $this->getTopbarComplexElement($message["remote_user"], $message["message"], $message["date"], $remoteavatar, "messages.php");
        }
        $result .= $this->getTopbarMenuFooter($this->lh->translationFor("see_all_messages"), "messages.php");
        return $result;
	}
	
	/**
	 * Generates the HTML for the main info boxes of the dashboard.
	 */
	public function dashboardInfoBoxes($userid) {
		$boxes = "";
		$firstCustomerType = $this->db->getFirstCustomerGroupTableName();
		$columnSize = isset($firstCustomerType) ? 3 : 4;

		// new contacts
		$contactsUrl = "./customerslist.php?customer_type=clients_1&customer_name=".urlencode($this->lh->translationFor("contacts"));
		$boxes .= $this->infoBox($this->lh->translationFor("new_contacts"), $this->db->getNumberOfNewContacts(), $contactsUrl, "user-plus", "aqua", $columnSize);
		// new customers
		if (isset($firstCustomerType)) {
			$customersURL = "./customerslist.php?customer_type=".$firstCustomerType["table_name"]."&customer_name=".urlencode($firstCustomerType["description"]);
			$boxes .= $this->infoBox($this->lh->translationFor("new_customers"), $this->db->getNumberOfNewCustomers(), $customersURL, "users", "green",  $columnSize);
		}
		// notifications
		$boxes .= $this->infoBox($this->lh->translationFor("notifications"), $this->db->getNumberOfTodayNotifications($userid), "notifications.php", "clock-o", "yellow", $columnSize);
		// events today // TODO: Change
		$boxes .= $this->infoBox($this->lh->translationFor("unfinished_tasks"), $this->db->getUnfinishedTasksNumber($userid), "tasks.php", "calendar", "red", $columnSize);

		return $boxes;
	}

	/**
	 * Generates the HTML for the alert notifications of a user as a dropdown list element to include in the top bar.
	 * @param $userid the id of the user.
	 */
	protected function getTopbarNotificationsMenu($user) {
		if (!$user->userHasBasicPermission()) return '';
		
		$notifications = $this->db->getTodayNotifications($user->getUserId());
		if (empty($notifications)) $notificationNum = 0;
		else $notificationNum = count($notifications);
		
		$headerText = $this->lh->translationFor("you_have").' '.$notificationNum.' '.strtolower($this->lh->translationFor("notifications"));
		$result = $this->getTopbarMenuHeader("warning", $notificationNum, CRM_UI_TOPBAR_MENU_STYLE_SIMPLE, $headerText, null, CRM_UI_STYLE_WARNING);

        foreach ($notifications as $notification) {
	        $result .= $this->getTopbarSimpleElement($notification["text"], $this->notificationIconForNotificationType($notification["type"]), "notifications.php", $this->getRandomUIStyle());
        }                                        
        $result .= $this->getTopbarMenuFooter($this->lh->translationFor("see_all_notifications"), "notifications.php");
        return $result;
	}
	
	protected function getTopbarTasksMenu($user) {
		if (!$user->userHasBasicPermission()) return '';

		$list = $this->db->getUnfinishedTasks($user->getUserId());
		$numTasks = count($list);
		
		$headerText = $this->lh->translationFor("you_have").' '.$numTasks.' '.$this->lh->translationFor("pending_tasks");
		$result = $this->getTopbarMenuHeader("tasks", $numTasks, CRM_UI_TOPBAR_MENU_STYLE_DATE, $headerText, null, CRM_UI_STYLE_DANGER);
                                    
        foreach ($list as $task) {
	        $result .= $this->getTopbarSimpleElementWithDate($task["description"], $task["creation_date"], "clock-o", "tasks.php", CRM_UI_STYLE_WARNING);
        }
                                    
        $result .= $this->getTopbarMenuFooter($this->lh->translationFor("see_all_tasks"), "tasks.php");
        return $result;

        return '';
    }

	/**
	 * Generates the HTML for the user's topbar menu.
	 * @param $userid the id of the user.
	 */
	protected function getTopbarUserMenu($user) {
		// menu actions & change my data(only for users with permissions).
		$menuActions = '';
		$changeMyData = '';
		if ($user->userHasBasicPermission()) {
			$menuActions = '<li class="user-body">
				<div class="text-center"><a href="" data-toggle="modal" data-target="#change-password-dialog-modal">'.$this->lh->translationFor("change_password").'</a></div>
				<div class="text-center"><a href="./messages.php">'.$this->lh->translationFor("messages").'</a></div>
				<div class="text-center"><a href="./notificationes.php">'.$this->lh->translationFor("notifications").'</a></div>
				<div class="text-center"><a href="./tasks.php">'.$this->lh->translationFor("tasks").'</a></div>
			</li>';
			$changeMyData = '<div class="pull-left"><a href="./edituser.php" class="btn btn-default btn-flat">'.$this->lh->translationFor("my_profile").'</a></div>';
		} 
		
		return '<li class="dropdown user user-menu">
	                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
	                    <img src="'.$user->getUserAvatar().'" class="user-image" alt="User Image" />
	                    <span>'.$user->getUserName().' <i class="caret"></i></span>
	                </a>
	                <ul class="dropdown-menu">
	                    <li class="user-header bg-light-blue">
	                        <img src="'.$user->getUserAvatar().'" class="img-circle" alt="User Image" />
	                        <p>'.$user->getUserName().'<small>'.$this->lh->translationFor("nice_to_see_you_again").'</small></p>
	                    </li>'.$menuActions.'
	                    <li class="user-footer">'.$changeMyData.'
	                        <div class="pull-right"><a href="./logout.php" class="btn btn-default btn-flat">'.$this->lh->translationFor("exit").'</a></div>
	                    </li>
	                </ul>
	            </li>';
	}

	public function getTopbarMenuHeader($icon, $badge, $menuStyle, $headerText = null, $headerLink = null, $badgeStyle = CRM_UI_STYLE_DEFAULT) {
		// header text and link
		if (!empty($headerText)) {
			$linkPrefix = isset($headerLink) ? '<a href="'.$headerLink.'">' : '';
			$linkSuffix = isset($headerLink) ? '</a>' : '';
			$headerCode = '<li class="header">'.$linkPrefix.$headerText.$linkSuffix.'</li>';
		} else { $headerCode = ""; }
		
		// return the topbar menu header
		return '<li class="dropdown '.$menuStyle.'-menu"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-'.$icon.'"></i><span class="label label-'.$badgeStyle.'">'.$badge.'</span></a>
					<ul class="dropdown-menu">'.$headerCode.'<li><ul class="menu">';
	}

	public function getTopbarMenuFooter($footerText, $footerLink = null) {
		$linkPrefix = isset($footerLink) ? '<a href="'.$footerLink.'">' : '';
		$linkSuffix = isset($footerLink) ? '</a>' : '';
		return '</ul></li><li class="footer">'.$linkPrefix.$footerText.$linkSuffix.'</li></ul></li>';
	}
	
	public function getTopbarSimpleElement($text, $icon, $link, $tint = CRM_UI_STYLE_DEFAULT) {
		$shortText = $this->substringUpTo($text, 40);
		return '<li style="text-align: left; !important;"><a href="'.$link.'"><i class="fa fa-'.$icon.' '.$tint.'"></i> '.$shortText.'</a></li>';
	}
	
	public function getTopbarSimpleElementWithDate($text, $date, $icon, $link, $tint = CRM_UI_STYLE_DEFAULT) {
		$shortText = $this->substringUpTo($text, 30);
	    $relativeTime = $this->relativeTime($date, 1);
		return '<li><a href="'.$link.'"><h3><p class="pull-left">'.$shortText.'</p><small class="label label-'.$tint.' pull-right"><i class="fa fa-'.$icon.'"></i> '.$relativeTime.'</small></h3></a></li>';
	}
	
	public function getTopbarComplexElement($title, $text, $date, $image, $link) {
		$shortTitle = $this->substringUpTo($title, 20);
		$shortText = $this->substringUpTo($text, 40);
	    $relativeTime = $this->relativeTime($date, 1);
		return '<li><a href="'.$link.'">
                    <div class="pull-left">
                        <img src="'.$image.'" class="img-circle" alt="User Image"/>
                    </div>
                    <h4>'.$title.' 
                    <small class="label"><i class="fa fa-clock-o"></i> '.$relativeTime.'</small>
                    </h4>
                    <p>'.$shortText.'</p>
                </a>
            </li>';
	}

	public function getTopbarCustomMenu($header, $elements, $footer) { return $header.$elements.$footer; }

	/** Sidebar */
	
	/**
	 * Generates the HTML for the sidebar of a user, given its role.
	 * @param $userid the id of the user.
	 */
	public function getSidebar($userid, $username, $userrole, $avatar) {
		$numMessages = $this->db->getUnreadMessagesNumber($userid);
		$numTasks = $this->db->getUnfinishedTasksNumber($userid);
		$numNotifications = $this->db->getNumberOfTodayNotifications($userid);
		$mh = \creamy\ModuleHandler::getInstance();
		
		$adminArea = "";
		if ($userrole == CRM_DEFAULTS_USER_ROLE_ADMIN) {
			$modulesWithSettings = $mh->modulesWithSettings();
			$adminArea = '<li class="treeview"><a href="#"><i class="fa fa-dashboard"></i> <span>'.$this->lh->translationFor("administration").'</span><i class="fa fa-angle-left pull-right"></i></a><ul class="treeview-menu">';
			$adminArea .= $this->getSidebarItem("./adminsettings.php", "gears", $this->lh->translationFor("settings")); // admin settings
			$adminArea .= $this->getSidebarItem("./adminusers.php", "user", $this->lh->translationFor("users")); // admin settings
			$adminArea .= $this->getSidebarItem("./adminmodules.php", "archive", $this->lh->translationFor("modules")); // admin settings
			$adminArea .= $this->getSidebarItem("./admincustomers.php", "users", $this->lh->translationFor("customers")); // admin settings	
			foreach ($modulesWithSettings as $k => $m) { $adminArea .= $this->getSidebarItem("./modulesettings.php?module_name=".urlencode($k), $m->mainPageViewIcon(), $m->mainPageViewTitle()); }
	        $adminArea .= '</ul></li>';
		}
		
		// get customer types
		$customerTypes = $this->db->getCustomerTypes();
		
		// prefix: structure and home link
		$result = '<aside class="main-sidebar" sidebar-offcanvas"><section class="sidebar">
	            <div class="user-panel">
	                <div class="pull-left image">
	                    <a href="edituser.php"><img src="'.$avatar.'" class="img-circle" alt="User Image" /></a>
	                </div>
	                <div class="pull-left info">
	                    <p>'.$this->lh->translationFor("hello").', '.$username.'</p>
	                    <a href="edituser.php"><i class="fa fa-circle text-success"></i> '.$this->lh->translationFor("online").'</a>
	                </div>
	            </div>
	            <ul class="sidebar-menu"><li class="header">'.strtoupper($this->lh->translationFor("menu")).'</li>';
	    // body: home and customer menus
        $result .= $this->getSidebarItem("./index.php", "bar-chart-o", $this->lh->translationFor("home"));
        // include a link for every customer type
        foreach ($customerTypes as $customerType) {
	        if (isset($customerType["table_name"]) && isset($customerType["description"])) {
		        $customerTableName = $customerType["table_name"];
		        $customerFriendlyName = $customerType["description"];
		        $url = 'customerslist.php?customer_type='.$customerTableName.'&customer_name='.$customerFriendlyName;
		        $result .= $this->getSidebarItem($url, "users", $customerFriendlyName);
	        }
        }

        // ending: messages, notifications, tasks
        $result .= $this->getSidebarItem("messages.php", "envelope", $this->lh->translationFor("messages"), $numMessages);
        $result .= $this->getSidebarItem("notifications.php", "exclamation", $this->lh->translationFor("notifications"), $numNotifications, "orange");
        $result .= $this->getSidebarItem("tasks.php", "tasks", $this->lh->translationFor("tasks"), $numTasks, "red");
        
        // suffix: modules
        $activeModules = $mh->activeModulesInstances();
        foreach ($activeModules as $shortName => $module) {
        	$result .= $this->getSidebarItem($mh->pageLinkForModule($shortName, null), $module->mainPageViewIcon(), $module->mainPageViewTitle(), $module->sidebarBadgeNumber());
        } 
        
		$result .= $adminArea.'</ul></section></aside>';
		return $result;
	}

	/**
	 * Generates the HTML code for a sidebar link.
	 */
	protected function getSidebarItem($url, $icon, $title, $includeBadge = null, $badgeColor = "green") {
		$badge = (isset($includeBadge)) ? '<small class="badge pull-right bg-'.$badgeColor.'">'.$includeBadge.'</small>' : '';
		return '<li><a href="'.$url.'"><i class="fa fa-'.$icon.'"></i> <span>'.$title.'</span>'.$badge.'</a></li>';
	}

	/** Customers */
   	
   	/**
	 * Generates a HTML table with all customer types for the administration panel.
	 */
	public function getCustomerTypesAdminTable() {
		// generate table		
		$items = array("Id", $this->lh->translationFor("name"));
		$table = $this->generateTableHeaderWithItems($items, "customerTypes", "table-bordered table-striped", true);
		if ($customerTypes = $this->db->getCustomerTypes()) {
			foreach ($customerTypes as $customerType) {
				$table .= "<tr><td>".$customerType["id"]."</td><td><span class='text'>".$customerType["description"].'
				</span><div class="tools pull-right">
				<a class="edit-customer" href="'.$customerType["id"].'" data-toggle="modal" data-target="#edit-customer-modal">
				<i class="fa fa-edit task-item"></i></a>
				<a class="delete-customer" href="'.$customerType["id"].'"><i class="fa fa-trash-o"></i></a>
				</div></td></tr>';
			}
		}
		$table .= $this->generateTableFooterWithItems($items, true);
		
		// generate companion JS code.
		// delete customer type
		$ec_ok = $this->reloadLocationJS();
		$ec_ko = $this->showRetrievedErrorMessageAlertJS();
		$deletephp = "./php/DeleteCustomerType.php";
		$deleteCustomerJS = $this->clickableClassActionJS("delete-customer", "customertype", "href", $deletephp, $ec_ok, $ec_ko, true);
		// edit customer type
		$idAssignment = $this->selfValueAssignmentJS("href", "customer-type-id");
		$textAssignment = $this->classValueFromParentAssignmentJS("text", "td", "newname");
		$editCustomerJS = $this->clickableFillValuesActionJS("edit-customer", array($idAssignment, $textAssignment));	

		// edit customer modal form
		$modalTitle = $this->lh->translationFor("edit_customer_type");
		$modalSubtitle = $this->lh->translationFor("enter_new_name_customer_type");
		$name = $this->lh->translationFor("name");
		$newnameInput = $this->singleFormInputGroup($this->singleFormInputElement("newname", "newname", "text required", $name));
		$hiddenidinput = $this->hiddenFormField("customer-type-id");
		$bodyInputs = $newnameInput.$hiddenidinput;
		$msgDiv = $this->emptyMessageDivWithTag("editcustomermessage");
		$modalFooter = $this->modalDismissButton("edit-customer-cancel").$this->modalSubmitButton("edit-customer-accept").$msgDiv;
		$modalForm = $this->modalFormStructure("edit-customer-modal", "edit-customer-form", $modalTitle, $modalSubtitle, $bodyInputs, $modalFooter, "user");
		
		// validate form javascript
		$successJS = $this->reloadLocationJS();
		$em_text = $this->lh->translationFor("error_editing_customer_name");
		$failureJS = $this->fadingInMessageJS($this->dismissableAlertWithMessage($em_text, false, true), "editcustomermessage");
		$preambleJS = $this->fadingOutMessageJS(false, "editcustomermessage");
		$javascript = $this->formPostJS("edit-customer-form", "./php/ModifyCustomerType.php", $successJS, $failureJS, $preambleJS);

		return $table."\n".$editCustomerJS."\n".$deleteCustomerJS."\n".$modalForm."\n".$javascript;
	}
	
	public function newCustomerTypeAdminForm() {
		// form
		$cg_text = $this->lh->translationFor("customer_group");
		$hc_text = $this->lh->translationFor("new_customer_group");
		$cr_text = $this->lh->translationFor("create");
		$inputfield = $this->singleFormInputGroup($this->singleFormInputElement("newdesc", "newdesc", "text", $cg_text));
		$formbox = $this->boxWithForm("createcustomergroup", $hc_text, $inputfield, $cr_text, CRM_UI_STYLE_PRIMARY, "creationmessage");
		
		// javascript form submit.
		$successJS = $this->reloadLocationJS();
		$ua_text = $this->lh->translationFor("unable_add_customer_group");
		$failureJS = $this->fadingInMessageJS($this->dismissableAlertWithMessage($ua_text, false, true), "creationmessage");
		$preambleJS = $this->fadingOutMessageJS(false, "creationmessage");
		$javascript = $this->formPostJS("createcustomergroup", "./php/CreateCustomerGroup.php", $successJS, $failureJS, $preambleJS);
		
		return $formbox."\n".$javascript;
	}
	
	/**
	 * Generates the HTML with an empty table for a list of contacts or customers.
	 */
	public function getEmptyCustomersList($customerType) {
	   // print prefix
	   $columns = $this->db->getCustomerColumnsToBeShownInCustomerList($customerType);
	   $columns[] = $this->lh->translationFor("action");
	   $result = $this->generateTableHeaderWithItems($columns, "contacts", "table-bordered table-striped", true);

       // print suffix
       $result .= $this->generateTableFooterWithItems($columns, true);
       return $result;
	}

   	/**
	 * Generates the HTML code for the editing customer form.
	 * @param customerId Int the id of the customer to edit
	 * @param customerType String the table name (= customer type identifier) of the customer to edit. 
	 * @param userHasWritePermission Bool true if the requesting user has write permissions.
	 */
	public function generateCustomerEditionForm($customerid, $customerType, $userHasWritePermissions) {
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
            if (isset($customerobj["marital_status"])) {
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
	        if (isset($customerobj["gender"])) {
	            $currentGender = $customerobj["gender"];
	            if ($currentGender < 0) $currentGender = -1;
	            if ($currentGender > 1) $currentGender = -1;
	        }
	        $cgSelectedDefault = $currentGender == -1 ? "selected" : "";
	        $cgSelected0 = $currentGender == 0 ? "selected" : "";
	        $cgSelected1 = $currentGender == 1 ? "selected" : "";
	        
	        // date as dd/mm/yyyy
			$dateAsDMY = "";
	        if (isset($customerobj["birthdate"])) { 
	            $time = strtotime($customerobj["birthdate"]);
	            $dateAsDMY = date('d/m/Y', $time); 
	        }

			// buttons at bottom (only for writing+ permissions)
			$buttons = "";
			if ($userHasWritePermissions) {
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
	                <form role="form" action="" method="post" name="modifycustomerform" id="modifycustomerform">
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
	                            <label>'.$this->lh->translationFor("gender").'</label>
	                            <select class="form-control" id="gender" name="gender">
									<option value="-1" '.$cgSelectedDefault.'>'.$this->lh->translationFor("choose_an_option").'</option>
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
			print $this->calloutErrorMessage($errormessage);
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
		// modules hovers.
		$mh = \creamy\ModuleHandler::getInstance();
		$moduleTaskHoverActions = $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_TASK_LIST_HOVER, array("taskid" => $task["id"]), CRM_MODULE_MERGING_STRATEGY_APPEND);
		
		return '<li id="'.$task["id"].'" '.$doneOrNot.'>'.$completeActionCheckbox.'<span class="text">'.$task["description"].'</span>
				  <small class="label label-warning pull-right"><i class="fa fa-clock-o"></i> '.$creationdate.'</small>
				  <div class="tools">'.$moduleTaskHoverActions.'
				  	'.$this->hoverActionButton("edit-task-action", "edit", $task["id"], "edit-task-dialog-modal", null, "task-item").'
				  	'.$this->hoverActionButton("delete-task-action", "trash-o", $task["id"]).'
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
		if (empty($tasks)) { return $this->calloutInfoMessage($this->lh->translationFor("you_dont_have_completed_tasks")); }
		else {
			$list = "<ul class=\"todo-list ui-sortable\">";
			foreach ($tasks as $task) {
				// generate row
				$taskHTML = $this->getTaskAsIndividualRow($task);
				$list = $list.$taskHTML;
			}
			
			$list = $list."</ul>";
	    	return $list;
		}
   	}

	/**
	 * Generates the HTML for a all tasks of a given user as a table row
	 * @param $userid Int id of the user to retrieve the tasks from.
	 * @return String the HTML representation of the user's tasks as a table.
	 */
	public function getUnfinishedTasksAsTable($userid) { 
		$tasks = $this->db->getUnfinishedTasks($userid);
		if (empty($tasks)) { return $this->calloutInfoMessage($this->lh->translationFor("you_dont_have_pending_tasks")); }
		else {
			$list = "<ul class=\"todo-list ui-sortable\">";
			foreach ($tasks as $task) {
				// generate row
				$taskHTML = $this->getTaskAsIndividualRow($task);
				$list = $list.$taskHTML;
			}
			
			$list = $list."</ul>";
	    	return $list;
		}
   	}
	
	/**
	 * Returns the tasks footer action hooks for modules.
	 */
	public function getTasksActionFooter() {
		$mh = \creamy\ModuleHandler::getInstance();
		return $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_TASK_LIST_ACTION, null, CRM_MODULE_MERGING_STRATEGY_APPEND);
	}
	
	/** Messages */

	/**
	 * Generates the list of users $myuserid can send message to or assign a task to as a HTML form SELECT.
	 * @param Int $myuserid 		id of the user that wants to send messages, all other user's ids will be returned.
	 * @param Boolean $includeSelf 	if true, $myuserid will appear listed in the options. If false (default), $myuserid will not be included in the options. If this parameter is set to true, the default option will be the $myuserid
	 * @param String $customMessage The custom message to ask for a selection in the SELECT, default is "send this message to...".
	 * @param String $selectedUser	If defined, this user will appear as selected by default.
	 * @return the list of users $myuserid can send mail to (all valid users except $myuserid unless $includeSelf==true) as a HTML form SELECT.
	 */
	public function generateSendToUserSelect($myuserid, $includeSelf = false, $customMessage = NULL, $selectedUser = null) {
		// perform query of users.
		if (empty($customMessage)) $customMessage = $this->lh->translationFor("send_this_message_to");
		$usersarray = $this->db->getAllEnabledUsers();

		// iterate through all users and generate the select
		$response = '<select class="form-control required" id="touserid" name="touserid"><option value="0">'.$customMessage.'</option>';
		foreach ($usersarray as $userobj) {
			// don't include ourselves.
			if ($userobj["id"] != $myuserid) {
				$selectedUserCode = "";
				error_log("Analizando usuario ".$userobj["id"].", selected user is $selectedUser");
				if (isset($selectedUser) && ($selectedUser == $userobj["id"])) { $selectedUserCode = 'selected="true"'; }
				$response = $response.'<option value="'.$userobj["id"].'" '.$selectedUserCode.' >'.$userobj["name"].'</option>';
			} else if ($includeSelf === true) { // assign to myself by default unless another $selectedUser has been specified.
				$selfSelectedCode = isset($selectedUser) ? "" : 'selected="true"';
				$response = $response.'<option value="'.$userobj["id"].'" '.$selfSelectedCode.'>myself</option>';
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
	private function getMessageListAsTable($messages, $folder) {
		$columns = array("", "favorite", "name", "subject", "attachment", "date");
		$table = $this->generateTableHeaderWithItems($columns, "messagestable", "table-hover table-striped mailbox table-mailbox", true, true);
		foreach ($messages as $message) {
			if ($message["message_read"] == 0) $table .= '<tr class="unread">';
			else $table .= '<tr>';
						
			// variables and html text depending on the message
			$favouriteHTML = "-o"; if ($message["favorite"] == 1) $favouriteHTML = "";
			$messageLink = '<a href="readmail.php?folder='.$folder.'&message_id='.$message["id"].'">';

			$table .= '<td><input type="checkbox" class="message-selection-checkbox" value="'.$message["id"].'"/></td>';
			$table .= '<td class="mailbox-star"><i class="fa fa-star'.$favouriteHTML.'" id="'.$message["id"].'"></i></td>';
			$table .= '<td class="mailbox-name">'.$messageLink.$message["remote_user"].'</a></td>';
			$table .= '<td class="mailbox-subject">'.$message["subject"].'</td>';
			$table .= '<td class="mailbox-attachment"></td>'; //<i class="fa fa-paperclip"></i></td>';
			$table .= '<td class="mailbox-date pull-right">'.$this->relativeTime($message["date"]).'</td>';
			$table .= '</tr>';
		}		
		$table .= $this->generateTableFooterWithItems($columns, true, true);
		return $table;
	}	
	
	/**
	 * Generates the HTML for a mailbox button.
	 */
	public function generateMailBoxButton($buttonClass, $icon, $param, $value) {
		return '<button class="btn btn-default btn-sm '.$buttonClass.'" '.$param.'="'.$value.'"><i class="fa fa-'.$icon.'"></i></button>';
	}
	
	/**
	 * Generates the button group for the mailbox messages table
	 */
	public function getMailboxButtons($folder) {
		// send to trash or recover from trash ?
		if ($folder == MESSAGES_GET_DELETED_MESSAGES) {
			$trashOrRecover = '<button class="btn btn-default btn-sm messages-restore-message"><i class="fa fa-undo"></i></button>';
		} else { 
			$trashOrRecover = '<button class="btn btn-default btn-sm messages-send-to-junk"><i class="fa fa-trash-o"></i></button>'; 
		}
		
		// basic buttons
		$buttons = '<button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>                    
		<div class="btn-group">
		  <button class="btn btn-default btn-sm messages-mark-as-favorite"><i class="fa fa-star"></i></button>
		  <button class="btn btn-default btn-sm messages-mark-as-read"><i class="fa fa-eye"></i></button>
		  <button class="btn btn-default btn-sm messages-mark-as-unread"><i class="fa fa-eye-slash"></i></button>
		  '.$trashOrRecover.'
		  <button class="btn btn-default btn-sm messages-delete-permanently"><i class="fa fa-times"></i></button>';
		// module buttons
		$mh = \creamy\ModuleHandler::getInstance();
		$buttons .= $mh->applyHookOnActiveModules(CRM_MODULE_HOOK_MESSAGE_LIST_FOOTER, array("folder" => $folder), CRM_MODULE_MERGING_STRATEGY_APPEND);
		// chevrons
		$buttons .= '</div><div class="pull-right"><div class="btn-group">
			<button class="btn btn-default btn-sm mailbox-prev"><i class="fa fa-chevron-left"></i></button>
			<button class="btn btn-default btn-sm mailbox-next"><i class="fa fa-chevron-right"></i></button>
		</div></div>';
		
		return $buttons;
	}
	
	/**
	 * Generates a HTML table with all inbox messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getInboxMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_INBOX_MESSAGES);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("unable_get_messages"));
		else return $this->getMessageListAsTable($messages);
	}
	
	/**
	 * Generates a HTML table with the unread messages of the user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getUnreadMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_UNREAD_MESSAGES);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with with the junk messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getJunkMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_DELETED_MESSAGES);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the sent messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getSentMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_SENT_MESSAGES);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
				
	/**
	 * Generates a HTML table with the favourite messages of a user.
	 * @param Int $userid user to retrieve the messages from
	 */
	public function getFavoriteMessagesAsTable($userid) {
		$messages = $this->db->getMessagesOfType($userid, MESSAGES_GET_FAVORITE_MESSAGES);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages);
	}
		
	/**
	 * Generates a HTML table with the messages from given folder for a user.
	 * @param Int $userid user to retrieve the messages from
	 * @param Int $folder folder to retrieve the messages from
	 */
	public function getMessagesFromFolderAsTable($userid, $folder) {
		$messages = $this->db->getMessagesOfType($userid, $folder);
		if ($messages == NULL) return $this->calloutInfoMessage($this->lh->translationFor("no_messages_in_list"));
		else return $this->getMessageListAsTable($messages, $folder);
	}
	
	/**
	 * Generates the HTML with the list of message folders as <li> items.
	 * @param $activefolder String current active folder the user is in.
	 * @return String the HTML with the list of message folders as <li> items.
	 */
	public function getMessageFoldersAsList($activefolder) {
		require_once('Session.php');
		$user = \creamy\CreamyUser::currentUser();
		// info for active folder and unread messages
        $unreadMessages = $this->db->getUnreadMessagesNumber($user->getUserId());
        $aInbox = $activefolder == MESSAGES_GET_INBOX_MESSAGES ? 'class="active"' : '';
        $aSent = $activefolder == MESSAGES_GET_SENT_MESSAGES ? 'class="active"' : '';
        $aFav = $activefolder == MESSAGES_GET_FAVORITE_MESSAGES ? 'class="active"' : '';
        $aDel = $activefolder == MESSAGES_GET_DELETED_MESSAGES ? 'class="active"' : '';
        
        return '<ul class="nav nav-pills nav-stacked">
			<li '.$aInbox.'><a href="messages.php?folder=0">
				<i class="fa fa-inbox"></i> '.$this->lh->translationFor("inbox").' 
				<span class="label label-primary pull-right">'.$unreadMessages.'</span></a>
			</li>
			<li '.$aSent.'><a href="messages.php?folder=3"><i class="fa fa-envelope-o"></i> '.$this->lh->translationFor("sent").'</a></li>
			<li '.$aFav.'><a href="messages.php?folder=4"><i class="fa fa-star"></i> '.$this->lh->translationFor("favorites").'</a></li>
			<li '.$aDel.'><a href="messages.php?folder=2"><i class="fa fa-trash-o"></i> '.$this->lh->translationFor("trash").'</a></li>
		</ul>';
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
		            <form action="#" method="post" id="show-message-form" name="show-message-form" role="form">
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
			return $this->modalErrorMessage($this->lh->translationFor("unable_get_message"), $this->lh->translationFor("error_getting_message"));
    	}

	} // end function

	/**
	 * Generates the HTML code for showing the attachements of a given message.
	 * @param Int $messageid 	identifier for the message.
	 * @param Int $folderid 	identifier for the folder.
	 * @param Int $userid 		identifier for the user.
	 * @return String The HTML code containing the code for the attachements.
	 */
	public function attachementsSectionForMessage($messageid, $folderid) {
		$attachements = $this->db->getMessageAttachements($messageid, $folderid);
		if (!isset($attachements) || count($attachements) < 1) { return ""; }
		
		$code = '<div class="box-footer non-printable"><ul class="mailbox-attachments clearfix">';
		foreach ($attachements as $attachement) {
			// icon/image
			$icon = $this->getFiletypeIconForFile($attachement["filepath"]);
			if ($icon != CRM_FILETYPE_IMAGE) {
				$hasImageCode = "";
				$iconCode = '<i class="fa fa-'.$icon.'"></i>';
				$attIcon = "paperclip";
			} else {
				$hasImageCode = "has-img";
				$iconCode = '<img src="'.$attachement["filepath"].'" alt="'.$this->lh->translationFor("attachement").'"/>';
				$attIcon = "camera";
			}
			// code
			$basename = basename($attachement["filepath"]);
			$code .= '<li><span class="mailbox-attachment-icon '.$hasImageCode.'">'.$iconCode.'</span>
                      <div class="mailbox-attachment-info">
                        <a href="'.$attachement["filepath"].'" target="_blank" class="mailbox-attachment-name">
                        <i class="fa fa-'.$attIcon.'"></i> '.$basename.'</a>
                        <span class="mailbox-attachment-size">
                          '.$attachement["filesize"].'
                          <a href="'.$attachement["filepath"].'" target="_blank" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
                        </span>
                      </div>
                    </li>';			
		}
		$code .= '</ul></div>';
		return $code;
	}

	/** 
	 * returns the filetype icon for a given file. This filetype can be used added to fa-
	 * for the icon representation of a file.	
	 */
	public function getFiletypeIconForFile($filename) {
		$mimetype = mime_content_type($filename);
		if (\creamy\CRMUtils::startsWith($mimetype, "image/")) { return CRM_FILETYPE_IMAGE; }
		else if ($mimetype == "application/pdf") { return CRM_FILETYPE_PDF; }
		else if ($mimetype == "application/zip") { return CRM_FILETYPE_ZIP; }
		else if ($mimetype == "text/plain") { return CRM_FILETYPE_TXT; }
		else if ($mimetype == "text/html") { return CRM_FILETYPE_HTML; }
		else if (\creamy\CRMUtils::startsWith($mimetype, "video/")) { return CRM_FILETYPE_VIDEO; }
		else { return CRM_FILETYPE_UNKNOWN; }
	}

	/** Notifications */

	/**
	 * Returns the HTML font-awesome icon for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the font-awesome icon for this notification type.
	 */
	public function notificationIconForNotificationType($type) {
		if ($type == "contact") return "user";
		else if ($type == "message") return "envelope";
		else return "calendar-o";
	}
	
	/**
	 * Returns the HTML UI color for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the UI color for this notification type.
	 */
	public function notificationColorForNotificationType($type) {
		if ($type == "contact") return "aqua";
		else if ($type == "message") return "blue";
		else return "yellow";
	}
	
	/**
	 * Returns the HTML action button text for notifications of certain type.
	 * @param $type String the type of notification.
	 * @return String the string with the action button text for this notification type.
	 */
	public function actionButtonTextForNotificationType($type) {
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
	public function headerTextForNotificationType($type, $action) {
		if ($type == "contact") 
		return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("contact") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("contact")."</a>";
		else if ($type == "message") 
			return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("message") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("message")."</a>";

		return empty($action) ? $this->lh->translationFor("you_have_a_new")." ".$this->lh->translationFor("event") : $this->lh->translationFor("you_have_a_new")." <a href=".$action.">".$this->lh->translationFor("event")."</a>";
	}
	
	/**
	 * Generates the HTML code for a timeline item action button.
	 * @param String $url 		the url to launch when pushing the button.
	 * @param String $title 	title for the button.
	 * @param String $style		Style for the button, one of CRM_UI_STYLE_*
	 * @return String			The HTML for the button to include in the timeline item.
	 */
	public function timelineItemActionButton($url, $title, $style = CRM_UI_STYLE_DEFAULT) {
		$actionHTML = '<div class="timeline-footer"><a class="btn btn-'.$style.' btn-xs" href="'.$url.'">'.$title.'</a></div>';
	}
	
	
	/**
	 * Generates the HTML code for a timeline item with the given data.
	 * @param String $title 		Title for the timeline item
	 * @param String $content		Main content (text) for the timeline item.
	 * @param String $date			Recognizable date for strtotime (see http://php.net/manual/es/datetime.formats.date.php).
	 * @param String $url			If set, an action for the notification, use 
	 * @param String $buttonTitle	Title for the button (if URL set).
	 * @param String $icon			Icon for the notification item (default calendar).
	 * @param String $buttonStyle	Style for the button, one of CRM_UI_STYLE_*
	 * @param String $badgeColor	Color for the badge notification bubble (default yellow).
	 * @return The HTML with the code of the timeline notification item to insert in the timeline list. 
	 */
	public function timelineItemWithData($title, $content, $date, $url = null, $buttonTitle, $icon = "calendar-o", $buttonStyle = CRM_UI_STYLE_DEFAULT, $badgeColor = "yellow") {
		// parameters
		$relativeTime = $this->relativeTime($date, 1);
		$actionHTML = isset($url) ? $this->timelineItemActionButton($url, $buttonTitle, $buttonStyle) : "";
		// return code.
		return '<li><i class="fa fa-'.$icon.' bg-'.$badgeColor.'"></i>
            <div class="timeline-item">
                <span class="time"><i class="fa fa-clock-o"></i> '.$relativeTime.'</span>
                <h3 class="timeline-header no-border">'.$title.'</h3>
				<div class="timeline-body">'.$content.'</div>
                '.$actionHTML.'
            </div></li>';
	}
	
	/**
	 * Generates the HTML for the beginning of the timeline.
	 */
	protected function timelineStart($message, $includeInitialTimelineStructure = true, $color = "green") {
		$tlCode = $includeInitialTimelineStructure ? '<ul class="timeline">' : '';
		return $tlCode.'<li class="time-label"><span class="bg-'.$color.'">'.$message.'</span></li>';
	}
	
	/**
	 * Generates the HTML for a intermediate label in the timeline (used to 
	 */
	public function timelineIntermediateLabel($message, $color = "purple") {
		return '<li class="time-label"><span class="bg-'.$color.'">'.$message.'</span></li>';
	}
	
	/**
	 * Generates the HTML for the timelabel ending section.
	 */
	public function timelineEnd($endingIcon = "clock-o") {
		return '<li><i class="fa fa-'.$endingIcon.'"></i></li></ul>';
	}
	
	/** 
	 * Generates the HTML for an simple timeline item without icon, just a message.
	 * @param String $message the message for the timeline item.	
	 */
	public function timelineItemWithMessage($title, $message, $style = CRM_UI_STYLE_INFO) {
		$content = $this->calloutMessageWithTitle($title, $message, $style);
		return '<li><div class="timeline-item">'.$content.'</div></li>';
	}
	
	/**
	 * Generates the HTML code for the given notification.
	 * @param $notification Array an associative array object containing the notification data.
	 * @return String a HTML representation of the notification.
	 */
	public function timelineItemForNotification($notification) {
		$type = $notification["type"];
		$action = isset($notification["action"]) ? $notification["action"]: NULL;
		$date = $notification["date"];
		$content = $notification["text"];
				
		$color = $this->notificationColorForNotificationType($type);
		$icon = $this->notificationIconForNotificationType($type);
		$title = $this->headerTextForNotificationType($type, $action);
		$buttonTitle = $this->actionButtonTextForNotificationType($type);

		return $this->timelineItemWithData($title, $content, $date, $action, $buttonTitle, $icon, CRM_UI_STYLE_SUCCESS, $color);
	}
	
	/**
	 * Generates the HTML code for the given notification.
	 * @param $notification Array an associative array object containing the notification data.
	 * @return String a HTML representation of the notification.
	 */
	public function getNotificationsAsTimeLine($userid) {
		setlocale(LC_ALL, CRM_LOCALE);
		$todayAsDate = strftime("%x");
		$todayAsText = $this->lh->translationFor(CRM_NOTIFICATION_PERIOD_TODAY)." ($todayAsDate)";
		
		// today
		$timeline = $this->timelineStart($todayAsText);
		
		$notifications = $this->db->getTodayNotifications($userid);
		if (empty($notifications)) {
			$title = $this->lh->translationFor("message");
			$message = $this->lh->translationFor("no_notifications_today");
			$timeline .= $this->timelineItemWithMessage($title, $message);
		} else {
			foreach ($notifications as $notification) {
				$timeline .= $this->timelineItemForNotification($notification);
			}
		}
		// module notifications for today
		$mh = \creamy\ModuleHandler::getInstance();
		$modNots = $mh->applyHookOnActiveModules(
			CRM_MODULE_HOOK_NOTIFICATIONS, 
			array(CRM_NOTIFICATION_PERIOD => CRM_NOTIFICATION_PERIOD_TODAY), 
			CRM_MODULE_MERGING_STRATEGY_APPEND);
		if (isset($modNots)) { $timeline .= $modNots; }
		
        // past week
        $pastWeek = $this->lh->translationFor(CRM_NOTIFICATION_PERIOD_PASTWEEK);
		$timeline .= $this->timelineIntermediateLabel($pastWeek);

        $notifications = $this->db->getNotificationsForPastWeek($userid);
		if (empty($notifications)) {
			$title = $this->lh->translationFor("message");
			$message = $this->lh->translationFor("no_notifications_past_week");
			$timeline .= $this->timelineItemWithMessage($title, $message);
		} else {
			foreach ($notifications as $notification) {
				$timeline .= $this->timelineItemForNotification($notification);
			}
		}
		// module notifications for past week
		$modNots = $mh->applyHookOnActiveModules(
			CRM_MODULE_HOOK_NOTIFICATIONS, 
			array(CRM_NOTIFICATION_PERIOD => CRM_NOTIFICATION_PERIOD_PASTWEEK), 
			CRM_MODULE_MERGING_STRATEGY_APPEND);
		if (isset($modNots)) { $timeline .= $modNots; }

		// end timeline
		$timeline .= $this->timelineEnd();
        
        return $timeline;
	}

	/** Statistics */

	protected function datasetWithLabel($label, $data, $color = null) {
		if (!isset($color)) $color = \creamy\CRMUtils::randomRGBAColor(false);
		return '{ label: "'.$label.'", 
			fillColor: "'.$this->rgbaColorFromComponents($color, "0.9").'",
	        strokeColor: "'.$this->rgbaColorFromComponents($color, "0.9").'",
	        pointColor: "'.$this->rgbaColorFromComponents($color, "1.0").'",
	        pointStrokeColor: "'.$this->rgbaColorFromComponents($color, "1.0").'",
	        pointHighlightFill: "#fff",
	        pointHighlightStroke: "'.$this->rgbaColorFromComponents($color, "1.0").'",
	        data: ['.implode(",", $data).'] },';
	}
		
	public function generateLineChartStatisticsData($colors = null) {
		// initialize values
		$labels = "labels: [";
		$datasets = "datasets: [";
		$data = array();
		$statsArray = $this->db->getLastCustomerStatistics();
		$customerTypes = $this->db->getCustomerTypes();

		// create the empty data fields.
		foreach ($customerTypes as $customerType) { $data[$customerType["table_name"]] = array(); }
		
		// iterate through all customers
		foreach ($statsArray as $obj) {
			// store labels
			$formattedDate = date("Y-m-d",strtotime($obj['timestamp']));
			$labels .= '"'.$formattedDate.'",';
				
			// store customer number
			foreach ($customerTypes as $customerType) { $data[$customerType["table_name"]][] = $obj[$customerType["table_name"]] or 0; }
		}
		// finish data
		$labels = rtrim($labels, ",")."],";
		$i = 0;
		foreach ($customerTypes as $customerType) { 
			$color = isset($colors[$i]) ? $colors[$i] : null;
			$datasets .= $this->datasetWithLabel($customerType["description"], $data[$customerType["table_name"]], $color); 
			$i++;
		}
		$datasets = rtrim($datasets, ",")."]";

		return $labels."\n".$datasets;
	}

	protected function pieDataWithLabelAndNumber($label, $number, $color = null) {
		if (!isset($color)) $color = \creamy\CRMUtils::randomRGBAColor(false);
		return '{ value: '.$number.', color: "'.$this->rgbaColorFromComponents($color, "1.0").'", highlight: "'.$this->rgbaColorFromComponents($color, "1.0").'", label: "'.$label.'" },';
	}
	
	public function generatePieChartStatisticsData($colors = null) {
		$result = "";
		$customerTypes = $this->db->getCustomerTypes();
		$i = 0;
		foreach ($customerTypes as $customerType) {
			$num = $this->db->getNumberOfClientsFromTable($customerType["table_name"]);
			$color = isset($colors[$i]) ? $colors[$i] : null;
			$result .= $this->pieDataWithLabelAndNumber($customerType["description"], $num, $color);
			$i++;
		}
		return $result;
	}

	public function generateStatisticsColors() {
		$num = $this->db->getNumberOfCustomerTypes();
		$result = array();
		for ($i = 0; $i < $num; $i++) { 
			$result[] = \creamy\CRMUtils::randomRGBAColor(false);
		}
		return $result;
	}

	public function rgbaColorFromComponents($components, $alpha = "1.0") {
		return "rgba(".$components["r"].", ".$components["g"].", ".$components["b"].", ".(isset($components["a"]) ? $components["a"] : $alpha).")";
	} 

	/** Utility functions */

	/**
	 * Generates a relative time string for a given date, relative to the current time.
	 * @param $mysqltime String a string containing the time extracted from MySQL.
	 * @param $maxdepth Int the max depth to dig when representing the time, 
	 *        i.e: 3 days, 4 hours, 1 minute and 20 seconds with $maxdepth=2 would be 3 days, 4 hours.
	 * @return String the string representation of the time relative to the current date.
	 */
	public function relativeTime($mysqltime, $maxdepth = 1) {
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