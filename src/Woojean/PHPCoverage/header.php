<?php
require_once 'Injecter.php';
Woojean\PHPCoverage\Injecter::Inject([
	'log_dir'=>'/srv/http/logs',
	'ignore_file'=> 'example.ignore',
	'is_repeat' => true
]);
?>
