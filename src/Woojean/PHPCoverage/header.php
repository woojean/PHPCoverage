<?php
require_once '/home/hui/PHPCoverage/src/Woojean/PHPCoverage/Injecter.php';
Woojean\PHPCoverage\Injecter::Inject([
	'log_dir'=>'/logs',
	'ignore_file'=> '/home/hui/PHPCoverage/demo/example.ignore',
	'is_repeat' => true
]);
?>
