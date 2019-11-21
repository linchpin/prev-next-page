=== Linchpin - PrevNextPage ===
Contributors: aware, linchpin_agency
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CH9GUS7UQ6SUN
Tags: pages, next, previous, prev, presentations, traverse, loop, linchpin, utilities, pagination, simple, shortcode
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: trunk

Create sibling page links. Similar to next_post_link() & previous_post_link() but for pages. Great for utilizing Wordpress for Presentations or iterating through pages based on the "Menu Order"

== Description ==

Create next/previous links for pages. Adds the functions next_page_link() and previous_page_link() which links sibling to page(s).
If your page doesn't have a direct sibling the plugin will try to go out to the parent of the current page and get the parent's next sibling. If enabled the plugin will also loop from end->beginning and beginning->end.

This plugin is great for utilizing WordPress for presentations. If you create a set of pages. This plugin will cycle through them. Additionally you could utilize some javascript to create a timer to automatically go from page to page.

This plugin is based on 'Next and Previous Page not Post' by Matt McInvale. Although it's gone through almost a complete rewrite from his release in 09.  This plugin is a bit more optimized to take advantage of newer plugin techniques. Includes code from banesto as well regarding references to grandparent pages. Also added in additional features such as $args array for easier customization down the road. This plugin is great for utilizing wordpress as a presentation tool.

= See the FAQs Page for Snippets and Examples =

== Frequently Asked Questions ==

= Examples =

Function Options

`next_page_link( $args:array );`
`previous_page_link( $args:array );`

*   'label' 		  => '',		// What ever you want your link to be labeled. If no label is given the url is utilized
*   'loop'			  => NULL,		// 'expand', 'loop'
*   'getPagesQuery'	  => 'sort_column=menu_order&sort_order=asc',
*   'link_class' 	  => '',		// class given to the anchor for styling or js library
*   'link_id' 		  => '',		// id given to the anchor for styling or js reference 
*	'echo'			  => 'true',	//
*   'direction'		  => '',		// 'next', 'prev'

Function Examples

*   `<?php echo previous_page_link( array ( 'label' => 'View My Previous Page', 'loop' => 'loop', 'link_class' => 'ui-button-disabled' ) ); ?>`
*   `<?php echo next_page_link( array ( 'label' => 'Next Page' ) ); ?>`

Shortcode Options

*   'label' 		  = '',
*   'loop'			  = '',	// 'expand', 'loop'
*   'getPagesQuery'	  = 'sort_column=menu_order&sort_order=asc',
*   'link_class' 	  = '',
*   'link_id' 		  = '',
*   'direction'		  = '',	// 'next', 'prev'

Shortcode Examples

*   [previous_page_link]
*   [next_page_link label="View My Next Page"]

= Why not use 'Next and Previous Page Not Post'? =

Great question. No reason really. I think this one is a bit more efficient but I didn't do an apples to apples comparison. Though this plugin does have a few more options available regarding customization such as classes and IDs being applied to the links if you choose.


== Installation ==

1. Manually upload `linchpin-nextprevpage.php` to the `/wp-content/plugins/linchpin-next-page-link-previous-page-link` directory on your server (creating the folder if necessary. Or utilize the seach and install within the 'Plugins' menu of the WordPress Admin
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php echo next_page_link(); ?>` or `<?php echo previous_page_link(); ?>` in your templates or utilize `[next_page_link]` `[previous_page_link]` shortcode or `do_shortcode([previous_page_link])`;

== Changelog ==

= 1.0.2 =
* Updated FAQs for easier implementation

= 1.0.1 =
* Fixed a bug where link_class was creating an attribute with no value of the defined link_class (EX previous="") instead of creating value for the class attribute. (EX class="previous")
* Fixed a bug where the next and previous links would show even if the current $post ID was the first or last page. This really only applies sites that have looping turned off.
* Minor readme.txt updates just to make things a little bit clearer for first time users

= 1.0 =
* Base plugin off of the Next and Previous Page Not Post plugin by Matt McInvale.
* Optimize plugin's method for getting the next and previous page.
* Added more controls for customizing the class and id of each element added.
* Created a few utility methods to cut down on code duplication
* Cleaned up short codes to match updated methods