<?php
/***** Theme setup *****/

load_theme_textdomain('free01', get_template_directory() . '/languages');
add_theme_support( 'post-thumbnails' );
add_theme_support( 'automatic-feed-links' );

/**	
	*	Add Home page to the menu handling
	*/
function free01_home_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'free01_home_page_menu_args' );

function free01_remove_submenus() {
	global $submenu;
	remove_submenu_page( 'themes.php', 'widgets.php' );
}
add_action('admin_menu', 'free01_remove_submenus');

function free01_setup() {
   
 /***** Navigation & Menu *****/
	$menus = array(
		__('Main menu', 'free01') => __('Main menu', 'free01'),
	);
	foreach ( $menus as $key=>$value  ) {
		if ( !is_nav_menu( $key ) ) wp_update_nav_menu_item( wp_create_nav_menu( $key ), 1 );
	}
	
	if ( function_exists( 'register_nav_menus' ) ) {
		register_nav_menus($menus);
	}
	
	remove_action('wp_head', '_admin_bar_bump_cb');
}
add_action( 'init', 'free01_setup' );

/**
  * Add items to the admin bar
  */
function free01_admin_bar() {
    global $wp_admin_bar;
    if ( !is_super_admin() || !is_admin_bar_showing() )
        return;
    
    $wp_admin_bar->add_menu( array( 'id' => 'wpshower_admin', 'title' => __( 'Expositio Theme', 'free01' ), 'href' => FALSE ) );
    $wp_admin_bar->add_menu( array( 'parent' => 'wpshower_admin', 'title' => __( 'Support Forum', 'free01' ), 'href' => 'http://wpshower.com/forums' ) );
    $wp_admin_bar->add_menu( array( 'parent' => 'wpshower_admin', 'title' => __( 'Theme Options', 'free01' ), 'href' => site_url().'/wp-admin/themes.php?page=theme_options.php' ) );
		
		
}
add_action( 'admin_bar_menu', 'free01_admin_bar', 1000 );

/**
 * 
 */
function free01_get_comments_link($post_id ) {
	
	if (!$post_id) return null;
	$strResult	=	'';
	$zero = __( 'No Comments', 'free01' );
	$one = __( '1 Comment', 'free01' );
	$more = __( '%d Comments', 'free01' );
	
	$obj_count_comments	=	wp_count_comments( $post_id );
	$numTmp	=	$obj_count_comments->approved;
	$strResult .= '<a href="'.get_permalink($post_id) . '#respond'.'" title="'.__('Post comment for this entry', 'free01').'">';
	if ($numTmp > 1) $strResult .= sprintf($more, $numTmp);
	else if ($numTmp == 1) $strResult .= $one;
	else $strResult .= $zero;
	$strResult .= '</a>';
	return	$strResult;
}

/**
 * 
 */
function free01_get_intro($strText, $numTmp	=	200, $strMore='') {
		$strTmp	=	$strText;
		$arrTmp = 	preg_split('/<!--more-->/', $strTmp);
		$strIntro	=	strip_tags($arrTmp[0]);
		if ($numTmp == 0)
		{
			return $strIntro;
		}
		else if (strlen($strIntro) > $numTmp)
		{
			$strIntro	=	@strpos($strIntro, ' ', $numTmp) ? substr($strIntro, 0, strpos($strIntro, ' ', $numTmp)) : $strIntro;
			return $strIntro.$strMore;
		}
		else 
		{
			return $strIntro;
		}
	}

class extended_walker extends Walker_Nav_Menu{
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];

		if ( is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );

		if( ! empty( $children_elements[$element->$id_field] ) )
			array_push($element->classes,'parent');

		$cb_args = array_merge( array(&$output, $element, $depth), $args);

		call_user_func_array(array(&$this, 'start_el'), $cb_args);

		$id = $element->$id_field;

		if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {

			foreach( $children_elements[ $id ] as $child ){

				if ( !isset($newlevel) ) {
					$newlevel = true;
					$cb_args = array_merge( array(&$output, $depth), $args);
					call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($newlevel) && $newlevel ){
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
		}

		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array(&$this, 'end_el'), $cb_args);
	}
}

require_once (TEMPLATEPATH . '/includes/theme_options.php');