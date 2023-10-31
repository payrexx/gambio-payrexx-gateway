<?php
/**
 * Class PayrexxStorage.
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

use Payrexx\PayrexxPaymentGateway\Classes\Config\PayrexxConfig;
use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;

/**
 * Class PayrexxStorage
 *
 * @category  PaymentModule
 * @package   PayrexxPaymentGateway
 * @author    Payrexx <integration@payrexx.com>
 * @copyright 2023 Payrexx
 * @license   MIT License
 * @link      https://www.payrexx.com
 */
class PayrexxStorage extends ConfigurationStorage
{
    /**
     * Namespace inside the configuration storage
     */
    const CONFIG_STORAGE_NAMESPACE = 'configuration';

    /**
     * Prefix
     */
    const CONFIG_STORAGE_PREFIX = 'MODULE_PAYMENT_PAYREXX_';

    /**
     * Array holding default values to be used in absence of configured values
     */
    protected $default_configuration;

    /**
     * Constructor initializes default configuration
     */
    public function __construct()
    {
        parent::__construct(self::CONFIG_STORAGE_NAMESPACE);
    }

    /**
     * Returns a single configuration value by its key
     *
     * @param string $key a configuration key (relative to the namespace prefix)
     *
     * @return string|false configuration value
     */
    public function get($key)
    {
        return parent::get(self::CONFIG_STORAGE_PREFIX . $key);
    }

    /**
     * Retrieves all keys/values
     *
     * @return array
     */
    public function getAll(): array
    {
        $prefix = self::CONFIG_STORAGE_PREFIX;
        $configValues = parent::get_all($prefix);
        foreach ($configValues as $key => $configValue) {
            $configValues[str_replace($prefix, '', $key)] = $configValue;
        }
        $configValues = $this->updateDefaultConfig($configValues);
        return $configValues;
    }

    /**
     * Set config value
     *
     * @param string $key   config key
     * @param string $value config value
     *
     * @return void
     */
    public function set($key, $value)
    {
        if (
            !in_array(
                $key,
                array_keys(PayrexxConfig::getBasicConfigurations())
            )
        ) {
            return false;
        }
        parent::set(self::CONFIG_STORAGE_PREFIX . $key, $value);
    }

    /**
     * update default config values
     *
     * @param array $configValues
     * @return array
     */
    public function updateDefaultConfig(array $configValues): array
    {
        // payment order status
        $configValues['PAYMENT_WAITING_STATUS_ID'] = $configValues['PAYMENT_WAITING_STATUS_ID'] ?? 1;
        $configValues['PAYMENT_SUCCESS_STATUS_ID'] = $configValues['PAYMENT_SUCCESS_STATUS_ID'] ?? 2;
        $configValues['PAYMENT_FAILED_STATUS_ID'] = $configValues['PAYMENT_FAILED_STATUS_ID'] ?? 99;

        $orderService = new OrderService();

        if (empty($configValues['PAYMENT_REFUNDED_STATUS_ID']) || empty($configValues['PAYMENT_PARTIALLY_REFUNDED_STATUS_ID'])) {
            $orderService->addNewOrderStatus();
        }
        $refudId = $orderService->orderStatusExists($orderService::STATUS_REFUNDED);
        $partiallyRefundId = $orderService->orderStatusExists($orderService::STATUS_PARTIALLY_REFUNDED);

        $configValues['PAYMENT_REFUNDED_STATUS_ID'] = $configValues['PAYMENT_REFUNDED_STATUS_ID'] ?? $refudId;
        $configValues['PAYMENT_PARTIALLY_REFUNDED_STATUS_ID'] = $configValues['PAYMENT_PARTIALLY_REFUNDED_STATUS_ID'] ?? $partiallyRefundId;

        return $configValues;
    }
}
