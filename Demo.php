<?php 

require 'Injecter.php';
PHPCoverage_Inject([
	'log_dir'=>'/vagrant/logs/PHPCoverage',
	'new_test'=>false,
	'ignore_file'=>'/vagrant/www/github/PHPCoverage/ignores/example.ignore'
]);


class A{
	private $_a = 0;
	private $_b = 1;

	public function add(){
		$c = $this->_a + $this->_b;
		return $c;		
	}
}

$a =  new A();
$c = $a->add();

echo $c;