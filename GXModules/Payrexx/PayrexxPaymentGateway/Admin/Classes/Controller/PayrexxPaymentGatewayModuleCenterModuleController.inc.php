<?php declare(strict_types=1);


use Payrexx\PayrexxPaymentGateway\Classes\Service\PayrexxApiService;
use Payrexx\PayrexxPaymentGateway\Classes\Util\PayrexxHelper;
use PayrexxStorage;

/**
 * Class PayrexxPaymentGatewayModuleCenterModuleController
 */
class PayrexxPaymentGatewayModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    /**
     * @var PayrexxStorage $configuration
     */
    protected $configuration;

    /**
     * @var PayrexxApiService $payrexxApiService
     */
    public $payrexxApiService;


    protected function _init(): void
    {
        $this->pageTitle = $this->languageTextManager->get_text('page_title', 'payrexx');
        $this->configuration = MainFactory::create('PayrexxStorage');
        $this->payrexxApiService = new PayrexxApiService();
    }

    /**
     * @return AdminLayoutHttpControllerResponse
     * @throws Exception
     */
    public function actionDefault(): AdminLayoutHttpControllerResponse
    {
        $title = new NonEmptyStringType($this->languageTextManager->get_text('page_title', 'payrexx'));
        $template = $this->getTemplateFile('Payrexx/PayrexxPaymentGateway/Admin/Html/basic_config.html');
        $data = MainFactory::create(
            'KeyValueCollection',
            [
                'pageToken' => $_SESSION['coo_page_token']->generate_token(),
                'configuration' => $this->configuration->getAll(),
                'platforms' => PayrexxHelper::getPlatforms(),
                'translate_section' => 'payrexx',
                'action_save' => xtc_href_link('admin.php', 'do=PayrexxPaymentGatewayModuleCenterModule/SaveConfig'),
            ]
        );

        return MainFactory::create('AdminLayoutHttpControllerResponse', $title, $template, $data);
    }

    /**
     * @return RedirectHttpControllerResponse
     * @throws Exception
     */
    public function actionSaveConfig(): RedirectHttpControllerResponse
    {
        $this->_validatePageToken();

        $postValues = $this->_getPostData('configuration');
        $signatureCheck = $this->payrexxApiService->validateSignature(
            $postValues['INSTANCE_NAME'],
            $postValues['API_KEY'],
            $postValues['PLATFORM'],
        );
        try {
            if ($signatureCheck) {
                foreach ($postValues as $key => $value) {
                    $this->configuration->set($key, $value);
                }
                $GLOBALS['messageStack']->add_session(
                    $this->languageTextManager->get_text('configuration_saved', 'payrexx'),
                    'info'
                );
            } else {
                throw new Exception('');
            }
        } catch (Exception $e) {
            $GLOBALS['messageStack']->add_session(
                $this->languageTextManager->get_text('error_saving_configuration', 'payrexx'),
                'error'
            );
        }
        return MainFactory::create(
            'RedirectHttpControllerResponse',
            xtc_href_link('admin.php', 'do=PayrexxPaymentGatewayModuleCenterModule')
        );
    }
}
