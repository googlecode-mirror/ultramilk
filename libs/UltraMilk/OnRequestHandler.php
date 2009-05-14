<?php
class UltraMilk_OnRequestHandler {
	private $object;
	private $method;
	public function __construct($method, $object = null) {
		$this->object = $object;
		$this->method = $method;
	}
	public function invoke($request) {
		$method = $this->method;
		$this->object ? $this->object->$method($request) : $method($request);
	}
}
?>