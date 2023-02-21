<?php

namespace Payrexx\PayrexxPaymentGateway\Classes\Service;

use StaticGXCoreLoader;
use MainFactory;
use StringType;
use IdType;
use IntType;
use BoolType;
use Payrexx\Models\Response\Transaction;
use Payrexx\PayrexxPaymentGateway\Classes\Util\ConfigurationUtil;
use Payrexx\PayrexxPaymentGateway\Classes\Repository\OrderRepository;

class OrderService
{
    /**
     * Order status
     */
    const STATUS_REFUNDED = 'Payrexx refunded';
    const STATUS_PARTIALLY_REFUNDED = 'Payrexx partially refunded';
    const STATUS_PENDING = 'Pending';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_CANCELED = 'Canceled';
    /**
     * Check order status exist
     *
     * @param string $statusName
     * @param string $langCode
     * @return bool|int
     */
    public function orderStatusExists($statusName, $langCode = 'en')
    {
        $orderStatusId = false;
        $orderStatusService = StaticGXCoreLoader::getService('OrderStatus');
        foreach ($orderStatusService->findAll() as $orderStatus) {
            if ($orderStatus->getName(MainFactory::create('LanguageCode', new StringType($langCode))) === $statusName) {
                return $orderStatusId = (int) $orderStatus->getId();
            }
        }
        return $orderStatusId;
    }

    public function addNewOrderStatus()
    {
        $orderService = new OrderService();
        $newOrderStatusConfig = ConfigurationUtil::getOrderStatusConfig();
        $orderStatusService = StaticGXCoreLoader::getService('OrderStatus');
        foreach ($newOrderStatusConfig as $statusConfig) {
            $newOrderStatus = MainFactory::create('OrderStatus');
            foreach (['en', 'de'] as $lang) {
                $statusName = $statusConfig['names'][$lang] ?? $statusConfig['names']['en'];
                if ($orderService->orderStatusExists($statusName, $lang)) {
                    continue 2;
                }
                $newOrderStatus->setName(
                    MainFactory::create('LanguageCode', new StringType($lang)),
                    new StringType($statusName)
                );
            }
            $newOrderStatus->setColor(new StringType($statusConfig['color']));
            $orderStatusService->create($newOrderStatus);
        }
    }

    /**
     * update transaction status for order
     *
     * @param int $orderId
     * @param string $status
     * @param array $invoice
     */
    public function handleTransactionStatus(int $orderId, string $status, array $invoice = [])
    {
        // status mapping
        switch ($status) {
            case Transaction::WAITING:
                $newStatusId = 1; // Pending
                $newStatus = static::STATUS_PENDING;
                break;
            case Transaction::CONFIRMED:
                $newStatusId = 2; // Processing
                $newStatus = static::STATUS_PROCESSING;
                break;
            case Transaction::CANCELLED:
            case Transaction::DECLINED:
            case Transaction::ERROR:
            case Transaction::EXPIRED:
                $newStatusId = 99; // Canceled
                $newStatus = static::STATUS_CANCELED;
                break;
            case Transaction::REFUNDED:
            case Transaction::PARTIALLY_REFUNDED:
                $newStatus = ConfigurationUtil::getOrderStatusConfig()[$status]['names']['en'];
                $newStatusId = $this->orderStatusExists($newStatus);
                if (!$newStatusId) {
                    $this->addNewOrderStatus();
                    $newStatusId = $this->orderStatusExists($newStatus);
                }
                if ($newStatus == static::STATUS_PARTIALLY_REFUNDED &&
                    !empty($invoice) &&
                    $invoice['originalAmount'] == $invoice['refundedAmount']
                ) {
                    $newStatus = static::STATUS_REFUNDED;
                    $newStatusId = $this->orderStatusExists($newStatus);
                }
                break;
            default:
                throw new \Exception($status . ' case not implemented.');
        }

        // check the status transition to change.
        if (!$this->allowedStatusTransition($orderId, $newStatus)) {
            throw new \Exception('Status transition not allowed');
        }
        $this->updateOrderStatus($orderId, $newStatusId, $newStatus);
    }

    /**
     * Check the transition is allowed or not
     *
     * @param int $orderId
     * @param string $newStatus
     * @return bool
     */
    public function allowedStatusTransition($orderId, $newStatus)
    {
        $orderRepository = new OrderRepository();
        $oldStatus = $orderRepository->getTransitionStatusByOrderId($orderId);
        if (empty($oldStatus) || $oldStatus === $newStatus) {
            return false;
        }

        switch ($oldStatus) {
            case static::STATUS_PENDING:
                return !in_array($newStatus, [
                    static::STATUS_REFUNDED,
                    static::STATUS_PARTIALLY_REFUNDED,
                ]);
            case static::STATUS_PROCESSING:
            case static::STATUS_PARTIALLY_REFUNDED:
                return in_array($newStatus, [
                    static::STATUS_REFUNDED,
                    static::STATUS_PARTIALLY_REFUNDED,
                ]);
        }
        return false;
    }

    /**
     * Update order status
     *
     * @param int $orderId
     * @param int $newStatusId
     * @param string $newStatus
     */
    private function updateOrderStatus(int $orderId, int $newStatusId, string $newStatus)
    {
        $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
        //update status and customer-history
        $orderWriteService->updateOrderStatus(
            new IdType($orderId),
            new IntType((int)$newStatusId),
            new StringType($newStatus . ' status updated by payrexx'),
            new BoolType(false)
        );
    }
}
