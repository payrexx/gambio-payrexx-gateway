<?php
/**
 * Class PayrexxPaymentController.
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
declare(strict_types=1);

namespace Payrexx\PayrexxPaymentGateway\Classes\Controller;

use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\BasketUtil;

/**
 * Class PayrexxPaymentController.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxPaymentController
{
    /**
     * Create Payrexx Gateway
     *
     * @param order $userOrder Order
     *
     * @return \Payrexx\Models\Response\Gateway
     */
    public function createPayrexxGateway($userOrder)
    {
        global $order;

        $payrexxApiService = new PayrexxApiService();

        $totalAmount = $userOrder->info['pp_total'] * 100;

        // Basket
        $basket = BasketUtil::collectBasketData($order);
        $basketAmount = 0;
        foreach ($basket as $basketItem) {
            $basketAmount += $basketItem['quantity'] * $basketItem['amount'];
        }

        // Purpose
        $purpose = null;
        if (round($basketAmount) !== round($totalAmount)) {
            $purpose = BasketUtil::createPurposeByBasket($basket);
            $basket = [];
        }

        // pm
        $pm = [];
        $paymentMethodCode = preg_replace('/payrexx_/', '', $userOrder->info['payment_method']);
        if ($paymentMethodCode !== 'payrexx') { // Payrexx is default payment method.
            $pm[] = str_replace('_', '-', $paymentMethodCode);
        }
        return $payrexxApiService->createGateway($userOrder, $basket, $purpose, $pm);
    }
}
