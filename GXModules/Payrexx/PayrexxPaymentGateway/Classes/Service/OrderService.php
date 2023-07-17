<?php
/**
 * Class OrderService.
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
namespace Payrexx\PayrexxPaymentGateway\Classes\Service;

use BoolType;
use IdType;
use IntType;
use MainFactory;
use Payrexx\Models\Response\Transaction;
use StaticGXCoreLoader;
use StringType;

/**
 * Class OrderService.
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
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
     * Add new order status.
     *
     * @return void
     */
    public function addNewOrderStatus()
    {
        $orderService = new OrderService();
        $newOrderStatusConfig = $this->_getOrderStatusConfig();
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
     * Update transaction status for order
     *
     * @param int    $orderId order id
     * @param string $status  status
     * @param array  $invoice Invoice details
     *
     * @return void
     */
    public function handleTransactionStatus(int $orderId, string $status, array $invoice = [])
    {
        $storage = MainFactory::create('PayrexxStorage');
        $configurations = $storage->getAll();

        $orderReadService  = StaticGXCoreLoader::getService('OrderRead');
        $gxOrder           = $orderReadService->getOrderById(new IdType((int)$orderId));
        
        // status mapping
        switch ($status) {
            case Transaction::WAITING:
                $newStatusId = $configurations['PAYMENT_WAITING_STATUS_ID']; // Pending
                break;
            case Transaction::CONFIRMED:
                $newStatusId = $configurations['PAYMENT_SUCCESS_STATUS_ID']; // Processing
                break;
            case Transaction::CANCELLED:
            case Transaction::DECLINED:
            case Transaction::ERROR:
            case Transaction::EXPIRED:
                $newStatusId = $configurations['PAYMENT_FAILED_STATUS_ID']; // Canceled
                break;
            case Transaction::REFUNDED:
            case Transaction::PARTIALLY_REFUNDED:
                $refundStatusId = $configurations['PAYMENT_REFUNDED_STATUS_ID'];
                $partiallyRefundId = $configurations['PAYMENT_PARTIALLY_REFUNDED_STATUS_ID'];
                $newStatusId = $partiallyRefundId;
                if (
                    $status == self::STATUS_PARTIALLY_REFUNDED &&
                    !empty($invoice) &&
                    $invoice['originalAmount'] == $invoice['refundedAmount']
                ) {
                    $newStatusId = $refundStatusId;
                }
                break;
            default:
                throw new \Exception($status . ' case not implemented.');
        }

        // check the status transition to change.
        if (!$this->_allowedStatusTransition($gxOrder->getOrderStatusId(), $newStatusId)) {
            throw new \Exception('Status transition not allowed');
        }
        $this->updateOrderStatus($orderId, $newStatusId, $status);
    }

    /**
     * Get order status
     *
     * @param string $langCode
     * @param int $id
     *
     * @return array
     */
    public function getOrderStatus(string $langCode = '', int $id = 0): array
    {
        $langCode = $langCode ?? 'EN';
        $orderStatusService = StaticGXCoreLoader::getService('OrderStatus');
        $status = [];
        foreach ($orderStatusService->findAll() as $orderStatus) {
            $status[$orderStatus->getId()] = $orderStatus->getName(
                MainFactory::create('LanguageCode', new StringType($langCode))
            );
        }
        if ($id) {
            return [$id => $status[$id]];
        }
        return $status;
    }

    /**
     * Check the transition is allowed or not
     *
     * @param int    $orderId   Order id
     * @param string $newStatus New status
     *
     * @return bool
     */
    private function _allowedStatusTransition($orderStatusId, $orderNewStatusId)
    {
        if ($orderStatusId == $orderNewStatusId) {
            return false;
        }

        $storage = MainFactory::create('PayrexxStorage');
        $configurations = $storage->getAll();

        $successOrderStatusId = $configurations['PAYMENT_SUCCESS_STATUS_ID'];
        $waitingOrderStatusId = $configurations['PAYMENT_WAITING_STATUS_ID'];
        $refundedOrderStatusId = $configurations['PAYMENT_REFUNDED_STATUS_ID'];
        $partiallyRefundedOrderStatusId = $configurations['PAYMENT_PARTIALLY_REFUNDED_STATUS_ID'];

        if ($orderStatusId === $waitingOrderStatusId) {
            return !in_array(
                $orderNewStatusId,
                [
                    $refundedOrderStatusId,
                    $partiallyRefundedOrderStatusId,
                ]
            );
        }

        if ($orderStatusId === $successOrderStatusId || 
            $orderStatusId === $partiallyRefundedOrderStatusId
        ) {
            return in_array(
                $orderNewStatusId,
                [
                    $refundedOrderStatusId,
                    $partiallyRefundedOrderStatusId,
                ]
            );
        }

        return false;
    }

    /**
     * Update order status
     *
     * @param int    $orderId     Order id
     * @param int    $newStatusId New status id
     * @param string $newStatus   New status
     *
     * @return void
     */
    private function updateOrderStatus(int $orderId, int $newStatusId, string $newStatus)
    {
        $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
        //update status and customer-history
        $orderWriteService->updateOrderStatus(
            new IdType($orderId),
            new IntType((int)$newStatusId),
            new StringType('Payrexx status: ' . $newStatus . ' is processed'),
            new BoolType(false)
        );
    }

    /**
     * Check order status exist
     *
     * @param string $statusName Order status name
     * @param string $langCode   Language code
     *
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

    /**
     * Get order status config
     *
     * @return array
     */
    private function _getOrderStatusConfig(): array
    {
        return [
            Transaction::REFUNDED => [
                'names' => [
                    'en' => self::STATUS_REFUNDED,
                    'de' => 'Payrexx Rückerstattung',
                ],
                'color' => '2196F3',
            ],
            Transaction::PARTIALLY_REFUNDED => [
                'names' => [
                    'en' => self::STATUS_PARTIALLY_REFUNDED,
                    'de' => 'Payrexx Teilrückerstattung',
                ],
                'color' => '2196F3',
            ],
        ];
    }
}
