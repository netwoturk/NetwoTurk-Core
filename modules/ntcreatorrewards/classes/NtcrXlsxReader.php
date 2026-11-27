<?php
class NtcrXlsxReader
{
    const MAX_FILE=20971520; const MAX_XML=134217728; const MAX_ROWS=100000;
    private $required=array('Data Month','Creator ID','Creator nickname','Handle','Diamonds','Valid days(d)','LIVE duration(h)','Estimated bonus - Activeness incentive');
    public function read($file)
    {
        if (!class_exists('ZipArchive')) { throw new RuntimeException('XLSX aktarımı için PHP Zip uzantısı gereklidir.'); }
        if (!is_file($file) || filesize($file)>self::MAX_FILE) { throw new RuntimeException('XLSX bulunamadı veya 20 MB sınırını aşıyor.'); }
        $zip=new ZipArchive(); if ($zip->open($file)!==true) { throw new RuntimeException('Geçerli bir XLSX dosyası değil.'); }
        $sheet=$this->firstWorksheet($zip); $shared=$this->sharedStrings($zip); $xml=$zip->getFromName($sheet);
        if ($xml===false || strlen($xml)>self::MAX_XML || stripos($xml,'<f')!==false) { $zip->close(); throw new RuntimeException('Çalışma sayfası okunamadı veya formül içeriyor.'); }
        $rows=$this->parseRows($xml,$shared); $zip->close(); return $this->map($rows);
    }
    private function firstWorksheet($zip)
    {
        for($i=0;$i<$zip->numFiles;$i++){ $n=$zip->getNameIndex($i); if(preg_match('#^xl/worksheets/sheet\d+\.xml$#',$n)) return $n; }
        throw new RuntimeException('XLSX içinde çalışma sayfası yok.');
    }
    private function sharedStrings($zip)
    {
        $xml=$zip->getFromName('xl/sharedStrings.xml'); if($xml===false) return array();
        if(strlen($xml)>self::MAX_XML) throw new RuntimeException('Paylaşılan metin tablosu çok büyük.');
        $sx=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NONET|LIBXML_COMPACT); $out=array();
        foreach($sx->si as $si){ $parts=array(); if(isset($si->t))$parts[]=(string)$si->t; foreach($si->r as $r)$parts[]=(string)$r->t; $out[]=implode('',$parts); } return $out;
    }
    private function parseRows($xml,$shared)
    {
        $sx=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NONET|LIBXML_COMPACT); $out=array();
        foreach($sx->sheetData->row as $row){ if(count($out)>=self::MAX_ROWS) throw new RuntimeException('Satır sınırı aşıldı.'); $r=array();
            foreach($row->c as $c){ $a=$c->attributes(); preg_match('/^([A-Z]+)/',(string)$a['r'],$m); $idx=$this->col($m[1]); $type=(string)$a['t'];
                if($type==='inlineStr')$v=(string)$c->is->t; else {$v=(string)$c->v; if($type==='s')$v=isset($shared[(int)$v])?$shared[(int)$v]:'';}
                $r[$idx]=$v;
            } if(array_filter($r,static function($v){return trim((string)$v)!=='';}))$out[]=$r;
        } return $out;
    }
    private function col($s){$n=0;for($i=0;$i<strlen($s);$i++)$n=$n*26+(ord($s[$i])-64);return $n-1;}
    private function map($rows)
    {
        $headerIndex=null;$map=array(); foreach($rows as $i=>$row){$trial=array();foreach($row as $k=>$v)$trial[trim((string)$v)]=$k;if(in_array('Data Month',array_keys($trial),true)&&in_array('Handle',array_keys($trial),true)){$headerIndex=$i;$map=$trial;break;}}
        if($headerIndex===null)throw new RuntimeException('Başlık satırı bulunamadı.'); foreach($this->required as $h)if(!isset($map[$h]))throw new RuntimeException('Zorunlu sütun eksik: '.$h);
        $out=array(); foreach(array_slice($rows,$headerIndex+1) as $n=>$row){$item=array('_source_row'=>$headerIndex+$n+2);foreach($this->required as $h)$item[$h]=isset($row[$map[$h]])?trim((string)$row[$map[$h]]):'';if($item['Data Month']===''&&$item['Handle']===''&&$item['Creator ID']==='')continue;if(!preg_match('/^\d{6}$/',$item['Data Month']))continue;$out[]=$item;} return $out;
    }
}
