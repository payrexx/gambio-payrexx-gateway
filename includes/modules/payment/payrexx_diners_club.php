<?php
/**
 * Class payrexx_diners_club_ORIGIN
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
class payrexx_diners_club_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_diners_club';
}

MainFactory::load_origin_class('payrexx_diners_club');
