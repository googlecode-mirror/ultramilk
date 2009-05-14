<?php
class UltraMilk_ConfReader {
	const BLOCK_START 	 = '{';
	const BLOCK_END	  	 = '}';
	const STRING_END  	 = ';';
	const PAIR_SEPARATOR = ':';
	const QUOTES		 = '"';	
	public $name;
	public $port;
	public $path;
	public $workers;
	public $role;
	private $data;
	private $pointer = 0;
	public function open($path) {
		$this->data = file_get_contents($path);
	}
	public function read() {
		$name = $this->readNameUntilBlockStarts();
		if($name === false) return false;
		$this->name = $name;
		while(true) {
			$pair = $this->readPairsUntilBlockEnds();
			if($pair === false) break;
			list($key, $value) = $pair;
			switch($key) {
				case 'port':
					$value = intval($value);
					if(!$value) throw new RuntimeException('Bad port');
					$this->port = $value;
					break;
				case 'host':
					if(!$value) throw new RuntimeException('Bad host');
					$this->host = $value;
					break;
				case 'path':
					if(!$value || !file_exists($value)) throw new RuntimeException('Bad path "'.$value.'"');
					$this->path = $value;
					break;
				case 'workers':
					$value = intval($value);
					if(!$value) throw new RuntimeException('Bad port');
					$this->workers = $value;
					break;
				case 'role':
					switch($value) {
						case 'responder':
							$this->role = UltraMilk_FCGI_Roles::RESPONDER;
							break;
						case 'filter':
							$this->role = UltraMilk_FCGI_Roles::FILTER;
							break;
						case 'authorizer':
							$this->role = UltraMilk_FCGI_Roles::AUTHORIZER;
							break;
						default:
							throw new RuntimeException('Bad role '.$value);
							break;
					}
					break;
				default:
					throw new RuntimeException('Bad key '.$key);
					break;
			}
		}
		return true;
	}
	public function close() {
		$this->data 	= null;
		$this->pointer 	= 0;
		$this->path 	= null;
		$this->role 	= null;
		$this->name 	= null;
		$this->host 	= null;
		$this->workers 	= null;
	}
	private function getNextChar() {
		while(true) {
			$char = $this->data[$this->pointer++];
			if($char === '') return false;
			if(strpos(" \n\r\t", $char) === false) return $char;
		}
	}
	private function readNameUntilBlockStarts() {
		$name = '';
		while(true) {
			$char = $this->getNextChar();
			if($char === false) return false;
			if($char === self::BLOCK_START) break;
			$name .= $char;
		}
		return $name;
	}
	private function readPairsUntilBlockEnds() {
		$string = '';
		while(true) {
			$char = $this->getNextChar();
			if($char === false) return false;
			if($char === self::BLOCK_END) {
				return false;
			}			
			if($char === self::STRING_END) return explode(self::PAIR_SEPARATOR, $string);
			$string .= $char;
		}
		
	}
}
?>