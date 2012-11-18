<?php

/*
 * Plugin Name: No Category in URL
 * Plugin URI: ttps://github.com/fazleelahhee/no-category-in-wordpress-url
 * Author: Fazle Elahee
 * Description: This is a simple plugins for remove category from URL
 * version: 1.0
 * Author URI: http://careers.stackoverflow.com/fazleelahee
 *  No Category in URL wordpress plugins
    Copyright (C) 2012  Fazle Elahee

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
register_activation_hook( __FILE__, 'func_update_permaLinkstructure' );

function func_update_permaLinkstructure() {
     global $wp_rewrite;
     $catlink = $wp_rewrite->get_category_permastruct();
     
     if ( empty( $catlink ) ) {
        $catlink = home_url('?cat=' . $category_id);
      } else {
        
        $option_name = 'permalink_structure' ;
        $new_value = '/%category%/%postname%/' ;

        if ( get_option( $option_name ) != $new_value ) {
            update_option( $option_name, $new_value );
            $wp_rewrite->set_category_base("/");
            $wp_rewrite->flush_rules();
        }
      }   
}
register_deactivation_hook(__FILE__, 'func_no_more_category_setback_original');

function func_no_more_category_setback_original() {
    $rules = get_option( 'rewrite_rules' );
    unset($rules['(.+)/(.+)/(.+)$']);
    global $wp_rewrite;
    
    $option_name = 'permalink_structure' ;
    $new_value = '/%postname%/' ;

    if ( get_option( $option_name ) != $new_value ) {
        update_option( $option_name, $new_value );
        $wp_rewrite->set_category_base("");
    }
    
    $wp_rewrite->flush_rules();
}

add_action('init', 'func_no_more_category_in_url');

function func_no_more_category_in_url() {
    $uri = $_SERVER['REQUEST_URI'];
    
    if(strpos($uri,"category/")) {
        $uri = str_replace("category/", '', $uri);
        header("HTTP/1.1 301 Moved Permanently"); 
        header("Location: ".site_url().$uri);
        exit;
    }
}

add_filter( 'category_link', 'remove_category_from_permalink', 10, 2 );
function remove_category_from_permalink( $catlink, $category_id )
{
    global $wp_rewrite;
    $catlink = $wp_rewrite->get_category_permastruct();
    

    if ( empty( $catlink ) ) {
        $catlink = home_url('?cat=' . $category_id);
    } else {
        
        $option_name = 'permalink_structure' ;
        $new_value = '/%category%/%postname%/' ;

        if ( get_option( $option_name ) != $new_value ) {
            update_option( $option_name, $new_value );
            $wp_rewrite->set_category_base("/");
            $wp_rewrite->flush_rules();
        } 
     
        $category = &get_category( $category_id );
        $category_nicename = $category->slug;
        $catlink = str_replace( '%category%', $category_nicename, $catlink );
        $catlink = home_url( user_trailingslashit( $catlink, 'category' ) );
        $catlink = str_replace("category/", '',  $catlink);
    }
    return $catlink;
}


add_action( 'wp_loaded','func_no_catgory__flush_rules' );
// flush_rules() if our rules are not yet included
function func_no_catgory__flush_rules(){
	$rules = get_option( 'rewrite_rules' );
	if ( ! isset( $rules['(.+)/(.+)/(.+)$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}
add_filter( 'rewrite_rules_array','func_no_catgory_insert_rewrite_rules' );
// Adding a new rule
function func_no_catgory_insert_rewrite_rules( $rules )
{
	$newrules = array();
	$newrules['(.+)/(.+)/(.+)$'] = 'index.php?category_name=$matches[0]&paged=$matches[2]';
	return $newrules + $rules;
}

function func_no_catgory_fix_pagination( $query ) {
   $ARR_url = explode("/", $_SERVER['REQUEST_URI']);
   if(count($ARR_url) >= 4 && in_array('page', $ARR_url)) {
      // var_dump($ARR_url);
       $idObj = get_category_by_slug($ARR_url[1]);
       if(!empty($idObj)) {
           $query->set( 'category_name', $ARR_url[1] );
           $query->set( 'paged', $ARR_url[3] );
       }
   }
   
}
add_action( 'pre_get_posts', 'func_no_catgory_fix_pagination', 1 );