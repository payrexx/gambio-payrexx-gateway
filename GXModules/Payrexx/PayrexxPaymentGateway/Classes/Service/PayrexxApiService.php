<?php
/**
 * Class PayrexxApiService.
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

namespace Payrexx\PayrexxPaymentGateway\Classes\Service;

use PayrexxStorage;
use Payrexx\Payrexx;
use Payrexx\Models\Request\SignatureCheck;
use Payrexx\Models\Request\Transaction;
use Payrexx\Models\Response\Gateway;
use Payrexx\PayrexxException;

/**
 * Class PayrexxApiService.
 *
 * @category PaymentModule
 * @package  PayrexxPaymentGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxApiService
{
    /**
     * Configuration
     *
     * @var PayrexxStorage $configuration
     */
    protected $configuration;

    /**
     * Instance name
     *
     * @var string
     */
    protected $instance;

    /**
     * Payrexx Api Key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Payrexx Platform
     *
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
     * Validate the api signature
     *
     * @param string $instance Instance name
     * @param string $apiKey   Api key
     * @param string $platform Platform
     *
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
     * @param integer $id Transaction id
     *
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
     * @throws PayrexxException
     */
    public function createGateway(
        $order,
        array $basket,
        string $purpose,
        array $pm,
        array $metaData = []
    ): Gateway {
        $currency = $order->info['currency'];
        $totalAmount = $order->info['pp_total'] * 100;
        $orderId = $order->info['orders_id'];

        // Redirect URL
        $successUrl = xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'payrexx_success=1', 'SSL');
        $failedUrl = xtc_href_link("shop.php", 'do=PayrexxCancel&payrexx_failed=1&id=' . $orderId, 'SSL');
        $cancelUrl = xtc_href_link("shop.php", 'do=PayrexxCancel&payrexx_cancel=1&id=' . $orderId, 'SSL');

        $gateway = new \Payrexx\Models\Request\Gateway();
        $gateway->setAmount((int)$totalAmount);
        $gateway->setCurrency($currency);

        $gateway->setSuccessRedirectUrl($successUrl);
        $gateway->setFailedRedirectUrl($failedUrl);
        $gateway->setCancelRedirectUrl($cancelUrl);

        $gateway->setPsp([]);
        $gateway->setPm($pm);

        $gateway->setReferenceId($orderId);
        $gateway->setValidity(15);

        if (!empty($basket)) {
            $gateway->setBasket($basket);
        } else {
            $gateway->setPurpose($purpose);
        }

        $gateway->setSkipResultPage(true);

        $billingStreet = $order->billing['street_address'];
        if (!empty($order->billing['house_number'])) {
            $billingStreet .= ' ' . $order->billing['house_number'];
        }
        $deliveryStreet = $order->delivery['street_address'];
        if (!empty($order->delivery['house_number'])) {
            $deliveryStreet .= ' ' . $order->delivery['house_number'];
        }
        $gateway->addField('forename', $order->billing['firstname']);
        $gateway->addField('surname', $order->billing['lastname']);
        $gateway->addField('company', $order->billing['company']);
        $gateway->addField('street', $billingStreet);
        $gateway->addField('postcode', $order->billing['postcode']);
        $gateway->addField('place', $order->billing['city']);
        $gateway->addField('country', $order->billing['country_iso_2']);
        $gateway->addField('phone', $order->customer['telephone']);
        $gateway->addField('email', $order->customer['email_address']);
        $gateway->addField('custom_field_1', $orderId, 'Gambio Order ID');
        $gateway->addField('delivery_forename', $order->delivery['firstname']);
        $gateway->addField('delivery_surname', $order->delivery['lastname']);
        $gateway->addField('delivery_company', $order->delivery['company']);
        $gateway->addField('delivery_street', $deliveryStreet);
        $gateway->addField('delivery_postcode', $order->delivery['postcode']);
        $gateway->addField('delivery_place', $order->delivery['city']);
        $gateway->addField('delivery_country', $order->delivery['country_iso_2']);

        if (!empty($this->configuration->get('LOOK_AND_FEEL_ID'))) {
            $gateway->setLookAndFeelProfile($this->configuration->get('LOOK_AND_FEEL_ID'));
        }

        $payrexx = $this->getInterface($this->instance, $this->apiKey, $this->platform);
        if (!empty($metaData)) {
            $payrexx->setHttpHeaders($metaData);
        }
        return $payrexx->create($gateway);
    }

    /**
     * Get Payrexx object
     *
     * @param string $instance Instance Name
     * @param string $apiKey   Api Key
     * @param string $platform Platform
     *
     * @return Payrexx
     */
    private function getInterface(
        string $instance,
        string $apiKey,
        string $platform
    ): Payrexx {
        return new Payrexx($instance, $apiKey, '', $platform);
    }
}
