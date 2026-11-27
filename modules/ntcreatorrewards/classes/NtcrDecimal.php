<?php
class NtcrDecimal
{
    public static function normalize($v,$scale=8)
    {
        $v=trim(str_replace(array("\xc2\xa0",' '),'',(string)$v));
        if ($v==='') { throw new InvalidArgumentException('Ondalık değer boş.'); }
        if (substr_count($v,',')===1 && strpos($v,'.')===false) { $v=str_replace(',','.',$v); }
        if (!preg_match('/^-?\d+(?:\.\d+)?$/D',$v)) { throw new InvalidArgumentException('Geçersiz ondalık değer: '.$v); }
        list($i,$f)=array_pad(explode('.',$v,2),2,''); $neg=substr($i,0,1)==='-'; $i=ltrim($i,'-');
        $f=substr(str_pad($f,$scale,'0'),0,$scale); return ($neg?'-':'').(ltrim($i,'0')===''?'0':ltrim($i,'0')).'.'.$f;
    }
    public static function points($incentive,$rate='0.02000000',$fx='40.00000000')
    {
        $a=self::toInt(self::normalize($incentive,8)); $b=self::toInt(self::normalize($rate,8)); $c=self::toInt(self::normalize($fx,8));
        if ($a<0 || $b<0 || $c<=0) { throw new InvalidArgumentException('Hesaplama değerleri pozitif olmalıdır.'); }
        if (function_exists('bcmul')) {
            $product=bcmul(bcmul((string)$a,(string)$b,0),(string)$c,0);
            return (int)bcdiv(bcmul($product,'1000',0),'1000000000000000000000000',0);
        }
        $share=self::mulDivFloor($a,$b,100000000);
        $tryScaled=self::mulDivFloor($share,$c,100000000);
        return intdiv($tryScaled*1000,100000000);
    }
    private static function toInt($v) { return (int)str_replace('.','',$v); }
    private static function mulDivFloor($a,$b,$d)
    {
        if (function_exists('bcdiv')) { return (int)bcdiv(bcmul((string)$a,(string)$b,0),(string)$d,0); }
        if ($a!==0 && $b>intdiv(PHP_INT_MAX,$a)) { throw new OverflowException('Ondalık hesap için BCMath gereklidir.'); }
        return intdiv($a*$b,$d);
    }
}
