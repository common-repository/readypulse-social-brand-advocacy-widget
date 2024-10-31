=== ReadyPulse Social Brand Advocacy Widget ===
Contributors: mihirreadypulse
Tags: brand advocacy, readypulse, widgets, social media, testimonials
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables embedding of ReadyPulse testimonial showcase widgets on WordPress blogs using a short code. Ex [readypulse-widget id="widget-id"]

== Description ==

    This plugin allows you to embed ReadyPulse widgets on your WordPress blog using a wordpress shortcode of type [readypulse-widget id="widget-id" option="value"]

    List of all options is given below.

*   "id" is the id of the ReadyPulse widget you want to embed. For example if your widget URL is http://widgets.readypulse.com/curations/251/embed, then the number 251 in the URL is the widget id. You can obtain the widget ID for your widget from the widget install section of the ReadyPulse app.
*   "theme" is the id of the ReadyPulse theme that could be used to style the widget. The Theme ID can be obtained from the widget install section of the ReadyPulse app.
*   "width" is the horizontal space or width (in pixels) you want to give the widget on your blog.
*   "height" is the vertical space or height (in pixels) you want to give the widget on your blog.
*   "type" is the type of visualization you want for your widget - valid types are 'feed', 'gallery' and 'album'.
*   "scope" is a technique to dynamically pass a filter to the ReadyPulse widget. Scopes help you further narrow down the content shown in the widgets. Scopes are a list of of key-value separated by '|' character. Following scope keys are supported. Each scope value is a list of comma separated terms.
    * "keywords" is used to only show content that has text matching the given keywords
	* "product_ids" is used to show content matching the specific product ids from your product catalog. This feature should not be used unless you are integrating your ecommerce product catalog with ReadyPulse
	* "product_categories" is used to show content matching the specific product categories from your product catalog. This feature should not be used unless you are integrating your ecommerce product catalog with ReadyPulse
	* "product_keywords" is used to show content matching the specific keywords from your product catalog. This feature should not be used unless you are integrating your ecommerce product catalog with ReadyPulse

== Installation ==

1. Upload the entire plugin directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Start using the short code as described above in your blog posts.

== Changelog ==

= 0.1 =
* Initial beta version

= 1.0.2 =
* First stable version

= 2.0 =
* Updated the plugin to use ReadyPulse PHP SDK
* BugFixes

