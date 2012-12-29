=== PhotoPress - PayPal Shopping Cart ===
Contributors: padams
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F83AGPR2W5AXS
Tags: photos, images, taxonomies, ecommerce, shopping carts, print sales, selling prints
Requires at least: 3.2.1
Tested up to: 3.5
Stable tag: 1.0

This plugin dynamically adds shopping cart functionality to Image Attachment Pages (or single Posts, or Pages) for use in selling prints/merchandise of any image on your WordPress website. 

== Description ==

This plugin allows you to add shopping cart functionality to single image (attachment) pages so that visitors can order prints or other merchandise of any image on your website. Specifically designed for photographers who want to enable print ordering for large image catalogs, this plugin utilizes the WordPress Simple Paypal Shopping Cart plugin to implement the shopping cart and accept payments via Paypal. The title of the Image/Page/post is dynamically used as the product name so there is no need to setup and maintain a parallel e-commerce "product catalog" like most other plugins require.

The plugin also provides a widget, shortcode, and template functions for displaying the shopping cart on single image (attachment) pages, single Posts, and Pages.

For more information on ways to use this nad other PhotoPress plugin see my [WordPress For Photographers e-Book](http://www.peteradamsphoto.com/?page_id=3357 "WordPress For Photographers").

== Installation ==

1. Install the [WordPress Simple Paypal Shopping Cart](http://wordpress.org/extend/plugins/wordpress-simple-paypal-shopping-cart/) plugin
1. Configure the settings of the WordPress Simple Paypal Shopping Cart plugin including entering your PayPal email address 
1. Create a shopping cart page for your website by creating a new Page in Wordpress add adding the `[show_wp_shopping_cart]` shortcode as its content
1. Upload the `photopress-paypal-shopping-cart` plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add a sidebar to the theme template file that is used to display your single images or attachments. This can be done  by adding `<?php dynamic_sidebar('papt-image-sidebar'); ?>` to the template file (typically attachment.php or image.php).
1. Populate this sidebar with the PhotoPress - Single Product Buy Button Widget from the `Appearance > Widgets` menu in WordPress.
1. Configure the Widget with any pricing, shipping or variations you require.

== Frequently Asked Questions ==

= Why do I need this plugin? =

You want a very simple and easy way to take orders for prints/merchandise of a large number of photos that appear on your website.

= Does this plugin handle the actual printing and delivery of photos? =

No. Printing and delivering the images is up to you. This plugin just allows visitors to order and pay for prints via Paypal.

= Do I need a Paypal Account to Use this Plugin =

Yes. This plugin utilizes *your* Paypal account.

= Can I have different prices per each product variation? =

Yes you can. Use the following syntax within the widget or shortcode:  `option1:$20|option2:$40|option3:$60`. *Note:* you must enter a currency symbol along with the price. 

Also, This feature is implemented by this plugin and is not part of the WordPress Simple Paypal Shopping Cart plugin. Please do not bother that developer for questions/support for this feature.

== Screenshots ==

== Changelog ==

= 1.0 =

Initial version of plugin.