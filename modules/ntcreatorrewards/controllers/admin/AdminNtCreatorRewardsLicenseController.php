<?php
class AdminNtCreatorRewardsLicenseController extends ModuleAdminController
{
    public function __construct(){parent::__construct();$this->bootstrap=true;}
    public function postProcess(){if(Tools::isSubmit('submitNtcrLicense'))try{(new NtcrLicenseClient())->activate(Tools::getValue('license_key'));$this->confirmations[]='Lisans etkinleştirildi.';}catch(Exception $e){$this->errors[]=$e->getMessage();}parent::postProcess();}
    public function initContent(){parent::initContent();$this->context->smarty->assign('ntcr_license',(new NtcrLicenseClient())->status());$this->content=$this->context->smarty->fetch(_PS_MODULE_DIR_.'ntcreatorrewards/views/templates/admin/license.tpl');$this->context->smarty->assign('content',$this->content);}
}
