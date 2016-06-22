<?php
// @author woojean
// https://github.com/woojean/PHPCoverage


// $logDir 覆盖率日志目录
// $isNew 是否是新的测试，如果是，将清除现有日志目录中的内容
// $ignoreFiles 需要忽略的目录、文件列表
function PHPCoverage_Inject($config=[]){
	if(empty($config['log_dir'])){
		var_dump('log dir '.$config['log_dir'].' can not be null!');
		exit(0);
	}
	if(!is_writable($config['log_dir'])){
		var_dump('log dir '.$config['log_dir'].' is not writable!');
		exit(0);
	}

	/*
	if($config['new_test']){
		PHPCoverage_ClearDir($config['log_dir']);
	}
	*/

	if (function_exists('xdebug_start_code_coverage')) {
		//xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);  // 太耗电
		xdebug_start_code_coverage();
		register_shutdown_function('PHPCoverage_Gather',$config['log_dir'],$config['ignore_file'],$config['new_test']);
	}
	else{
		var_dump('xdebug unreachable !');
	}
}

function PHPCoverage_Gather($logDir,$ignoreFile,$isNew){
	//$coverageData = xdebug_get_code_coverage(XDEBUG_CC_UNUSED|XDEBUG_CC_DEAD_CODE);
	
	$coverageData = xdebug_get_code_coverage();
	xdebug_stop_code_coverage();
	//$dir = "/vagrant/logs/PHPCoverage";
	$file = sprintf('%s/%s.coverage', $logDir, uniqid());
	file_put_contents($file,json_encode($coverageData));
	
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
}


/* tool functions */
function PHPCoverage_ClearDir($dir) {
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
