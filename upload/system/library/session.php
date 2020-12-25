<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Session class
*/
class Session {
	protected $adaptor;
	protected $session_id;
	public $data = array();

	/**
	 * Constructor
	 *
	 * @param	string	$adaptor
	 * @param	object	$registry
 	*/
	public function __construct($adaptor, $registry = '') {
		$class = 'Session\\' . $adaptor;
		
		if (class_exists($class)) {
			if ($registry) {
				$this->adaptor = new $class($registry);
			} else {
				$this->adaptor = new $class();
			}	
			
			register_shutdown_function([$this, 'close']);
		} else {
			throw new \Exception('Error: Could not load session adaptor ' . $adaptor . ' session!');
		}
	}
	
	/**
	 * Get Session ID
	 *
	 * @return	string
 	*/	
	public function getId() {
		return $this->session_id;
	}

	/**
	 * Start
	 *
	 * @param	string	$session_id
	 *
	 * @return	string
 	*/	
	public function start($session_id = '') {
		if (!$session_id) {
			if (function_exists('random_bytes')) {
				$session_id = substr(bin2hex(random_bytes(26)), 0, 26);
			} else {
				$session_id = substr(bin2hex(openssl_random_pseudo_bytes(26)), 0, 26);
			}
		}

		if (preg_match('/^[a-zA-Z0-9,\-]{22,52}$/', $session_id)) {
			$this->session_id = $session_id;
		} else {
			error_log('Error: Invalid session ID!');
		}
		
		$this->data = $this->adaptor->read($session_id);
		
		return $session_id;
	}
	
	/**
	 * Close
	 *
	 * Writes the session data to storage
 	*/
	public function close() {
		$this->adaptor->write($this->session_id, $this->data);
	}
	
	/**
	 * Destroy
	 *
	 * Deletes the current session from storage
 	*/	
	public function destroy() {
		$this->data = array();

		$this->adaptor->destroy($this->session_id);
	}
}