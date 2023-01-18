# Payrexx Payment Gateway
Payrexx Payment Gateway Plugin for Gambio allows you to integrate your Gambio shop easily with Payrexx Payment system.

## System Requirements

- Gambio GX4 platform

## Installation

- Download the plugin
- Extract the zip file.
- Copy the extracted file and paste it into the Gambio shop root directory.
- Sign into your Gambio Back Office.
- Run the install command from the path /GXModules/Payrexx/PayrexxPaymentGateway
    ```
    # composer install
    ```
- Go to the Toolbox > Cache and clear all caches

#####  Module Center

- Click on "Modules > Module Center > Payrexx"
- Click Payrexx > install then edit.
- Enter correct data from Payrexx and click Save.


##### Payment Systems

- Click on Modules > Payment Systems > Miscellaneous -> added modules
- Click Payrexx > install the Payrexx payment system
- Click Edit, Enable payrexx module and Enable the payment methods as per your requirement. if nothing selected, it show all.
- Save the changes.

##### Payrexx Configuration
 - Get your shop webhook URL from Modules > Module Center > Payrexx > edit
 - To Configure the webhook URL in Payrexx, Log in your Payrexx account.
 - Click Webhooks > Add webhook

Enjoy using Payrexx!!
