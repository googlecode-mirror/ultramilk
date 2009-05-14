<?php
class UltraMilk_FCGI_Record {
	private $version;
	private $type;
	private $requestId;
	private $content;
	public static function intToByte($int, $num) {
		$current = $int;
		$result  = '';
		for($i = $num-1; $i >= 0; $i--) {
			$temp 	 = $current >> (8*$i);
			$result	 .= chr($temp);
			$current = $current - ($temp << (8*$i));		  
		}
		return $result;		
	}
	public static function write($conn, $record) {
		if(!in_array($record->getType(), array(	UltraMilk_FCGI_RecordTypes::END_REQUEST,
		UltraMilk_FCGI_RecordTypes::STDOUT,
		UltraMilk_FCGI_RecordTypes::STDERR,
		UltraMilk_FCGI_RecordTypes::GET_VALUES_RESULT
		))) {
			throw new UltraMilk_FCGI_UnexpectedRecordException();
		}
		$bytes = '';
		$res = chr(0);
		switch($record->getType()) {
			case UltraMilk_FCGI_RecordTypes::GET_VALUES_RESULT:
				throw new Exception('Not implemented');
				break;
			case UltraMilk_FCGI_RecordTypes::END_REQUEST:
				$content = self::intToByte($record->getAppStatus(), 4);
				$content .= $record->getProtocolStatus();
				$content .= $res.$res.$res;
				break;
			default:
				$content = $record->getContent();
				break;
		}
		$contentLength = strlen($content);
		if($contentLength > 65535) throw new OutOfRangeException();
		$bytes .= chr($record->getVersion());
		$bytes .= chr($record->getType());
		$bytes .= self::intToByte($record->getRequestId(), 2);
		$bytes .= self::intToByte($contentLength,2);
		$bytes .= $res.$res;
		//echo 'write[header]:'.$bytes."\n";
		$bytes .= $content;
		//echo 'write[body]:'.$content."\n";
		fwrite($conn, $bytes);
	}
	public static function read($conn) {
		$bytes = stream_get_contents($conn, 8);
		//echo 'read[header]:'.$bytes."\n";
		if(!$bytes) return false;
		$version 		= ord($bytes[0]);
		$type			= ord($bytes[1]);
		$requestId		= (ord($bytes[2]) << 8) + ord($bytes[3]);
		$contentLength	= (ord($bytes[4]) << 8) + ord($bytes[5]);
		$paddingLength	= ord($bytes[6]);
		$reserved		= ord($bytes[7]);
		$contentData    = stream_get_contents($conn, $contentLength);
		//echo 'read[body]:'.$contentData."\n";
		stream_get_contents($conn, $paddingLength);
		switch($type) {
			case UltraMilk_FCGI_RecordTypes::BEGIN_REQUEST:
				$role 	= (ord($contentData[0]) << 8) + ord($contentData[1]);
				$flags	= ord($contentData[2]);
				return new UltraMilk_FCGI_BeginRequestRecord($version, $requestId, $role, $flags);
				break;
			case UltraMilk_FCGI_RecordTypes::ABORT_REQUEST:
				return new UltraMilk_FCGI_AbortRequestRecord($version, $requestId);
				break;
			case UltraMilk_FCGI_RecordTypes::PARAMS:
				$params = array();
				$offset = 0;
				while($param = self::readParam($contentData, $offset)) $params[] = $param;
				return new UltraMilk_FCGI_ParamsRecord($version, $requestId, $params);
				break;
			case UltraMilk_FCGI_RecordTypes::STDIN:
				return new UltraMilk_FCGI_StdInRecord($version, $requestId, $contentData);
				break;
		}
	}
	private static function readParamLength($data, &$offset) {
		if(($data[$offset] >> 7) == 0) {
			$length = ord($data[$offset]);
			$offsetIncrement = 1;
		} else {
			$length = ((ord($data[$offset]) & 127) << 24) + (ord($data[$offset+1]) << 16) + (ord($data[$offset+2]) << 8) + ord($data[$offset+3]);
			$offsetIncrement = 4;
		}
		$offset += $offsetIncrement;
		return $length;
	}
	private static function readParam($data, &$offset) {
		if($offset >= strlen($data)) return false;
		$nameLength 	= self::readParamLength($data, $offset);
		$valueLength 	= self::readParamLength($data, $offset);
		$name 			= substr($data, $offset, $nameLength);
		$offset			+= $nameLength;
		$value 			= substr($data, $offset, $valueLength);
		$offset			+= $valueLength;
		return array($name => $value);
	}
	public function __construct($version, $type, $requestId, $content) {
		$this->version		= $version;
		$this->type			= $type;
		$this->requestId	= $requestId;
		$this->content		= $content;
	}
	public function getVersion() {
		return $this->version;
	}
	public function getType() {
		return $this->type;
	}
	public function getRequestId() {
		return $this->requestId;
	}
	public function getContent() {
		return $this->content;
	}
}
?>