<?php
/*--------------------------------------------------------------------------------------------------
    payrexx.lang.inc.php
    https://www.payrexx.com
    Copyright (c) 2023 payrexx
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

$t_language_text_section_content_array = [
    'text_title'          => 'Payrexx Payment Gateway',
    'text_description'    => 'Payrexx enables you to process payments securely and easily. You do not need your own website or programming skills to accept over 100+ payment methods.<br/> If you have any questions, Please email us at <a href="mailto:integrations@payrexx.com">integrations@payrexx.com</a> OR visit <a href="https://www.payrexx.com">https://www.payrexx.com</a><br><br>',
    'text_description2'    => '<a class="btn" href="' . DIR_WS_ADMIN . 'admin.php?do=ModuleCenter#PayrexxPaymentGateway" style="width: 100%; margin:0;">Basic Configuration</a> <br>',
    'text_info'           => 'The Payrexx payment gateway accept many different payment methods securely.',
    'page_title'          => 'Payrexx',
    'page_description'    => 'Payrexx Payment Method',
    'platform'            => 'Platform',
    'platform_tooltip'    => 'Choose the platform provider from the list',
    'instance_name'       => 'Instance Name',
    'instance_name_tooltip'  => 'Enter the instance name here. The instance name is part of your Payrexx-url (INSTANCENAME.payrexx.com)',
    'api_key'             => 'Api Key',
    'api_key_tooltip'        => 'Paste here your API key from the Integrations page of your Payrexx merchant backend.',
    'status'              => 'Status',
    'save_configuration'  => 'Save',
    'basic_configuration' => 'Basic Configuration',
    'prefix'              => 'Prefix',
    'prefix_tooltip'      => 'This is necessary when you use more than one shop with only one Payrexx account.',
    'look_and_feel_id'    => 'Look and Feel Id',
    'look_and_feel_id_tooltip' => 'Enter a profile ID if you wish to use a specific Look&Feel profile.',
    'configuration_saved' => 'Configuration saved successfully',
    'error_saving_configuration' => 'Please enter valid credentials! Try again',
    'payment_failed' => 'Payment failed! Please try again.',
    'payment_cancel' => 'Payment cancelled! Please choose Payrexx and try again.',
    'accept_payment_by' => 'Do you want to accept payment by ',
    'status_title' => 'Enable/Disable Payrexx Module',
    'status_desc' => 'Do you want to accept payment by Payrexx?',
    'sort_order_title' => 'Display Sort Order',
    'sort_order_desc' => 'Display sort order; the lowest value is displayed first.',
    'zone_title' => 'Payment Zone' ,
    'zone_desc' => 'When a zone is selected, this payment method will be enabled for that zone only.',
    'allowed_title' => 'Allowed Zones',
    'allowed_desc' => 'Please enter the zones <b>individually</b> that should be allowed to use this module (e.g. US, UK (leave blank to allow all zones))',
    'checkout_name_title' => 'Module Title',
    'checkout_name_desc' => 'This controls the Module Description on the checkout page',
    'checkout_description_title' => 'Module Desciption',
    'checkout_description_desc' => 'This controls the Module Description on the checkout page',
    'config_invalid' => 'Invalid configuration. Please check!',
];
