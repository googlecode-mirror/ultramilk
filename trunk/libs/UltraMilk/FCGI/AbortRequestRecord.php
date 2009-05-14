<?php
class UltraMilk_FCGI_AbortRequestRecord extends UltraMilk_FCGI_Record {
	public function __construct($version, $requestId) {
		parent::__construct($version, UltraMilk_FCGI_RecordTypes::ABORT_REQUEST, $requestId, null);
	}
}
?>