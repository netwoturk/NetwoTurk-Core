<?php
class AdminNtCreatorRewardsController extends ModuleAdminController
{
    public function __construct(){parent::__construct();$this->bootstrap=true;}
    public function initContent(){parent::initContent();$s=(new NtcrLicenseClient())->status();$shop=(int)$this->context->shop->id;$stats=Db::getInstance()->getRow('SELECT COUNT(*) batches,SUM(status=\'draft\') drafts FROM `'._DB_PREFIX_.'ntcr_batch` WHERE id_shop='.$shop);$wallet=Db::getInstance()->getRow('SELECT COALESCE(SUM(w.available_points),0) available,COALESCE(SUM(w.reserved_points),0) reserved FROM `'._DB_PREFIX_.'ntcr_wallet` w INNER JOIN `'._DB_PREFIX_.'ntcr_publisher` p ON p.id_publisher=w.id_publisher WHERE p.id_shop='.$shop);$this->content=$this->module->display(__FILE__,'../../views/templates/admin/dashboard.tpl');$this->context->smarty->assign(array('ntcr_license'=>$s,'ntcr_stats'=>$stats,'ntcr_wallet'=>$wallet));$this->content=$this->context->smarty->fetch(_PS_MODULE_DIR_.'ntcreatorrewards/views/templates/admin/dashboard.tpl');$this->context->smarty->assign('content',$this->content);}
}
