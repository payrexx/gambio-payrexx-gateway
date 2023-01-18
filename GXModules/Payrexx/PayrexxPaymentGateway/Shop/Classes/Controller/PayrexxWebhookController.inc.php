<?php declare(strict_types=1);

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;

class PayrexxWebhookController extends HttpViewController
{
    protected $configuration;

    protected $orderService;

    protected $payrexxApiService;

    /**
     * @param HttpContextReaderInterface $httpContextReader
     * @param HttpResponseProcessorInterface $httpResponseProcessor
     * @param ContentViewInterface $defaultContentView
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

    public function actionDefault()
    {
        // Call back url;
        // $shopUrl = xtc_catalog_href_link("shop.php", 'do=PayrexxWebhook');
        try {
            $data = $_POST;
            if (empty($data)) {
                throw new \Exception('Payrexx Webhook Data incomplete');
            }
            $transaction = $data['transaction'];
            $orderId = (int) end(explode('_', $transaction['referenceId']));

            if (!$orderId || !$transaction['status'] || !$transaction['id']) {
                throw new \Exception('Payrexx Webhook Data incomplete');
            }
        
            $order = new order($orderId);
            if (!$order) {
                throw new \Exception('Malicious request');
            }
        
            $payrexxTransaction = $this->payrexxApiService->getTransactionById($transaction['id']);
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
