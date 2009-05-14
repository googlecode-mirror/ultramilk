<?php
class UltraMilk_FCGI_Request {
	private $conn;
	private $role;
	private $version;
	private $requestId;
	private $flags;
	private $status = 0;
	protected $params;
	public static function create($conn, $version, $role, $requestId, $flags) {
		switch($role) {
			case UltraMilk_FCGI_Roles::RESPONDER:
				return new UltraMilk_FCGI_ResponderRequest($conn, $version, $requestId, $flags);
				break;
		}
	}
	public function __construct($conn, $version, $role, $requestId, $flags) {
		$paramsStreamRecord = new UltraMilk_FCGI_StreamRecord($conn, $version, UltraMilk_FCGI_RecordTypes::PARAMS, $requestId);
		$this->params 		= $paramsStreamRecord->getContent();
		$this->conn			= $conn;
		$this->version		= $version;
		$this->requestId	= $requestId;
		$this->role			= $role;
		$this->flags		= $flags;
	}
	public function close($force = false) {
		UltraMilk_FCGI_Record::write($this->conn, new UltraMilk_FCGI_EndRequestRecord($this->version, $this->requestId, $this->status, UltraMilk_FCGI_EndRequestRecord::REQUEST_COMPLETE));
		if($this->flags !== UltraMilk_FCGI_BeginRequestRecord::KEEP_CONN || $force == true) fclose($this->conn);
	}
	public function getParams() {
		return $this->params;
	}
	public function getRole() {
		return $this->role;
	}
	public function setAppStatus($status) {
		$this->status = $status;
	}
}
?>