<?php
/**
 * Class PayrexxPaymentGatewayModuleCenterModule.
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPayemntGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 *
 * VERSION HISTORY:
 * 1.0.0 Payrexx Payment Gateway.
 */
declare(strict_types=1);

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\ConfigurationUtil;

/**
 * Class PayrexxPaymentGatewayModuleCenterModule.
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
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
        foreach (ConfigurationUtil::getBasicConfigurations() as $key => $value) {
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
