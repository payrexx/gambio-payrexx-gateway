<?php
/**
 * Class PayrexxApiService.
 *
 * Payment gateway for Payrexx AG.
 *
 * @category  Payment Module
 * @link      https://www.payrexx.com
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 *
 * VERSION HISTORY:
 * 1.0.0 Payrexx Payment Gateway.
 */

namespace Payrexx\PayrexxPaymentGateway\Classes\Service;

use Payrexx\Payrexx;
use PayrexxStorage;
use Payrexx\Models\Request\SignatureCheck;
use Payrexx\Models\Request\Transaction;

class PayrexxApiService
{
    /**
     * @var PayrexxStorage $configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $instance;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $platform;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configuration = new PayrexxStorage();
        $this->instance = $this->configuration->get('INSTANCE_NAME');
        $this->apiKey = $this->configuration->get('API_KEY');
        $this->platform = $this->configuration->get('PLATFORM');
    }

    /**
     * validate the api signature
     *
     * @param string $instance
     * @param string $apiKey
     * @param string $platform
     * @return true|false
     */
    public function validateSignature(string $instance, string $apiKey, string $platform): bool
    {
        $payrexx = $this->getInterface($instance, $apiKey, $platform);
        try {
            $payrexx->getOne(new SignatureCheck());
            return true;
        } catch (\Payrexx\PayrexxException $e) {
            return false;
        }
    }

    /**
     * Get Transaction by transaction id
     *
     * @param integer $id
     * @return array|Transaction
     */
    public function getTransactionById(int $id)
    {
        $payrexx = $this->getInterface($this->instance, $this->apiKey, $this->platform);;
        $transaction = new Transaction();
        $transaction->setId($id);

        try {
            $response = $payrexx->getOne($transaction);
            return $response;
        } catch (\Payrexx\PayrexxException $e) {
            return [];
        }
    }

    /**
     * Create Gateway
     *
     * @param order $order
     * @param array $basket
     * @param string $purpose
     * @param array $pm
     */
    public function createGateway($order, array $basket, $purpose, array $pm)
    {
        $currency = $order->info['currency'];
        $totalAmount = $order->info['pp_total'] * 100;
        $orderId = $order->info['orders_id'];

        // Redirect URL
        $successUrl = xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'payrexx_success=1', 'SSL');
        $failedUrl = xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'payrexx_failed=1', 'SSL');
        $cancelUrl = xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'payrexx_cancel=1', 'SSL');

        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setAmount($totalAmount);
        $gateway->setCurrency($currency);

        $gateway->setSuccessRedirectUrl($successUrl);
        $gateway->setFailedRedirectUrl($failedUrl);
        $gateway->setCancelRedirectUrl($cancelUrl);

        $gateway->setPsp([]);
        $gateway->setPm($pm);

        $gateway->setReferenceId($orderId);
        $gateway->setValidity(15);

        $gateway->setBasket($basket);
        $gateway->setPurpose($purpose);

        $gateway->setSkipResultPage(true);

        $gateway->addField('forename', $order->billing['firstname']);
        $gateway->addField('surname', $order->billing['lastname']);
        $gateway->addField('company', $order->billing['company']);
        $gateway->addField('street', $order->billing['street_address']);
        $gateway->addField('postcode', $order->billing['postcode']);
        $gateway->addField('place', $order->billing['city']);
        $gateway->addField('country', $order->billing['country_iso_2']);
        $gateway->addField('phone', $order->customer['telephone']);
        $gateway->addField('email', $order->customer['email_address']);
        $gateway->addField('custom_field_1', $orderId, 'Gambio Order ID');

        if (!empty($this->configuration->get('LOOK_AND_FEEL_ID'))) {
            $gateway->setLookAndFeelProfile($this->configuration->get('LOOK_AND_FEEL_ID'));
        }

        $payrexx = $this->getInterface($this->instance, $this->apiKey, $this->platform);
        return $payrexx->create($gateway);
    }

    /**
     * @return Payrexx
     */
    private function getInterface(string $instance, string $apiKey, string $platform): Payrexx
    {
        return new Payrexx($instance, $apiKey, '', $platform);
    }
}
