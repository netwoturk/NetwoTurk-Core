<?php
class AdminNtCreatorRewardsRequestsController extends ModuleAdminController
{
    public function __construct(){parent::__construct();$this->bootstrap=true;}
    public function initContent(){parent::initContent();$rows=Db::getInstance()->executeS('SELECT r.*,p.handle FROM `'._DB_PREFIX_.'ntcr_request` r INNER JOIN `'._DB_PREFIX_.'ntcr_publisher` p ON p.id_publisher=r.id_publisher WHERE r.id_shop='.(int)$this->context->shop->id.' ORDER BY r.id_request DESC');$this->context->smarty->assign('ntcr_requests',$rows);$this->content=$this->context->smarty->fetch(_PS_MODULE_DIR_.'ntcreatorrewards/views/templates/admin/requests.tpl');$this->context->smarty->assign('content',$this->content);}
}
