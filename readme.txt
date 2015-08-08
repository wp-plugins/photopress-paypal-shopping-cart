=== PhotoPress - PayPal Shopping Cart ===
Contributors: padams
Donate link: http://www.photopressdev.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: photos, images, taxonomies, ecommerce, shopping carts, print sales, selling prints, selling photos, print ordering
Requires at least: 3.2.1
Tested up to: 4.2.4
Stable tag: 1.7

This plugin dynamically adds shopping cart functionality to Image Attachment Pages (or single Posts, or Pages) for use in selling prints/merchandise.

== Description ==

This plugin allows you to add shopping cart functionality to single image (attachment) pages so that visitors can order prints or other merchandise of any image on your website. Specifically designed for photographers who want to enable print ordering for large image catalogs, the plugin uses the title of the image as the product name - requiring no setup of a parallel e-commerce "product catalog" (like most other plugins require). This plugin relies on the WordPress Simple Paypal Shopping Cart plugin to implement the shopping cart and accept payments via Paypal.

= Features include =

* Custom image taxonomy for storing purchase variations (e.g. print sizes and finishes)
* Set unique prices for each purchase variation
* Choose purchase variations by image or for all images on your website
* Widget for single Image/attachment page
* Template functions

= Premium Support =
The PhotoPress team does not provide support for this plugin on the WordPress.org forums. One on one email support is available to users that purchase one of our [Premium Support Plans](http://www.photopressdev.com).  

= The Guide To WordPress For Photographers =
For more information on ways to use PhotoPress and other plugins to build a photpgraphy website check out the [WordPress For Photographers e-Book](http://wpphotog.com/product/the-guide-to-wordpress-for-photographers/ "WordPress For Photographers").

== Installation ==

1. Install the [WordPress Simple Paypal Shopping Cart](http://wordpress.org/extend/plugins/wordpress-simple-paypal-shopping-cart/) plugin
1. Configure the settings of the WordPress Simple Paypal Shopping Cart plugin including entering your PayPal email address 
1. Create a shopping cart page for your website by creating a new Page in Wordpress add adding the `[show_wp_shopping_cart]` shortcode as its content
1. Upload the `photopress-paypal-shopping-cart` plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add Purchase Variations using the Media > Purchase Variations admin screen. Purchase variations take the from of label:price (i.e. 11x14 Gallery Print:100)
1. Add a sidebar to the theme template file that is used to display your single images or attachments. This can be done  by adding `<?php dynamic_sidebar('papt-image-sidebar'); ?>` to the template file (typically attachment.php or image.php).
1. Populate this sidebar with the PhotoPress - Single Product Buy Button Widget from the `Appearance > Widgets` menu in WordPress.
1. Configure the Widget with any pricing, shipping or variations you require.

== Frequently Asked Questions ==

= Why do I need this plugin? =

You want a very simple and easy way to sell prints (or other merchadise) of images that appear on your website.

= Does this plugin handle the actual printing and delivery of photos? =

No. Printing and delivering the images is up to you. This plugin just allows visitors to order and pay for the prints via Paypal.

= Do I need a Paypal account to use this plugin? =

Yes. This plugin utilizes *your* Paypal account.

= Can I have different purchase variations? =

Yes. You can create an unlimited number of purchase variations such as size and finish. Variations take the Form of `label:price` (i.e. 11 x 14 Glossy:$100 ) and are entered via the Purchase Variations Media admin page.

= Can I choose which variations are available on an image by image basis? =

Yes. Variations can be selected via the image's attachment page. If no local variation choices are made for an image, all variations will be presented. This backfill behavior can be turned off by setting the "explicit mode" option on the settings admin page.

= Can I have different prices per each product variation? =

Yes you can. Use the following syntax within the widget or shortcode:  `option1:$20|option2:$40|option3:$60`. *Note:* you must enter a currency symbol along with the price. 

Also, This feature is implemented by this plugin and is not part of the WordPress Simple Paypal Shopping Cart plugin. Please do not bother that developer for questions/support for this feature.

== Changelog ==

= 1.0 =

Initial version of plugin.

= 1.1 = 

Adding missing sidebar registration for image page.

= 1.2.= 

- added new custom taxonomy for storing purchase variations
- purchase variations can now be set on an image by image basis
- added "explicit mode" option which only diplays purchase variations explicitly set on each image.

= 1.3 =

- fixed broken product name when adding item to cart.

= 1.4 =

- fixed broken global shipping costs

= 1.5 =

- fixed missing product name in cart.

= 1.6 =

- added support ofr price validations introduced by WSPSC.

= 1.7 =

- preparing for PHP 7.