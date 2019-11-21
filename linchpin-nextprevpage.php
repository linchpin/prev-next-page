<?php
/**
 * Plugin Name: PrevNextPage
 * Plugin URI: https://wordpress.org/plugins/linchpin-next-page-link-previous-page-link/
 * Description: Create next/previous navigation for pages. Adds the functions next_page() & prev_page() which link sibling to page(s). If your page doesn't have a direct sibling the plugin will try to go out to the parent of the current page and get the parent's next sibling. If enabled the plugin will also loop from end->beginning and beginning->end. This plugin is based on 'Next and Previous Page not Post' by Matt McInvale. Although it's gone through almost a complete rewrite from his release in 09.  This plugin is a bit more optimized to take advantage of newer plugin techniques. Includes code from banesto as well regarding references to grandparent pages. Also added in additional features such as $args array for easier customization down the road. This plugin is great for utilizing wordpress as a presentation tool.
 * Version: 1.1.0
 * Text Domain: prev-next
 * Domain Path: /languages
 * Author: Linchpin
 * Author URI: https://linchpin.com/?utm_source=prev-next-page&utm_medium=plugin-admin-page&utm_campaign=wp-plugin
 * License: GPLv2 or later
 *
 * @package Mesh
 */

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	exit;
}

/**
 * Define all globals.
 */
define( 'LINCHPIN_PNP_VERSION', '1.1.0' );
define( 'LINCHPIN_PNP_PLUGIN_NAME', esc_html__( 'PrevNextPage', 'prev-next' ) );
define( 'LINCHPIN_PNP__MINIMUM_WP_VERSION', '3.0' );
define( 'LINCHPIN_PNP___PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LINCHPIN_PNP___PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if (!class_exists('Linchpin_NextPrevPage')) {

	/**
	 * @author aware
	 * @package Linchpin contentEngine
	 */

	class Linchpin_NextPrevPage {

		public $defaults = array(
			'label'         => '',
			'loop'          => null, // 'expand', 'loop'
			'getPagesQuery' => 'sort_column=menu_order&sort_order=asc',
			'link_class'    => '',
			'link_id'       => '',
			'echo'          => 'true',
			'direction'     => '' // 'next', 'prev'
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



// Add Columns to Page admin to hide from prev/next
add_filter( 'manage_pages_columns', 'add_admin_prev_next_column' );
add_action( 'manage_pages_custom_column', 'add_admin_prev_next_column_content' );
add_action( 'admin_footer', 'hpn_trigger_checkbox' );
add_action( 'wp_ajax_page_meta_save', 'hpn_checkbox_ajax' );

function add_admin_prev_next_column( $columns_array = '' ) {
	$columns_array['hide_prev_next'] = 'Hide from Prev/Next?';
	return $columns_array;
}


function add_admin_prev_next_column_content( $column = '', $post_id = '' ) {

	if ( 'hide_prev_next' === $column ) {
		$post = get_post( $post_id );
		?>

		<div>
			<input type="checkbox" class="check-hide-prev-next" id="hide-prev-next-<?php echo esc_html( $post->ID ) ?>" data-page-id="<?php echo esc_html( $post->ID ) ?>" <?php checked( 'yes', get_post_meta( get_the_ID(), 'hide_prev_next', true ), true ) ?> />
			<small style="display:block;color:#3FC1D0"></small>
		</div>
		<?php
	}
}


function hpn_trigger_checkbox(){

	echo "<script>jQuery(function($){
		$('.check-hide-prev-next').click(function(){
			var checkbox = $(this),
			    checkbox_value = (checkbox.is(':checked') ? 'yes' : 'no' );
			$.ajax({
				type: 'POST',
				data: {
					action: 'page_meta_save', // wp_ajax_{action} WordPress hook to process AJAX requests
					value: checkbox_value,
					page_id: checkbox.attr('data-page-id'),
					hpn_ajax_nonce : '" . wp_create_nonce( 'activating_checkbox' ) . "'
				},
				beforeSend: function( xhr ) {
					checkbox.prop('disabled', true );
				},
				url: ajaxurl, // as usual, it is already predefined in /wp-admin
				success: function(data){
					checkbox.prop('disabled', false ).next().html(data).show().fadeOut(800);
				}
			});
		});
	});</script>";

}

/**
 * Fires the Ajax
 */
function hpn_checkbox_ajax(){

	check_ajax_referer( 'activating_checkbox', 'hpn_ajax_nonce' );

	if( update_post_meta( $_POST[ 'page_id'] , 'hide_prev_next', $_POST['value'] ) ) {
		echo 'Saved';
	}

	die();
}
