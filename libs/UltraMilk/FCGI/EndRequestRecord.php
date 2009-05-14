<?php
class UltraMilk_FCGI_EndRequestRecord extends UltraMilk_FCGI_Record {
	const REQUEST_COMPLETE	= 0;
	const CANT_MPX_CONN		= 1;
	const OVERLOADED		= 2;
	const UltraMilk_FCGI_UNKNOWN_ROLE = 3;
	private $appStatus;
	private $protocolStatus;
	public function __construct($version, $requestId, $appStatus, $protocolStatus) {
		parent::__construct($version, UltraMilk_FCGI_RecordTypes::END_REQUEST, $requestId, null);
		$this->appStatus		= $appStatus;
		$this->protocolStatus	= $protocolStatus;
	}
	public function getAppStatus() {
		return $this->appStatus;
	}
	public function getProtocolStatus() {
		return  $this->protocolStatus;
	}
}
?>