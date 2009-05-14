<?php
class UltraMilk_FCGI_StdInRecord extends UltraMilk_FCGI_Record {
	public function __construct($version, $requestId, $content) {
		parent::__construct($version, UltraMilk_FCGI_RecordTypes::STDIN, $requestId, $content);
	}
}
?>