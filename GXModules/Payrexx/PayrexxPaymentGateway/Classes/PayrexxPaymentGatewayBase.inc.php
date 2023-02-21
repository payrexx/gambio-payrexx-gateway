<?php
/*--------------------------------------------------------------------------------------------------
    PayrexxPaymentGatewayBase.php 12-01-2023
    https://www.payrexx.com
    Copyright (c) 2023 payrexx
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

use Payrexx\PayrexxPaymentGateway\Classes\Util\ConfigurationUtil;
use Payrexx\Models\Response\Transaction;
use Payrexx\PayrexxPaymentGateway\Classes\Service\OrderService;
use Payrexx\PayrexxPaymentGateway\Classes\Controller\PayrexxPaymentController;

class PayrexxPaymentGatewayBase
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var int
     */
    public $sort_order = 0;

    /**
     * @var string
     */
    public $info;

    /**
     * @var bool
     */
    public $tmpOrders = true;

    /**
     * @var LanguageTextManager
     */
    private $langText;

    /**
     * @var string
     */
    public $code = 'payrexx';

    const IMAGE_PATH = 'GXModules/Payrexx/PayrexxPaymentGateway/Images/Icons/Payment/';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->langText    = MainFactory::create('LanguageTextManager', 'payrexx', $_SESSION['languages_id']);
        $this->title       = ucwords(str_replace('_', ' ', $this->code));
        $this->info        = defined($this->getConstant('TEXT_INFO')) ? $this->getConstantValue('TEXT_INFO') : $this->langText->get_text('text_info');
        $this->sort_order  = defined($this->getConstant('SORT_ORDER')) ? $this->getConstantValue('SORT_ORDER') : $this->sort_order;
        $this->enabled     = defined($this->getConstant('STATUS')) && filter_var(constant($this->getConstant('STATUS')), FILTER_VALIDATE_BOOLEAN);
        $this->description = $this->langText->get_text('text_description');
        if (defined('DIR_WS_ADMIN')) {
            $this->addAdditionalInfo();
        }
        $this->defineConstants();
    }

    /**
     * Add more information to admin view
     *
     * @return string
     */
    public function addAdditionalInfo()
    {
        // title
        $this->title .= xtc_image(
            xtc_catalog_href_link(
                self::IMAGE_PATH . $this->code . '.svg',
                '',
                'SSL'
            ),
            $this->title . ' logo'
        );

        // description
        $this->description .= $this->langText->get_text('text_description2');
        if (!$this->credentialsCheck()) {
            $this->description .= '<br><span style="color:#ff0000">' . $this->langText->get_text('config_invalid') . '</span><br><br>';
        }
    }

    /**
     * Get constant
     *
     * @param string $key
     * @return string
     */
    public function getConstant(string $key): string
    {
        return 'MODULE_PAYMENT_' .  strtoupper($this->code) . '_' . $key;
    }

    /**
     * Get constant value
     *
     * @param string $key
     * @return string
     */
    public function getConstantValue(string $key): string
    {
        return constant(MODULE_PAYMENT_ . strtoupper($this->code) . _ . $key);
    }

    /**
     * Initialize the constants.
     */
    public function defineConstants()
    {
        $configKeys = array_keys(ConfigurationUtil::getModuleConfigurations());
        foreach ($configKeys as $key) {
            if (in_array(strtolower($key), ConfigurationUtil::getPaymentMethods())) {
                $title = str_replace('_', ' ', ucfirst(strtolower($key)));
                $desc = $this->langText->get_text('accept_payment_by') . $title .'?';
            } else {
                $title = $this->langText->get_text(strtolower($key) . '_title');
                $desc = $this->langText->get_text(strtolower($key). '_desc');
            }
            define($this->getConstant($key) . '_TITLE', $title);
            define($this->getConstant($key) . '_DESC', $desc);
        }
    }

    public function update_status()
    {
        global $order;
        if (($this->enabled == true) && ((int)$this->getConstantValue('ZONE') > 0)) {
            $check_flag = false;
            $sql        = xtc_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '"
                . $this->getConstantValue('ZONE') . "' AND zone_country_id = '"
                . $order->billing['country']['id'] . "' ORDER BY zone_id");

            while ($check = xtc_db_fetch_array($sql)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }
            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    /**
     * @return false
     */
    public function javascript_validation()
    {
        return false;
    }

    /**
     * @return array|false
     */
    public function selection()
    {
        if (isset($_GET['payrexx_cancel'])) {
            $_SESSION['gm_error_message'] = urlencode($this->langText->get_text('payment_cancel'));
        }

        $selection = [
            'id' => $this->code,
            'module' => $this->getConstantValue('CHECKOUT_NAME'),
            'description' => $this->getDescription(),
            'logo_url' => xtc_href_link(
                self::IMAGE_PATH . 'payrexx.svg',
                '',
                'SSL'
            ),
        ];

        return $selection;
    }

    /**
     * It is similar to Signature check
     *
     * @return bool
     */
    private function credentialsCheck()
    {
        $storage = MainFactory::create('PayrexxStorage');
        if (empty($storage->get('API_KEY')) || empty($storage->get('INSTANCE_NAME'))) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    protected function getDescription()
    {
        $description = $this->getConstantValue('CHECKOUT_DESCRIPTION');
        foreach (ConfigurationUtil::getPaymentMethods() as $method) {
            if ($this->getConstantValue(strtoupper($method)) === 'true') {
                $description .= $this->getPaymentMethodIcon($method);
            }
        }
        return $description;
    }

    /**
     * @param $paymentMethod
     *
     * @return string
     */
    protected function getPaymentMethodIcon(string $paymentMethod)
    {
        $path = self::IMAGE_PATH . 'card_' . $paymentMethod . '.svg';
        if (file_exists(DIR_FS_CATALOG . $path)) {
            return xtc_image(xtc_href_link($path, '', 'SSL'), $paymentMethod);
        }
        return '';
    }

    /**
     * @return false
     */
    public function pre_confirmation_check()
    {
        return false;
    }

    /**
     * @return false
     */
    public function confirmation()
    {
        if (isset($_GET['payrexx_failed'])) {
            $_SESSION['gm_error_message'] = urlencode($this->langText->get_text('payment_failed'));
        }
        return false;
    }

    /**
     * @return false
     */
    public function process_button()
    {
        return false;
    }

    /**
     * Execute after order saved
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
        $payrexxPaymentUrl = str_replace('?', $_SESSION['language_code'] . '/?', $response->getLink());
        xtc_redirect($payrexxPaymentUrl);
    }

    /**
     * @return false
     */
    public function before_process()
    {
        return false;
    }

    /**
     * @return false
     */
    public function after_process()
    {
        global $insert_id;

        if (!isset($_GET['payrexx_cancel']) && !isset($_GET['payrexx_failed'])) {
            return false;
        }

        try {
            $orderservice = new OrderService();
            $orderservice->handleTransactionStatus($insert_id, Transaction::CANCELLED);
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
     * @return false
     */
    public function get_error()
    {
        return false;
    }

    /**
     * @return int|mixed|string
     */
    public function check()
    {

        if (!isset($this->_check)) {
            $query  = xtc_db_query("SELECT `value` FROM " . TABLE_CONFIGURATION
                . " WHERE `key` = 'configuration/MODULE_PAYMENT_" . strtoupper($this->code)
                . "_STATUS'");
            $this->_check = xtc_db_num_rows($query);
        }
        return $this->_check;
    }

    /**
     * Determines the module's configuration keys.
     *
     * @return array
     */
    public function keys(): array
    {
        $ckeys = array_keys(ConfigurationUtil::getModuleConfigurations());
        $keys  = [];
        foreach ($ckeys as $key) {
            $keys[] = 'configuration/' . $this->getConstant($key);
        }
        return $keys;
    }

    /**
     * Installs the Module configurations.
     */
    public function install()
    {
        $config     = ConfigurationUtil::getModuleConfigurations();
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
     */
    public function remove()
    {
        xtc_db_query(
            "DELETE FROM " . TABLE_CONFIGURATION . "
                WHERE `key`
                IN ('" . implode("', '", $this->keys()) . "')"
        );
    }

    /**
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
}
