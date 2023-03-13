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

use StaticGXCoreLoader;
use MainFactory;
use StringType;
use IdType;
use IntType;
use BoolType;
use Payrexx\Models\Response\Transaction;
use Payrexx\PayrexxPaymentGateway\Classes\Repository\OrderRepository;

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
        $newOrderStatusConfig = $this->getOrderStatusConfig();
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
     *
     * @return void
     */
    public function handleTransactionStatus(int $orderId, string $status)
    {
        // status mapping
        switch ($status) {
            case Transaction::WAITING:
                $newStatusId = 1; // Pending
                $newStatus = self::STATUS_PENDING;
                break;
            case Transaction::CONFIRMED:
                $newStatusId = 2; // Processing
                $newStatus = self::STATUS_PROCESSING;
                break;
            case Transaction::CANCELLED:
            case Transaction::DECLINED:
            case Transaction::ERROR:
            case Transaction::EXPIRED:
                $newStatusId = 99; // Canceled
                $newStatus = self::STATUS_CANCELED;
                break;
            case Transaction::REFUNDED:
            case Transaction::PARTIALLY_REFUNDED:
                $newStatus = $this->getOrderStatusConfig()[$status]['names']['en'];
                $newStatusId = $this->orderStatusExists($newStatus);
                if (!$newStatusId) {
                    $this->addNewOrderStatus();
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
     * @param int    $orderId   Order id
     * @param string $newStatus New status
     *
     * @return bool
     */
    private function allowedStatusTransition($orderId, $newStatus)
    {
        $orderRepository = new OrderRepository();
        $oldStatus = $orderRepository->getTransitionStatusByOrderId($orderId);
        if (empty($oldStatus) || $oldStatus === $newStatus) {
            return false;
        }

        switch ($oldStatus) {
            case self::STATUS_PENDING:
                return !in_array($newStatus, [
                    self::STATUS_REFUNDED,
                    self::STATUS_PARTIALLY_REFUNDED,
                ]);
            case self::STATUS_PROCESSING:
            case self::STATUS_PARTIALLY_REFUNDED:
                return in_array($newStatus, [
                    self::STATUS_REFUNDED,
                    self::STATUS_PARTIALLY_REFUNDED,
                ]);
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
            new StringType($newStatus . ' status updated by payrexx'),
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
    private function orderStatusExists($statusName, $langCode = 'en')
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
    private function getOrderStatusConfig(): array
    {
        return [
            'refunded' => [
                'names' => [
                    'en' => self::STATUS_REFUNDED,
                    'de' => 'Payrexx Rückerstattung',
                ],
                'color' => '2196F3',
            ],
            'partially-refunded' => [
                'names' => [
                    'en' => self::STATUS_PARTIALLY_REFUNDED,
                    'de' => 'Payrexx Teilrückerstattung',
                ],
                'color' => '2196F3',
            ],
        ];
    }
}
