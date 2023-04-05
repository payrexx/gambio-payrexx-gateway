<?php
/* --------------------------------------------
 * Class payrexx_apple_pay_ORIGIN.
 *
 * Payment gateway for Payrexx AG.
 *
 * @category   Payment Module
 *
 * @link https://www.payrexx.com
 *
 * @author Payrexx <support@payrexx.com>
 *
 * @copyright  2023 Payrexx
 *
 * @license MIT License
 *
* VERSION HISTORY:
* 1.0.0 Payrexx Payment Gateway.
---------      -----------------------------*/

class payrexx_apple_pay_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_apple_pay';
}

MainFactory::load_origin_class('payrexx_apple_pay');
