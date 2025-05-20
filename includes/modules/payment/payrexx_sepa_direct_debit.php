<?php
/**
 * Class payrexx_sepa_direct_debit_ORIGIN
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
class payrexx_sepa_direct_debit_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_sepa_direct_debit';
}

MainFactory::load_origin_class('payrexx_sepa_direct_debit');
