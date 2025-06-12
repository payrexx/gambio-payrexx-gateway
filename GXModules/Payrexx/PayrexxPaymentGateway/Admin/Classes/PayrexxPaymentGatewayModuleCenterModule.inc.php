<?php
/**
 * Class PayrexxPaymentGatewayModuleCenterModule.
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPaymentGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright Payrexx AG
 * @license   MIT License
 * @link      https://www.payrexx.com
 */
declare(strict_types=1);

use Payrexx\PayrexxPaymentGateway\Classes\Config\PayrexxConfig;
use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;

/**
 * Class PayrexxPaymentGatewayModuleCenterModule.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxPaymentGatewayModuleCenterModule extends AbstractModuleCenterModule
{
    /**
     * Initialize the module
     *
     * @return void
     */
    protected function _init()
    {
        $this->title       = $this->languageTextManager->get_text('page_title', 'payrexx');
        $this->description = $this->languageTextManager->get_text('page_description', 'payrexx');
        $this->sortOrder   = 99999;
    }

    /**
     * Install module and set own install flag in module table
     *
     * @return void
     */
    public function install()
    {
        parent::install();
        $configuration = MainFactory::create('PayrexxStorage');
        $orderService = new OrderService();
        $orderService->addNewOrderStatus();
        foreach (PayrexxConfig::getBasicConfigurations() as $key => $value) {
            $configuration->set($key, $value['value']);
        }
    }

    /**
     * Uninstall module and set own install flag in module table
     *
     * @return void
     */
    public function uninstall()
    {
        parent::uninstall();
        xtc_db_query(
            "DELETE FROM " . TABLE_CONFIGURATION . " WHERE `key` LIKE '%configuration/MODULE_PAYMENT_PAYREXX_%'"
        );
    }
}
