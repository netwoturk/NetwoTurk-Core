<?php
class NtcrHandle
{
    public static function normalize($input)
    {
        $value=trim((string)$input);
        if ($value==='') { throw new InvalidArgumentException('TikTok kullanıcı adı boş bırakılamaz.'); }
        if (preg_match('#^https?://#i',$value)) {
            $u=parse_url($value);
            $host=strtolower(isset($u['host'])?$u['host']:'');
            if (!in_array($host,array('tiktok.com','www.tiktok.com','m.tiktok.com'),true)) {
                throw new InvalidArgumentException('Yalnızca gerçek TikTok profil bağlantıları kabul edilir.');
            }
            $path=isset($u['path'])?$u['path']:'';
            if (!preg_match('#^/@([^/]+?)/?$#u',$path,$m)) {
                throw new InvalidArgumentException('Bağlantıdan kullanıcı adınızı belirleyemedik. Lütfen profilinizdeki @ ile başlayan kullanıcı adını yazın.');
            }
            $value=rawurldecode($m[1]);
        }
        $value=ltrim(trim($value),'@');
        if ($value==='' || preg_match('/\s/u',$value) || preg_match('~[/?#]~u',$value)) {
            throw new InvalidArgumentException('Geçerli bir TikTok kullanıcı adı girin; görünen ad veya yalnızca @ işareti kabul edilmez.');
        }
        $length=function_exists('mb_strlen')?mb_strlen($value,'UTF-8'):strlen($value);
        if ($length>64) { throw new InvalidArgumentException('TikTok kullanıcı adı çok uzun.'); }
        return function_exists('mb_strtolower')?mb_strtolower($value,'UTF-8'):strtolower($value);
    }
}
