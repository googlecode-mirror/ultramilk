<?php
class UltraMilk_FCGI_StreamRecord {
	private $conn;
	private $type;
	private $requestId;
	private $length;
	private $closed;
	private $content = '';
	private $isReadable;
	private $version;
	public function __construct($conn, $version, $type, $requestId, $length = -1) {
		if(!in_array($type, array(UltraMilk_FCGI_RecordTypes::PARAMS, UltraMilk_FCGI_RecordTypes::STDIN, UltraMilk_FCGI_RecordTypes::STDOUT, UltraMilk_FCGI_RecordTypes::STDERR))) {
			throw new UltraMilk_FCGI_NotStreamingRecordException();
		}
		if(!in_array($type, array(UltraMilk_FCGI_RecordTypes::PARAMS, UltraMilk_FCGI_RecordTypes::STDIN))) {
			$this->isReadable = true;
		} else {
			$this->isReadable = false;
		}
		if($type == UltraMilk_FCGI_RecordTypes::PARAMS) {
			$this->content = array();
		}
		$this->conn			= $conn;
		$this->version		= $version;
		$this->type			= $type;
		$this->requestId	= $requestId;
		$this->length		= $length;
	}
	public function isReadable() {
		return $this->isReadable;
	}
	public function read() {
		if($this->closed) return false;
		$record = UltraMilk_FCGI_Record::read($this->conn);
		if(!$record) return false;
		if($record->getType() === UltraMilk_FCGI_RecordTypes::ABORT_REQUEST) {
			throw new UltraMilk_FCGI_AbortRequestException();
		}
		if($record->getRequestId() !== $this->requestId || $record->getType() !== $this->type) {
			throw new UltraMilk_FCGI_UnexpectedRecordException();
		}
		if(($content = $record->getContent()) == null) {
			$this->closed = true;
			return false;
		}
		if($this->type == UltraMilk_FCGI_RecordTypes::PARAMS) {
			$this->content = array_merge($this->content, $content);
		} else {
			$this->content .= $content;
		}
		return $content;
	}
	public function getContent() {
		if($this->closed) return $this->content;
		while($this->read());
		return $this->content;
	}
	public function write($value) {
		if($this->closed) return;
		$length = strlen($value);
		$l = 65535;
		$offset = 0;
		while($offset < $length) {
			if($offset + 65535 > $length) {
				$l = $length - $offset;
			}
			UltraMilk_FCGI_Record::write($this->conn, new UltraMilk_FCGI_Record($this->version, $this->type, $this->requestId, substr($value, $offset, $l)));
			$offset += 65535;
		}
	}
	public function putContent($value) {
		if($this->closed) return;
		$this->write($value);
		$this->close();
	}
	public function close() {
		if($this->closed) return;
		UltraMilk_FCGI_Record::write($this->conn, new UltraMilk_FCGI_Record($this->version, $this->type, $this->requestId, null));
	}
}
?>