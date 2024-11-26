<?php
/**
 * Class payrexx_pay_by_bank_ORIGIN
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPayemntGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2024 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 */

/**
 * Class payrexx_pay_by_bank_ORIGIN
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class payrexx_pay_by_bank_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_pay_by_bank';
}

MainFactory::load_origin_class('payrexx_pay_by_bank');
