<?php
/**
 * Class ConfigurationUtil.
 *
 * Payment gateway for Payrexx AG.
 *
 * @category  Payment Module
 * @link      https://www.payrexx.com
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 *
 * VERSION HISTORY:
 * 1.0.0 Payrexx Payment Gateway.
 */

namespace Payrexx\PayrexxPaymentGateway\Classes\Util;

class ConfigurationUtil
{
    /**
     * Get paymentmethods
     *
     * @return array
     */
    public static function getPaymentMethods(): array
    {
        $paymentMethods =  [
            'masterpass',
            'mastercard',
            'visa',
            'apple-pay',
            'maestro',
            'jcb',
            'american-express',
            'wirpay',
            'paypal',
            'bitcoin',
            'sofort',
            'billpay',
            'bonus',
            'cashu',
            'cb',
            'diners-club',
            'sepa-direct-debit',
            'discover',
            'elv',
            'ideal',
            'invoice',
            'myone',
            'paysafecard',
            'post-finance-card',
            'post-finance-e-finance',
            'swissbilling',
            'twint',
            'barzahlen',
            'bancontact',
            'giropay',
            'eps',
            'google-pay',
            'klarna-paynow',
            'klarna-paylater',
            'oney',
        ];

        return $paymentMethods;
    }

    /**
     * Get payment method configurations
     *
     * @return array
     */
    public static function getModuleConfigurations(): array
    {
        $config = [
            'STATUS' => [
                'value' => 'False',
                'type' => 'switcher',
            ],
            'SORT_ORDER' => [
                'value' => '-9999',
                'type' => 'number'
            ],
            'ALLOWED'    => [
                'value' => '',
                'type' => 'text'
            ],
            'ZONE' => [
                'value' => '',
                'type' => 'geo-zone',
            ],
        ];

        $config['CHECKOUT_NAME'] = ['value' => 'Payrexx Payment Gateway', 'type' => 'text'];
        $config['CHECKOUT_DESCRIPTION'] = ['value' => '', 'type' => 'text'];

        /**
         * Creating checkbox for each payment method.
         */
        foreach (self::getPaymentMethods() as $method) {
            $config[strtoupper($method)] = ['value' => 'False','type'  => 'switcher'];
        }

        return $config;
    }

    /**
     * Get basic configurations
     *
     * @return array
     */
    public static function getBasicConfigurations(): array
    {
        return [
            'INSTANCE_NAME' => [
                'value' => '',
                'type' => 'text'
            ],
            'API_KEY' => [
                'value' => '',
                'type' => 'text'
            ],
            'PLATFORM' => [
                'value'  => 'payrexx.com',
                'type' => 'text'
            ],
            'LOOK_AND_FEEL_ID' => [
                'value' => '',
                'type' => 'text'
            ]
        ];
    }

    /**
     * Platforms
     *
     * @return array
     */
    public static function getPlatforms(): array
    {
        return [
            'payrexx.com',
            'zahls.ch',
            'spenden-grunliberale.ch',
            'deinshop.online',
            'swissbrain-pay.ch',
            'loop-pay.com',
            'shop-and-pay.com',
            'ideal-pay.ch',
            'payzzter.com',
        ];
    }
}