<?php
/**
 * Lightspeed high-performance hiphop-php optimized PHP framework
 *
 * Copyright (C) <2011> by <Priit Kallas>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Priit Kallas <kallaspriit@gmail.com>
 * @package Lightspeed
 * @subpackage JSON
 */

/**
 * Class for sending a JSON response.
 *
 * @author Priit Kallas <kallaspriit@gmail.com>
 * @package Lightspeed
 * @subpackage JSON
 */
class JsonResponse {
	
	/**
	 * The success message to send back.
	 * 
	 * @var string 
	 */
	private $_successMessage;
	
	/**
	 * The error message to send back.
	 * 
	 * @var string 
	 */
	private $_errorMessage;
	
	/**
	 * Was the request successful.
	 * 
	 * @var boolean 
	 */
	private $_success = true;
	
	/**
	 * Array of validation field errors.
	 * 
	 * Keys are the namse of form fields and values are the error messages. Used
	 * with the Validator.
	 * 
	 * @var array 
	 */
	private $_fieldErrors = array();
	
	/**
	 * Various response data to send back.
	 * 
	 * @var type 
	 */
	private $_data = array();
	
	/**
	 * Debug data.
	 * 
	 * @var array 
	 */
	private $_debug = array();
	
	/**
	 * Redirection request url.
	 * 
	 * @var string 
	 */
	private $_redirect = null;

	/**
	 * Returns whether a data field exists.
	 * 
	 * @param string $field Properties name
	 * @return boolean 
	 */
	public function __isset($field) {
		if (array_key_exists($field, $this->_data)) {
			return true;
		}

		return false;
	}

	/**
	 * Returns data parameter value or null if not set.
	 * 
	 * @param string $field Properties name
	 * @return mixed 
	 */
	public function __get($field) {
		if (array_key_exists($field, $this->_data)) {
			return $this->_data[$field];
		}

		return null;
	}

	/**
	 * Sets a parameter value.
	 * 
	 * @param string $field Properties name
	 * @param mixed $value The value
	 */
	public function __set($field, $value) {
		$this->_data[$field] = $value;
	}

	/**
	 * Populates the data with given value, all previous data is lost.
	 * 
	 * @param array $data Data to use
	 */
	public function populate(array $data) {
		$this->_data = $data;
	}

	/**
	 * Returns all of the data that has been set.
	 * 
	 * @return array 
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Resets the data to an empty array. 
	 */
	public function reset() {
		$this->_data = array();
	}

	/**
	 * Sets the state to success with optional message.
	 * 
	 * The message can be a translation key in which case its translated to
	 * current language.
	 * 
	 * Clears any error message if set.
	 * 
	 * @param string $message Message itself or translation key
	 */
	public function success($message = null) {
		if (!empty($message)) {
			$this->_successMessage = Translator::getIfExists($message);
		}

		$this->_success = true;
		$this->_errorMessage = null;
	}

	/**
	 * Sets the state to failure with optional message.
	 * 
	 * The message can be a translation key in which case its translated to
	 * current language.
	 * 
	 * The list of form field errors can be requested from the Validator.
	 * 
	 * Clears any error message if set.
	 * 
	 * @param string $message Message itself or translation key
	 * @param array $fieldErrors Optional list of form field errors
	 */
	public function error($message = null, array $fieldErrors = array()) {
		if (!empty($message)) {
			$this->_errorMessage = Translator::getIfExists($message);
		}

		$this->_fieldErrors = array_merge($this->_fieldErrors, $fieldErrors);

		$this->_success = false;
		$this->_successMessage = null;
	}

	/**
	 * Adds a debug value to the response.
	 * 
	 * The debug value is only sent back if LS_DEBUG is set to true.
	 * 
	 * @param mixed $var The value to add
	 * @param string $title Optional title
	 */
	public function debug($var, $title = null) {
		if (!isset($title)) {
			$title = 'Debug #'.(count($this->_debug) + 1);
		}
		
		$this->_debug[$title] = $var;
	}
	
	/**
	 * Requests for the page to redirect to a new URL.
	 * 
	 * @param string $url Url to navigate to
	 */
	public function redirect($url) {
		$this->_redirect = $url;
	}

	/**
	 * Returns the current success message.
	 * 
	 * @return string
	 */
	public function getSuccessMessage() {
		return $this->_successMessage;
	}

	/**
	 * Returns the current error message.
	 * 
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->_errorMessage;
	}

	/**
	 * Returns the response data compiled into JSON.
	 * 
	 * The response contains the following keys:
	 * - success: was the request successful
	 * - successMessage: optional success message, null if not set
	 * - errorMessage: optional error message, null if not set
	 * - fieldErrors: array of field errors, empty array if not set
	 * - redirect: optional redirect request url, null if not set
	 * - debug: array of debug data, empty array if not set or not debug mode
	 * 
	 * @return string 
	 */
	public function getJson() {
		$data = array_merge(
			array(
				'success'        => $this->_success,
				'successMessage' => $this->_successMessage,
				'errorMessage'   => $this->_errorMessage,
				'fieldErrors'    => $this->_fieldErrors,
				'redirect'       => $this->_redirect,
				'debug'          => LS_DEBUG ? $this->_debug : array()
			),
			$this->_data
		);

		return json_encode($data);
	}

	/**
	 * Sends the JSON response.
	 * 
	 * Clears any previous output if available and also sends cache control
	 * headers to stop IE from caching AJAX requests if requested and possible.
	 * 
	 * Also exists to stop other parts of the script from messing up the
	 * response. This can be disabled by a parameter.
	 * 
	 * You can disable sending the content type header by setting the parameter
	 * to null.
	 * 
	 * No content type of cache headers are sent if they have already been sent.
	 * 
	 * @param boolean $exit Should the script exit after sending the response
	 * @param boolean $avoidCache Should the script send cache avoiding headers
	 * @param string $contentType Content type header to send
	 */
	public function send(
		$exit = true,
		$avoidCache = true,
		$contentType = 'application/json'
	) {
		ob_end_clean();
		
		if (!headers_sent()) {
			if (isset($contentType)) {
				header('Content-type: '.$contentType);
			}
			
			if ($avoidCache) {
				// fuck you too IE, why the heck would you cache AJAX requests
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			}
		}
		
		echo $this->getJson();
		
		if ($exit) {
			exit(0);
		}
	}

	/**
	 * Returns the string representation thats the same as returned by getJson()
	 * 
	 * @return string 
	 */
	public function __toString() {
		return $this->getJson();
	}
}

?>