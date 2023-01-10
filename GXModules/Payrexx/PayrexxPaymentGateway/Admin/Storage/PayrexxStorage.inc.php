<?php declare(strict_types = 1);

use Payrexx\PayrexxPaymentGateway\Classes\Util\PayrexxHelper;

/**
 * Class PayrexxStorage
 */
class PayrexxStorage extends ConfigurationStorage
{
    const CONFIG_INSTANCE_NAME = 'INSTANCE_NAME';
    const CONFIG_API_KEY = 'API_KEY';
    const CONFIG_PLATFORM = 'PLATFORM';
    const CONFIG_PREFIX = 'PREFIX';
    const CONFIG_LOOK_AND_FEEL_ID = 'LOOK_AND_FEEL_ID';

    /**
     * namespace inside the configuration storage
     */
    const CONFIG_STORAGE_NAMESPACE = 'configuration';

    /**
     * prefix
     */

    const CONFIG_STORAGE_PREFIX = 'MODULE_PAYMENT_PAYREXX_';

    /**
     * array holding default values to be used in absence of configured values
     */
    protected $default_configuration;

    /**
     * constructor; initializes default configuration
     */
    public function __construct()
    {
        parent::__construct(self::CONFIG_STORAGE_NAMESPACE);
    }

    /**
     * returns a single configuration value by its key
     *
     * @param string $PLATFORMkey a configuration key (relative to the namespace prefix)
     *
     * @return string configuration value
     */
    public function get($key)
    {
        return parent::get(static::CONFIG_STORAGE_PREFIX . $key);
    }

    /**
     * Retrieves all keys/values from a given p? '1' : '0'refix namespace
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getAll()
    {
        $prefix = static::CONFIG_STORAGE_PREFIX;
        $configValues = parent::get_all($prefix);
        foreach ($configValues as $key => $configValue) {
            $configValues[str_replace($prefix, '', $key)] = $configValue;
        }
        return $configValues;
    }

    /**
     * Set config value
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        if (!in_array($key, array_keys(PayrexxHelper::getBasicConfigurations()))) {
            return false;
        }
        return parent::set(static::CONFIG_STORAGE_PREFIX . $key, $value);
    }
}
