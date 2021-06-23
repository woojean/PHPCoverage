<?php
namespace Woojean\PHPCoverage;

use Redis;



class Diff{

    private $host1;
    private $port1;
    private $host2;
    private $port2;

    function __construct($host1, $port1, $host2, $port2){
        $this->host1 = $host1;
        $this->port1 = $port1;
        $this->host2 = $host2;
        $this->port2 = $port2;
    }
    public function report(){
        $allCoverageData1 = $this->getCoveragesFromRedis($this->host1,$this->port1);
        $allCoverageData2 = $this->getCoveragesFromRedis($this->host2,$this->port2);
        $filenames1 = array_keys($allCoverageData1);
        $filenames2 = array_keys($allCoverageData2);
        $commonFilenames = array_intersect($filenames1, $filenames2);
        $commonCoverageData = [];
        foreach ($commonFilenames as $filename){
           $commonCoverageData[$filename] = array_intersect($allCoverageData1[$filename],$allCoverageData2[$filename]);
        }
        $commonLineNums = 0;
        foreach ($commonCoverageData as $filename => $temp) {
            $coverIndex = array_values($temp);
            var_dump($coverIndex);
            $src = file_get_contents($filename);
            $srcCodeArr = explode(PHP_EOL, $src);
            $coverNum =count($coverIndex);
            foreach ($srcCodeArr as $key => $value) {
                if($key<1){
                    continue;
                }
                if(!$this->is_line_excutable($value)){
                    if(in_array($key+1, $coverIndex)){
                        $coverNum -= 1; // !!!
                    }
                }
            }
            $commonLineNums += $coverNum;
        }
        echo $commonLineNums;

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

    protected function getCoveragesFromRedis($host,$port){
        $allCoverageData = [];
        $redis = new Redis();
        $redis->connect($host, $port);
        echo "Connection to server successfully...\n";
        $fileNames = $redis->keys("*.php");
        foreach ($fileNames as $filename){
            $lines = $redis->sMembers($filename);
            $allCoverageData[$filename] = $lines;
        }
        return $allCoverageData;
    }

}

$diff = new Diff("127.0.0.1","6379","127.0.0.1","6379");
$diff->report();