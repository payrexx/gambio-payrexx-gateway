<?php
/**
 * Class PayrexxStorage.
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
declare(strict_types = 1);

use Payrexx\PayrexxPaymentGateway\Classes\Util\ConfigurationUtil;

/**
 * Class PayrexxStorage
 */
class PayrexxStorage extends ConfigurationStorage
{
    const CONFIG_INSTANCE_NAME = 'INSTANCE_NAME';
    const CONFIG_API_KEY = 'API_KEY';
    const CONFIG_PLATFORM = 'PLATFORM';
    const CONFIG_LOOK_AND_FEEL_ID = 'LOOK_AND_FEEL_ID';

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
    public function getAll()
    {
        $prefix = self::CONFIG_STORAGE_PREFIX;
        $configValues = parent::get_all($prefix);
        foreach ($configValues as $key => $configValue) {
            $configValues[str_replace($prefix, '', $key)] = $configValue;
        }
        return $configValues;
    }

    /**
     * Set config value
     *
     * @param string $key   config key
     * @param string $value config value
     */
    public function set($key, $value)
    {
        if (!in_array($key, array_keys(ConfigurationUtil::getBasicConfigurations()))) {
            return false;
        }
        parent::set(self::CONFIG_STORAGE_PREFIX . $key, $value);
    }
}
