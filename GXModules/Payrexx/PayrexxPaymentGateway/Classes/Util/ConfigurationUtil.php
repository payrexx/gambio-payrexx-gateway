<?php

namespace Payrexx\PayrexxPaymentGateway\Classes\Util;

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;

class ConfigurationUtil
{
    /**
     * Get paymentmethods
     */
    public static function getPaymentMethods(): array
    {
        $paymentMethods =  [
            'masterpass',
            'mastercard',
            'visa',
            'apple_pay',
            'maestro',
            'jcb',
            'american_express',
            'wirpay',
            'paypal',
            'bitcoin',
            'sofort',
            'billpay',
            'bonus',
            'cashu',
            'cb',
            'diners_club',
            'sepa_direct_debit',
            'discover',
            'elv',
            'ideal',
            'invoice',
            'myone',
            'paysafecard',
            'post_finance_card',
            'post_finance_e_finance',
            'swissbilling',
            'twint',
            'barzahlen',
            'bancontact',
            'giropay',
            'eps',
            'google_pay',
            'klarna_paynow',
            'klarna_paylater',
            'oney',
        ];

        return $paymentMethods;
    }

    /**
     * Get payment method configurations
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
            'PREFIX' => [
                'value' => '',
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