<?php
/**
 * Class to load config files
 *
 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
 */
class PMAI_Config implements IteratorAggregate {
	
	protected $config = [];
	
	protected $loaded = [];
	
	public static function createFromFile(string $filePath, string $section = NULL): self {
		$config = new self();

		return $config->loadFromFile($filePath, $section);
	}
	
	public function loadFromFile(string $filePath, string $section = NULL): self {
		if ( ! is_null($section)) {
			$this->config[$section] = self::createFromFile($filePath);
		} else {
			$filePath = realpath($filePath);
			if ($filePath and ! in_array($filePath, $this->loaded)) {
				require $filePath;

                $config = (!isset($config)) ? array() : $config;
				$this->loaded[] = $filePath;
				$this->config = array_merge($this->config, $config);
			}
		}
		return $this;
	}
	/**
	 * Return value of setting with specified name
	 * @param string $field Setting name
	 * @param string[optional] $section Section name to look setting in
	 * @return mixed
	 */
	public function get($field, $section = NULL) {
		return ! is_null($section) ? $this->config[$section]->get($field) : $this->config[$field];
	}
	
	/**
	 * Magic method for checking whether some config option are set
	 * @param string $field
	 * @return mixed
	 */
	public function __isset($field) {
		return isset($this->config[$field]);
	}
	/**
	 * Magic method to implement object-like access to config parameters
	 * @param string $field
	 * @return mixed
	 */
	public function __get($field) {
		return $this->config[$field];
	}
	
	/**
	 * Return all config options as array
	 * @return array
	 */
	public function toArray($section = null) {
		return ! is_null($section) ? $this->config[$section]->toArray() : $this->config;
	}
	
	public function getIterator() {
		return new ArrayIterator($this->config);
	}
	
}