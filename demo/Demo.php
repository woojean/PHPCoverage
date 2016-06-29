<?php 

require_once '/vagrant/www/github/PHPCoverage/src/Woojean/PHPCoverage/Injecter.php';
Woojean\PHPCoverage\Injecter::Inject([
	'log_dir'=>'/vagrant/logs/PHPCoverage',
	'ignore_file'=>'/vagrant/www/github/PHPCoverage/demo/example.ignore',
	'is_repeat' => true 
]);

/*
$reporter = new PHPCoverageReporter('/vagrant/logs/PHPCoverage','/vagrant/www/github/PHPCoverage/ignores/example.ignore',true);
$reporter->report();
*/

class A{
	private $_a = 0;
	private $_b = 1;

	public function func(){
		$c = $this->func1()+$this->func2()+$this->func3()+$this->func4();
		return $c;		
	}

	public function func1(){
		$c = $this->_a + $this->_b;
		echo $c.' ';
		return $c;		
	}

	public function func2(){
		$c = $this->_a - $this->_b;
		echo $c.' ';
		return $c;		
	}


	public function func3(){
		$c = $this->_a * $this->_b;
		echo $c.' ';
		return $c;		
	}

	public function func4(){
		$c = $this->_a / $this->_b;
		echo $c.' ';
		return $c;		
	}

	public function func5(){
		$c = $this->_a ^ $this->_b;
		echo $c.' ';
		return $c;		
	}
}

$a =  new A();
$c = $a->func();

echo $c;


$a = [
	'1'=>1,
	'2'=>1
];

$b = [
	'2'=>1,
	'3'=>1
];

$c = $a+$b;

echo $c;


