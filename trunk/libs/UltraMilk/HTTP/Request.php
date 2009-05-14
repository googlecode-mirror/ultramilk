<?php
class UltraMilk_HTTP_Request {
	private $code = 200;
	private $headers = array();
	private $headersWritten = false;
	private $request;
	public function __construct(UltraMilk_FCGI_ResponderRequest  $request) {
		$this->request = $request;
	}
	public function write($value) {
		if(!$this->headersWritten) $this->writeHeaders();
		$this->request->getStdOut()->write($value);
	}
	public function read() {
		return $this->request->getStdIn()->read();
	}
	public function close() {
		if(!$this->headersWritten) $this->writeHeaders();
		$this->request->setAppStatus($this->code);
		$this->request->close();
	}
	public function setStatusCode($code) {
		$this->code = $code;
	}
	public function setHeader($name, $value) {
		if($this->headersWritten) throw new RuntimeException('Headers already sent!');
		$this->headers[] = array($name, $value);	
	}
	private function writeHeaders() {
		$out = '';
		foreach($this->headers as $header) {
			$out .= $header[0].':'.SP.$header[1].CRLF;
		}
		$this->request->getStdOut()->write($out.CRLF);	
		$this->headersWritten = true;
	}
}
?>