<?php
class Test {
	private $i = 1;
	private $initMemUsage;
	public function __construct() {
		$this->initMemUsage = memory_get_usage();
	}
	public function run($request) {
		$num = $this->i++;
		$delta = memory_get_usage() - $this->initMemUsage;
		$request->setHeader('Content-type', 'text/html');
		$request->write('pid: '.getmypid().', current child request number: '.$num.', current memory usage: '.memory_get_usage().', memory usage delta: '.$delta.', your request is:');
		while($data = $request->read()) {
			$request->write($data);
		}
		$request->close();
	}
}
?>