<?php

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\PayrexxHelper;

class PayrexxPaymentGatewayModuleCenterModule extends AbstractModuleCenterModule
{
    protected function _init()
    {
        $this->title       = $this->languageTextManager->get_text('page_title', 'payrexx');
        $this->description = $this->languageTextManager->get_text('page_description', 'payrexx');
        $this->sortOrder   = 99999;
    }

    /**
    * Install module and set own install flag in module table
    */
    public function install()
    {
        parent::install();
        $configuration = MainFactory::create('PayrexxStorage');
        $orderService = new OrderService();
        $orderService->addNewOrderStatus();
        foreach (PayrexxHelper::getBasicConfigurations() as $key => $value) {
            $configuration->set($key, $value['value']);
        }
    }

   /**
    * Uninstall module and set own install flag in module table
    */
    public function uninstall()
    {
        parent::uninstall();
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `key` LIKE '%configuration/MODULE_PAYMENT_PAYREXX_%'");
    }
}
