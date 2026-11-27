<?php
class AdminNtCreatorRewardsRequestsController extends ModuleAdminController
{
    public function __construct(){parent::__construct();$this->bootstrap=true;}
    public function postProcess(){if(Tools::isSubmit('transitionNtcrRequest'))try{NtcrRequestService::transition((int)Tools::getValue('id_request'),Tools::getValue('to_status'),(int)$this->context->employee->id,(int)$this->context->shop->id,Tools::getValue('public_note'),Tools::getValue('private_note'));$this->confirmations[]='Talep durumu güncellendi.';}catch(Exception $e){$this->errors[]=$e->getMessage();}parent::postProcess();}
    public function initContent(){parent::initContent();$rows=Db::getInstance()->executeS('SELECT r.*,p.handle FROM `'._DB_PREFIX_.'ntcr_request` r INNER JOIN `'._DB_PREFIX_.'ntcr_publisher` p ON p.id_publisher=r.id_publisher WHERE r.id_shop='.(int)$this->context->shop->id.' ORDER BY r.id_request DESC');$this->context->smarty->assign('ntcr_requests',$rows);$this->content=$this->context->smarty->fetch(_PS_MODULE_DIR_.'ntcreatorrewards/views/templates/admin/requests.tpl');$this->context->smarty->assign('content',$this->content);}
}
