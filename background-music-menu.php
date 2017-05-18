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
			add_action('wp_enqueue_scripts', array($this, 'load_my_scripts'));
		}

		/*
		* Install js and css.
		*/
		function load_my_scripts() {

			if ( !empty( get_option( 'bmm-sound' ) ) ) {

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
			if ( !empty( get_option( 'bmm-sound' ) ) ) {
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

					description = '<div class="sound-section"><audio id="audio1" autoplay loop src="<?php echo get_option('bmm-sound');?>"></audio><button id="sound-frame" class="sound-frame-class play-sound" type="button"><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i><i id="icon-sound"></i></button></div>';

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
				<p><?php esc_attr_e( 'Please configure the plugin.' ); ?></p>
				<p><?php esc_attr_e( 'Go to Settings -> Background Music Menu. Or click' ); ?> <a href="<?php echo admin_url( 'options-general.php?page=background_music_menu_settings' ); ?>"><?php esc_attr_e( 'here' ); ?></a>.</p>
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
			$nonce = $_POST[ 'description-nonce' ];
			if ( !wp_verify_nonce( $nonce, 'gs-sim-description-nonce' ) ) {
				die();
			}

			$item = $_POST[ 'menu-item' ];

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

			$menu_items_data = array();
			foreach ( (array) $_POST[ 'menu-item' ] as $menu_item_data ) {
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

	}

}

// Initiate once plugins have been loaded
add_action( 'plugins_loaded', 'wp_backgroundmusicmenu');

/*
* This function calls the class BackgroundMusicMenu defined above.
* This means the class above behaves like a module or
*/	
function wp_backgroundmusicmenu() {

	$bmm_ins		 = new BackgroundMusicMenu();
	$bmm_ins_init = $bmm_ins->init();
}


// add page for admin menu
add_action('admin_menu', 'bmm_add_admin_page');

/*
* Create a plugin page.
*/	
function bmm_add_admin_page() {
	    add_submenu_page ( 'options-general.php', 'Background Music Menu Page', 'Background Music Menu', 'manage_options', 'background_music_menu_settings', 'bmm_all_settings_page');
}


// Save Custom Variables.
if ( is_admin() ) {
	add_action( 'admin_init', 'bmm_save_settings' );
}

/*
* Registering Custom Variables.
*/	
function bmm_save_settings() {
		register_setting( 'bmm-settings-group', 'bmm-sound' );
		register_setting( 'bmm-settings-group', 'bmm-margin-top' );
		register_setting( 'bmm-settings-group', 'bmm-margin-botton' );
		register_setting( 'bmm-settings-group', 'bmm-margin-right' );
		register_setting( 'bmm-settings-group', 'bmm-margin-left' );	
}


/*
* Page plugin.
*/
function bmm_all_settings_page() {
	if ( is_admin() ) {
		?>
		<div class="wrap">
		<h2><?php esc_attr_e( 'Background Music Menu' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'bmm-settings-group' ); ?>
			<?php do_settings_sections( 'bmm-settings-group' );?>
			<h4><?php esc_attr_e( 'Get started' ); ?>:</h4>
			<p> <?php esc_attr_e( 'Go to Appearance -> Menus and open the Background Music Menu, click add' ); ?>.</p>
			<table class="form-table">
				
				<tr>
					<th scope="row"><?php esc_attr_e( 'Url sound' ); ?>:</th>
					<td>
						<input type="text" name="bmm-sound" value="<?php echo get_option('bmm-sound'); ?>" style="width: 100%;" required />
					</td>
				</tr>

				<tr>
					<th colspan="2"><?php esc_attr_e( 'Fill in these fields if you need to align the icon' ); ?>.</th>
				</tr>

				<tr>
					<th scope="row"><?php esc_attr_e( 'Margin top' ); ?>:</th>
					<td>
						<input type="number" name="bmm-margin-top" value="<?php echo get_option('bmm-margin-top'); ?>" /> px
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_attr_e( 'Margin right' ); ?>:</th>
					<td>
						<input type="number" name="bmm-margin-right" value="<?php echo get_option('bmm-margin-right'); ?>" /> px
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_attr_e( 'Margin botton' ); ?>:</th>
					<td>
						<input type="number" name="bmm-margin-botton" value="<?php echo get_option('bmm-margin-botton'); ?>" /> px
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_attr_e( 'Margin left' ); ?>:</th>
					<td>
						<input type="number" name="bmm-margin-left" value="<?php echo get_option('bmm-margin-left'); ?>" /> px
					</td>
				</tr>


			</table>
			<?php submit_button(); ?>
		</form>
		</div>
		<?php 
	}
}

// add style to header
add_action('wp_head','bmm_add_css');

/*
* Setting styles.
*/
function bmm_add_css() {

	if ( !empty( get_option( 'bmm-margin-top' ) ) ) {
		$bmm_margin_top = get_option('bmm-margin-top');
	} else {
		$bmm_margin_top = '0';
	}

	if ( !empty( get_option( 'bmm-margin-botton' ) ) ) {
		$bmm_margin_botton = get_option('bmm-margin-botton');
	} else {
		$bmm_margin_botton = '0';
	}

	if ( !empty( get_option( 'bmm-margin-right' ) ) ) {
		$bmm_margin_right = get_option('bmm-margin-right');
	} else {
		$bmm_margin_right = '0';
	}

	if ( !empty( get_option( 'bmm-margin-left' ) ) ) {
		$bmm_margin_left = get_option('bmm-margin-left');
	} else {
		$bmm_margin_left = '0';
	}
			
	?>
	<style type="text/css">
		button#sound-frame {
			margin: <?php echo ( $bmm_margin_top . 'px ' . $bmm_margin_left . 'px ' . $bmm_margin_botton . 'px ' . $bmm_margin_right . 'px' ); ?>;
		}
	</style>
	<?php
}