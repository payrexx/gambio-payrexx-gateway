<?php

namespace Payrexx\PayrexxPaymentGateway\Classes\Util;

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;

class PayrexxHelper
{
    /**
     * Get paymentmethods
     */
    public static function getPaymentMethods(): array
    {
        $paymentMethods =  [
            'masterpass', 'mastercard', 'visa', 'apple_pay', 'maestro', 'jcb', 'american_express', 'wirpay',
            'paypal', 'bitcoin', 'sofortueberweisung_de', 'airplus', 'billpay', 'bonuscard', 'cashu', 'cb',
            'diners_club', 'direct_debit', 'discover', 'elv', 'ideal', 'invoice', 'myone', 'paysafecard',
            'postfinance_card', 'postfinance_efinance', 'swissbilling', 'twint', 'barzahlen', 'bancontact',
            'giropay', 'eps', 'google_pay', 'klarna_paynow', 'klarna_paylater', 'oney'
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
        foreach (static::getPaymentMethods() as $method) {
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
                'value' => 'gambio',
                'type' => 'text'
            ],
            'LOOK_AND_FEEL_ID' => [
                'value' => '',
                'type' => 'text'
            ]
        ];
    }

    /**
     * Get order status config
     */
    public static function getOrderStatusConfig(): array
    {
        return [
            'refunded' => [
                'names' => [
                    'en' => OrderService::STATUS_REFUNDED,
                    'de' => 'Payrexx Rückerstattung',
                ],
                'color' => '2196F3',
            ],
            'partially-refunded' => [
                'names' => [
                    'en' => OrderService::STATUS_PARTIALLY_REFUNDED,
                    'de' => 'Payrexx Teilrückerstattung',
                ],
                'color' => '2196F3',
            ],
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

    /**
     * Get basic and payment methods configurations
     */
    public static function getAllConfigurations(): array
    {
        return array_merge(static::getBasicConfigurations() + static::getModuleConfigurations());
    }

    /**
     * Get location of image
     */
    public static function getImagePath()
    {
        return 'GXModules/Payrexx/PayrexxPaymentGateway/Images/Icons/Payment/';
    }
}
