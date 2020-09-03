=== WooCommerce Point of Sale ===

Author: actualityextensions
Tags: woocommerce, pos, point, of, sale, point of sale, tiles, grids, outlets, register, till, cashier, scan, barcode, stock, control, card, cash
Requires at least: 5.1
Tested up to: 5.2.1
Stable tag: 4.5.18
Requires WooCommerce at least: 3.6.0
Tested WooCommerce up to: 3.6.4

Extend your online WooCommerce store by adding a 'brick and mortar' Point of Sale (POS) interface.

== Description ==

WooCommerce Point of Sale is an extension which allows you to place orders through a Point of Sale interface swiftly using the WooCommerce products and orders database. This extension is most suitable for retailers who have both an online and offline store.

= Synchronised Stock =
With this plugin, you do not need to re-add the products to a POS database as it automatically fetches the products from your existing WooCommerce products inventory. This includes variable products with individual stock quantities.

= Add Customers =
You can easily add customers or use the guest checkout when placing orders for your walk-in customers. The fields loaded when processing a customer is identical to the fields loaded at the checkout page on the web shop.

= Payment Options =
Use your existing payment gateway or accept cash with our amount tendered feature giving you quick calculations on discounts, change given and cash tendered.

= Card Scanning =
You can scan cards and have the payment details parsed automatically from the card scanner. You can check which gateways are compatible [here](http://actualityextensions.com/supported-payment-gateways/). If you want to check whether your existing card scanner works with this feature, please use our card scanner validator [here](http://demo.actualityextensions.com/card-swipe/).

= Product Grids =
Display your products in a grid for easier access to product catalogue. This works beautifully with variable products as well. You can also display a quantity keypad / toggle for efficient store management. The tiles in the grid can show product image, title and price.

= Status Management =
Manage the status of orders processed through the Point of Sale by choosing a status for complete orders, saved orders and what orders should appear when loading orders. You can also load web orders placed that are to be tendered in store.

= Offline =
Take orders offline (cash, bank and cheque) and then re-sync them to the online database when connection is restored. Allowing your orders and business not to be affected by poor or lack of internet connection.

= Integrations =
Our plugin is compatible with the following plugins:

1. [WooCommerce Checkout Field Editor](https://www.woothemes.com/products/woocommerce-checkout-field-editor/)
2. [WooCommerce Address Validation](https://www.woothemes.com/products/postcodeaddress-validation/)
3. [WooCommerce Bookings](https://woocommerce.com/products/woocommerce-bookings/)
4. [WooCommerce Points & Rewards](https://woocommerce.com/products/woocommerce-points-and-rewards/)
5. [WooCommerce Product Add-Ons](https://woocommerce.com/products/product-add-ons/)
6. [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)
7. [WooCommerce Advanced Custom Fields](https://www.advancedcustomfields.com/)

= Documentation =
You can find the documentation to [here](http://actualityextensions.com/documentation/woocommerce-point-of-sale/). If there is anything missing from the documentation that you would like help on, please fill in our contact [form](http://actualityextensions.com/contact/).

= Bugs =
Should you find a bug, please do not hesitate to contact us through our support form found [here](http://actualityextensions.com/contact/).

== Installation ==

1. Upload the 'woocommerce-point-of-sale' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==
= 4.5.33 - 2019.08.20 =
* Fix - loading orders when tax settings is set to inclusive did not show correct total.
* Fix - integration with product add-ons on variable products was not working correctly.

= 4.5.32 - 2019.08.13 =
* Fix - coupons with product restrictions were not being applied correctly.

= 4.5.31 - 2019.08.12 =
* Fix - cashier user role was not able to process orders.

= 4.5.30 - 2019.08.09 =
* Fix - products loaded were not respecting edited prices.

= 4.5.29 - 2019.08.08 =
* Fix - products were not loading for some users.
* Fix - report not showing when clicking print on reports page.

= 4.5.28 - 2019.08.05 =
* Fix - rounding errors were causing total to be displayed incorrectly.

= 4.5.27 - 2019.07.31 =
* Fix - rounding total was not being respected when taking orders.
* Fix - auth permissions were not removed.

= 4.5.26 - 2019.07.29 =
* Fix - integration with Product Add-Ons not showing fields on receipt.
* Fix - coupons not working on checkout page.

= 4.5.25 - 2019.07.26 =
* Fix - saving orders with discounts would not load the discounts properly.
* Fix - integration with Product Add-Ons was not rendering fields correctly.
* Fix - integration with Account Funds was not updating accounts correctly.

= 4.5.24 - 2019.07.10 =
* Feature - ability to control how tiles appear in product grids when using automatic mode. You can choose for tiles to appear in category view or product view.
* Fix - Paymentsense integration fixed with latest version.

= 4.5.23 - 2019.07.03 =
* Fix - improve response message when incorrect card details entered in Stripe integration.
* Fix - copying address from billing to shipping was not working correctly.
* Tweak - integration with Account Funds working correctly on update and order processing.
* Tweak - integration with Paymentsense working correctly on update and order processing.

= 4.5.22 - 2019.06.24 =
* Fix - products in child categories were not showing in more than one parent category.
* Fix - scanning products with a particular format was not working for some users.
* Fix - ACF fields were not saving.
* Fix - stock number on register was not updating after order is placed.
* Fix - invoice emails using invoice plugin from WooCommerce was not sending invoice.

= 4.5.21 - 2019.06.18 =
* Fix - credit card swiper parser for Authorize.Net gateway.
* Fix - discount not being preserved when loading saved order with custom discount.
* Fix - default country not being preserved on customer panel.

= 4.5.20 - 2019.06.14 =
* Fix - required customer fields were not respected.
* Fix - CloudPRNT integration was not printing when orders were being made.

= 4.5.19 - 2019.06.14 =
* Fix - customer loading page when a customer is loaded.
* Fix - error notice would not show on Stripe gateway.
* Fix - receipt width was not being respected when printing from register.
* Tweak - loading of orders with customer and tax data.
* Feature - taller tiles have been added for users who want to display larger photos for their tiles.

= 4.5.18 - 2019.06.07 =
* Fix - payment was not charging correctly with Authorize.net CIM gateway.
* Fix - item notes were not saving to orders.

= 4.5.17 - 2019.06.07 =
* Fix - loading an order would not load customer information.
* Fix - sessions reports were not showing for certain logged in users.
* Fix - subtotal reflects inline item discounting.
* Feature - default setting for customer adding and saving added.

= 4.5.16 - 2019.06.05 =
* Fix - meta data for custom products was not saving.
* Fix - out of stock products were showing in search when function is disabled.
* Fix - SKU not showing when generating barcodes.
* Fix - subtotal value was not changing when editing product prices.
* Tweak - price included in search results.

= 4.5.15 - 2019.06.04 =
* Fix - discount and coupons were not being reflected after API update.
* Fix - tax rate was not being applied based on outlet based rules.
* Fix - integration with Stripe gateway corrected.
* Fix - error when adding products with stock value set to out of stock.
* Fix - custom product titles not being shown on order details.
* Fix - gift receipt was not being printed.
* Tweak - product search styling to fit more results.
* Tweak - go back tile grid will not show until all tiles in respected grid is loaded. 

= 4.5.14 - 2019.05.24 =
* Fix - variations were not showing for some users.
* Fix - navigating through grids was causing user interface errors when loading is not complete.
* Tweak - customer search refined.

= 4.5.13 - 2019.05.23 =
* Fix - refund was not searching all order statuses.
* Fix - reprinting of receipt was showing logged in user rather than cashier name.
* Fix - cashier cannot load previous orderes.
* Fix - out of stock products were appearing despite backend setting.
* Fix - barcode scanning was not respecting scanning field to use.
* Fix - bug with default dining option on fresh installs.
* Tweak - search results display.

= 4.5.12 - 2019.05.21 =
* Fix - loading orders were not loading order ID and respecting chip & PIN method.
* Fix - product search was not working for some users.
* Tweak - chip & PIN payment method page.
* Tweak - shipping page.

= 4.5.11 - 2019.05.17 =
* Fix - barcode scanning was not working as expected after search changes.

= 4.5.10 - 2019.05.17 =
* Fix - product search on variable products was not showing the variations when selecting the result.

= 4.5.9 - 2019.05.13 =
* Tweak - refactor searching of products.
* Tweak - refactor loading of grids.
* Tweak - refactor category request limit.
* Tweak - refactor searching of customers.
* Tweak - relocation of offline settings found under Settings > Advanced.
* Tweak - reference number for chip & PIN payment method.
* Fix - customer product meta not saved.
* Feature - item count on register screen to display number of products in the basket.

= 4.5.8 - 2019.05.01 =
* Fix - custom product name was not showing on receipt.
* Tweak - reset settings button.

= 4.5.7 - 2019.04.29 =
* Fix - product visibility rules were not being respected on web store.
* Fix - email of receipts were not being sent when setting is enabled.
* Fix - search indexing further improved.

= 4.5.6 - 2019.04.28 =
* Fix - product search was not working after last update.

= 4.5.5 - 2019.04.27 =
* Fix - barcode scanning not working on all products and custom grids.

= 4.5.4 - 2019.04.26 =
* Fix - category tiles were not displaying correctly for some users.
* Fix - pay button was not working for some users.
* Fix - barcode scanning was not working, partial fix on category type grids.
* Fix - SKU is now available to search through product search.
* Tweak - metabox on orders page shows account balance if order has balance or is overpaid.

= 4.5.3 - 2019.04.25 =
* Fix - discounted products would not respect tax on order details.
* Fix - cash management page would not take into consideration tendered value, only total value.
* Fix - refund button would show regardless of configured settings.
* Fix - products were not loading for some users.
* Fix - when adding custom product, error would appear when changing stock value.
* Fix - cashier roles were not able to process orders after API changes.

= 4.5.2 - 2019.04.24 =
* Fix - shipping was not working when adding to orders for some users.
* Fix - barcode generation was not producing barcode properly.
* Fix - for some users not all the categories would load.

= 4.5.1 - 2019.04.23 =
* Fix - custom products were not being able to add.
* Fix - customers were not being shown when searching using non-indexed method.

= 4.5.0 - 2019.04.23 =
* Tweak - square and rectangular tiles loading.
* Feature - utilisation of latest API to make performance better.
* Feature - ability to set the limit of products to load on all products grid.
* Fix - end of sales reports were not sending.
* Fix - autofill issue on Chrome browsers.
* Fix - WooCommerce 3.6.X support.

= 4.4.33 - 2019.04.09 =
* Fix - gift receipt showing stock errors.
* Fix - numpad keyup change function removed.
* Fix - cash management totalling partial paid orders.

= 4.4.32 - 2019.03.28 =
* Fix - state field was not loading when loading existing order / customer.
* Fix - total was not sending correctly for third party payment methods such as wallets, etc.
* Tweak - receipt template settings changed to make management of receipt template easier.
* Tweak - session reports that have been force logged will include asterisks to indicate this forced closure.

= 4.4.31 - 2019.03.26 =
* Fix - decimal places on decimal items were not being respected in stock quantity and selector.
* Fix - loading of web order with variation was not showing variable correctly.
* Feature - setting to hide or show the total row in the receipt template has been added.
* Feature - ability to add wifi password to outlet added.
* Feature - ability to disable payment per user profile added.

= 4.4.30 - 2019.03.19 =
* Fix - CloudPRNT after disabled would not show print dialogue on register.
* Fix - CloudPRNT would not show currency symbol.
* Fix - plugin conflict with ACF.
* Fix - error shown on edit register page.

= 4.4.29 - 2019.03.13 =
* Fix - when no orders made, end of report would show error.
* Fix - sales by session page showed error.
* Fix - CloudPRNT was showing error when disabled.
* Fix - barcode page was showing error.
* Fix - ACF integration was showing error.
* Fix - receipt was not showing change for cash payments.
* Tweak - Paymentsense integration.
* Feature - auto log out function.
* Feature - tax summary on end of report.

= 4.4.28 - 2019.02.19 =
* Fix - terminal settings were appearing on the edit registers page.
* Fix - ACF conflict on registers page with new update of ACF.

= 4.4.27 - 2019.02.14 =
* Fix - tax not being shown in emails for some useres.
* Fix - latin characters validation on register name.
* Fix - signature required bugs when clicking void.
* Fix - bank transfer value not being shown in metabox.
* Fix - instant quantity tile decimal value.
* Fix - chip & PIN method title in order details page.
* Fix - shipping was not including in receipt tax summary.

= 4.4.26 - 2019.02.07 =
* Fix - bookable products for walk-in-customers should be guest.
* Fix - header error on settings > advanced page.

= 4.4.25 - 2019.02.06 =
* Fix - paymentsense API check implemented.
* Fix - bookings was not working with register showing times.
* Tweak - mobile display of register to accommodate payment gateways for mobiles.

= 4.4.24 - 2019.02.05 =
* Fix - receipt tax settings were not respecting format on global settings.

= 4.4.23 - 2019.01.28 =
* Fix - back to notices needed to be correctly styled and positioned.
* Fix - rewrite issue was causing performance issues for users.
* Fix - cashier page kicked database errors.
* Fix - tax disaply settings in WC settings were not being respected on receipt template.
* Fix - refunding of variable products with 1 stock were not being restored and processed correctly.
* Fix - cashier permissions to reports are now removed.
* Fix - POS admin bar only shown to those who can access register.
* Tweak - PS integrations.
* Tweak - CloudPRNT printers are now tabulated.
* Tweak - CloudPRNT settings hide and show printer related settings.
* Tweak - CloudPRNT printers can now clear the print queue from the table.
* Feature - ability to take over an already opened register.

= 4.4.22 - 2019.01.05 =
* Fix - gateway errors showing on My Account page for some users.
* Fix - ACF fields were not recording for some users.
* Tweak - requirement checker introduced.

= 4.4.21 - 2019.01.01 =
* Fix - ability to add top level domain names for outlet email.
* Tweak - reset POS cashier button resets number of orders on cashiers page for cashier count.
* Tweak - state fields showing correctly on customer modal.
* Beta - performance updates.

= 4.4.20 - 2018.12.21 =
* Fix - tax not showing on receipt page.
* Fix - when ACF active, icons were not loading correctly from the dashicons library.

= 4.4.19 - 2018.12.13 =
* Fix - variation tiles go back issue.
* Fix - product discount bug when applying % on product keypad.
* Feature - users can open registers from My Accounts > Point of Sale. Outlet assignment and capability required.
* Feature - rectangular tiles can be used for a more responsive UX.
* Feature - registers page can be accessed from top admin bar.
* Tweak - delay added when searching customer database.
* Tweak - ability to reset cashier orders count.
* Tweak - shipping price label localisation.

= 4.4.18 - 2018.12.08 =
* Fix - WP 5.0 tweaks.
* Fix - updater trigger bugs.
* Fix - double quantity being added with beta enabled.
* Fix - variation images were not displaying correctly.
* Feature - users can now be assigned to more than one outlet.

= 4.4.17 - 2018.12.03 =
* Fix - updater trigger bugs.
* Fix - register was not loading for some users.
* Fix - orders page scan was not working properly.

= 4.4.16 - 2018.11.29 =
* Fix - integration with WooCommerce Product Add-ons was not working.
* Fix - integration with WooCommerce Bookings was not working.

= 4.4.15 - 2018.11.29 =
* Fix - ACF integration fixed and working again.
* Fix - some users were not able to save receipt templates.

= 4.4.14 - 2018.11.28 =
* Tweak - EMV POS status isolated from web orders.

= 4.4.13 - 2018.11.27 =
* Fix - register not loading for some users.
* Fix - save orders no longer sending payment emails.

= 4.4.12 - 2018.11.25 =
* Fix - receipt template not preserving.
* Tweak - receipt styling modified to accommodate printer widths.
* Tweak - Arabic translations.

= 4.4.11 - 2018.11.13 =
* Fix - customer cannot be found when beta API is running.
* Fix - refund button was not loading correctly.

= 4.4.10 - 2018.11.02 =
* Fix - reduced stock line was being shown on stock products on receipt.
* Fix - % was not being inputted in normal admin pages.

= 4.4.9 - 2018.11.02 =
* Fix - register for updates link corrected to show license page.
* Fix - customer button was not working on register
* Fix - cash method can now be enabled for POS only.

= 4.4.8 - 2018.10.30 =
* Fix - stripe parameter error.
* Fix - receipt included cashier email after WooCommerce update.
* Fix - user profile language was not being respected within POS pages.
* Fix - customer modal window was being shown after customer was loaded.
* Fix - navigation pages were broken on some web pages when POS visibility was enabled.
* Fix - end of day report email included incorrect details.
* Fix - shipping zero was added for some users.
* Fix - apache errors due to Bookings conflict.
* Fix - order count status issue validated when setting for report.
* Tweak - accommodations plugin more integrated.
* Tweak - translatable strings have been updated.
* Tweak - signature capture window restyled.
* Feature - shop managers can print receipts and view them from the order details page as well.
* Feature - refund permissions can be set per user.
* Feature - when printing barcodes for variable products, you can choose which attribute to print.


= 4.4.7 - 2018.10.10 =
* Feature - refunds can now be taken from the POS page. Scan order, select item and then apply refund.
* Integration - better integration with WooCommerce Accommodations.
* Fix - bug with third party plugin.
* Fix - customer notes were not being saved to order for some users.
* Fix - receipt time validation for users who enter incorrect time format.

= 4.4.6 - 2018.10.05 =
* Beta - auto category now working on beta.
* Beta - refund and returns new interface.
* Fix - customer modal was not opening for some users.
* Fix - fatal errors after update for some users.
* Fix - gift receipt was displaying debug code.

= 4.4.5.4 - 2018.09.28 =
* Fix - 3 x 9 labels were not printing correctly.
* Fix - keyboard shortcuts was producing JS error.
* Fix - receipt was not printing for some users.
* Feature - if account funds enabled, it now loads balance when customer is loaded.
* Feature - print test page on printing settings page to show status.

= 4.4.5.3 - 2018.09.26 =
* Fix - database updater tweaks.

= 4.4.5.1 - 2018.09.25 = 
* Fix - report not showing when closing reigster.

= 4.4.5 - 2018.09.25 =
* Feature - account funds integration to show and reset after each payment.
* Tweak - add print test page to the printers page.
* Tweak - receipt template includes dining option to enable and disable.
* Tweak - customer modal window shows add if adding a customer and save if saving existing customer.
* Fix - load order status respects the CSS of the orders page.
* Fix - currency symbols shown on tendered amount (beta).
* Fix - scanning card will close modal window after scanned.
* Fix - API keys are now created from user profile page.

= 4.4.4 - 2018.09.05 =
* Feature - further details are now shown on end of sale report. Title of report can be configured from the settings page.
* Feature - reports now have a unique ID for accounting and tax uses.
* Feature - view signature button on orders page if order includes signature.
* Feature - barcode for 30 labels per sheet have been tweaked to accommodate space for more information.
* Feature - remove PassPRNT code as CloudPRNT is now being used instead.
* Tweak - CloudPRNT web orders option added.
* Tweak - status of receipt printing shown when printing from order details page.
* Tweak - scanning customer card from customer modal and scan button when customer cards feature enabled.
* Tweak - loading magnetic card with customer number possible from the load card button on user profile page.
* Fix - beta was not displaying tiles in correct order.
* Fix - beta was not displaying all products grid variables correctly.
* Fix - cashier table filter was not showing correct results.
* Fix - beta product search not working.
* Fix - account funds were being shown after order complete in payment modal.
* Fix - custom product X10 issue was showing on receipts.
* Fix - discounts were using old API.
* Fix - points were not being redeemed correctly with points and rewards.
* Fix - enable guest customer checkbox when not checked, always requiring customer message would appear.
* Fix - CloudPRNT printing when disabled.

= 4.4.3 - 2018.09.04 =
* Fix - new Stripe update would not load register.
* Fix - products appearing after searching using site search widget.
* Fix - cashier page showing incorrect users.
* Fix - stocks were being reduced by double for variable products.
* Fix - tax was not correctly added when loaded order payment was taken.
* Fix - order date was not displayed in friendly way on orders page.
* Tweak - currency symbol added to cash tendered suggested amounts.
* Tweak - bulk edit product status now possible.
* Tweak - member discount reason added under discount options.
* Tweak - shop managers can quickly load store address when adding outlet.
* Tweak - denominations tab moved under register tab.
* Tweak - web orders were not being taken for payment when loading to register.
* Tweak - tendered amount notice disappears when clicking clear on cash keypad.
* Tweak - customer card number cannot be changed unless change card number button is pressed.
* Feature - Star CloudPRNT integration thanks to the team at Star (America and Europe).
* Feature - new barcode labels template 3 x 9 on A4.
* Feature - integration with PayPal Here by Angell EYE.
* Feature - Point of Sale metabox introduced in the orders page for quick glance at POS related information.

= 4.4.2 - 2018.07.31 =
* Fix - deprecated fix with stocks being updated after order.
* Fix - small bugs found related to the orders API.
* Fix - refund orders are shown accordingly in report.

= 4.4.1 - 2018.07.25 =
* Fix - adding multiple items to the cart was causing register to go out of screen for some users.
* Fix - customers recently added were not added to sync to show when searched.

= 4.4 - 2018.07.13 =
* Fix - Firefox users were having CSS issues with register as items were added.
* Fix - stocks were being reduced twice when POS visibility was enabled.
* Fix - cash management was showing incorrect time when adding and removing cash.
* Fix - header response code fixes.
* Fix - settings were being reset when saving payment methods.
* Fix - sale prices were not showing original price on receipt.
* Feature - signature panel options to allow capturing of signature for orders.

= 4.3.8 - 2018.06.21 =
* Fix - 500 error when printing receipts for some users.
* Tweak - Stripe checkout fields tidied CSS.

= 4.3.7 - 2018.06.18 =
* Tweak - loading screen introduced.
* Fix - customer avatar was not loading correctly.
* Fix - emails of order complete was not sending.
* Fix - notices appearing when creating outlet.
* Fix - shipping methods were not being shown.

= 4.3.6 - 2018.06.07 =
* Fix - chip & PIN error notices when using the gateway.
* Fix - small notice fix.
* Fix - ACF conflict.

= 4.3.5.4 - 2018.06.06 =
* Fix - conflict between Advanced Custom Fields when loading register.
* Fix - small notice was showing is now fixed.
* Feature - ability to add multiple chip & PIN methods.

= 4.3.5.3 - 2018.06.05 =
* Fix - table errors for some users related to the new dining options.

= 4.3.5.2 - 2018.06.04 =
* Fix - variable model keypad would always keep 1 value after adding first product.
* Fix - custom products were not being checked out for some users.
* Tweak - updated ES translation.

= 4.3.5.1 - 2018.06.01 =
* Fix - compatibility with WooCommerce 3.4.1.

= 4.3.5 - 2018.05.30 =
* Fix - tax rates were not being applied correctly for some users after WooCommerce 3.4 update.
* Fix - register not loading for some users after WooCommerce 3.4 update.
* Fix - stock was reducing double for some users when using cash and chip & PIN.
* Tweak - option to enable or disable user card scanning.

= 4.3.4 - 2018.05.26 =
* Fix - receipt reprint was not displaying printing dialogue.
* Fix - receipt reprint was not opening in new tab.
* Fix - DE translation was causing problems.
* Fix - save customer option was not working for some users.
* Fix - web shop filters were not working.
* Fix - keypad tweaks for tablet users.
* Fix - POS custom product was being created after activation.
* Fix - end of day report print command did not open print command.
* Feature - receipt template now allows you to show discount strike per item.
* Feature - customer card number scanning and setting up.
* Tweak - register prints receipt before logging out for change users option.
* Tweak - add customer now shows save customer for existing customers.
* Tweak - shipping methods are not being displayed.
* Tweak - save prompt for when orders are being saved.
* Support - WooCommerce 3.4 support.

= 4.2.8.6 - 2018.04.16 =
* Fix - subscriptions were not renewing with latest updates.

= 4.2.8.5 - 2018.04.13 =
* Fix - note used to prompt pay after addition.

= 4.2.8.4 - 2018.04.10 =
* Fix - fees staying after order finished.
* Fix - customer first and last name not saving when indexing is disabled.

= 4.2.8.3 - 2018.04.05 =
* Fix - WC 3.0 API tweaks and errors were showing in PHP error logs.
* Tweak - quick edit POS visiblity from products page.
* Tweak - barcode generation was not rendering barcode properly.

= 4.2.8.2 - 2018.04.03 =
* Fix - report time difference was not correct.
* Fix - PayPal Pro users were being redirected to orders page.
* Fix - dining option defaults to default dining option.

= 4.2.8.1 - 2018.04.03 =
* Fix - receipt print was freezing for some users.
* Feature - ability to define what type of dining the order is.

= 4.2.8 - 2018.03.28 =
* Fix - scroll to top issue when scrolling through the register.
* Fix - custom product grid is not loading for some users.
* Fix - SQL query optimisation.
* Feature - disable indexing of customer when loading the register.
* Feature - ability to add a tab for customers who stay within store. This is a beta feature so please update and use with caution.
* Tweak - customer input fields tidied.

= 4.2.7.5 - 2018.03.21 =
* Tweaks - made changes to make screen more user friendly for iPad users including disabling of keyboard on instant quantity.
* Fix - country selector was not displaying correctly when adding shipping and single country set in settings.

= 4.2.7.4 - 2018.03.15 =
* Fix - cash rounding error after shipping task.

= 4.2.7.3 - 2018.03.15 =
* Feature - shipping methods can now be dynamically loaded with in each register.
* Feature - default shipping method can be set per outlet.
* Fix - receipt was not displaying tax number and logo.
* Tweak - performance tweaks with database and query handling.

= 4.2.7.2 - 2018.03.09 =
* Fix - PayPal Pro payments were not sending the right expiry date.
* Fix - receipt was not printing when copies set to 2 after latest receipt template update.

= 4.2.7.1 - 2018.03.06 =
* Fix - item note was staying when removing item.
* Fix - product cost keypad was responding incorrectly for some users.

= 4.2.7 - 2018.03.05 =
* Fix - variations not showing for some users when using tile variable mode.
* Feature - customer telephone appears on receipt.
* Feature - item note can be added to the product now in the register.
* Feature - hide and show cost column in receipt.
* Feature - added Snapchat and Instagram as social profiles to outlet.
* Tweak - grid and register styling tweaked.
* Tweak - receipt settings page refined.
* Tweak - setup wizard refined.

= 4.2.6.6 - 2018.03.02 =
* Fix - Stripe payment gateway not working.
* Tweak - receipt table showing more key information.
* Tweak - add Snapchat and Instgram to the outlet settings.
* Tweak - currency included in subject of email reports.

= 4.2.6.5 - 2018.02.24 =
* Feature - ability to enable and disable required fields on customer creation modal window.
* Fix - report via email was displaying customers incorrectly for some users.
* Fix - installation was enabled notes on default.

= 4.2.6.4 - 2018.02.16 =
* Fix - Stripe payments were not working when users had terms page set.
* Tweak - terms confirmation toggle added on payment screen.

= 4.2.6.3 - 2018.02.14 =
* Feature - ability to add preset discounts with decimal places.
* Fix - amount tendered notice would show if amount was higher for some useres.
* Fix - beta development of PassPRNT support.

= 4.2.6.2 - 2018.02.07 =
* Fix - latest JS assets were not being pulled to cache based sites.

= 4.2.6.1 - 2018.02.07 =
* Fix - latest Stripe stopped working on POS users.
* Fix - going back in product grid was not loading previous products for some users.

= 4.2.6 - 2018.01.30 =
* Fix - issue with get POS API.
* Fix - overwriting of orders when loading order and voiding it.
* Tweak - hooks for after billing and shipping added.
* Tweak - design interface for tiles and cash payment.

= 4.2.5.18 - 2018.01.18 =
* Fix - barcode scanning stopped working for some users.

= 4.2.5.17 - 2018.01.10 =
* Fix - large amount error when cash payments are used.
* Fix - tax summary on receipt was not showing correctly.

= 4.2.5.16 - 2018.01.05 =
* Feature - add print button when loading orders.
* Feature - saving orders now show printing modal.
* Tweak - to products that have no SKUs added.
* Tweak - include currency in email report subject.
* Tweak - re-uploaded DE lang files corrected.

= 4.2.5.15 - 2017.12.20 =
* Fix - conflict with theme for users.
* Fix - barcode scanning was not working correctly for some users.
* Fix - cash management icon was not being assigned correctly.

= 4.2.5.14 - 2017.12.17 =
* Fix - translations were causing certain pages to break.
* Fix - shipping address will not print on receipt if there is no shipping added.
* Fix - report sent via email was including incorrect information for certain users.

= 4.2.5.13 - 2017.12.08 =
* Fix - German translation broke Orders page.

= 4.2.5.12 - 2017.12.06 =
* Fix - sound was not working on iOS devices.
* Fix - server was displaying default customer for some users.
* Fix - modal notices appearing causing unindexed register.
* Fix - page title added for cash management.
* Fix - header errors appearing on receipt print.
* Add - added Portguease, Swedish and Chinese (Simplfied) language.

= 4.2.5.11 - 2017.12.05 =
* Tweak - updated POT template.
* Tweak - updated existing language files.
* Add - added Greek, Italian and German language files.

= 4.2.5.10 - 2017.11.30 =
* Fix - keypad was a bit off on Windows machines.
* Fix - remove was showing on edit button.
* Fix - session reports was showing SQL console data.
* Tweak - default country appears neater now.

= 4.2.5.9 - 2017.11.28 =
* Fix - register position was not being respected.
* Fix - force updates was emptying sessions.
* Tweak - CSS has been optimised.

= 4.2.5.8 - 2017.11.24 =
* Fix - receipt was not displaying tax correctly for some users.
* Fix - forced database updates were returning errors for some users.
* Tweak - points and rewards now shows neatly in the discount modal.
* Tweak - quantity and price input now more responsive.

= 4.2.5.7 - 2017.11.23 =
* Fix - keypad for quantity not resetting when used after initial use.
* Fix - WordPress time not registering on receipt.
* Fix - question mark symbol shown on receipt.
* Tweak - prompt showing now when you attempt to close the register.
* Tweak - email report subject formatted to more readable format.

= 4.2.5.6 - 2017.11.23 =
* Tweak - added new keypad support for product add ons.

= 4.2.5.5 - 2017.11.23 =
* Fix - submenu pages appearing on menu editor plugins.
* Tweak - quantity modal and entry.

= 4.2.5.4 - 2017.11.22 =
* Fix - bug with POS custom product appearing on receipt.
* Tweak - brought back image in the register for users on larger screens.
* Tweak - admin bar default hidden upon first installation.
* Tweak - Russian language files added.

= 4.2.5.3 - 2017.11.21 =
* Fix - main colour scheme not applying for new users.
* Tweak - keypad inputs on register more streamlined.
* Tweak - changed 80:20 to 60:40.

= 4.2.5.2 - 2017.11.20 =
* Fix - save receipt bugs

= 4.2.5.1 - 2017.11.19 =
* Fix - corrected error with installation wizard tweaks.

= 4.2.5 - 2017.11.18 =
* Fix - redirect issue happening after update.
* Tweak product add-ons integration to suit new UX.

= 4.2.4 - 2017.11.17 =
* Fix - redirect issue happening on certain domains.

= 4.2.3 - 2017.11.17 =
* Fix - when you click on back and product is main, the variables show.
* Feature - support for smart coupons.

= 4.2.2 - 2017.11.16 =
* Fix - bug with payment methods not recording.

= 4.2.1 - 2017.11.15 =
* Fix bug with offline ordering now working.
* Tweak to modal windows to suit new design.

= 4.2.0 - 2017.11.14 =
* Revised design UX and roadmap changes.
* Tweak to accommodate towards point number 1.
* Fix bug with outlet creating.
* Tweak to the setup wizard.
* Introduced ability for shop owners to brand their POS.
* Introduced AE logo as part of company branding.
* Introduced Material inspired colour schemes plus WooCommerce.
* Tweaked design to suit modern design influences.

= 4.1.9.11 - 2017.11.12 =
* Feature - users can now define exact width of receipt under receipt template.
* Feature - users can decide between the old 50:50 layout or the new default 80:20.
* Tweak - default settings such as register position and connection status.
* Fix - object message displaying when defining default customer.
* Fix - disable of taxes bug appearing when calculating.
* Fix - return enter discount fix.

= 4.1.9.10 - 2017.11.07 =
* Fix - full size attachment image in the receipt.
* Fix - temporarily removed the connection status option.
* Fix - fixed coupons bug.

= 4.1.9.9 - 2017.11.02 =
* Fix - disable sales prices not working with variations.

= 4.1.9.8 - 2017.11.02 =
* Fix - fix date bug in booking products metadata.
* Tweak - search by title when custom search field enabled.

= 4.1.9.7 - 2017.10.31 =
* Fix - bug with WordPress menu not showing on category page.
* Fix - tendered amount bug fix.

= 4.1.9.6 - 2017.10.26 =
* Fix - coupon properties were not being respected properly.

= 4.1.9.5 - 2017.10.20 =
* Fix - conflict with Uber menu plugin causing speed to slow down performance of site.
* Fix - could not search custom product meta fields when using register search function.
* Feature - ability to add fees to the order using backend setting and the action buttons.
* Tweak - design of register to give user more space for products.
* Tweak - certain buttons (actions) have moved to a new area called actions panel.

= 4.1.9.4 - 2017.10.10 =
* Fix - fatal error was showing when plugin is activated.

= 4.1.9.3 - 2017.10.06 =
* Fix - installation of database now includes session reports.

= 4.1.9.2 - 2017.10.06 =
* Fix - bug with quantity keypad not respecting digits entered.
* Fix - error codes were appearing when printing a gift receipt.
* Fix - default attribute for variable products were not being displayed correctly.

= 4.1.9.1 - 2017.10.05 =
* Fix new bug with reports with cash management showing error when entering fields.
* Fix new bug with change not being calculated.
* Fix new bug with notices appearing when viewing existing cash in cash management page.

= 4.1.9 - 2017.10.02 =
* Fix bug with updater setup wizard notice appearing randomly.
* Fix bug with shipping tax not being included.
* Fix bug with inc. tax showing.
* Fix bug with notices appearing when WP_CONFIG enabled.
* Fix bug with outlet and slashes.
* Tweak to register layout and style.
* Tweak to keypad on amount tendered and discount modal.
* Tweak to coloured tiles.
* Tweak to receipt template to respect text size in header and footer.
* Feature added to display tax summary on receipts.

= 4.1.8.15 - 2017.09.16 =
* Added 40mm x 20mm barcode label.
* Fixed bug with customer information being fetched.
* Tweak to barcodes on A4 and Letter styles.
* Tweak to rounded indicator.

= 4.1.8.14 - 2017.09.14 =
* Fix bug with Dynamic Pricing & Discount plugin conflict.

= 4.1.8.13 - 2017.09.13 =
* Tweak to barcode options and added continuous feed for 4cm x 3cm.
* Gift receipt bug fixed.
* New shipping methods to appear on register settings.

= 4.1.8.12 - 2017.09.01 =
* Fix bug with Polylang conflict.
* Fix bug with variations throwing JS errors in console.
* Fix bug with search field throwing JS errors in console.

= 4.1.8.11 - 2017.08.21 =
* Fix bug with category taxonomy.
* Fix bug with setup not adhering to new select2.
* Tweak to filters on orders page.

= 4.1.8.10 - 2017.08.16 =
* Fix bug with receipt not printing page breaks.

= 4.1.8.9 - 2017.08.15 =
* Fix bug with syncing of inventory and product details.
* Fix bug with coupons not working.
* Tweak to product scroll.

= 4.1.8.8 - 2017.08.10 =
* Fix bug with notes not appearing at end of sale as it should do if setting turned on.
* Fix bug with Authorize.NET redirect payment gateway.
* Fix bug with category tiles not displaying tiles correctly.
* Further tweaks made to debug the grey screen error.

= 4.1.8.7 - 2017.08.06 =
* Fix bug with rounding decimal not showing when comma is decimal separator.
* Fix bug with cancelled orders not showing in sales report.
* Fix bug with Firefox scrolling.
* Fix bug with orders that are rounded to remain the same after admin edit of order.
* Implemented debug mode for users who are facing grey screen error to show loading progress of POS.
* Tweak to quantity keyboard.

= 4.1.8.6 - 2017.07.31 =
* Fix bug with sales report and order values.
* Fix bug with Bookings integration datepicker.
* Fix bug with Bookings integration resources.
* Fix bug with variations not showing when tile view is enabled for some users.
* Fix bug with rounding value setting showing previous order value.
* Tweak to scrolling on tablets.
* Tweak to receipt template page.

= 4.1.8.5 - 2017.07.18 =
* Fix bug with category cycle grid not showing child categories correctly.
* Fix bug with orders page not showing correctly when in mobile view.

= 4.1.8.4 - 2017.07.18 =
* Fix bug with search results on product grid page.

= 4.1.8.3 - 2017.07.13 =
* Fix bug with cash management not loading and showing correct cash values for some users.
* Fix bug with discount not being able to be removed for some users.
* Tweak to calendar styling for WooCommerce Bookings.

= 4.1.8.2 - 2017.07.08 =
* Fix bug with resources not showing on bookable products.
* Fix bug with category grids of child categories not showing.

= 4.1.8.1 - 2017.07.03 =
* Fix bug with payment error showing NaN.

= 4.1.8 - 2017.06.23 =
* Feature added to make the grid infinite scroll rather than pagination.
* Feature added to show user there is a loaded order.
* Fix bug with f_count in JS console.
* Fix bug with POS Online Only products appearing under category view.
* Tweak to settings page, settings will only show if the checkbox of the enabler is checked.

= 4.1.7 - 2017.06.07 =
* Fix bug with products not showing on archive for some users.
* Fix bug with POS only products not showing stock.

= 4.1.6 - 2017.06.06 =
* Fix bug with cash management float not working for some users.
* Fix bug with receipt printing gift when it should not.
* Feature to set rounding value for cash transactions.

= 4.1.5 - 2017.06.01 =
* Fix bug with product variables not appearing in stock controller page.
* Fix bug related to offline feature.
* Fix bug related to coupons per product not working after major WC 3.0 update.
* Fix bug related to tiles not displaying correctly for some users.
* Fix bug related to product visibility not being applied for some users.
* Feature added to send end of day report via email.
* Tweak to number of copies printed when gift receipt is printed (one for gift and one for purchase).

= 4.1.4 - 2017.05.19 =
* Fix bug with product visibility and search on front end.

= 4.1.3 - 2017.05.17 =
* Fix bug with cash management float warning notice.
* Fix bug with receipt not printing site name.
* Tweak support for gateway of custom type.

= 4.1.2 - 2017.05.16 =
* Fix bug with product addons products not loading.
* Fix bug with cache manifest.

= 4.1.1 - 2017.05.12 =
* Fix bug with loading orders creating double value.
* Fix bug with keypad not clearing value when exiting payment modal window.
* Fix bug with tax value not saving when editing product item.
* Fix bug with receipt displaying taxes when removed through custom action.
* Fix bug with default customer not working on register setting page.
* Tweak to offline syncing.

= 4.1.0 - 2017.05.03 =
* Fix bug with saved orders being loaded with double count.
* Fix bug with note not being saved after order.
* Fix bug with shipping states not loading.
* Fix bug with points and rewards plugin.
* Feature to allow orders to be taken offline (cash, cheque and bank).

= 4.0.4 - 2017.04.20 =
* Fix bug with iPad JS error appearing in console.
* Fix bug with get product terms not being forced upon update.
* Tweak to button layouts and keyboard.

= 4.0.3 - 2017.04.19 =
* Tweak to favicon loading on register page.
* Fix bug with keypad width.
* Fix bug with barcode select2 not loading.
* Fix bug with WC3 notices.
* Fix bug with cash management debug message.

= 4.0.2 - 2017.04.12 =
* Fix bug with double quantity.
* Fix bug with barcode select2 fields.
* Fix bug with POS custom product appearing on receipt.
* Fix bug with product add-ons affecting product visibility.
* Fix bug with cash management report.

= 4.0.1 - 2017.04.11 =
* Fix bug with DB notices.

= 4.0.0 - 2017.04.11 =
* WooCommerce 3.0 compatibility
* Feature to set products to POS only or online only.
* Tweak to design of register modals, messages, grids, etc.

= 3.2.6.11 - 2017.03.27 =
* Feature added to show whats new after update.
* Tweak to bill screen.
* Tweak to cash management feature on closing report.
* Fix bug with notices.

= 3.2.6.9 - 2017.03.15 =
* Feature added to define quantity of receipts to print from receipt template.

= 3.2.6.8 - 2017.03.15 =
* Feature added to allow keyboard shortcuts for common functions.
* Feature added to display bill screen on separate display.
* Tweak to settings labels.
* Tweak to setup wizard.

= 3.2.6.7 - 2017.03.10 =
* Fix bug with guest checkout not being enforced after setup wizard.

= 3.2.6.6 - 2017.03.07 =
* Fix bug with receipt template being presented on setup wizard.

= 3.2.6.5 - 2017.03.07 =
* Fix bug with receipt templates not being saved and preserved upon setup wizard.

= 3.2.6.4 - 2017.03.03 =
* Fix bug with Subscriptions integration not recording payment method.
* Fix bug with registers being added without name.
* Fix bug with discount presets showing undefined when none set.
* Fix bug with revert button in inline discount not resetting original price.
* Fix bug with saved orders taking stock twice.

= 3.2.6.3 - 2017.02.24 =
* Fix bug with positions not being preserved with update.

= 3.2.6.2 - 2017.02.24 =
* Feature added where you can add defined percentage discount per product.
* Feature added where you can reset to original price per product.
* Feature added where you can switch grid from left to right.
* Fix bug with currency symbol not changing on inline discount.
* Fix bug with scan order button restored on orders page.
* Fix bug with pagination styles not working correctly.
* Tweak to keypad screen now pop up as opposed to bottom slider.

= 3.2.6.1 - 2017.02.20 =
* Fix bug with jQuery Keypad not allowing more than 4 items to be totalled.
* Fix bug with clear price of keypad.

= 3.2.6 - 2017.02.15 =
* Fix bug with add custom product not working.
* Fix bug with price not being able to add when editing the price of the product.
* Support for EAN payment gateway.

= 3.2.5.1 - 2017.02.13 =
* Fix bug with customer not loading when finding them.
* Fix bug with variable images not being allocated correctly.
* Fix bug with receipt not saving.

= 3.2.5 - 2017.02.12 =
* Tweak to the status page showing status of installation and updater.

= 3.2.4 - 2017.02.11 =
* Tweak to reports by sessions.
* Feature to log status of plugin installation.

= 3.2.3 - 2017.02.09 =
* Fix bug with receipts showing no prices from Orders page.
* Fix bug with default customer not being applied to guest orders.
* Fix bug with receipt templates not saving.

= 3.2.2 - 2017.02.08 =
* Feature added to view sales by sessions.

= 3.2.0 - 2017.02.07 =
* Feature to control the float of the cash in the drawer when opening a register.
* Feature to print gift receipt when printing receipt for orders.
* Feature added to make guest payment compulsory.
* Feature to make the screen full screen from the register.
* Feature to apply inline discount per product in both currency and percentage.
* Tweak to receipt template page to show and hide the SKU on the receipt.
* Tweak to notes to display breaks in the customer notes page. 
* Tweak to show a message when tendered amount is less than amount due.
* Tweak to print the site name and outlet name for the receipt.
* Tweak to receipt options improving clarity for receipt templates.
* Fix bug with user avatars not showing when loading customers.
* Fix bug with variable products modal window not closing behaviour.
* Fix bug with account creation is not ticked.
* Fix bug with product pagination on the register.
* Fix bug with booking date when purchasing a bookable product.
* Integration with WooCommerce Product Add-Ons.

= 3.1.7.7 - 2016.12.19 =
* Updated translations.

= 3.1.7.6 - 2016.12.14 =
* Fix bug with cart breaking on front end.
* Fix bug with swipe card for Authorize.NET gateways.

= 3.1.7.5 - 2016.12.12 =
* Fix bug with Secure Submit gateway.
* Fix bug with tablet not loading customers.
* Fix bug with bookings label on defined blocks.
* Tweak to input on cash payment method.
* Feature added for WooCommerce Subscriptions.

= 3.1.7.4 - 2016.11.17 =
* Fix bug with strike through price appearing on some products.
* Fix bug with broken dependancies.

= 3.1.7.3 - 2016.11.10 =
* Fix bug with updater not fetching for local installations.

= 3.1.7.2 - 2016.11.06 =
* Tweak to license page.

= 3.1.7.1 - 2016.11.02 =
* Fix bug with license updater showing on two pages.

= 3.1.7 - 2016.11.01 =
* Fix bug with blur on Windows OS.
* Tweak to A4 labels.
* Tweak to font icons used.
* Tweak to keypad.

= 3.1.6.12 - 2016.10.26 =
* Fix bug with order note appearing after saved order is load.
* Tweak to allow products on back-order to be accepted by basket.
* Tweak to decimal quantity value to be chosen from 0.1, 0.25, 0.5 and 1.
* Tweak to translatable strings being considered.

= 3.1.6.11 - 2016.10.24 =
* Fix bug with order number and stock not updating.
* Fix bug with tax rounding on saved items.

= 3.1.6.10 - 2016.10.17 =
* Fix bug with jQuery not loading on all installations.
* Tweak to cache loading by introducing random parameter in string.
* Tweak to saved orders being included as separate row in reports.

= 3.1.6.9 - 2016.10.14 =
* Fix bug with barcode labels not appearing correctly on 30 and 80 Avery templates.

= 3.1.6.8 - 2016.10.13 =
* Fix bug with cash on delivery not marking bookings as paid.

= 3.1.6.7 - 2016.10.11 =
* Feature added bookings integration with WooCommerce Bookings.

= 3.1.6.6 - 2016.09.20 =
* Feature added to barcode page to add product categories and product variables from single SKU.
* Fix bug with local installations of plugin displaying errors on register page.

= 3.1.6.5 - 2016.09.16 =
* Tweak to overlay variable stock indicator, SKU and price to be reset.
* Feature added to define CSS per receipt.

= 3.1.6.4 - 2016.09.15 =
* Fix bug with scan field API.
* Tweak to show price on variable products when using overlay mode.
* Tweak to email receipt option for register.
* Tweak to product price to show original price after editing.

= 3.1.6.3 - 2016.09.13 =
* Tweak to the modal window closing feature.
* Tweak to product title text box auto focus when loading the add custom product window.
* Tweak to quantity number pad when loading the add custom product window.
* Tweak to the variable product price display when tiles are selected.
* Fix bug with card details on console bug.

= 3.1.6.2 - 2016.09.10 =
* Fix bug with NMI payment gateway.
* Feature to the connection status setting.
* Tweak to the design of icons.

= 3.1.6.1 - 2016.09.05 =
* Fix bug with Authorize.NET payment gateway.
* Fix bug with deleted products appearing.
* Fix bug with syncing between products after stock.

= 3.1.6 - 2016.08.25 =
* Feature added to show product image on receipt.
* Feature added to allow multiple products to be loaded when printing barcodes.
* Feature added to print on Avery 20 x 4 template.

= 3.1.5.5 - 2016.08.05 =
* Fix bug with PayPal gateway not working.

= 3.1.5.4 - 2016.08.04 =
* Feature added where you get an error note if there is no SKU found.
* Feature added where notes are automatically displayed when loading an order.
* Tweak to email prompt showing for guest users.
* Fix bug with users having the 'Invalid Order' bug.

= 3.1.5.3 - 2016.08.03 =
* Feature added where you can define what the scanning field is scanned to return a product.

= 3.1.5.2 - 2016.08.02 =
* Fix bug with JSON validation for some users.
* Fix bug with variations not loading properly in search bar.

= 3.1.5.1 - 2016.08.01 =
* Fix bug with card scanning feature.

= 3.1.5 - 2016.07.28 =
* Tweak to receipt text headers.
* Tweak to product search requests.
* Tweak to customer layout and hover points.
* Fix bug where change is shown after purchase.
* Fix bug where some users had stock updating twice.
* Fix bug where some users had basket loading without correct settings.

= 3.1.4.9 - 2016.07.21 =
* Fix bug with customer loading after removing previous customer on guest orders.
* Fix bug with WC 2.6.x update affecting tax from being applied.
* Fix bug with rounding errors caused after WC 2.6.x update.
* Fix bug with Gravatar HTTPS error.
* Fix bug with quantity stock errors produced when quantity is approaching zero.

= 3.1.4.8 - 2016.07.20 =
* Fix bug with product discounts not being applied correctly.
* Fix bug with adding, saving and loading orders with discount.

= 3.1.4.7 - 2016.07.15 =
* Fix bug with customer search not working for some users.

= 3.1.4.6 - 2016.07.08 =
* Fix bug of customers staying after the order is complete.
* Fix bug with loading orders after they have been saved through custom order.
* Fix bug with default tile sorting.
* Fix bug with shipping state not appearing correctly on shipping fields.

= 3.1.4.5 - 2016.06.22 =
* Tweak to blur effect being appleid incorrectly on Firefox and Safari browsers causing slow performance.
* Tweak to selecting text when double clicking in register, no longer possible to ensure quick response and user friendliness.
* Tweak to sounds used when adding items to cart.
* Tweak to icons used to follow WooCommerce font system.

= 3.1.4.4 - 2016.06.17 =
* Tweak to layout and design - to conform and compliment WooCommerce 2.6.

= 3.1.4.3 - 2016.06.16 =
* Fix bug with setup wizard not appearing after activation.
* Fix bug with pre WC2.6 support.

= 3.1.4.2 - 2016.06.16 =
* Tweak to styles post 2.6 update.

= 3.1.4.1 - 2016.06.15 =
* Fix bug with register not loading post 2.6 update.

= 3.1.4 - 2016.06.15 =
* WooCommerce 2.6 compatibility.
* Feature integration with Points & Rewards.
* Tweak to product grid when loading multiple screens.

= 3.1.3.9 - 2016.06.09 =
* Fix bug with custom fields not appearing on users page.
* Fix bug with customer search not working properly for some users.

= 3.1.3.8 - 2016.06.07 =
* Fix bug with loading orders and not showing tax on products.
* Fix bug with loading and saving orders with certain type of products.
* Fix bug with Authorize.NET CIM payment gateway.
* Fix bug with special characters in product category grids.
* Fix bug with duplicate customer names appearing in search.
* Fix bug with emails to guests not sending.
* Fix bug with state/country addresses not saving correctly.
* Fix bug with report not showing for cashier users.
* Tweak to Spanish translation.

= 3.1.3.7 - 2016.05.20 =
* Fix bug with coupons with product discount.

= 3.1.3.6 - 2016.05.20 =
* Fix bug with register arrays not loading correctly for some users.

= 3.1.3.5 - 2016.05.19 =
* Fix bug with stock not reloading when loading saved orders.
* Fix bug with error line appearing on registers page.
* Fix bug with custom products not loading correctly.
* Fix bug with printed reports not displaying properly.

= 3.1.3.4 - 2016.05.13 =
* Fix bug with receipt not printing properly.

= 3.1.3.3 - 2016.05.10 =
* Feature integration with WooCommerce Admin Custom Order Fields.
* Feature integration with WooCommerce Advanced Custom Fields.
* Feature order type indicator.
* Fix bug with tiles go back not appearing correctly.

= 3.1.3.2 - 2016.05.04 =
* Fix bug with register not loading on Safari devices.
* Feature added to display reports when register is closed.
* Feature added to set the served user on receipt.

= 3.1.3.1 - 2016.05.01 =
* Fix bug with blurs appearing on Chrome browsers on non-retina devices.
* Tweak to default settings of auto update.

= 3.1.3 - 2016.04.27 =
* Tweak to display prices including or excluding tax on product grid.
* Tweak to product grid now displays a tile for going back to parent category.
* Tweak to toaster notifications replacing each other when similar window appears.
* Feature added to allow users to send email from payment modal window.

= 3.1.2.2 - 2016.04.21 =
* Fix bug with quantity keypad not allowing products to be added when decimal quantity is disabled.
* Fix bug with search customer not working using last names.
* Fix bug with conflict with 'Better WordPress Minify'.

= 3.1.2.1 - 2016.04.20 =
* Fix bug with cashier receipt showing logged in users rather than cashier.
* Tweak to the customer details page.

= 3.1.2 - 2016.04.19 =
* Fix bug with coupons limited to 1 product not being applied correctly.
* Fix bug with loading web orders incorrectly.
* Feature introduced to allow decimal quantities to be used for stock in register / backend.

= 3.1.1.1 - 2016.04.18 =
* Tweak to the register column rearrangement.
* Tweak to responsive styles tweaks.
* Tweak to settings page.
* Tweak to subtotal page.

= 3.1.1 - 2016.04.18 =
* Fix bug with connections interrupted messages appearing.
* Fix bug with register cart not scrolling as you add products.
* Tweak to settings to set stock intervals in seconds.

= 3.1.0 - 2016.04.11 =
* Fix bug with customer details being updated.
* Fix bug with default sorting not working correctly.
* Fix bug with removing, reloading and saving orders.
* Tweak to stock updater automatically updating information.

= 3.0.10 - 2016.03.02 =
* Feature integration of Address Validation plugin from WooThemes.
* Feature search customers using telephone numbers.
* Fix bug with minimum spend coupon toaster notification showing USD.
* Fix bug with (ex. VAT) being displayed.
* Fix bug with setup wizard showing incorrect default options for register options.
* Fix bug with default country being displayed for guest users.
* Fix bug with checkout field editor plugin.

= 3.0.9 - 2016.02.25 =
* Fix bug with reports not showing.

= 3.0.8 - 2016.02.24 =
* Fix bug when loading the register on iOS device.
* Fix bug with tiling ordering not working correctly.

= 3.0.7 - 2016.02.18 =
* Support for Simplify Commerce gateway.
* Fix bug with loading variable products with missing attributes.
* Fix bug with email notifications being sent when they should not be.

= 3.0.6 - 2016.02.15 =
* Fix bug with cancelled orders appearing in report.
* Fix bug with customer search email not working.
* Fix bug with variable products not loading for some users.
* Feature setup wizard for first time users.
* Feature to force open register when opened in another tab.
* Tweak to modal windows when showing notifications.

= 3.0.5 - 2016.02.04 =
* Fix bug with Authorize.NET payment gateway not working correctly.
* Fix bug with stock issue when saving and reloading an order.
* Compatibility with WooCommerce 2.5 - fix bug with variable products/custom products not loading properly when loading saved orders.
* Tweak to first installation; default country is set to Base Country of WooCommerce store.
* Tweak to image sizes loading on the register, new option to set quality of images to load.

= 3.0.4 - 2016.02.03 =
* Fix bug with Checkout Field Editor not saving the fields to the order.
* Compatibility with WooCommerce 2.5 - fix bug with report giving error on closing of the register.
* Compatibility with WooCommerce 2.5 - fix bug with stock of non-stock products displaying null in stock. 

= 3.0.3 - 2016.02.01 =
* Compatibility with WooCommerce 2.5 - fixed issue with grid not loading.

= 3.0.2 - 2015.12.18 =
* Fix bug with payment method title not changing as payment is selected.
* Fix bug with registers table not showing icons in other languages.
* Fix bug with quantity not updated on keypad on custom product.
* Fix bug with customer not being linked to the order on the Orders page.
* Fix bug with variable products not appearing correctly when attributes and variations are custom entered.
* Tweak to the closed register report including date and time.
* Tweak to the time format of closed and opening times for when register is reported.
* Tweak to the stock affection to products when using the chip & PIN method.

= 3.0.1 - 2015.12.14 =
* Fix bug with reports not being displayed.
* Fix bug with complete status being set as completed status and not returning this.
* Fix bug with cycle not loading correctly for some users.
* Fix bug with cashier role not being able to process orders.
* Feature added custom CSS on register.

= 3.0.0 - 2015.12.08 =
* Fix bug with tax calculations.
* Fix bug with shipping calculations with tax.
* Fix bug with coupons discount appearing on receipt.
* Fix bug with SSL authentication.
* Feature added to control stock inventory.
* Feature added to print labels on Avery 30 labels per sheet template.
* Feature added where variations can be displayed as tiles rather than overlay.
* Feature added to cater for more receipt fields; customer note, shipping, name and email.
* Feature added to define default country for shipping.
* Feature added to display or hide out of stock products.
* Feature added to display or hide customer details popup upon customer search.
* Feature added to display tiles with price as well as image only.
* Feature added to display notification when adding note and applying discount.
* Feature added to modify the product name when processing orders.
* Feature added to modify and add tax on each product.
* Feature added to disable sound notifications.
* Feature added to rearrange gateways.
* Refactoring of register, cart and order functionality.
* Tweak to validation when amount tendered is zero.
* Tweak to validation when basket is empty.
* Tweak to the overlay popup for variable products.

= 2.4.20 - 2015.09.26 =
* Fix bug with register not loading on Safari browsers (desktop and mobile).
* Fix bug with quantity buttons not styled properly to be clicked on at any point.
* Fix bug with tax not being displayed in the receipt.
* Fix bug with buttons staying on colour of hovered class.
* Fix bug with default customer not having email address set.

= 2.4.19 - 2015.09.16 =
* Tweak to documentation link to updated documentation page.
* Tweak to hooks for email actions when checkout is processed.
* Fix bug of order received end point upon successful transaction.
* Fix bug with one custom product being process to the order.

= 2.4.18 - 2015.08.31 =
* Tweak to inline discounts putting strike through the price.
* Tweak to sale products indicating when product is on sale and original price.
* Tweak to settings page in attempt to make settings more clearer for users.
* Tweak to user roles now including POS Manager and Cashier.
* Fix bug with header file and plugin activation.
* Fix bug uninstallation of plugin.
* Fix bug of receipt not including tax when set to including from backend.
* Fix bug with receipt padding.

= 2.4.17 - 2015.08.29 =
* Fix bug with variable products not adding to basket for some users.
* Fix bug with coupon and item limit not being applied.
* Fix bug with variable products not appearing on receipts.
* Fix bug with how discounts were being applied on variable products.
* Fix bug with quantity addition.
* Tweak to translatable strings.

= 2.4.16 - 2015.08.27 =
* Fix bug with guest orders showing null.
* Fix bug with variations not loading after WooCommerce 2.4 update.
* WooCommerce 2.4 compatibility.
* Fix bug with no variations showing on custom orders after WooCommerce 2.4 update.
* Fix bug with voiding orders not cancelling the order after load.
* Fix bug with Scan Order button disappearing after WordPress 2.3 update.
* Fix bug with Outlet page loading error after changing country.
* Fix bug with cash method not working after WooCommerce 2.4 update.
* Fix bug with shipping not being taxed after WooCommerce 2.4 update.
* Fix bug with discount presets not being limited to 4.
* Tweak to chip & PIN method not showing change on receipt.
* Tweak to accepting hooks on register page.
* Tweak to returning username email address upon searching for customer.
* Tweak to barcode scanning to listen to scan at any time without clicking in search bar.
* Tweak to receipt template, now includes date format.
* Tweak to layout of attributes in receipt which now show in list rather than side by side.
* Tweak to customer details screen.
* Tweak to payment screen to cater for different and multiple payment methods.


= 2.4.15 - 2015.08.17 =
* Fix bug with receipt not displaying correctly.
* Fix bug with double emails showing when account is loaded.
* Tweak to report page to work with Firefox browser.
* Tweak to receipt when item discount added.
* Tweak to register of amount zero setting to complete.

= 2.4.14 - 2015.08.11 =
* Fix bug with tax not showing, sorry about that.
* Fix bug with tax not loading on loaded orders.
* Support for swipe for PayTrace and PayPal Pro. Official WooThemes plugins only.

= 2.4.13 - 2015.08.06 =
* Fix bug with javascript and stripe gateway.
* Fix bug with images not correctly pulled.
* Tweak to receipt and prices being displayed.
* Tweak to category taxonomy grids.
* Tweak to discounted items on receipt.
* Fix bug with tax not being calculated after discount applied.
* Fix bug with barcode scanner.
* Tweak to email notifications.
* Tweak to variations selector now includes slug.
* Feature search in load orders panel.

= 2.4.12 - 2015.07.07 =
* Fix bug when editing image/colour tiles.
* Fix bug with tax rates not being applied correctly.

= 2.4.11 - 2015.07.01 =
* Fix bug with receipt templates not being added.

= 2.4.10 - 2015.06.25 =
* Fix bug with Chip & PIN payment method not working.
* Feature added in styling receipts (position and size only).
* Tweak to the receipt template page.
* Fix bug with CRM conflict when viewing customer orders.

= 2.4.9 - 2015.06.24 =
* Fix bug with receipt printing blurry, thanks to those who helped.
* Fix bug with css conflicts with other plugins.
* Tweak to the loading screen.
* Tweak to copying the billing to shipping.
* Tweak to receipt layout.

= 2.4.8 - 2015.06.12 =
* Fix bug with keyboard not appearing on tablets.
* Tweak to how available payment gateways appear.
* Tweak to state field when customer details laods.
* Tweak to loading of localisation files.
* Tweak to notifications.

= 2.4.7 - 2015.06.10 =
* Fix bug with Stripe gateway not working.
* Fix bug with search bar conflict.
* Tweak to French language included.
* Fix bug with rounding issue when tax is applied.
* Tweak to default shipping method feature.
* Tweak to scan order button.
* Tweak to HTTPS protocol.

= 2.4.6 - 2015.06.04 =
* Fix bug with tax not being calculated when coupon applied.
* Fix bug with rewrite issue.
* Tweak to loading of products thanks to lfontanez.

= 2.4.5 - 2015.05.30 =
* Fix bug with register not loading on Safari browsers.
* Fix bug with tax subtotal being incorrect.

= 2.4.4 - 2015.05.29 =
* Fix bug with cart % coupons not being applied properly.
* Fix bug with tax not being calculated properly.
* Fix bug with products not adding.
* Tweak to the order status options; can select any status.

= 2.4.3 - 2015.05.26 =
* Feature added on tile settings, can decide whether to show image or image and text.
* Feature added on stock quantity indicator when adding products to the register.
* Feature added default shipping method selection.
* Feature added on sorting tiles by custom ordering and many more.
* Feature added where you can decide whether emails should be automatically sent or not when adding new customer.
* Fix bug where tax was still being calculated when disabled.

= 2.4.2 - 2015.05.18 =
* Fix bug with coupons being applied on sale items with product %.

= 2.4.1 - 2015.05.17 =
* Tweak to the payment screen; layout and responsiveness adjusted for default payment method texts.
* Tweak to hover tips not shown on screens less than 1024px.
* Tweak to on screen keyboard position.
* Fix bug with cash method payment not showing up for some users.
* Fix bug with decimal and thousand separator not following the general settings on orders, receipts and register.
* Fix bug with coupon/discount not being applied per product.
* Fix bug with coupon/discount not showing on receipts.

= 2.4.0 - 2015.05.09 =
* Fix bug with WPML.
* Fix currency decimal issue.
* Fix Stripe payment bug.
* Fix Braintree payment bug.
* Fix notes space issue bug.

= 2.3.9 - 2015.04.24 =
* Fix bug with pay button loading notes when shouldn't.
* Fix bug when variable products have a label with two words.
* Fix bug with coloured tiles not added when clicking on the text.
* Fix bug with barcode scanner not working entirely to specs.
* Fix bug with WordPress 4.2 & XSS vulnerability.

= 2.3.8 - 2015.04.20 =
* Feature added to reporting function; can now view reports by register, outlet and cashier.
* Feature added to the filters on the Orders page, can now filter orders by particular register.
* Tweak to the overlay keypad behaviour after using it.
* Tweak to the name 'Users' to 'Cashiers'.
* Fix bug with receipt not showing items.
* Fix bug with user being logged out when clicking on the Orders page.

= 2.3.7 - 2015.04.15 =
* Feature added custom payment gateway for chip & PIN payments.
* Tweak to the quantity selector on product grid, auto hides after adding product.
* Feature added email guest users - prompt window asks for email address.
* Tweak to the email validation, now accepts + characters.
* Fix bug with tablets and hover tips.
* Fix bug with database arrays when empty.
* Fix bug which affected updates.

= 2.3.6 - 2015.04.10 =
* Fix bug with register not loading on IE and Windows 8.
* Tweak to descriptions of magnetic card support function.
* Fix bug with default customer not staying after order is complete.
* Feature added support for free products being added to register.
* Fix bug with state being duplicated.
* Fix bug with charge limit.
* Feature added to retrieve particular orders of a status.
* Feature added to retrieve web based orders.
* Feature added where users can define what status the order should be when saved.
* Tweak to the users page now shows total sales done by user.
* Tweak to the display name being shown when register is opened.
* Tweak to the payment page and cash page.
* Fix layout of gateways fields.
* Feature added where you can load all products in the register.
* Feature added where you can load parent and child categories as tiles.

= 2.3.5 - 2015.03.26 =
* Fix issue with tax not being calculated on Safari browsers.
* Tweak to select2 on orders page.

= 2.3.4 - 2015.03.12 =
* Fix discount not showing on receipt.
* Remove the discount after tax (not supported anymore).
* Tweak to tax rates applied when no address is set.
* Fix bug with loaded customer staying after order is processed.

= 2.3.3 - 2015.03.05 =
* Fix missing menu icon.
* Fix missing js files.

= 2.3.2 - 2015.03.03 =
* Fix bug with change not being calculated.
* Tweak to grids with images added text.

= 2.3.1 - 2015.03.02 =
* Coupon support added.
* New scan order button added to Orders page to locate orders quickly.
* Tweak style of the main buttons.
* Tweak to discount pop up and keypad.
* Fix bug with discount and tax not being shown.
* Voiding a saved order now cancels the order.
* Printing receipt freeze issue fixed.

= 2.3 - 2015.02.17 =
* WooCommerce 2.3 compatibility.

= 2.2.4 - 2015.02.09 =
* Tweak to HTTPS not being forced, users now have option to force it.
* Fix bug related to barcode being printed on receipt when users disable it.

= 2.2.3 - 2015.02.05 =
* Tweak to validation procedures when deleting items.
* Tweak to notifications about permalinks not being set up upon first installation.
* Tweak with forced SSL for payment gateways.
* Tweak to saved orders being retrieved with saved note.
* Tweak to way orders are saved with prefix and suffix, editing after no longer affects.
* Fix bug that does not pull items.
* Fix bug when deleting receipts, outlets, registers and grids in bulk.
* Fix bug when logging in and not having same date set.
* Fix bug with never ending loading on tablet devices.
* Fix bug on user page.
* Fix bug caused to variable products.
* Fix bug when retrieving sales and taxes being saved.

= 2.2.2 - 2015.02.02 =
* Fix bug with cash option and other payment methods not showing.
* Fix bug with API not being checked and validated.
* Tweak to custom POS product being added.
* Tweak to dynamic receipt widths.
* Tweak to SSL force on gateways.

= 2.2.1 - 2015.01.27 =
* Fix bug with none taxable products being taxed.
* Fix bug with address not being pulled properly.
* Fix bug with hanging screen when customer is being added.
* Fix bug with duplicate and missing SKU's.
* Fix bug with receipt showing for earlier orders.
* Fix bug with prepopulated cash and credit card fields.

= 2.2.0 - 2015.01.22 =
* Feature added to support Authorize CIM and AIM gateways.
* Tweak filters and functions.
* Fix bug with Fusion plugin.
* Localisation support for NL.
* Fix bug with failed orders and hanging page.
* Fix bug with tax number not being displayed on receipt.
* Fix bug when prices are entered excluding tax.
* Fix bug with more than two decimal places in price.
* Fix bug with register not showing upon refresh.
* Fix bug with receipt widths.


= 2.1.6 - 2015.01.06 =
* Feature added now where you can set a default customer per register.
* Tweak compatibility with 'striper'.
* Fix bug with tax not being calculated when loading a customer.

= 2.1.5 - 2014.12.29 =
* Tweak to receipt layout and width.
* Tweak to disabled variations being shown on the front end product grid.
* Tweak to choosing tax being applied before or after the total.
* Fix bug with tax being applied after customer is loaded.
* Fix bug with discounts not being applied when saving the order.
* Fix bug with first variation always being selected.
* Fix bug with double stock being reduced.

= 2.1.4 - 2014.12.12 =
* Feature custom meta data can be added to regular products as they are added to basket.
* Feature print reports now available.
* Tweak to how customer details appearing when clicking on customer name.
* Tweak to the receipt page, now use dynamic width and variable products.
* Fix bug with retrieving sales and notes column not appearing correctly.
* Fix bug with toaster notifications appearing on receipt.
* Fix bug with customer details not appearing.

= 2.1.3 - 2014.12.09 =
* Feature sound notifications when adding products, successful order and out of stock items.
* Feature added to editing inline price items using % or amount.
* Tweak to the notifications.
* Tweak to the date format of the sync.
* Tweak to the decimal places of the amount.
* Fix bug when receipt was enabled and making orders completed.

= 2.1.2 - 2014.12.03 =
* Feature sync products and stock instantly from the register page with time showing when last synced.
* Feature when register goes offline, indicator is shown and when online it goes green.
* Feature notifications when sale is made, product is added and stock  is zero. 
* Tweak to the payment icons, now using SVG.
* Fix bug with registers, outlets and product grids not saving for some users.
* Fix bug when large number of variations are loaded.
* Fix bug with additional information not showing (using checkout field manager plugin)

= 2.1.1 - 2014.11.21 =
* Tweak to the basket inline items.
* Tweak to the way orders are saved to WooComemrce.
* Fix retrieving saved orders using total.
* Fix saving orders with incorrect totals.
* Fix bug on users page.

= 2.1 - 2014.11.12 =
* Feature on disabling discounts per user.
* Feature having quantity selector appear when pressing grids.
* Feature added in having receipt print button appear on payment screen.
* Feature can now accept custom products (many thanks to David Schroeder of Amis Direct Furniture - amisdirectfurniture.com for help).
* Tweak with close register and go back to registers.
* Tweak to the meta labels for variable products.
* Tweak allowing users to load large amounts of data.
* Tweak in making CSS more tidy and less duplicates.
* Tweak to the font icons used.
* Tweak to the user interface of the POS Register.
* Fix bug with SSL issue.
* Fix bug when adding new customer.
* Fix bug when moving tiles on the Product Grids page.

= 2.0.3 - 2014.10.28 =
* Fix bug when printed barcode on the receipt.
* Feature added, now supports multisite.

= 2.0.2 - 2014.10.27 =
* Fix bug when retrieving sales.
* Fix bug when uploading logo to receipts.

= 2.0.1 - 2014.10.24 =
* Feature added localisation, sorry for the wait.
* Tweak to status of order when saved.
* Fix bug when adding tax after discount is applied.

= 2.0 - 2014.10.16 =
* Feature credit card scanning feature implemented for RealEx, Braintree and Stripe.
* Fix bug with user already being logged in.
* Tweak to the retrieve sales page.
* Tweak to CSS of the product grid.

= 1.9.1 - 2014.10.07 =
* Fix bug when adding customers, sorry about that.
* Feature added when retrieving sales, can now retrieve from other registers.

= 1.9 - 2014.10.03 =
* Feature added can now add categories to product grids.
* Feature added can now select 'No Shipping' as a shipping option.
* Tweak to hover classes, better usage on tablets and phones. 
* Tweak functions improving speed and performance.
* Fix bug with blank screen registers (nuisance bug, sorry guys).

= 1.8.6 - 2014.09.29 =
* Feature added where you can define what discount presets appear when applying discount.
* Feature added where you can define what status the order is when completed by POS.
* Fix bug with prefix and suffix not appearing.

= 1.8.5 - 2014.09.23 =
* Feature added ability to select product grids from the register page.
* Feature added to select which product grid a product is assigned to from products page.
* Feature added called 'Ready To Scan' which allows you scan straight away.
* Fix bug with total including tax when not set or enabled.

= 1.8.4 - 2014.09.17 =
* Fix bug when opening a regsiter and blank screen appears.
* Fix bug when activating the plugin and register not loading.
* Tweak to the Orders Type style on Orders page.

= 1.8.3 - 2014.09.11 =
* Fix bug when adding a register to the table.

= 1.8.2 - 2014.09.10 =
* Feature compatbility for WooCommerce 2.2.
* Fix bugs with customer details.

= 1.8.1 - 2014.09.05 =
* Tweak to the main register, payment methods and user interface.
* Tweak to the payment icons.
* Tweak to the keypad, can now be used using normal keyboard.
* Tweak to the quantity keypad adding -/+ buttons.
* Tweak to the discount keypad adding currency and % buttons.
* Tweak to the cash keypad adding possible cash values.
* Fix bug with variable products with no attribute defined.
* Fix bug when deleting an Outlet, no more error message.

= 1.8 - 2014.08.22 =
* Fix bug when there is an existing tile on the product grid.
* Fix for major bug wheere database couldn't open.
* Fix number of items recorded to sum all quantities.
* Tweak the receipt page.
* Feature page added under settings called Layout allows users to customise the registers.
* Feature added where you can enable and disable payment methods on the POS only.
* Feature added where you can view the status of the register, open or closed.


= 1.7.1 - 2014.08.22 =
* Fix bug when adding a tile with coloured background.
* Fix bug when loading register on a non IndexedDB supported browser.
* Fix bug when adding variable products to the cart.
* Fix bug when removing products from the basket.
* Fix bug with Stripe gateway.
* Fix bug when entering quantity through keypad.
* Tweak saving orders, now resets the basket.
* Tweak retrieving orders, now closes the modal window.

= 1.7 - 2014.08.12 =
* Feature of adding products via barcode scanner reader.
* Feature of implementing IndexedDB.
* Feature added where you can define whether tax settings are inherited from WooCommerce or disabled.
* Feature added to allow user to remove the discounts applied.
* Tweak to the tile preview page.
* Tweak to the shipping field position.
* Tweak to the frontend variation.
* Tweak to the tiles introduction of tile styles.
* Tweak to the orders page to reprint receipts from Orders page.
* Fix bug with WooCommerce's Stripe payment gateway.
* Fix bug on selecting a state when adding a customer based in US.
* Fix bug with undefined index hook_suffix.
* Fix bug when voiding the register, closing and reopening it, no longer shows voided items.
* Fix bug when adding products as tiles to the product grids.

= 1.6 - 2014.07.17 =
* Feature added where you can create an account, username uses the email address and password generated.
* Feature added where you can now close the register.
* Fix bug where customer fields were not empty when adding a customer.
* Fix bug where permalinks and 404 errors were appearing.

= 1.5.1 - 2014.07.10 =
* Fix permalink bug.
* Fix checkout on shop page bug.
* Fix order saving bug.

= 1.5 - 2014.07.03 =
* Fix decimal total not working with change.
* Fix shipping method not showing when entering customer details.
* Fix bug with shipping total not showing on payment screen.
* Feature implemented where permalinks are used to display the registers.
* Feature added where you can retrieve an order based on pending status and logged in register.
* Tweak when searching for product, selecting will auto add.
* Tweak showing user no product grid set up when logging in register.
* Tweak when adding a regsiter, ensuring name, grid, receipt and outlet set up.
* Tweak barcode height.
* Tweak barcode continuous print.

= 1.4.1 - 2014.06.28 =
* Fix the functionality of End of Sale behaviour.
* Fix issue with decimal places on the currency.
* Tweak Orders screen with filter for POS and Web orders.
* Fix bug with shipping with Guest customer.
* Fix End of Sale notes on all sales.

= 1.4 - 2014.06.23 =
* Feature new shipping options.
* Feature new barcode printing module.
* Tweak the create customer screen, now viewable on responsive devices.
* Tweak the Outlet address on Outlet screen.
* Tweak the currently locked message.
* Remove customer balance from the customer panel.
* Feature new end of sale actions now operative.
* Tweak the orders page.

= 1.3 - 2014.06.12 =
* Feature where you can now print receipts.
* Feature added to the Orders page to show which orders are from online and POS.
* Feature added to allow payments from the gateway.
* Fix featured image issue when setting it on a product.

= 1.2.0 - 2014.06.09 =
* Feature added, you can see who is on each register, assign user to each register.
* Feature added, you can now customise the receipts.
* Feature added, you can set a tile for variable products.

= 1.1.0 - 2014.06.08 =
* Feature added where you can now set grids and tiles for products.
* Tweaks made to the user interface.

= 1.0.0 - 2014.04.08 =
* Initial release!

== FAQ ==

= What hardware is compatible with this plugin? =
You can find a list of user submitted hardware [here](http://actualityextensions.com/hardware-submission/supported-hardware/).

= Where can I get support or talk to other users? =
If you come across a bug or a problem, please contact us [here](http://actualityextensions.com/contact/).

For queries on customisation and modifications to the plugin, please fill this [form](http://actualityextensions.com/contact/).

You can view comments on this plugin on [Envato](http://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665/comments).

= Where can I find the documentation? =
You can find the documentation of our Point of Sale plugin on our [documentations page](http://actualityextensions.com/documentation/woocommerce-point-of-sale/).