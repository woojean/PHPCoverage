<?php

class Reporter{

}

$keywords = array('__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor','const','CONST');

$coverIndex = [10,15,16,18,20,44,90,91,92,93,94,96,160,161,167];

$srcFile = 'UserCouponModel.php';

$s = file_get_contents($srcFile);


//$s = str_replace(' ','&nbsp;', $s);



$arr = split(PHP_EOL, $s);

$html = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
.line_unexcutable{
	background-color:#F0F0F0;
}

.line_covered{
	background-color:#B4EEB4;
}

.line_uncovered{
	background-color:#FFFAF0;
}

.keyword{
	font-weight:bold;
	color:blue;
}

.line_num{
	font-size:0.8em;
}

</style>
</head>
<body>
	$TABLE$
</body>
</html>
';

$allLines = count($arr);
$excutableLines = 0;
$coverLines =count($coverIndex);



$str = '';
foreach ($arr as $key => $value) {
	$code = str_replace(' ','&nbsp;', $value);
	//if(trim($value)[-1] != ';'){
	if(!is_line_excutable($value)){
		$str .= '<tr class="line_unexcutable"><td class="line_num">'.$key.'</td><td >'.$code.'</td></tr>';
	}
	elseif(in_array($key+1, $coverIndex)){
		$str .= '<tr class="line_covered"><td class="line_num">'.$key.'</td><td>'.$code.'</td></tr>';
		$excutableLines +=1;
	}
	else{
		$str .= '<tr class="line_uncovered"><td class="line_num">'.$key.'</td><td >'.$code.'</td></tr>';
		$excutableLines +=1;
	}

}

$flag = false;

function is_line_excutable($line){
	global $flag;
	if($flag && empty(strstr($line,'*/'))){
		return false;
	}

	if( !empty(strstr($line,'/*')) ){
		$flag = true;
		return false;
	}
	if( !empty(strstr($line,'*/')) ){
		$flag = false;
		return false;
	}
	if( !empty(strstr($line,'//')) ){
		return false;
	}
	if(strlen(trim($line))<2){
		return false;
	}
	return true;
}

$str = '<table>'.$str.'</table>';

for($i=1;$i<2;$i++){
	$str.=$str;
}

$html = str_replace('$TABLE$', $str, $html);
foreach ($keywords as $key => $value) {
	$html=str_replace($value.'&nbsp;','<label class="keyword">'.$value.'&nbsp;</label>', $html);
}

var_dump($allLines);
var_dump($excutableLines);
var_dump($coverLines);
echo $html;


//file_put_contents('report.html', $str);






