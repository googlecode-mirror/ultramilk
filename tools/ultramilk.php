#!/usr/bin/php
<?php
define('CONF_FILE', '/usr/local/etc/ultramilk.conf');
include_once(dirname(__FILE__).'/../libs/UltraMilk.php');
spl_autoload_register(array('UltraMilk', 'autoload'));
if(!isset($argv[1]) || !function_exists($argv[1])) {
	help();
} else {
	$command = $argv[1];
	$command();	
}
function start() {
	$output;
	exec('ps -aux | grep ultramilk', &$output);
	if(count($output) > 1) {
		echo 'UltraMilk already running...'."\n";
		exit;
	}
	echo 'starting UltraMilk...'."\n";
	$reader = new UltraMilk_ConfReader();
	$reader->open(CONF_FILE);
	while($reader->read()) {
		include_once($reader->path);
		$app = new $reader->name;
		$ultraMilk = new UltraMilk('tcp://'.$reader->host.':'.$reader->port, $reader->workers, $reader->role, new UltraMilk_OnRequestHandler('run', $app));
		$pid = pcntl_fork();
		if($pid == -1) {
			echo 'Failed to start UltraMilk...';
			exit;
		} elseif($pid == 0) {
			$ultraMilk->run();
			break;
		}
	}
	echo 'UltraMilk started...'."\n";
}
function stop() {
	exec('ps -aux | grep ultramilk | awk \'$2!~/'.getmypid().'/ {print "kill "$2}\' | sh');	
	echo 'UltraMilk stopped...'."\n";
}
function restart() {
	stop();
	start();
}
function help() {
	echo 'usage: ultramilk start | stop | restart | help'."\n";
}
?>