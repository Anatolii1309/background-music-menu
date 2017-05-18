<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
foreach( $menus as $menu ){
	$items = wp_get_nav_menu_items( $menu );
	foreach( $items as $item ){
		if( $item->type == 'bmm_wp' ){
			wp_delete_post( $item->db_id );
		}
	}
}