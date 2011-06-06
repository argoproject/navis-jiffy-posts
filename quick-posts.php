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

class Navis_Quick_Posts {
    function __construct() {
        add_action( 'init', array( &$this, 'register_post_type' ) );

        add_action( 'admin_print_scripts-post.php', 
            array( &$this, 'register_admin_scripts' )
        );
        add_action( 'admin_print_scripts-post-new.php', 
            array( &$this, 'register_admin_scripts' )
        );

        add_action( 'admin_menu', array( &$this, 'add_post_meta_boxes' ) );

        add_action( 'admin_menu', array( &$this, 'add_options_page' ) );
    }


    function register_post_type() {
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


    function register_admin_scripts() {
        if ( 'quickpost' != get_post_type() )
            return;

        // Embed.ly's JavaScript client
        $libsrc = plugins_url( 
            'js/embedly-jquery/jquery.embedly.js', __FILE__ 
        );
        wp_enqueue_script( 'jquery-embedly', $libsrc, 
            array( 'jquery' ), '2.0.0' 
        );

        // Our JS routines
        $oursrc = plugins_url( 'js/navis-quick-posts-admin.js', __FILE__ );
        wp_enqueue_script( 'navis-quick-posts-admin', $oursrc, 
            array( 'jquery-embedly' ), '0.1' 
        );
    }


    function add_post_meta_boxes() {
        add_meta_box( 'navisembedurl', 'Embed a URL', 
            array( &$this, 'embed_url_box' ), 'quickpost', 
            'side', 'core' 
        );

        add_meta_box( 'navisembedpreview', 'Preview Embed',
            array( &$this, 'embed_preview_box' ), 'quickpost',
            'normal', 'high'
        );
    }


    function embed_url_box() {
    ?>
        URL: <input type="text" name="navis_embed_url" id="navis_embed_url" value="" style="width: 80%;" />
        <input type="button" class="button" id="submitUrl" value="Embed" label="Embed" />
    <?php
    }


    function embed_preview_box() {
    ?>
        <div id="embedlyPreviewArea"></div>
    <?php
    }

    function add_options_page() {
        add_options_page( 'Quick Posts', 'Quick Posts', 'manage_options',
                          'navis_qp', array( &$this, 'options_page' ) );
    }


    function options_page() {
    ?>
        <div>
            <h2>Navis Quick Posts</h2>
            <form action="options.php" method="post">
                <?php settings_fields( 'navis_qp' ); ?>
                <?php do_settings_sections( 'navis_qp' ); ?>

                <input name="Submit" type="submit" value="<?php esc_attr( 'Save Changes' ); ?>" />
            </form>
        </div>
    <?php
    }

}

new Navis_Quick_Posts;
