<?php
class UltraMilk_FCGI_ResponderRequest extends UltraMilk_FCGI_Request {
	private $in;
	private $out;
	private $err;
	public function __construct($conn, $version, $requestId, $flags) {
		parent::__construct($conn, $version, UltraMilk_FCGI_Roles::RESPONDER, $requestId, $flags);
		$this->in 	= new UltraMilk_FCGI_StreamRecord($conn, $version, UltraMilk_FCGI_RecordTypes::STDIN, $requestId, $this->params['CONTENT_LENGTH']);
		$this->out 	= new UltraMilk_FCGI_StreamRecord($conn, $version, UltraMilk_FCGI_RecordTypes::STDOUT, $requestId);
		$this->err 	= new UltraMilk_FCGI_StreamRecord($conn, $version, UltraMilk_FCGI_RecordTypes::STDERR, $requestId);
	}
	public function getStdIn() {
		return $this->in;
	}
	public function getStdOut() {
		return $this->out;
	}
	public function getStdErr() {
		return $this->err;
	}
	public function close($force = false) {
		$this->out->close();
		$this->err->close();
		parent::close();
	}
}
?>