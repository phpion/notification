<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class to manage notifications between application requests.
 * 
 * @package Kohana/Notification
 * @author Michał "phpion" Płonka <michal@notifero.pl>
 */
abstract class Kohana_Notification {
	/**
	 * Array of Notification class instances.
	 * 
	 * @var array
	 */
	protected static $instances = array();
	
	/**
	 * Messages array.
	 * 
	 * @var array
	 */
	protected $messages = array();
	
	/**
	 * Configuration array.
	 * 
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * Session object.
	 * 
	 * @var Session
	 */
	protected $session;
	
	/**
	 * Returns instance of Notification class.
	 * 
	 * @param string $name Istance name.
	 * @param array $config Configuration array.
	 * @return Notification Instance.
	 */
	public static function instance($name = 'default', array $config = array()) {
		$name = (string)$name;

		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new Notification($name, $config);
		}
		
		return self::$instances[$name];
	}
	
	/**
	 * Constructor.
	 * 
	 * @param string $name Istance name.
	 * @param array $config Configuration array.
	 */
	protected function __construct($name = 'default', array $config = array()) {
		$this->config = array_merge(Kohana::$config->load('notification.'.(string)$name), $config);
		
		$this->session = Session::instance($this->config['session']['type']);
		
		foreach ($this->session->get_once($this->config['session']['key'], array()) as $message) {
			$this->set($message['type'], $message['message']);
		}
	}
	
	/**
	 * Creates confirmation notification.
	 * 
	 * @param string $message Message.
	 * @return int Current notification key.
	 */
	public function confirm($message) {
		return $this->set(self::CONFIRM, $message, TRUE);
	}
	
	/**
	 * Creates error notification.
	 *
	 * @param string $message Message.
	 * @return int Current notification key.
	 */
	public function error($message) {
		return $this->set(self::ERROR, $message, FALSE);
	}
	
	/**
	 * Creates notification.
	 * 
	 * @param string $type Type.
	 * @param string $message Message.
	 * @param bool $session TRUE to add notification to session, FALSE otherwise.
	 * @return int Current notification key.
	 */
	public function set($type, $message, $session = FALSE) {
		$type = (string)$type;
		$message = (string)$message;
		
		$data = array(
			'type' => $type,
			'message' => $message,
			'timestamp' => time()
		);
		
		$this->messages[] = $data;
		
		end($this->messages);
		$key = key($this->messages);
		
		if ($session === TRUE) {
			$session = $this->session->get_once($this->config['session']['key'], array());
			
			$session[$key] = $data;
			
			$this->session->set($this->config['session']['key'], $session);
		}
		
		return $key;
	}
	
	/**
	 * Returns notification(s).
	 * 
	 * @param int|string|NULL $key int for single message, string for all messages of given type, NULL for all messages.
	 * @return mixed Single message, all messages of given type, all messages.
	 */
	public function get($key = NULL) {
		if (is_int($key)) {
			if (isset($this->messages[$key])) {
				return $this->messages[$key];
			}
			else {
				return NULL;
			}
		}
		else if (is_string($key)) {
			$return = array();
			
			foreach ($this->messages as $id => $message) {
				if ($message['type'] === $key) {
					$return[$id] = $message;
				}
			}
			
			return $return;
		}
		else {
			return $this->messages;
		}
	}
	
	/**
	 * Deletes notification(s)
	 * @param int|string|NULL $key int for single message, string for all messages of given type, NULL for all messages.
	 * @return boolean TRUE if any message has been deleted, FALSE otherwise.
	 */
	public function delete($key = NULL) {
		if (is_int($key)) {
			if (isset($this->messages[$key])) {
				unset($this->messages[$key]);
				
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else if (is_string($key)) {
			$return = FALSE;
			
			foreach ($this->messages as $id => $message) {
				if ($message['type'] === $key) {
					unset($this->messages[$id]);
					$this->session->delete($this->config['session']['key'].'.'.$id);
					
					$return = TRUE;
				}
			}
			
			return $return;
		}
		else {
			$this->messages = array();
			$this->delete($this->config['session']['key']);
			
			return TRUE;
		}
	}
	
	/**
	 * Renders notifications or single notification.
	 * 
	 * @param string $type Type.
	 * @param string $message Message.
	 * @return string Rendered notification(s).
	 */
	public function render($type = NULL, $message = NULL) {
		if ($type !== NULL && $message !== NULL) {
			$type = (string)$type;
			$message = (string)$message;
			
			if (isset($this->config['template'][$type])) {
				return sprintf($this->config['template'][$type], $message);
			}
			else {
				return sprintf($this->config['template']['default'], $type, $message);
			}
		}
		else {
			$return = '';
			
			foreach ($this->messages as $message) {
				$return .= $this->render($message['type'], $message['message']);
			}
			
			return $return;
		}
	}
	
	/**
	 * Renders notifications.
	 * 
	 * @return string Rendered notifications.
	 */
	public function __toString() {
		return $this->render();
	}
	
	/**
	 * Confirmation type.
	 * 
	 * @var string
	 */
	const CONFIRM = 'confirm';
	
	/**
	 * Error type.
	 * 
	 * @var string
	 */
	const ERROR = 'error';
}