# WP Travel Custom Payment Prototype Docs
## “Instruction to follow before making payment addon for WP Travel.”

If you are using gitlab to clone the addon prototype.

Clone the repo [wp-travel-payment-prototype](https://gitlab.com/ws-plugins/wp-travel-payment-prototype) in your plugin folder.
Go to plugin folder cd wp-travel-payment-prototype. Checkout to the dev branch.

**Note:- All the core files of the payment prototype files are inside `/inc/modules/payment-prototype-core` folder. You have to change in `payment-prototype-core` directory only.**

Step by step guide:

You can just rename the files and directory. update the text consists of `payment-prototype` to `your-payment-addon` in the file or directory.
1. Rename File `wp-travel-payment-prototype/inc/modules/payment-prototype-core/wp-travel-payment-prototype-core.php` to `wp-travel-payment-prototype/inc/modules/payment-prototype-core/wp-travel-your-payment-addon-core.php`.
2. Rename Directory `wp-travel-payment-prototype/inc/modules/payment-prototype-core` to `wp-travel-payment-prototype/inc/modules/your-payment-addon`.
3. Rename the main plugin file name `wp-travel-payment-prototype/wp-travel-payment-prototype.php` to `wp-travel-payment-prototype/wp-travel-your-payment-addon.php`.
4. Rename the main plugin directory name `wp-travel-payment-prototype` to `wp-travel-your-payment-addon`.

open newly renamed custom payment prototype directory `wp-travel-your-payment-addon` in vscode or any other IDE and do search and replace the following words below:

**Note: Search in folder using a match case so that exact word will be found while search.**

| Search | Replace with |
| ----------- | ----------- |
| PAYMENT_PROTOTYPE | `YOUR_PAYMENT_ADDON` |
| Payment_Prototype | `Your_Payment_Addon`|
| Payment Prototype | `Your Payment Addon` |
| PaymentPrototype | `YourPaymentAddon` |
| payment_prototype | `your_payment_addon` |
| payment-prototype | `your-payment-addon` |




After replacing all of the above words, open the terminal inside the addon folder and type ‘npm install’ or ‘yarn install’. 

Wait!! It will take some time. After completion, go inside the `payment-prototype-core` folder and type ‘yarn install’.. This will install necessary dependencies on the folder and you are ready to develop.

Now you can type yarn start to start your development work to compile js on dev mode.
If you are working on the admin settings payment tab ( Screenshot here ), work on wp-travel-your-payment-addon/inc/module/your-payment-addon-core/app/admin/settings/index.js file. 

After saving the file this will automatically compile your js file. 

After developing you can minify the js using ‘yarn build’.

Now, you can start developing your custom payment addon for WP Travel. Main hooks and addon listing are given inside the core main php file. 

For saving the fields on the backend, a settings file has been added on ( wp-travel-your-payment-addon/inc/module/your-payment-addon-core/inc/admin/settings.php). Here you can add a field default value like one added in the file for enabling/disabling addon.



**In Summary** 

- Replace words in above **directory & files**.
- After replacing the words, You have to do yarn install in 2 directories:
  - `wp-travel-your-payment-addon` [ Renamed directory ]
  - `wp-travel-your-payment-addon/inc/module/your-payment-addon-core` [ Renamed directory ].
- You only have to change in `your-payment-addon-core` directory in order to change your payment addon code:
  - For development code : `yarn start`
  - For Production code : `yarn build`
- After developing code you can make your own plugin zip/bundle from `wp-travel-your-payment-addon` (Final setp : only for production )
  - `yarn build`
