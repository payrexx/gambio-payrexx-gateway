<?php
/* --------------------------------------------
 * Class payrexx_sepa_direct_debit_ORIGIN.
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

class payrexx_sepa_direct_debit_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_sepa_direct_debit';
}

MainFactory::load_origin_class('payrexx_sepa_direct_debit');
