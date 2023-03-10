<?php
/**
 * Class PayrexxPaymentController.
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
declare(strict_types=1);

namespace Payrexx\PayrexxPaymentGateway\Classes\Controller;

use MainFactory;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\BasketUtil;
use Payrexx\PayrexxPaymentGateway\Classes\Util\ConfigurationUtil;

class PayrexxPaymentController
{
    /**
     * Create Payrexx Gateway
     *
     * @param order $userOrder
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
        foreach (ConfigurationUtil::getPaymentMethods() as $method) {
            if ($configuration->get(strtoupper($method)) === 'true') {
                $pm[] = str_replace('_', '-', $method);
            }
        }
        return $payrexxApiService->createGateway($userOrder, $basket, $purpose, $pm);
    }
}
