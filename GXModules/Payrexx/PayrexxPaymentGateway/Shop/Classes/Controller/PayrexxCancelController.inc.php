<?php
/**
 * Class PayrexxCancelController.
 *
 * Payment gateway for Payrexx AG.
 *
 * PHP version 7,8
 *
 * @category  PaymentModule
 * @package   PayrexxPaymentGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2024 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 */
declare(strict_types=1);

use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\Models\Response\Transaction;

/**
 * Class PayrexxCancelController.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxCancelController extends HttpViewController
{

    /**
     * Order service
     *
     * @var OrderService
     */
    protected $orderService;

    /**
     * Language Text
     *
     * @var LanguageTextManager
     */
    private $langText;

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
        $this->orderService = new OrderService();
        $this->langText = MainFactory::create('LanguageTextManager', 'payrexx', $_SESSION['languages_id']);
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
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                $this->orderService->handleTransactionStatus($id, Transaction::CANCELLED);
            }
        } catch (Exception $e) {}
        // Error messages.
        $errorMessage = isset($_GET['payrexx_cancel'])
            ? $this->langText->get_text('payment_cancel')
            : $this->langText->get_text('payment_failed');
        $_SESSION['gm_error_message'] = urlencode($errorMessage);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        exit();
    }
}
