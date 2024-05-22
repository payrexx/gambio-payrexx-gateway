<?php
/**
 * Class PayrexxPaymentGatewayBase.
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

use Payrexx\PayrexxPaymentGateway\Classes\Config\PayrexxConfig;
use Payrexx\PayrexxPaymentGateway\Classes\Controller\PayrexxPaymentController;
use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\Models\Response\Transaction;

/**
 * Class PayrexxPaymentGatewayBase.
 *
 * @category PaymentModule
 * @package  PayrexxPayemntGateway
 * @author   Payrexx <integration@payrexx.com>
 * @license  MIT License
 * @link     https://www.payrexx.com
 */
class PayrexxPaymentGatewayBase
{
    /**
     * Title
     *
     * @var string
     */
    public $title;

    /**
     * Description
     *
     * @var string
     */
    public $description;

    /**
     * Enabled
     *
     * @var boolean
     */
    public $enabled;

    /**
     * Sorting order
     *
     * @var int
     */
    public $sort_order = 0;

    /**
     * Info
     *
     * @var string
     */
    public $info;

    /**
     * Temp orders
     *
     * @var bool
     */
    public $tmpOrders = true;

    /**
     * Language Text
     *
     * @var LanguageTextManager
     */
    private $langText;

    /**
     * Payment Code
     *
     * @var string
     */
    public $code = 'payrexx';

    /**
     * Card Images path
     */
    const IMAGE_PATH = 'GXModules/Payrexx/PayrexxPaymentGateway/Images/Icons/Payment/';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->langText = MainFactory::create('LanguageTextManager', 'payrexx', $_SESSION['languages_id']);
        $this->title = ucwords(str_replace('_', ' ', $this->code));
        $this->info = defined($this->_getConstant('TEXT_INFO')) ? $this->_getConstantValue('TEXT_INFO') : $this->langText->get_text('text_info');
        $this->sort_order = defined($this->_getConstant('SORT_ORDER')) ? $this->_getConstantValue('SORT_ORDER') : $this->sort_order;
        $this->enabled = defined($this->_getConstant('STATUS')) && filter_var(constant($this->_getConstant('STATUS')), FILTER_VALIDATE_BOOLEAN);
        $this->description = $this->langText->get_text('text_description');
        if (defined('DIR_WS_ADMIN')) {
            $this->addAdditionalInfo();
        }
        $this->defineConstants();
    }

    /**
     * Initialize the constants.
     *
     * @return void
     */
    public function defineConstants()
    {
        // Payment method title and zone
        $pmConstants = [
            $this->_getConstant('TEXT_TITLE') => ucwords(str_replace('_', ' ', $this->code)),
            $this->_getConstant('ZONE') => '',
        ];

        // configuration
        $configKeys = array_keys(PayrexxConfig::getModuleConfigurations($this->code));
        foreach ($configKeys as $key) {
            $pmConstants[$this->_getConstant($key) . '_TITLE'] = $this->langText->get_text(strtolower($key) . '_title');
            $pmConstants[$this->_getConstant($key) . '_DESC'] = $this->langText->get_text(strtolower($key) . '_desc');
        }

        // order edit: define all payrexx payment methods title and zone.
        if (defined('MODULE_PAYMENT_INSTALLED') && trim(constant('MODULE_PAYMENT_INSTALLED') != '')) {
            $payments = explode(';', constant('MODULE_PAYMENT_INSTALLED'));
            foreach ($payments as $payment) {
                $paymentKey = substr($payment, 0, strrpos($payment, '.'));
                if (!preg_match('/payrexx/', $paymentKey)) {
                    continue;
                }
                $pmConstants['MODULE_PAYMENT_' .  strtoupper($paymentKey) . '_TEXT_TITLE'] = ucwords(str_replace('_', ' ', $paymentKey));
                $pmConstants['MODULE_PAYMENT_' .  strtoupper($paymentKey) . '_ZONE'] = '';
            }
        }

        // define constants
        foreach ($pmConstants as $constantKey => $constantValue) {
            if (!defined($constantKey)) {
                define($constantKey, $constantValue);
            }
        }
    }

    /**
     * Update edit page changes
     *
     * @return void
     */
    public function update_status()
    {
        global $order;
        if (($this->enabled == true) && ((int)$this->_getConstantValue('ZONE') > 0)) {
            $checkFlag = false;
            $sql = xtc_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '"
                . $this->_getConstantValue('ZONE') . "' AND zone_country_id = '"
                . $order->billing['country']['id'] . "' ORDER BY zone_id"
            );

            while ($check = xtc_db_fetch_array($sql)) {
                if ($check['zone_id'] < 1) {
                    $checkFlag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $checkFlag = true;
                    break;
                }
            }
            if ($checkFlag == false) {
                $this->enabled = false;
            }
        }
    }

    /**
     * Javascript validation
     *
     * @return false
     */
    public function javascript_validation()
    {
        return false;
    }

    /**
     * Selection
     *
     * @return array|false
     */
    public function selection()
    {
        if (isset($_GET['payrexx_cancel'])) {
            $_SESSION['gm_error_message'] = urlencode(
                $this->langText->get_text('payment_cancel')
            );
        }

        $logoImageName = ($this->code === 'payrexx')
            ? 'payrexx'
            : str_replace('payrexx', 'card', $this->code);
        $logo = self::IMAGE_PATH . $logoImageName . '.svg';
        $selection = [
            'id' => $this->code,
            'module' => $this->_getConstantValue('CHECKOUT_NAME'),
            'description' => $this->_getConstantValue('CHECKOUT_DESCRIPTION'),
            'logo_url' => xtc_href_link($logo, '', 'SSL', false, false, false, true, true),
        ];

        return $selection;
    }

    /**
     * Executes before confirmation
     *
     * @return false
     */
    public function pre_confirmation_check()
    {
        return false;
    }

    /**
     * Order confirmation
     *
     * @return false
     */
    public function confirmation()
    {
        if (isset($_GET['payrexx_failed'])) {
            $_SESSION['gm_error_message'] = urlencode(
                $this->langText->get_text('payment_failed')
            );
        }
        return false;
    }

    /**
     * Excutes the function click payment button
     *
     * @return false
     */
    public function process_button()
    {
        return false;
    }

    /**
     * Execute after order saved
     *
     * @return false|void
     */
    public function payment_action()
    {
        global $insert_id;

        $orderId = $insert_id;
        if (isset($_GET['payrexx_success'])) {
            return false;
        }

        try {
            $paymentController = new PayrexxPaymentController();
            $order = new order($orderId);
            $response = $paymentController->createPayrexxGateway($order);
        } catch (\Payrexx\PayrexxException $e) {
            return false;
        }
        try {
            $orderservice = new OrderService();
            $orderservice->handleTransactionStatus(
                $orderId,
                Transaction::WAITING
            );
        } catch(Exception $e) {}
        $payrexxPaymentUrl = str_replace(
            '?',
            $_SESSION['language_code'] . '/?',
            $response->getLink()
        );
        xtc_redirect($payrexxPaymentUrl);
    }

    /**
     * Before payment process
     *
     * @return false
     */
    public function before_process()
    {
        return false;
    }

    /**
     * Executes fter payment process
     *
     * @return false|void
     */
    public function after_process()
    {
        global $insert_id;

        if (!isset($_GET['payrexx_cancel']) && !isset($_GET['payrexx_failed'])) {
            return false;
        }

        try {
            $orderservice = new OrderService();
            $orderservice->handleTransactionStatus(
                $insert_id,
                Transaction::CANCELLED
            );
        } catch (Exception $e) {
        }
        // Error messages.
        $errorMessage = isset($_GET['payrexx_cancel'])
            ? $this->langText->get_text('payment_cancel')
            : $this->langText->get_text('payment_failed');
        $_SESSION['gm_error_message'] = urlencode($errorMessage);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }

    /**
     * Get error
     *
     * @return false
     */
    public function get_error()
    {
        return false;
    }

    /**
     * Check the module status
     *
     * @return int|mixed|string
     */
    public function check()
    {
        if (!isset($this->_check)) {
            $query  = xtc_db_query("SELECT `value` FROM " . TABLE_CONFIGURATION
                . " WHERE `key` = 'configuration/MODULE_PAYMENT_" . strtoupper($this->code)
                . "_STATUS'"
            );
            $this->_check = xtc_db_num_rows($query);
        }
        return $this->_check;
    }

    /**
     * Determines the module's configuration keys.
     *
     * @return array configuration keys
     */
    public function keys(): array
    {
        $configKeys = array_keys(PayrexxConfig::getModuleConfigurations($this->code));
        $keys  = [];
        foreach ($configKeys as $key) {
            $keys[] = 'configuration/' . $this->_getConstant($key);
        }
        return $keys;
    }

    /**
     * Installs the Module configurations.
     *
     * @return void
     */
    public function install()
    {
        $config = PayrexxConfig::getModuleConfigurations($this->code);
        $config['STATUS'] = ['value' => 'True', 'type' => 'switcher'];
        $sortOrder = 0;
        foreach ($config as $key => $data) {
            $installQuery = "INSERT INTO `gx_configurations` ( `key`, `value`, `sort_order`, `type`, `last_modified`) "
                . "values ('configuration/MODULE_PAYMENT_" . strtoupper($this->code) . "_" . $key . "', '"
                . $data['value'] . "', '" . $sortOrder . "', '" . addslashes($data['type']) . "', now())";
            xtc_db_query($installQuery);
            $sortOrder++;
        }
    }

    /**
     * Removes the Module configurations.
     *
     * @return void
     */
    public function remove()
    {
        $keys = $this->keys();
        $keys[] = 'configuration/' . $this->_getConstant('STATUS');
        xtc_db_query(
            "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE `key`
                IN ('" . implode("', '", $keys) . "')"
        );
    }

    /**
     * Check the module installed or not
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        $isInstalled = true;
        foreach ($this->keys() as $key) {
            if (!defined($key)) {
                $isInstalled = false;
            }
        }
        return $isInstalled;
    }

    /**
     * Add more information to admin view
     *
     * @return void
     */
    private function addAdditionalInfo()
    {
        // title
        $logoImageName = ($this->code === 'payrexx')
            ? 'payrexx'
            : str_replace('payrexx', 'card', $this->code);
        $this->title .= xtc_image(
            xtc_catalog_href_link(
                self::IMAGE_PATH . $logoImageName . '.svg',
                '',
                'SSL'
            ),
            $this->title . ' logo'
        );

        // description
        $this->description .= $this->langText->get_text('text_description2');
        if (!$this->_credentialsCheck()) {
            $this->description .= '<br><span style="color:#ff0000">';
            $this->description .= $this->langText->get_text('config_invalid');
            $this->description .= '</span><br><br>';
        }
    }

    /**
     * Get constant
     *
     * @param string $key Contant key
     *
     * @return string constant
     */
    private function _getConstant(string $key): string
    {
        return 'MODULE_PAYMENT_' .  strtoupper($this->code) . '_' . $key;
    }

    /**
     * Get constant value
     *
     * @param string $key Constant Key
     *
     * @return string constant value
     */
    private function _getConstantValue(string $key): string
    {
        return constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_' . $key);
    }

    /**
     * It is similar to Signature check
     *
     * @return bool
     */
    private function _credentialsCheck(): bool
    {
        $storage = MainFactory::create('PayrexxStorage');
        if (empty($storage->get('API_KEY')) || empty($storage->get('INSTANCE_NAME'))) {
            return false;
        }
        return true;
    }
}
