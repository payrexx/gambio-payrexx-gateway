<?php declare(strict_types=1);

namespace Payrexx\PayrexxPaymentGateway\Classes\Controller;

use MainFactory;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\PayrexxHelper;

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
        $basket = PayrexxHelper::collectBasketData($order);
        $basketAmount = 0;
        foreach ($basket as $basketItem) {
            $basketAmount += $basketItem['quantity'] * $basketItem['amount'];
        }

        // Purpose
        $purpose = null;
        if (round($basketAmount) !== round($totalAmount)) {
            $purpose = PayrexxHelper::createPurposeByBasket($basket);
            $basket = [];
        }

        // pm
        $configuration = MainFactory::create('PayrexxStorage');
        $pm = [];
        foreach (PayrexxHelper::getPaymentMethods() as $method) {
            if ($configuration->get(strtoupper($method)) === 'true') {
                $pm[] = str_replace('_', '-', $method);
            }
        }
        return $payrexxApiService->createGateway($userOrder, $basket, $purpose, $pm);
    }
}
