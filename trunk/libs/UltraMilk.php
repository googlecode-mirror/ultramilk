<?php
define('CRLF', 	"\r\n");
define('SP', 	' ');
define('UltraMilk_DIR', dirname(__FILE__));
class UltraMilk {
	private $socketString 	= 'tcp://127.0.0.1:9000';
	private $maxConnections	= 5;
	private $onRequestHandler;
	private $role;
	private $children = array();
	public function __construct($socketString, $maxConnections, $role, UltraMilk_OnRequestHandler $onRequestHandler) {
		$this->socketString 	= $socketString;
		$this->maxConnections	= $maxConnections;
		$this->role 			= $role;
		$this->onRequestHandler = $onRequestHandler;
	}
	public function run() {
		$count = 0;
		$socket = stream_socket_server($this->socketString, &$errno, &$errstr);
		if(!$socket) throw new RuntimeException($errstr);
		while(true) {
			while(count($this->children) < $this->maxConnections) {
				$count++;
				$pid = pcntl_fork();
				if($pid == -1) {
					throw new RuntimeException('Couldn\'t fork');
				} elseif($pid == 0) {
					$this->createChild($socket, $count);
					exit;
				}
				$this->children[] = $pid;
			}
			while(pcntl_wait($status, WNOHANG OR WUNTRACED) > 0) {
				usleep(500000);
			}
			while(list($key, $val) = each($this->children)) {
				if(!posix_kill($val, 0)) {
					unset($this->children[$key]);
				}
			}
			$this->children = array_values($this->children);
			usleep(500000);
		}
	}
	private function createChild($socket, $count) {
		while ($conn = stream_socket_accept($socket, -1)) {
			while($record = UltraMilk_FCGI_Record::read($conn)) {
				if($record->getType() == UltraMilk_FCGI_RecordTypes::BEGIN_REQUEST) {
					if($record->getRole() !== $this->role) throw new RuntimeException('UltraMilk role not equal request role!');
					if($record->getRole() == UltraMilk_FCGI_Roles::RESPONDER) {
						$this->onRequestHandler->invoke(new UltraMilk_HTTP_Request(UltraMilk_FCGI_Request::create($conn, $record->getVersion(), $this->role, $record->getRequestId(), $record->getFlags())));
					} else {
						throw new RuntimeException('This role doesn\'t supported at this moment!');
					}
				}
				unset($record);
				if(!is_resource($conn)) break;
			}
		}

	}
	public static function autoload($name) {
		if(strpos($name, 'UltraMilk') !== 0) {
			return false;
		}
		$path = UltraMilk_DIR.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $name).'.php';
		if(file_exists($path)) {
			include_once($path);
			return true;
		}
		return false;
	}
}
?>
