<?php
/**
 * Class PayrexxConfig.
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

namespace Payrexx\PayrexxPaymentGateway\Classes\Config;

/**
 * Class PayrexxConfig.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxConfig
{
    /**
     * Payment methods no longer supported by Payrexx. Their module files remain in shops that are
     * updated by copying the new plugin files over the old ones, so they have to be disabled explicitly.
     */
    const REMOVED_PAYMENT_METHODS = [
        'payrexx_billpay',
        'payrexx_bitcoin',
        'payrexx_bonus',
        'payrexx_cashu',
        'payrexx_elv',
        'payrexx_klarna_paylater',
        'payrexx_klarna_paynow',
        'payrexx_masterpass',
        'payrexx_myone',
        'payrexx_oney',
        'payrexx_paysafecard',
        'payrexx_wirpay',
    ];

    /**
     * Get payment method configurations
     *
     * @return array
     */
    public static function getModuleConfigurations(string $paymentCode = ''): array
    {
        $value = empty($paymentCode) 
            ? 'Payrexx Payment Gateway' 
            : ucwords(str_replace('_', ' ', $paymentCode));
        $config = [
            'CHECKOUT_NAME' => [
                'value' => $value,
                'type' => 'text',
            ],
            'CHECKOUT_DESCRIPTION' => [
                'value' => '',
                'type' => 'text',
            ],
            'SORT_ORDER' => [
                'value' => '-9999',
                'type' => 'number',
            ],
            'ALLOWED' => [
                'value' => '',
                'type' => 'text',
            ],
        ];
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
                'type' => 'text',
            ],
            'API_KEY' => [
                'value' => '',
                'type' => 'text',
            ],
            'PLATFORM' => [
                'value'  => 'payrexx.com',
                'type' => 'text',
            ],
            'LOOK_AND_FEEL_ID' => [
                'value' => '',
                'type' => 'text',
            ],
            'PAYMENT_SUCCESS_STATUS_ID' => [
                'value' => '2',
                'type'  => 'order-status',
            ],
            'PAYMENT_FAILED_STATUS_ID' => [
                'value' => '99',
                'type'  => 'order-status',
            ],
            'PAYMENT_WAITING_STATUS_ID' => [
                'value' => '1',
                'type'  => 'order-status',
            ],
            'PAYMENT_REFUNDED_STATUS_ID' => [
                'value' => '',
                'type'  => 'order-status',
            ],
            'PAYMENT_PARTIALLY_REFUNDED_STATUS_ID' => [
                'value' => '',
                'type'  => 'order-status',
            ], 
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
            'wawipay.com',
        ];
    }
}