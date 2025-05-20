<?php
/**
 * Class payrexx_powerpay_ORIGIN
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPaymentGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2025 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 */
class payrexx_powerpay_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_powerpay';
}

MainFactory::load_origin_class('payrexx_powerpay');
