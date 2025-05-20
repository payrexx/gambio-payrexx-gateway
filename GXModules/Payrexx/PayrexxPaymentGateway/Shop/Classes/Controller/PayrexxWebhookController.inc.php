<?php
/**
 * Class PayrexxWebhookController.
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

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;

/**
 * Class PayrexxWebhookController.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxWebhookController extends HttpViewController
{
    /**
     * Payrexx Configuration
     *
     * @var PayrexxStorage
     */
    protected $configuration;

    /**
     * Order service
     *
     * @var OrderService
     */
    protected $orderService;

    /**
     * Payrexx Api Service
     *
     * @var PayrexxApiService
     */
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
            $this->orderService->handleTransactionStatus(
                $orderId,
                $transaction['status'],
                $transaction['invoice']
            );
            echo 'Success: Webhook processed!';
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        exit();
    }
}
