<?php

namespace Payrexx\PayrexxPaymentGateway\Classes\Util;

class BasketUtil
{

    /**
     * Collect basket data
     *
     * @return array
     */
    public static function collectBasketData($order): array
    {
        $customerStatus = $_SESSION['customers_status'];
        $addTaxToBasket = $customerStatus['customers_status_show_price_tax'] == 0 &&
            $customerStatus['customers_status_add_tax_ot'] == 1;

        $basketItems = [];
        foreach ($order->products as $item) {
            $basketItems[] = [
                'name' => [
                    2 => $item['name']
                ],
                'description' => [
                    2 => $item['checkout_information']
                ],
                'quantity' => (int) $item['qty'],
                'amount' => round($item['price'] * 100),
            ];
        }

        // Discount
        if (isset($order->info['deduction']) && $order->info['deduction'] > 0) {
            $basketItems[] = [
                'name' => [
                    2 => 'Discount',
                ],
                'quantity' => 1,
                'amount' => -(round($order->info['deduction'] * 100)),
            ];
        }

        // Shipping
        if (isset($order->info['shipping_cost']) && $order->info['shipping_cost'] > 0) {
            $basketItems[] = [
                'name' => [
                    2 => 'Shipping',
                ],
                'quantity' => 1,
                'amount' => round($order->info['shipping_cost'] * 100),
            ];
        }

        // Tax
        if ($addTaxToBasket && isset($order->info['tax']) && $order->info['tax'] > 0) {
            $basketItems[] = [
                'name' => [
                    2 => 'Tax',
                ],
                'quantity' => 1,
                'amount' => round($order->info['tax'] * 100),
            ];
        }

        return $basketItems;
    }

    /**
     * Create purpose by basket items.
     */
    public static function createPurposeByBasket($basket): string
    {
        $desc = [];
        foreach ($basket as $product) {
            $desc[] = implode(' ', [
                $product['name']['2'],
                $product['quantity'],
                'x',
                number_format($product['amount'] / 100, 2, '.', ','),
            ]);
        }
        return implode('; ', $desc);
    }
}
