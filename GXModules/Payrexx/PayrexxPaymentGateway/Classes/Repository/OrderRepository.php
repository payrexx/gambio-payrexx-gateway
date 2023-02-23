<?php

namespace Payrexx\PayrexxPaymentGateway\Classes\Repository;

use StaticGXCoreLoader;
use Exception;

class OrderRepository
{
    /**
     * Get transition status by order id
     *
     * @param int $id transaction id
     *
     * @return string
     */
    public function getTransitionStatusByOrderId(int $id)
    {
        try {
            $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
            $languages = $db->select('languages_id')
                ->where('code', 'en')
                ->get('languages')
                ->result_array();
            $langId =  $languages[0]['languages_id'];

            $orderStatus = $db->select('orders_status.orders_status_name')
                ->join('orders_status', 'orders.orders_status = orders_status.orders_status_id')
                ->where('orders_status.language_id', $langId)
                ->where('orders.orders_id', $id)
                ->limit(1)
                ->get('orders')
                ->result_array();
            return $orderStatus[0]['orders_status_name'];
        } catch (Exception $e) {
            return '';
        }
    }
}
