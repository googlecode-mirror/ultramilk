<?php
class Test {
	private $i = 1;
	public function run($request) {
		$num = $this->i++;
		$request->setHeader('Content-type', 'text/html');
		$request->write('Hello! Your request is:');
		while($data = $request->read()) {
			$request->write($data);
		}
		$request->close();
	}
}
?>