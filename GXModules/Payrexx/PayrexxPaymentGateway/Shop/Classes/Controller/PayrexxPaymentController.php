<?php
/**
 * Class PayrexxPaymentController.
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
declare(strict_types=1);

namespace Payrexx\PayrexxPaymentGateway\Classes\Controller;

use MainFactory;
use Payrexx\PayrexxPaymentGateway\Classes\Config\PayrexxConfig;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\BasketUtil;

/**
 * Class PayrexxPaymentController.
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
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
        $configuration = MainFactory::create('PayrexxStorage');
        $pm = [];
        foreach (PayrexxConfig::getPaymentMethods() as $method) {
            if ($configuration->get(strtoupper($method)) === 'true') {
                $pm[] = str_replace('_', '-', $method);
            }
        }
        return $payrexxApiService->createGateway($userOrder, $basket, $purpose, $pm);
    }
}
