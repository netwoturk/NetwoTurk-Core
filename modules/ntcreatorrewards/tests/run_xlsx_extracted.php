<?php
if ($argc<2) { fwrite(STDERR,"Usage: php run_xlsx_extracted.php <extracted-dir>\n"); exit(2); }
class ZipArchive
{
    public $numFiles=0; private $root; private $names=array();
    public function open($path){$this->root=rtrim((string)getenv('NTCR_XLSX_EXTRACTED'),'/\\');$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root,FilesystemIterator::SKIP_DOTS));foreach($it as $f){if($f->isFile())$this->names[]=str_replace('\\','/',substr($f->getPathname(),strlen($this->root)+1));}$this->numFiles=count($this->names);return true;}
    public function getNameIndex($i){return $this->names[$i];}
    public function getFromName($n){$p=$this->root.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$n);return is_file($p)?file_get_contents($p):false;}
    public function close(){return true;}
}
require __DIR__.'/../classes/NtcrHandle.php';require __DIR__.'/../classes/NtcrDecimal.php';require __DIR__.'/../classes/NtcrXlsxReader.php';
$rows=(new NtcrXlsxReader())->read($argv[1]);$sum='0';$positive=0;$zeroId=0;$missingHandle=0;$neither=0;$neitherSum='0';foreach($rows as $r){$v=$r['Estimated bonus - Activeness incentive'];if($v!==''){$sum=bcadd($sum,NtcrDecimal::normalize($v,8),8);if(bccomp($v,'0',8)>0)$positive++;}if(trim($r['Creator ID'])==='0')$zeroId++;if(trim($r['Handle'])==='')$missingHandle++;if((trim($r['Creator ID'])===''||trim($r['Creator ID'])==='0')&&trim($r['Handle'])===''){$neither++;if($v!=='')$neitherSum=bcadd($neitherSum,$v,8);}}
$got=array('rows'=>count($rows),'total'=>$sum,'positive'=>$positive,'zero_creator_id'=>$zeroId,'missing_handle'=>$missingHandle,'missing_both'=>$neither,'missing_both_total'=>$neitherSum);echo json_encode($got,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
$expected=array('rows'=>131,'total'=>'267.72520000','positive'=>41,'zero_creator_id'=>31,'missing_handle'=>3,'missing_both'=>2,'missing_both_total'=>'1.32640000');if($got!==$expected){fwrite(STDERR,"Reference validation failed\n");exit(1);}echo "REFERENCE OK\n";
