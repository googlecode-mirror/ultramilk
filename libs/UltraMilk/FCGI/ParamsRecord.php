<?php
class UltraMilk_FCGI_ParamsRecord extends UltraMilk_FCGI_Record {
	public function __construct($version, $requestId, $params) {
		parent::__construct($version, UltraMilk_FCGI_RecordTypes::PARAMS, $requestId, $params);
	}
}
?>