<?php
/**
 * Class payrexx_maestro_ORIGIN
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

/**
 * Class payrexx_maestro_ORIGIN
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class payrexx_maestro_ORIGIN extends PayrexxPaymentGatewayBase
{
    public $code = 'payrexx_maestro';
}

MainFactory::load_origin_class('payrexx_maestro');
