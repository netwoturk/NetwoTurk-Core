<?php
require __DIR__.'/../classes/NtcrHandle.php';require __DIR__.'/../classes/NtcrDecimal.php';require __DIR__.'/../classes/NtcrXlsxReader.php';
$ok=0;$assert=function($cond,$msg)use(&$ok){if(!$cond)throw new RuntimeException('FAIL: '.$msg);$ok++;};
$assert(NtcrHandle::normalize('dilekalk13')==='dilekalk13','plain handle');$assert(NtcrHandle::normalize('@DilekAlk13')==='dilekalk13','at handle');$assert(NtcrHandle::normalize('https://www.tiktok.com/@dilekalk13?x=1')==='dilekalk13','url handle');
try{NtcrHandle::normalize('https://tiktok.example/@x');$assert(false,'fake domain');}catch(InvalidArgumentException $e){$assert(true,'fake domain rejected');}
$assert(NtcrDecimal::points('50','0.02','40')===40000,'50 USD');$assert(NtcrDecimal::points('52.213','0.02','40')===41770,'52.213 USD');$assert(NtcrDecimal::points('26.607','0.02','40')===21285,'26.607 USD');
if(isset($argv[1])){$rows=(new NtcrXlsxReader())->read($argv[1]);$sum='0';$positive=0;$zeroId=0;$missingHandle=0;foreach($rows as $r){$v=$r['Estimated bonus - Activeness incentive'];if($v!==''){$sum=bcadd($sum,NtcrDecimal::normalize($v,8),8);if(bccomp($v,'0',8)>0)$positive++;}if(trim($r['Creator ID'])==='0')$zeroId++;if(trim($r['Handle'])==='')$missingHandle++;}$assert(count($rows)===131,'reference row count');$assert(bccomp($sum,'267.7252',8)===0,'reference incentive sum');$assert($positive===41,'positive incentive count');$assert($zeroId===31,'zero creator id count');$assert($missingHandle===3,'missing handle count');}
echo "OK {$ok} assertions\n";
