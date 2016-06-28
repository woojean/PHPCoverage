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
	$reporter = new PHPCoverageReporter($logDir,$ignoreFile);
	$reporter->report();
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

class PHPCoverageReporter{
	private $logDir = '';
	private $ignoreFile = '';

	function __construct($logDir,$ignoreFile){
		$this->logDir = $logDir;
		$this->ignoreFile = $ignoreFile;
	}

	protected function isIgnore($srcPath){
		$ignores = require($this->ignoreFile);

		foreach ($ignores as $key => $value) {			
			$count = substr_count($srcPath,$value);
			if($count > 0){
				return true;
			}
		}
		return false;
	}
	

	public function report(){
		$allCoverageData = $this->mergeCoverages();
		
		$html = $this->TEMPLATE_REPORT;
		$items = '';

		$sumFiles = 0;
		$sumLines = 0;
		$sumExcutable = 0;
		$sumCovered = 0;
		$sumCoverRate = 0;

		foreach ($allCoverageData as $file => $lines) {
			if($this->isIgnore($file)){
				continue;
			}



			$fileItem = $this->TEMPLATE_NAV_ITEM;
			$reportPath = str_replace('/','-',$file);
			$reportPath = str_replace('.php','.html',$reportPath);

			$ret = $this->parseSrcFile($reportPath,$file,$lines);

			$fileStyle = '';
			$coverageRate = floatval($ret['coverage_rate']);
			if($coverageRate <= 0.2){
				$fileStyle = 'coverage_5';
			}
			else if( 0.2 < $coverageRate && $coverageRate <= 0.4){
				$fileStyle = 'coverage_4';
			}
			else if( 0.2 < $coverageRate && $coverageRate <= 0.4){
				$fileStyle = 'coverage_3';
			}
			else if( 0.2 < $coverageRate && $coverageRate <= 0.4){
				$fileStyle = 'coverage_2';
			}
			else{
				$fileStyle = 'coverage_1';
			}

			
			$fileCoverage = strval($coverageRate*100).'% ('.$ret['lines_covered'].'/'.$ret['lines_excutable'].')';

			$fileItem = str_replace('%FILE_PATH%',$reportPath,$fileItem);
			$fileItem = str_replace('%FILE_NAME%',$file,$fileItem);
			$fileItem = str_replace('%FILE_STYLE%',$fileStyle,$fileItem);
			$fileItem = str_replace('%FILE_COVERAGE%',$fileCoverage,$fileItem);

			$items .= $fileItem;

			$sumFiles += 1;
			$sumLines += intval($ret['lines_all']);
			$sumExcutable += intval($ret['lines_excutable']);
			$sumCovered += intval($ret['lines_covered']);
		}
		$sumCoverRate = strval(($sumCovered/$sumExcutable)*100).'%';

		$html = str_replace('%FILE_ITEMS%', $items, $html);


        $html = str_replace('%SUM_FILES%', $sumFiles, $html);
        $html = str_replace('%SUM_LINES%', $sumLines, $html);
        $html = str_replace('%SUM_EXCUTABLE%', $sumExcutable, $html);
        $html = str_replace('%SUM_COVERED%', $sumCovered, $html);
        $html = str_replace('%SUM_COVERRATE%', $sumCoverRate, $html);

		file_put_contents($this->logDir.DIRECTORY_SEPARATOR.'report.html', $html);
	}

	protected function parseSrcFile($reportPath,$srcPath,$lines){
		
		$result = [
			'lines_all' => 0,
			'lines_excutable' => 0,
			'lines_covered' => 0,
			'coverage_rate' => 0
		];

		$coverIndex = array_keys($lines);
		$src = file_get_contents($srcPath);
		$arr = split(PHP_EOL, $src);

		$html = $this->TEMPLATE_FILR_REPORT;
		$allLines = count($arr)-2;
		$excutableLines = 0;
		$coverLines =count($coverIndex);

		$str = '';
		foreach ($arr as $key => $value) {
			if($key<1){
				continue;
			}
			$code = preg_replace('/\s+/','&nbsp;',$value);

			if(!$this->is_line_excutable($value)){
				if(in_array($key+1, $coverIndex)){
					$coverLines -= 1; // !!!
				}
				$str .= '<tr class="e"><td class="line_num">'.$key.'</td><td >'.$code.'</td></tr>';
			}
			elseif(in_array($key+1, $coverIndex)){
				$str .= '<tr class="c"><td class="line_num">'.$key.'</td><td>'.$code.'</td></tr>';
				$excutableLines +=1; 
			}
			else{
				$str .= '<tr class="u"><td class="line_num">'.$key.'</td><td >'.$code.'</td></tr>';
				$excutableLines +=1;
			}

		}
		$str = '<table>'.$str.'</table>';
		$html = str_replace('$TABLE$', $str, $html);
		foreach ($this->keywords as $key => $value) {
			$html=str_replace($value.'&nbsp;','<label class="k">'.$value.'&nbsp;</label>', $html);
		}

		$reportPath = $this->logDir.DIRECTORY_SEPARATOR.$reportPath;

		file_put_contents($reportPath, $html);


		$result['lines_all'] = $allLines;
		$result['lines_excutable'] = $excutableLines;
		$result['lines_covered'] = $coverLines;
		$result['coverage_rate'] = round(floatval($coverLines)/floatval($excutableLines) ,2);
		return $result;
	}


	private $docFlag = false;
	public function is_line_excutable($line){
		if($this->docFlag && empty(strstr($line,'*/'))){
			return false;
		}

		if( !empty(strstr($line,'/*')) ){
			$this->docFlag = true;
			return false;
		}
		if( !empty(strstr($line,'*/')) ){
			$this->docFlag = false;
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

	protected function mergeCoverages(){
		$allCoverageData = [];
		$coverageFiles = $this->getCoverageFiles();
		foreach ($coverageFiles as $key => $value) {
			$file = $this->logDir.DIRECTORY_SEPARATOR.$value;
			$arr = json_decode(file_get_contents($file),true);
			foreach ($arr as $fileName => $coverageLines) {
				if(isset($allCoverageData[$fileName])){
					$allCoverageData[$fileName] = $allCoverageData[$fileName]+$coverageLines;
				}
				else{
					$allCoverageData[$fileName] = $coverageLines;
				}
			}
		}
		return $allCoverageData;
	}

	protected function getCoverageFiles(){
		$files = [];
		$dh = opendir($this->logDir);
  		while ($file=readdir($dh)) {
  			if('coverage' == pathinfo($file)['extension']){
  				$files[] = $file;
  			}
    	}
  		closedir($dh);
  		return $files;
	}



	// ====================== templates ===========================
	private $keywords = array('__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor','const','CONST');

	private $TEMPLATE_NAV_ITEM = '<li><a href="%FILE_PATH%"><label>%FILE_NAME%</label><span class="%FILE_STYLE%">%FILE_COVERAGE%</span></a></li>';

	private $TEMPLATE_REPORT = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHPCoverage Code Coverage Report</title>
    <style>
        html,body,div,span,iframe{
            margin: 0;
            padding: 0;
        }

		html,body{
			height: 100%;
            font-family: "Microsoft YaHei" ! important;
		}
		
		ul{  
			list-style-type: none;  
			margin:0;
			padding:0;
			float: left;
            width: 100%;
		}  

		li{
			list-sytle-type:none;
            height: 30px;
            line-height: 30px;
            background: #CCFFCC;
			font-size:0.8em;
			text-align:left;
			border:1px solid white;
			padding:0;
        }

        .coverage_1{
            background: #AAFFAA;
        }
        .coverage_2{
            background: #B3EE3A;
        }
        .coverage_3{
            background: #EEEE00;
        }
        .coverage_4{
            background: #FFC125;
        }
        .coverage_5{
            background: #F08080;
        }

        li label{
			width:70%;
            float:left;
			height: 30px;
            cursor:pointer;
        }
		
		li span{
			width:25%;
			height: 30px;
            float:right;
            text-align: right;
        }
		
        .select{
            border:3px solid red;
        }

        .sum{
            position:fixed;
            left:0;
            top:0;
            width: 100%;
            height: 30px;
            line-height: 30px;       
            padding-left: 50px;
            border:1px solid #eee;
            background: #FFF68F;
            
        }

        .sum {
           font-size: 0.8em; 
        }

        .sum label{

        }

        .sum span{
            color: red;
            margin-right: 50px;
        }

        .sum a {
            float: right;
            margin-right: 100px;
        }


        .navgation{
			margin-top: 30px;
			border:1px solid #eee;
			width:30%;
            text-align: left;
			padding:0;
            overflow:auto;
        }
		
        .filelist{
            overflow:auto;
        }

        .con{
            width: 100%;
			height:100%;
			float:right;
        }

		.container{
            position:fixed;
            left:30%;
            width: 70%;
            top:30px;
			height:100%;
		}
    </style>
</head>
<body>
    <div class="sum">
        <label>执行总文件数：</label><span>%SUM_FILES%</span>
        <label>代码总行数：</label><span>%SUM_LINES%</span>
        <label>可执行代码行数：</label><span>%SUM_EXCUTABLE%</span>
        <label>覆盖可执行代码行数：</label><span>%SUM_COVERED%</span>
        <label>可执行代码覆盖率：</label><span>%SUM_COVERRATE%</span>
        <a href="https://github.com/woojean/PHPCoverage">Generated by PHPCoverage</a>
    </div>
    <div class="navgation" id="navgation">
        <div class="filelist">
		<ul>
			%FILE_ITEMS%
		</ul>
        </div>
    </div>
	<div class="container">
		<iframe class="con" src="file_1.html" frameborder="0" id="content"></iframe>
	</div>
</body>
<script>
    var btns = document.getElementById("navgation").getElementsByTagName("a");
    var tabCon = document.getElementById("content");
    for (var i = 0; i < btns.length; i++) {
        btns[i].onclick = function(){
            for (var i = 0; i < btns.length; i++) {
                btns[i].className = "";
            };
            this.className = "select";
            tabCon.src = this.href;
            return false;
        }
    };
</script>
</html>';

	private $TEMPLATE_FILR_REPORT = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
.e{
	background-color:#F0F0F0;
}

.c{
	background-color:#B4EEB4;
}

.u{
	background-color:#FFFAF0;
}

.k{
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
}
