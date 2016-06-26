<?php
// @author woojean
// https://github.com/woojean/PHPCoverage


// $logDir 覆盖率日志目录
// $ignoreFiles 需要忽略的目录、文件列表
// $repeat 是否累加测试（累加测试期间，代码文件不应该变动，否则影响覆盖率行的判断）
function PHPCoverage_Inject($config=[]){
	$logDir = isset($config['log_dir']) ? $config['log_dir'] : '';
	$ignoreFile = isset($config['ignore_file']) ? $config['ignore_file'] : '';
	$isRepeat = isset($config['is_repeat']) ? $config['is_repeat'] : False;

	if(!is_writable($logDir)){
		echo ('PHPCoverage config error ：log dir "<u>'.$logDir.'</u>" can not be null and must be writable !');
		exit(0);
	}

	if(!empty($ignoreFile) && !file_exists($ignoreFile)){
		echo ('PHPCoverage config error ：ignore file "<u>'.$ignoreFile.'</u>" is not exists !');
		
	}

	if(!$isRepeat){
		PHPCoverage_ClearDir($config['log_dir']);
	}

	if (function_exists('xdebug_start_code_coverage')) {
		xdebug_start_code_coverage();
		register_shutdown_function('PHPCoverage_Gather',$logDir,$ignoreFile);
	}
	else{
		echo ('PHPCoverage config error ：xdebug unreachable !');
		exit(0);
	}
}


function PHPCoverage_Gather($logDir,$ignoreFile){
	$coverageData = xdebug_get_code_coverage();
	xdebug_stop_code_coverage();
	$coverageFile = sprintf('%s/%s.coverage', $logDir, uniqid());
	file_put_contents($coverageFile,json_encode($coverageData));
	PHPCoverage_Reporter($logDir,$ignoreFile);
}

function PHPCoverage_Reporter($logDir,$ignoreFile){
	echo $logDir;
}


function PHPCoverage_ClearDir($dir) {
  	$dh = opendir($dir);
  	while ($file=readdir($dh)) {
    	if($file!="." && $file!="..") {
	      	$fullpath=$dir."/".$file;
	      	if(!is_dir($fullpath)) {
	          	unlink($fullpath);
	      	} else {
	          	PHPCoverage_ClearDir($fullpath);
	      	}
    	}
  	}
  	closedir($dh);
}


function PHPCoverage_GetPhpCode($src) {
  	$dh = opendir($dir);
  	while ($file=readdir($dh)) {
    	if($file!="." && $file!="..") {
	      	$fullpath=$dir."/".$file;
	      	var_dump($fullpath);
	      	if(!is_dir($fullpath)) {
	          	unlink($fullpath);
	      	} else {
	          	PHPCoverage_ClearDir($fullpath);
	      	}
    	}
  	}
  	closedir($dh);
}


class Reporter{

}



/*

	$reportPath = $logDir.'/report.html';
	$html = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
.container{
	width:100%;
}

.header{
	position:fixed;
	top:0px;
	left:0px;
	width:100%;
	height:200px;
}

.content{
	width:100%;
}

.left{
	width:30%;
}

.navigator_title{
	width:100%;
}

.navigator{
	width:30%;
	position:fixed;
    left:10px;
    top:200px;
    border:1px solid red;
}

.right{
	width:70%;
	float:right;
}

.logs{
}


.placeholder{
	height:100px;
}

</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h2>报告</h2>
		</div>
		<div class="content">
			<div class="left">
				<div class="navigator">
					<div class="navigator_title">
						<label>文件列表</label>
					</div>
					<ul>$FILE_LIST$</ul>
				</div>
			</div>
			<div class="right">
				<div class="logs">
					<ul>$LOG_LIST$</ul>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
';
	$strFileList = '';
	$strLogList = '';
	foreach ($coverageData as $key => $value) {
		$file = '<li><a href="#'.$key.'">'.$key.'</a></li>';
		//$src = file_get_contents($key);
		$src = '带行号的源文件';
		$log = '<li><div class="log"><div><u>'.$key.'</u></div>'.$src.'</div></li>';
		$strFileList.=$file;
		$strLogList.=$log;
	}
	$html = str_replace('$FILE_LIST$',$strFileList,$html);
	$html = str_replace('$LOG_LIST$',$strLogList,$html);
	file_put_contents($reportPath,$html);

*/