<?php
/**
 * Class PayrexxWebhookController.
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

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;

/**
 * Class PayrexxWebhookController.
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxWebhookController extends HttpViewController
{
    protected $configuration;

    protected $orderService;

    protected $payrexxApiService;

    /**
     * Constructor
     *
     * @param HttpContextReaderInterface     $httpContextReader     Context Reader
     * @param HttpResponseProcessorInterface $httpResponseProcessor Response Processor
     * @param ContentViewInterface           $defaultContentView    Content view
     */
    public function __construct(
        HttpContextReaderInterface $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface $defaultContentView
    ) {
        $this->configuration = MainFactory::create('PayrexxStorage');
        $this->orderService = new OrderService();
        $this->payrexxApiService = new PayrexxApiService();

        parent::__construct($httpContextReader, $httpResponseProcessor, $defaultContentView);
    }

    /**
     * Executes default action
     *
     * @return string
     * @throws Exception
     */
    public function actionDefault()
    {
        try {
            $data = $_POST;
            if (empty($data)) {
                throw new \Exception('Payrexx Webhook Data incomplete');
            }
            $transaction = $data['transaction'];
            $orderId = (int) $transaction['referenceId'];

            if (!$orderId || !$transaction['status'] || !$transaction['id']) {
                throw new \Exception('Payrexx Webhook Data incomplete');
            }

            $order = new order($orderId);
            if (!$order) {
                throw new \Exception('Malicious request');
            }

            $payrexxTransaction = $this->payrexxApiService->getTransactionById((int)$transaction['id']);
            if ($payrexxTransaction->getStatus() !== $transaction['status']) {
                throw new \Exception('Fraudulent transaction status');
            }
            $this->orderService->handleTransactionStatus($orderId, $transaction['status']);
            echo 'Success: Webhook processed!';
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        exit();
    }
}
