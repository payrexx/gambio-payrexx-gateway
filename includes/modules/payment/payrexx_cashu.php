<?php
/**
 * Class payrexx_cashu_ORIGIN
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
class payrexx_cashu_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_cashu';
}

MainFactory::load_origin_class('payrexx_cashu');
