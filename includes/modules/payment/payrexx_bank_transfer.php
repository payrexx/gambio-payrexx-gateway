<?php
/**
 * Class payrexx_bank_transfer_ORIGIN
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPaymentGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2024 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 */
class payrexx_bank_transfer_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_bank_transfer';
}

MainFactory::load_origin_class('payrexx_bank_transfer');
