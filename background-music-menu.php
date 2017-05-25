<?php
/*
   Plugin Name: Background Music Menu
   Plugin URI: http://novgrodit.com/work/wp/plugins/background-music-or-sound-for-websites-plugin-wordpress/
   Description: Background Music or sound for websites plugin wordpress. Created to give a mood to your users.
   Version: 1.0.0
   Author: Anatoli Navahrodsky
   Author URI: http://glanit.com/
   License: GPL2
*/
/*  Copyright 2017  Anatoli Navahrodsky  (email : anatolii1309 {at} tut.by)

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

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

if ( !class_exists( 'BackgroundMusicMenu' )  && ! function_exists( 'BackgroundMusicMenu' ) ) {

    class BackgroundMusicMenu {
		public function __construct() {

			add_action( 'init', array( $this, 'init' ) );

			add_action( 'init', array( $this, 'start_unit' ) );

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			add_action( 'admin_init', array( $this, 'admin_unit' ) );

			add_action( 'wp_head', array( $this, 'bmm_add_css' ) );
		}

		public function init() {	

			// add a meta-box in the menu
			add_action( 'admin_init', array( $this, 'setting_meta_box' ) );

			// processing the query as html
			add_filter( 'walker_nav_menu_start_el', array( $this, 'start_el' ), 10, 2 );

			//decorates a menu item object with the shared navigation menu
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'decorates_item' ), 10, 1 );

			//save html through a post request
			add_action( 'wp_ajax_bmm_wp_table_change', array( $this, 'table_change' ) );

			//ajax handler for adding a menu item
			add_action( 'wp_ajax_add-menu-item', array( $this, 'ajax_add_menu_item' ), 0 );

			//add files js and css
			add_action( 'wp_enqueue_scripts', array( $this, 'load_my_scripts' ) );
		}

		/*
		* Plugin Options.
		*/
		public function start_unit() {

			$this->options = array_merge( array(
				'bmm-sound' 		=> '',
				'bmm-margin-top' 	=> '',
				'bmm-margin-right' 	=> '',
				'bmm-margin-botton' => '',
				'bmm-margin-left' 	=> '',
			), (array) get_option( 'background-music-menu', array() ) );
		}

		/*
		* Plugin page.
		*/
		public function admin_unit () {

			register_setting( 	'background-music-menu', 'background-music-menu', array( $this, 'sanitize_options' ) );
			add_settings_section( 'bmm_general', false, '__return_false', 'background-music-menu' );
			add_settings_field( 'bmm-sound', __( 'Url sound:', 'background-music-menu' ), array( $this, 'field_code_url' ), 'background-music-menu', 'bmm_general' );
			add_settings_field( 'add-text-margin', __( 'Fill in these fields if you need to align the icon.', 'background-music-menu' ), '', 'background-music-menu', 'bmm_general' );
			add_settings_field( 'bmm-margin-top', __( 'Margin top:', 'background-music-menu' ), array( $this, 'field_code_margin_top' ), 'background-music-menu', 'bmm_general' );
			add_settings_field( 'bmm-margin-right', __( 'Margin right:', 'background-music-menu' ), array( $this, 'field_code_margin_right' ), 'background-music-menu', 'bmm_general' );
			add_settings_field( 'bmm-margin-botton', __( 'Margin botton:', 'background-music-menu' ), array( $this, 'field_code_margin_botton' ), 'background-music-menu', 'bmm_general' );
			add_settings_field( 'bmm-margin-left', __( 'Margin left:', 'background-music-menu' ), array( $this, 'field_code_margin_left' ), 'background-music-menu', 'bmm_general' );
		}

		/*
		* Field url.
		*/
		public function field_code_url() {

			?>
			<input name="background-music-menu[bmm-sound]" id="bmm-sound" type="text" value="<?php echo esc_url( $this->options['bmm-sound'] ); ?>" style="width: 100%;" required />

			<?php
		}

		/*
		* Field margin top.
		*/
		public function field_code_margin_top() {

			?>
				<input type="number" name="background-music-menu[bmm-margin-top]" value="<?php echo absint( $this->options['bmm-margin-top'] ); ?>" /> px
			<?php
		}

		/*
		* Field margin right.
		*/
		public function field_code_margin_right() {

			?>
				<input type="number" name="background-music-menu[bmm-margin-right]" value="<?php echo absint( $this->options['bmm-margin-right'] ); ?>" /> px
			<?php
		}

		/*
		* Field margin botton.
		*/
		public function field_code_margin_botton() {

			?>
				<input type="number" name="background-music-menu[bmm-margin-botton]" value="<?php echo absint( $this->options['bmm-margin-botton'] ); ?>" /> px
			<?php
		}

		/*
		* Field margin left.
		*/
		public function field_code_margin_left() {

			?>
				<input type="number" name="background-music-menu[bmm-margin-left]" value="<?php echo absint( $this->options['bmm-margin-left'] ); ?>" /> px
			<?php
		}

		/*
		* Sanitize options plugin.
		*/
		public function sanitize_options( $input ) {

			$output = array();

			if ( isset( $input['bmm-sound'] ) ) {
				$output['bmm-sound'] = ( esc_url( $input['bmm-sound'] ) );
			}

			if ( isset( $input['bmm-margin-top'] ) ) {
				$output['bmm-margin-top'] = ( absint( $input['bmm-margin-top'] ) );
			}

			if ( isset( $input['bmm-margin-right'] ) ) {
				$output['bmm-margin-right'] = ( absint( $input['bmm-margin-right'] ) );
			}

			if ( isset( $input['bmm-margin-botton'] ) ) {
				$output['bmm-margin-botton'] = ( absint( $input['bmm-margin-botton'] ) );
			}

			if ( isset( $input['bmm-margin-left'] ) ) {
				$output['bmm-margin-left'] = ( absint( $input['bmm-margin-left'] ) );
			}

			return $output;
		}

		/*
		* Install js and css.
		*/
		function load_my_scripts() {

			if ( !empty( esc_url( $this->options['bmm-sound'] ) ) ) {

				wp_enqueue_script( 'sound-script', plugins_url( '/assets/js/background-music-menu.min.js', __FILE__ ), array( 'jquery' ) );

				wp_enqueue_style( 'style-name', plugins_url( '/assets/css/background-music-menu.css', __FILE__ ) );
			}
		}

		/*
		* The next element we get.
		*/
		public function next_menu_id( $last_menu_id ) {

			$menu_id = (int) $last_menu_id;

			$menu_id++;

			$menu_id = ($menu_id < 1) ? 1 : $menu_id;

			update_option( 'bmm_wp_last_menu_id', $menu_id );

			return $menu_id;
		}

		/*
		* Add meta box to nav-menus.php.
		*/
		public function setting_meta_box() {
			add_meta_box( 'add-background-music-section', __( 'Background Music Menu' ), array( $this, 'background_music_box' ), 'nav-menus', 'side', 'default' );
		}

		/*
		* Setting meta box.
		*/
		public function background_music_box() {
			global $_nav_menu_placeholder, $nav_menu_selected_id;
			$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
			$last_menu_id	 = get_option( 'bmm_wp_last_menu_id', 0 );
			$menu_id		 = $this->next_menu_id( $last_menu_id );
			if ( !empty( esc_url( $this->options['bmm-sound'] ) ) ) {
			?>
			<div id="section_background_music">
				<p class="button-controls">
					<span class="add-to-menu">
						<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" id="send_background_music" />
						<span class="clicks"></span>
					</span>
				</p>
			</div>
			<script>
			jQuery( 'document' ).ready( function () {

				jQuery( '#send_background_music' ).on( 'click', function ( e ) {
					wpNavMenu.registerChange();

					AddCustomMenu();
				} );


				function AddCustomMenu( ) {

					description = '<div class="sound-section"><audio id="audio1" autoplay loop src="<?php echo esc_url( $this->options['bmm-sound'] ); ?>"></audio><button id="sound-frame" class="sound-frame-class play-sound" type="button"><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i></button></div>';

					menuItems = { };

					processMethod = wpNavMenu.addMenuItemToBottom;

					var e = jQuery( '#section_background_music' );

					e.find( '.clicks' ).show();

					nonce = "<?php echo wp_create_nonce( 'gs-sim-description-nonce' ) ?>";

					baseparam = {
							'menu-item-db-id' : "0",
							'menu-item-description' : description,
							'menu-item-title' : 'Sound Icon',
							'menu-item-object' : "bmm_wp",
							'menu-item-object-id' : "<?php echo $menu_id; ?>",
							'menu-item-type' : "bmm_wp"
						};
					params = {
						'action': 'bmm_wp_table_change',
						'description-nonce': nonce,
						'menu-item': baseparam
					};
					menuItems = {
						'<?php echo $_nav_menu_placeholder; ?>' : baseparam 
					}
					jQuery.post( ajaxurl, params, function ( menuid ) {

						menuid = "<?php echo $menu_id; ?>";

						wpNavMenu.addItemToMenu( menuItems, processMethod, function () {
							e.find( '.clicks' ).hide();
						} );
					} );
				}
			} );
			</script>
			<?php
			} else {
			?>
				<p><?php _e( 'Please configure the plugin.' ); ?></p>
				<p><?php _e( 'Go to Settings -> Background Music Menu. Or click' ); ?> <a href="<?php echo admin_url( 'options-general.php?page=background-music-menu' ); ?>"><?php _e( 'here' ); ?></a>.</p>
			<?php 
			}
		}

		/*
		* Whether the passed content contains the specified shortcode.
		*/
		function has_shortcode( $content ) {

			if ( false !== strpos( $content, '[' ) ) {

				preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );

				if ( !empty( $matches ) ) {
					return true;
				}
			}
			return false;
		}

		/*
		* Change the link to html.
		*/
		public function start_el( $item_output, $item ) {
			if ( $item->object != 'bmm_wp' ) {

				if ( $item->post_title == 'FULL HTML OUTPUT' ) {

					$item_output = do_shortcode( $item->url );
				} else {
					$item_output = do_shortcode( $item_output );
				}

			} else {
				$item_output = do_shortcode( $item->description );
			}

			return $item_output;
		}

		/*
		* Modify the menu item.
		*/
		public function decorates_item( $item ) {

			if ( !is_object( $item ) )
				return $item;

			// only if it is our object
			if ( $item->object == 'bmm_wp' ) {

				// setup our label
				$item->type_label = __( 'Background Music Menu' );

				if ( $item->post_content != '' ) {
					$item->description = $item->post_content;
				} else {

					// set up the description from the transient
					$item->description = get_transient( 'bmm_wp_table_change_' . $item->menu_id );

					// discard the transient
					delete_transient( 'bmm_wp_table_change_' . $item->menu_id );
				}
			}
			return $item;
		}
		
		/*
		* Create a post request to change the record in the database.
		*/
		public function table_change() {
			$nonce = sanitize_text_field( $_POST['description-nonce'] );
			update_post_meta( $post->ID, 'description-nonce', $nonce );
			if ( !wp_verify_nonce( $nonce, 'gs-sim-description-nonce' ) ) {
				die();
			}

			$item = sanitize_text_field( $_POST['menu-item'] );
			update_post_meta( $post->ID, 'menu-item', $item );

			set_transient( 'bmm_wp_table_change_' . $item[ 'menu-item-object-id' ], $item[ 'menu-item-description' ] );

			// increment the object id, so it can be used by js
			$menu_id = $this->next_menu_id( $item[ 'menu-item-object-id' ] );

			echo $menu_id;

			die();
		}

		/*
		* Ajax handler for adding a menu item.
		* https://developer.wordpress.org/reference/functions/wp_ajax_add_menu_item/
		*/
		public function ajax_add_menu_item() {

			check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );

			if ( !current_user_can( 'edit_theme_options' ) )
				wp_die( -1 );

			require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

			// For performance reasons, we omit some object properties from the checklist.
			// The following is a hacky way to restore them when adding non-custom items.

			$itembmm = ( current_user_can( 'unfiltered_html' ) ) ? $_POST[ 'menu-item' ] : wp_kses_post( $_POST[ 'menu-item' ] );;
			
			$menu_items_data = array();
			foreach ( (array) $itembmm as $menu_item_data ) {
				if (
				!empty( $menu_item_data[ 'menu-item-type' ] ) &&
				'custom' != $menu_item_data[ 'menu-item-type' ] &&
				'bmm_wp' != $menu_item_data[ 'menu-item-type' ] &&
				!empty( $menu_item_data[ 'menu-item-object-id' ] )
				) {
					switch ( $menu_item_data[ 'menu-item-type' ] ) {
						case 'post_type' :
							$_object = get_post( $menu_item_data[ 'menu-item-object-id' ] );
							break;

						case 'taxonomy' :
							$_object = get_term( $menu_item_data[ 'menu-item-object-id' ], $menu_item_data[ 'menu-item-object' ] );
							break;
					}

					$_menu_items = array_map( 'wp_setup_nav_menu_item', array( $_object ) );
					$_menu_item	 = reset( $_menu_items );

					// Restore the missing menu item properties
					$menu_item_data[ 'menu-item-description' ] = $_menu_item->description;
				}

				$menu_items_data[] = $menu_item_data;
			}

			$item_ids = wp_save_nav_menu_items( 0, $menu_items_data );
			if ( is_wp_error( $item_ids ) )
				wp_die( 0 );

			$menu_items = array();

			foreach ( (array) $item_ids as $menu_item_id ) {
				$menu_obj = get_post( $menu_item_id );
				if ( !empty( $menu_obj->ID ) ) {
					$menu_obj		 = wp_setup_nav_menu_item( $menu_obj );
					$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
					$menu_items[]	 = $menu_obj;
				}
			}

			/** This filter is documented in wp-admin/includes/nav-menu.php */
			$walker_class_name = apply_filters( 'wp_edit_nav_menu_walker', 'Walker_Nav_Menu_Edit', $_POST[ 'menu' ] );

			if ( !class_exists( $walker_class_name ) )
				wp_die( 0 );

			if ( !empty( $menu_items ) ) {
				$args = array(
					'after'			 => '',
					'before'		 => '',
					'link_after'	 => '',
					'link_before'	 => '',
					'walker'		 => new $walker_class_name,
				);
				echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
			}
			wp_die();
		}

		/*
		* Create a plugin page.
		*/	
		public function admin_menu() {
			add_options_page( __( 'Background Music Menu', 'background-music-menu' ), __( 'Background Music Menu', 'background-music-menu' ), 'manage_options', 'background-music-menu', array( $this, 'render_options' ) );
		}

		/*
		* Render options.
		*/
		public function render_options() {
			?>
			<div class="wrap">
				<h2><?php _e( 'Background Music Menu', 'background-music-menu' ); ?></h2>
				<p><?php _e( 'Get started:', 'background-music-menu' ); ?></p>
				<p><?php _e( 'Go to Appearance -> Menus and open the Background Music Menu, click add.', 'background-music-menu' ); ?></p>
				<form action="options.php" method="POST">
					<?php settings_fields( 'background-music-menu' ); ?>
					<?php do_settings_sections( 'background-music-menu' ); ?>
					<?php submit_button( __( 'Update Options', 'background-music-menu' ) ); ?>
				</form>
			</div>
			<?php
		}

		/*
		* Setting add styles.
		*/
		public function bmm_add_css() {

			?>
			<style type="text/css">
				button#sound-frame {
					margin: <?php echo ( absint( $this->options['bmm-margin-top'] ) . 'px ' . absint( $this->options['bmm-margin-left'] ) . 'px ' . absint( $this->options['bmm-margin-botton'] ) . 'px ' . absint( $this->options['bmm-margin-right'] ) . 'px' ); ?>;
				}
			</style>
			<?php
		}
		
	}

	$GLOBALS['background_music_menu'] = new BackgroundMusicMenu;
}
