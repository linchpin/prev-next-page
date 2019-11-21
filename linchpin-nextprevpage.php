<?php
/*
Plugin Name: Linchpin next_page_link, previous_page_link
Plugin URI: http://http://wordpress.org/extend/plugins/linchpin-next-page-link-previous-page-link
Description: Create next/previous navigation for pages. Adds the functions next_page() & prev_page() which link sibling to page(s). If your page doesn't have a direct sibling the plugin will try to go out to the parent of the current page and get the parent's next sibling. If enabled the plugin will also loop from end->beginning and beginning->end. This plugin is based on 'Next and Previous Page not Post' by Matt McInvale. Although it's gone through almost a complete rewrite from his release in 09.  This plugin is a bit more optimized to take advantage of newer plugin techniques. Includes code from banesto as well regarding references to grandparent pages. Also added in additional features such as $args array for easier customization down the road. This plugin is great for utilizing wordpress as a presentation tool.
Version: 1.0.2
Author: Aaron Ware - Linchpin
Author URI: http://linchpinagency.com
License: GPL2

Copyright 2011 Linchpin A Digital Octane,LLC Company  (email : info@linchpinagency.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (!class_exists('Linchpin_NextPrevPage')) {

	/**
	 * @author aware
	 * @package Linchpin contentEngine
	 */

	class Linchpin_NextPrevPage {
		
		public $defaults = array(
			'label' 		  => '',
			'loop'			  => NULL,		// 'expand', 'loop'
			'getPagesQuery'	  => 'sort_column=menu_order&sort_order=asc',
			'link_class' 	  => '',
			'link_id' 		  => '',
			'echo'			  => 'true',
			'direction'		  => ''			// 'next', 'prev'
		);

		public function __construct() {
			add_action( 'init', array( $this , 'init') );
		}
		
		public function init() {			
			add_shortcode('next_page_link', array($this, 'next_page_shortcode'));
			add_shortcode('previous_page_link', array($this, 'prev_page_shortcode'));
		}
	
		/**
		 * Short code for next_page_link. Does everything the next_page_link does
		 *
		 * @author aware
		 * @param 	$atts	string	A string of params that will be merged with the defaults
		 * @returns returns a string of either the sibling page's url or a formated html anchor
		 */
		public function next_page_shortcode( $atts ) {
			
			$args = shortcode_atts( $this->defaults, $atts ); // AW: pull in defaults array
			
			return $this->merge_arguments( $args, 'next' );
		}
	
		/**
		 * Does everything the previous_page_link does
		 *
		 * @author aware
		 * @param 	$atts	string	A string of params that will be merged with the defaults
		 * @returns returns a string of either the sibling page's url or a formated html anchor
		 */
		public function prev_page_shortcode( $atts ) {
	
			$args = shortcode_atts( $this->defaults, $atts ); // AW: pull in defaults array
		
			return $this->merge_arguments( $args, 'prev' );
		}
	
		/**
		 * Utility method to cut down on code in both the prev_page and next_page methods
		 * Define our defaults to be used in both the next_page and prev_page methods. Both methods use the same
		 * defaults excluding 'direction'.
		 *
		 * @author aware
		 * @param 	$args		array	array of params for next|previous link customization
		 * @param	$direction	string	Defines the direction (next|prev) to grab the next link (by default this is based on the menu_order)
		 * @returns the merged arguments and the defaults defined within this method.
		 */
		public function merge_arguments ( $args = '', $direction = '' ) {
			
			$args = wp_parse_args( array('direction' => $direction ), $args );
			$args = wp_parse_args( $args, $this->defaults ); // Parse incomming $args into an array and merge it with $defaults
			
			return $this->prev_next( $args );
		}
	
		/**
		 * The techniques needed to get both the previous and the next pages are very similar. To cut down on
		 * code we create the prev_next method which does most of our heavy lifting. While this has optimized
		 * what was build in the original 'next-page-not-next-post' Plugin that this plugin was build off of.
		 *
		 * @todo It could definitely be optimized further.
		 * @author aware, with influence from mmcinvale & banesto
		 * @param 	$args		array	array of params for next|previous link customization
		 * @returns returns a string of either the sibling page's url or a formated html anchor
		 */
		function prev_next( $args = '' ) {
			
			global $post;
		
			extract( $args, EXTR_SKIP );				//AW: Declare each item in $args as its own variable i.e. $type, $before.
			
			$parentID = $post->post_parent;
		
			$getPages = get_pages('child_of=' . $parentID . '&' . $getPagesQuery); //AW: first, we'll query all pages with a similar parent
		
			for($p = 0, $pCount = count($getPages); $p < $pCount; $p++) {
				if ($post->ID == $getPages[$p]->ID) break;	  //AW: get the array key for our entry
			}
			
			$siblings = array(
				'current' => $p,
				'prev' => $p - 1,
				'next' => $p + 1,
				'last' => $pCount - 1
			);
		
			$key = $siblings[ $direction ]; //AW: direction is within our args either 'next' || 'prev'
			$page = $getPages[ $key ];
			
			if ( isset( $page ) && !empty( $page ) ) {
			
				$title = $page->post_title;
				$id = $page->ID;
			
			} elseif ($loop == 'expand') {
		
				if ( $direction == 'prev' ) {
					if ($parentID != 0) {
						$title = get_the_title( $parentID );
						$id = $parentID;
					} else {
						$title = $getPages[ $siblings[ 'last' ] ]->post_title;
						$id = $getPages[ $siblings[ 'last' ] ]->ID;
					}
			
				} elseif ( $direction == 'next' ) {
					
					$parentPage = get_page( $parentID );
					$parentPageID = $parentPage->post_parent;
					$getParentPages = get_pages('child_of=' . $parentPageID . '&' . $getPagesQuery); //AW: query the parent's pages	
		
					for( $pp = 0, $parentPageCount = count($getParentPages); $pp < $parentPageCount; $pp++ ) {
						if ( $post->ID == $getParentPages[$pp]->ID ) break; // AW: break if we find a match to our parent '$pp' key
					}
		
					$parentNextPage = $getParentPages[ $pp + 1 ];
					
					if ( isset( $parentNextPage ) ) {
						$title = $parentNextPage->post_title;
						$id = $parentNextPage->ID;
					}
				}
				
			} elseif ( $loop == 'loop' ) {
				
				$output .= 'loop';
		
				$sibling_key = ($direction == 'prev')? $siblings['last'] : 0;	
				
				$title = $getPages[ $sibling_key ]->post_title;		
				$id = $getPages[ $sibling_key ]->ID;
			}
		
			if($id != $post->id)
				$path = get_permalink($id);
		
			if ($path) {
				
				if( $echo != 'true' ) $path; //AW: return just our path if we don't want to echo the link
	
				if ($label == '')
					$label = $title;
				else
					$label = str_replace('%title', $title, $label); // AW: Added %title functionality before release. Figured people are used to this.
					
				$linkID = isset($link_id)? ' id="' . $link_id . '"' : '';
				$linkCSS = isset($link_class)? ' class="' . $link_class . '"' : '';
				
				$output .= '<a href="' . $path . '" title="' . $title . '"' . $linkCSS . $link_id . '>' . $label . '</a>';
			}
		
			return $output;
		}
	}
	
	$lpNPP = new Linchpin_NextPrevPage();

	/**
	 * simple method to get the next page in your wordpress site
	 *
	 * @author aware
	 * @param 	$args		array	array of params for next|previous link customization
	 * @returns a formatted link to the next sibling page. Loopin to the first page if the 'loop' arguement is defined
	 */
	function next_page_link( $args = '' ) {	
		$lpNPP = new Linchpin_NextPrevPage();
	
		return $lpNPP->merge_arguments( $args, 'next');
	}
	
	/**
	 * simple method to get the previous page with your wordpress site
	 *
	 * @author aware 
	 * @param 	$args		array	array of params for next|previous link customization
	 * @returns a formatted link to the previous sibling page. Looping to the last page if 'loop' argument is defined
	 */
	function previous_page_link( $args = '' ) {
		$lpNPP = new Linchpin_NextPrevPage();
		
		return $lpNPP->merge_arguments( $args, 'prev');
	}
}

?>