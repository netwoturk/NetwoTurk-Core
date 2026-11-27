<?php
if (!defined('_PS_VERSION_')) { exit; }

require_once __DIR__.'/classes/NtcrInstaller.php';
require_once __DIR__.'/classes/NtcrHandle.php';
require_once __DIR__.'/classes/NtcrDecimal.php';
require_once __DIR__.'/classes/NtcrXlsxReader.php';
require_once __DIR__.'/classes/NtcrService.php';
require_once __DIR__.'/classes/NtcrLicenseClient.php';

class NtCreatorRewards extends Module
{
    const VERSION = '1.0.0';
    public function __construct()
    {
        $this->name='ntcreatorrewards'; $this->tab='administration'; $this->version=self::VERSION;
        $this->author='NetwoTürk'; $this->bootstrap=true; $this->need_instance=0;
        $this->controllers=array('rewards');
        parent::__construct();
        $this->displayName='Influencer Ajans – Yayıncı Ödül Sistemi';
        $this->description='TikTok aylık performans raporlarını puana dönüştürür ve yayıncı ödül taleplerini yönetir.';
        $this->ps_versions_compliancy=array('min'=>'8.2.0','max'=>'8.99.99');
    }
    public function install()
    {
        return parent::install() && NtcrInstaller::install($this)
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('displayMyAccountBlock')
            && $this->registerHook('actionFrontControllerSetMedia');
    }
    public function uninstall() { return NtcrInstaller::uninstall($this) && parent::uninstall(); }
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminNtCreatorRewards'));
    }
    public function hookDisplayCustomerAccount()
    {
        $this->context->smarty->assign('ntcr_url',$this->context->link->getModuleLink($this->name,'rewards'));
        return $this->display(__FILE__,'views/templates/hook/account-link.tpl');
    }
    public function hookDisplayMyAccountBlock($p) { return $this->hookDisplayCustomerAccount($p); }
    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof NtcreatorrewardsRewardsModuleFrontController) {
            $this->context->controller->registerStylesheet('ntcr-front','modules/'.$this->name.'/views/css/front.css',array('version'=>$this->version));
            $this->context->controller->registerJavascript('ntcr-front','modules/'.$this->name.'/views/js/front.js',array('position'=>'bottom','version'=>$this->version));
        }
    }
}
