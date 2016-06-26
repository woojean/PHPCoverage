<?php 

require 'Injecter.php';
PHPCoverage_Inject([
	'log_dir'=>'/vagrant/logs/PHPCoverage',
	'ignore_file'=>'/vagrant/www/github/PHPCoverage/ignores/example.ignore',
	'is_repeat' => true 
]);


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