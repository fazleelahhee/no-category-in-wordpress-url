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

add_action('init', 'func_no_more_category_in_url');

function func_no_more_category_in_url() {
    $uri = $_SERVER['REQUEST_URI'];
    
    if(strpos($uri,"category/")) {
        $uri = str_replace("category/", '', $uri);
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
