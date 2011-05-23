<?php
/**
 * Plugin Name: Navis Quick Posts
 * Description: Makes it easy to quickly create a post from a URL
 * Version: 0.1
 * Author: Marc Lavallee 
 * License: GPLv2
*/
/*
    Copyright 2011 National Public Radio, Inc. 

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

function navis_qp_register_post_types() {
    register_post_type( 'quickpost', array(
        'labels' => array(
            'name' => 'Quick Posts',
            'singular_name' => 'Quick Post',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Quick Post',
            'edit' => 'Edit',
            'edit_item' => 'Edit Quick Post',
            'view' => 'View',
            'view_item' => 'View Quick Post',
            'search_items' => 'Search Quick Posts',
            'not_found' => 'No quick posts found',
            'not_found_in_trash' => 'No quick posts found in Trash',
        ),
        'description' => 'Quick Posts',
        'supports' => array( 'title', 'editor', 'comments', 'author' ),
        'public' => true,
        'menu_position' => 6,
        'taxonomies' => array(),
    ) );
}
add_action( 'init', 'navis_qp_register_post_types' );

