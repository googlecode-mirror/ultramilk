<?php
class UltraMilk_FCGI_BeginRequestRecord extends UltraMilk_FCGI_Record {
	const KEEP_CONN = 1;
	private $role;
	private $flags;
	public function __construct($version, $requestId, $role, $flags) {
		parent::__construct($version, UltraMilk_FCGI_RecordTypes::BEGIN_REQUEST, $requestId, null);
		$this->role 	= $role;
		$this->flags	= $flags;
	}
	public function getRole() {
		return $this->role;
	}
	public function getFlags() {
		return $this->flags;
	}
}
?>