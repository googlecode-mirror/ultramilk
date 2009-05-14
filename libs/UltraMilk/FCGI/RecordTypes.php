<?php
class UltraMilk_FCGI_RecordTypes {
	const BEGIN_REQUEST 	= 1;
	const ABORT_REQUEST 	= 2;
	const END_REQUEST		= 3;
	const PARAMS			= 4;
	const STDIN				= 5;
	const STDOUT			= 6;
	const STDERR			= 7;
	const UltraMilk_FCGI_DATA			= 8;
	const GET_VALUES		= 9;
	const GET_VALUES_RESULT = 10;
	const UNKNOWN_TYPE 		= 11;
	const MAXTYPE 			= self::UNKNOWN_TYPE;
}
?>