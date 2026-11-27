<?php
class NtcrService
{
    public static function publisherForCustomer($idCustomer,$idShop)
    { return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ntcr_publisher` WHERE id_customer='.(int)$idCustomer.' AND id_shop='.(int)$idShop); }
    public static function requestHandle($idCustomer,$idShop,$input)
    {
        $h=NtcrHandle::normalize($input);$now=date('Y-m-d H:i:s');$db=Db::getInstance();$row=self::publisherForCustomer($idCustomer,$idShop);
        $conflict=(int)$db->getValue("SELECT COUNT(*) FROM `"._DB_PREFIX_."ntcr_publisher` WHERE id_shop=".(int)$idShop." AND handle='".pSQL($h)."' AND (id_customer IS NULL OR id_customer<>".(int)$idCustomer.")");
        if($conflict)throw new RuntimeException('Bu kullanıcı adı başka bir eşleştirmede bulundu. Ajans incelemesi gerekiyor.');
        if($row)$ok=$db->update('ntcr_publisher',array('pending_handle'=>pSQL($h),'status'=>$row['handle']===$h?'verified':'pending','date_upd'=>$now),'id_publisher='.(int)$row['id_publisher']);
        else $ok=$db->insert('ntcr_publisher',array('id_shop'=>(int)$idShop,'id_customer'=>(int)$idCustomer,'pending_handle'=>pSQL($h),'status'=>'pending','date_add'=>$now,'date_upd'=>$now));
        if(!$ok)throw new RuntimeException('Eşleştirme talebi kaydedilemedi.'); return $h;
    }
    public static function import($path,$name,$idShop,$idEmployee)
    {
        $rows=(new NtcrXlsxReader())->read($path);if(!$rows)throw new RuntimeException('Aktarılabilir kayıt bulunamadı.');
        $periods=array_unique(array_column($rows,'Data Month'));if(count($periods)!==1)throw new RuntimeException('Bu sürümde her aktarım tek dönem içermelidir. Dönemleri ayrı yükleyin.');$period=reset($periods);
        $hash=hash_file('sha256',$path);$db=Db::getInstance();$total='0.00000000';foreach($rows as $r){if($r['Estimated bonus - Activeness incentive']!=='')$total=self::addDecimal($total,NtcrDecimal::normalize($r['Estimated bonus - Activeness incentive'],8));}
        if(!$db->insert('ntcr_batch',array('id_shop'=>$idShop,'period'=>$period,'file_hash'=>$hash,'source_name'=>pSQL($name),'row_count'=>count($rows),'source_total_usd'=>$total,'share_rate'=>Configuration::get('NTCR_RATE')?:'0.02000000','status'=>'draft','id_employee'=>$idEmployee,'date_add'=>date('Y-m-d H:i:s'))))throw new RuntimeException('Bu rapor daha önce aktarılmış olabilir.');
        $idBatch=(int)$db->Insert_ID();foreach($rows as $r)self::insertRow($db,$idBatch,$idShop,$r,$idEmployee);return $idBatch;
    }
    private static function insertRow($db,$batch,$shop,$r,$employee)
    {
        $creator=trim($r['Creator ID']);$creator=($creator===''||$creator==='0')?null:$creator;$handle=null;try{if($r['Handle']!=='')$handle=NtcrHandle::normalize($r['Handle']);}catch(Exception $e){}
        list($pub,$status,$msg)=self::matchPublisher($db,$shop,$creator,$handle,$employee);
        if($r['Estimated bonus - Activeness incentive']===''){$status='invalid';$msg='Aktiflik teşviki boş.';}
        $incentive=$r['Estimated bonus - Activeness incentive']===''?null:NtcrDecimal::normalize($r['Estimated bonus - Activeness incentive'],8);
        $db->insert('ntcr_source_row',array('id_batch'=>$batch,'source_row_no'=>(int)$r['_source_row'],'period'=>$r['Data Month'],'creator_id'=>$creator===null?null:pSQL($creator),'handle'=>$handle===null?null:pSQL($handle),'nickname'=>pSQL($r['Creator nickname']),'diamonds'=>$r['Diamonds']===''?null:NtcrDecimal::normalize($r['Diamonds'],4),'valid_days'=>$r['Valid days(d)']===''?null:NtcrDecimal::normalize($r['Valid days(d)'],4),'live_hours'=>$r['LIVE duration(h)']===''?null:NtcrDecimal::normalize($r['LIVE duration(h)'],6),'incentive_usd'=>$incentive,'id_publisher'=>$pub?(int)$pub['id_publisher']:null,'validation_status'=>$status,'validation_message'=>pSQL($msg)));
    }
    private static function matchPublisher($db,$shop,$creator,$handle,$employee)
    {
        $matches=array();
        if($creator!==null)$matches=$db->executeS("SELECT * FROM `"._DB_PREFIX_."ntcr_publisher` WHERE id_shop=".(int)$shop." AND creator_id='".pSQL($creator)."' LIMIT 2");
        if(!$matches&&$handle)$matches=$db->executeS("SELECT * FROM `"._DB_PREFIX_."ntcr_publisher` WHERE id_shop=".(int)$shop." AND status='verified' AND handle='".pSQL($handle)."' LIMIT 2");
        if(count($matches)>1)return array(null,'conflict','Birden fazla yayıncı eşleşti; yönetici incelemesi gerekiyor.');
        if(count($matches)===1)return array($matches[0],'ready','');
        if($handle){$pending=$db->executeS("SELECT id_publisher FROM `"._DB_PREFIX_."ntcr_publisher` WHERE id_shop=".(int)$shop." AND status='pending' AND pending_handle='".pSQL($handle)."' LIMIT 2");if(count($pending)>1)return array(null,'conflict','Aynı kullanıcı adı için birden fazla bekleyen kayıt var.');if(count($pending)===1)return array(null,'unmatched','Yayıncı başvurusu yönetici onayı bekliyor.');}
        return array(null,'unmatched','Kayıtlı müşteri eşleşmesi bulunamadı.');
    }
    public static function rematchDraftRows($shop,$employee)
    {
        $db=Db::getInstance();$rows=$db->executeS('SELECT r.* FROM `'._DB_PREFIX_.'ntcr_source_row` r INNER JOIN `'._DB_PREFIX_.'ntcr_batch` b ON b.id_batch=r.id_batch WHERE b.id_shop='.(int)$shop." AND b.status='draft'");$ready=0;$unmatched=0;$conflicts=0;
        foreach($rows as $r){if($r['incentive_usd']===null)continue;list($pub,$status,$msg)=self::matchPublisher($db,$shop,$r['creator_id']?:null,$r['handle']?:null,$employee);$db->update('ntcr_source_row',array('id_publisher'=>$pub?(int)$pub['id_publisher']:null,'validation_status'=>pSQL($status),'validation_message'=>pSQL($msg)),'id_source_row='.(int)$r['id_source_row']);if($status==='ready')$ready++;elseif($status==='conflict')$conflicts++;else$unmatched++;}
        return array('ready'=>$ready,'unmatched'=>$unmatched,'conflicts'=>$conflicts);
    }
    public static function approve($batch,$fx,$employee,$shop)
    {
        $fx=NtcrDecimal::normalize($fx,8);$db=Db::getInstance();$db->execute('START TRANSACTION');try{$locked=$db->executeS('SELECT * FROM `'._DB_PREFIX_.'ntcr_batch` WHERE id_batch='.(int)$batch.' AND id_shop='.(int)$shop.' LIMIT 1 FOR UPDATE');$b=$locked?$locked[0]:false;if(!$b||$b['status']==='approved')throw new RuntimeException('Parti bulunamadı veya zaten onaylandı.');
            $rows=$db->executeS('SELECT * FROM `'._DB_PREFIX_.'ntcr_source_row` WHERE id_batch='.(int)$batch." AND validation_status='ready' FOR UPDATE");
            foreach($rows as $r){$target=NtcrDecimal::points($r['incentive_usd'],$b['share_rate'],$fx);$entitlement=$db->executeS('SELECT net_points FROM `'._DB_PREFIX_.'ntcr_period_entitlement` WHERE id_shop='.(int)$shop.' AND id_publisher='.(int)$r['id_publisher']." AND period='".pSQL($b['period'])."' LIMIT 1 FOR UPDATE");$old=$entitlement?(int)$entitlement[0]['net_points']:0;$diff=$target-$old;
                if($diff!==0){$key='period:'.$shop.':'.$r['id_publisher'].':'.$b['period'].':target:'.$target;$db->insert('ntcr_ledger',array('id_shop'=>$shop,'id_publisher'=>(int)$r['id_publisher'],'period'=>$b['period'],'type'=>$diff>0?'earning':'correction','points'=>$diff,'idempotency_key'=>pSQL($key),'reference_type'=>'batch','reference_id'=>$batch,'description'=>pSQL($b['period'].' dönemi onaylı puan farkı'),'id_employee'=>$employee,'date_add'=>date('Y-m-d H:i:s')));$db->execute('INSERT INTO `'._DB_PREFIX_.'ntcr_wallet` (id_publisher,available_points,reserved_points,debt_points,date_upd) VALUES ('.(int)$r['id_publisher'].','.(int)$diff.',0,0,NOW()) ON DUPLICATE KEY UPDATE available_points=available_points+'.(int)$diff.',date_upd=NOW()');}
                $db->execute('INSERT INTO `'._DB_PREFIX_.'ntcr_period_entitlement` (id_shop,id_publisher,period,net_points,last_batch_id,date_upd) VALUES ('.(int)$shop.','.(int)$r['id_publisher'].',\''.pSQL($b['period']).'\','.(int)$target.','.(int)$batch.',NOW()) ON DUPLICATE KEY UPDATE net_points='.(int)$target.',last_batch_id='.(int)$batch.',date_upd=NOW()');$db->update('ntcr_source_row',array('target_points'=>$target),'id_source_row='.(int)$r['id_source_row']);}
            $db->update('ntcr_batch',array('fx_rate'=>$fx,'status'=>'approved','date_approved'=>date('Y-m-d H:i:s')),'id_batch='.(int)$batch);$db->execute('COMMIT');return count($rows);
        }catch(Exception $e){$db->execute('ROLLBACK');throw $e;}
    }
    private static function addDecimal($a,$b){if(function_exists('bcadd'))return bcadd($a,$b,8);return number_format((float)$a+(float)$b,8,'.','');}
}
