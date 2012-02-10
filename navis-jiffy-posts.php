<?php
/**
 * Plugin Name: Navis Jiffy Posts
 * Description: Makes it easy to quickly create a post from a URL
 * Version: 0.1
 * Author: Argo Project
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

class Navis_Jiffy_Posts {
    function __construct() {
        add_action( 'init', array( &$this, 'register_post_type' ) );
        
        add_action( 'wp_print_styles', array( &$this, 'add_stylesheet' ) );
        add_filter( 'post_class', array( &$this, 'add_post_class' ) );

        // make sure jiffy posts show up in the loop
        add_action( 'pre_get_posts', array(&$this, 'add_to_query') );

        if ( ! is_admin() )
            return;

        add_action( 'admin_menu', array( &$this, 'add_options_page' ) );

        add_action( 'admin_init', array( &$this, 'init_settings' ) );


        add_action( 'admin_head-post.php', 
            array( &$this, 'provide_embedly_config' )
        );
        add_action( 'admin_head-post-new.php', 
            array( &$this, 'provide_embedly_config' )
        );

        add_action( 'admin_print_scripts-post.php', 
            array( &$this, 'register_admin_scripts' )
        );
        add_action( 'admin_print_scripts-post-new.php', 
            array( &$this, 'register_admin_scripts' )
        );

        add_action( 
            'admin_print_styles-post.php', array( &$this, 'add_stylesheet' ) 
        );
        add_action( 
            'admin_print_styles-post-new.php', 
            array( &$this, 'add_stylesheet' ) 
        );

        add_action( 
            'admin_print_styles-post.php', 
            array( &$this, 'register_admin_styles' ) 
        );
        add_action( 
            'admin_print_styles-post-new.php', 
            array( &$this, 'register_admin_styles' ) 
        );

        add_action( 'admin_menu', array( &$this, 'add_post_meta_boxes' ) );

        add_filter( 
            'teeny_mce_buttons', 
            array( &$this, 'modify_teeny_mce_buttons' ) 
        );

        add_action( 'save_post', array( &$this, 'save_post' ) );

        add_filter( 'wp_insert_post_data', 
            array( &$this, 'insert_post_content' ) 
        );     
        add_action( 'admin_footer-post.php', array(&$this, 'teeny_mce') );
        add_action( 'admin_footer-post-new.php', array(&$this, 'teeny_mce') );
                
    }
    
    function teeny_mce() {
        if ( 'jiffypost' == get_post_type() )
        wp_tiny_mce( true,
            array(
                'editor_selector' => 'leadintext',
                'setup' => 'tinyMCESetup',
            )
        );

    }
    
    /**
	 * Activation.
	 * source: https://github.com/mfields/mfields-bookmarks
	 * When a user activates this plugin the public pages
	 * for both custom taxonomies and post_types will need
	 * to be immediately available. To ensure that this happens
	 * both post_types and taxonomies need to be registered at
	 * activation so that their rewrite rules will be present
	 * when new rules are added to the database during flush.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	static public function activate() {
		self::register_post_type();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation.
	 * source: https://github.com/mfields/mfields-bookmarks
	 * When a user chooses to deactivate jiffy posts it is
	 * important to remove all custom object rewrites from
	 * the database.
	 *
	 * @return     void
	 * @since      2011-02-20
	 */
	static public function deactivate() {
		flush_rewrite_rules();
	}
    
    function register_post_type() {
        register_post_type( 'jiffypost', array(
            'labels' => array(
                'name' => 'Jiffy Posts',
                'singular_name' => 'Jiffy Post',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Jiffy Post',
                'edit' => 'Edit',
                'edit_item' => 'Edit Jiffy Post',
                'view' => 'View',
                'view_item' => 'View Jiffy Post',
                'search_items' => 'Search Jiffy Posts',
                'not_found' => 'No Jiffy Posts found',
                'not_found_in_trash' => 'No Jiffy Posts found in Trash',
            ),
            'description' => 'Jiffy Posts',
            'supports' => array( 'title', 'comments', 'author' ),
            'public' => true,
            'menu_position' => 6,
            'taxonomies' => array(),
            'rewrite' => array( 'slug' => 'jp' ),
        ) );
    }


    function add_stylesheet() {
        $style_css = plugins_url( 'css/style.css', __FILE__ );
        wp_enqueue_style( 
            'navis-jiffy-post-styles', $style_css, array(), '1.1'
        );
    }


    function add_post_class( $classes ) {
        global $post;
        
        $linktype = get_post_meta( $post->ID, '_linktype', true );
        if ( $linktype )
            $classes[] = 'jiffy-' . $linktype;

        return $classes;
    }


    function register_admin_scripts() {
        if ( 'jiffypost' != get_post_type() )
            return;

        // Embed.ly's JavaScript client
        $libsrc = plugins_url( 
            'js/embedly-jquery/jquery.embedly.js', __FILE__ 
        );
        wp_enqueue_script( 'jquery-embedly', $libsrc, 
            array( 'jquery' ), '2.0.0' 
        );

        // Jeditable plugin
        $jeditsrc = plugins_url(
            'js/jquery_jeditable/jquery.jeditable.js', __FILE__
        );
        wp_enqueue_script( 'jquery-jeditable', $jeditsrc,
            array( 'jquery' ), '1.4.2'
        );

        // Our JS routines
        $oursrc = plugins_url( 'js/navis-jiffy-posts-admin.js', __FILE__ );
        wp_enqueue_script( 'navis-jiffy-posts-admin', $oursrc, 
            array( 'jquery-embedly', 'jquery-jeditable' ), '0.29' 
        );
    }


    function register_admin_styles() {
        if ( 'jiffypost' != get_post_type() )
            return;

        $style_css = plugins_url( 'css/jiffy-posts-admin.css', __FILE__ );
        wp_enqueue_style( 
            'navis-jiffy-post-admin-styles', $style_css, array(), '1.0'
        );
    }


    function add_post_meta_boxes() {
        add_meta_box( 'navisembedurl', 'Embed a URL', 
            array( &$this, 'embed_url_box' ), 'jiffypost', 
            'normal', 'high' 
        );

        add_meta_box( 'navisleadin', 'Lead in text',
            array( &$this, 'embed_leadin_box' ), 'jiffypost', 
            'normal', 'high' 
        );
        
        

        add_meta_box( 'navisviainfo', 'Via',
            array( &$this, 'via_info_box' ), 'jiffypost',
            'normal', 'high'
        );

        add_meta_box( 'navisembedpreview', 'Preview Embed',
            array( &$this, 'embed_preview_box' ), 'jiffypost',
            'normal', 'high'
        );

        add_meta_box( 'navisjiffybookmarklet', 'Jiffy Post Bookmarklet',
            array( &$this, 'bookmarklet_box' ), 'jiffypost',
            'side', 'low'
        );
    }


    function embed_url_box( $post ) {
        $navis_embed_url = get_post_meta( $post->ID, '_navis_embed_url', true );
    ?>
        URL: <input type="text" name="navis_embed_url" id="navis_embed_url" value="<?php echo $navis_embed_url; ?>" style="width: 75%;" />
        <input type="button" class="button" id="submitUrl" value="Embed" label="Embed" />
        <div id="navisActivityIndicator" style="display: none; height: 30px; vertical-align: middle; padding-left: 5px;text-align:center;font-size:14px;vertical-align:middle;margin-top:10px;">
            <img id='activityIndicator' src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>"/> <span class='message'></span>
        </div>
    <?php
    }

    
    function embed_leadin_box( $post ) {
        $leadintext = get_post_meta( $post->ID, '_leadintext', true );
		/***
        wp_tiny_mce( true,
            array(
                'editor_selector' => 'leadintext',
                'setup' => 'tinyMCESetup',
            )
        );
        ***/

    ?>
        <p align="right">
            <a id="edButtonHTML" class="">HTML</a>
            <a id="edButtonPreview" class="active">Visual</a>
        </p>
        <textarea id="leadintext" class="leadintext" name="leadintext" style="width: 98%"><?php echo $leadintext; ?></textarea>
    <?php
    }


    function via_info_box( $post ) {
        $via_name = get_post_meta( $post->ID, '_via_name', true );
        $via_url = get_post_meta( $post->ID, '_via_url', true );
    ?>

        Via name: <input type="text" name="via_name" id="via_name" value="<?php echo $via_name; ?>" style="width: 50%;" /><br />
        Via URL: <input type="text" name="via_url" id="via_url" value="<?php echo $via_url; ?>" style="width: 80%;" />
    <?php
    }


    function embed_preview_box( $post ) {
        $leadintext = get_post_meta( $post->ID, '_leadintext', true );
        $custom_description = get_post_meta( 
            $post->ID, '_custom_description', true 
        );
        $hide_image = get_post_meta( $post->ID, '_hide_image', true );
    ?>

        <div id="content"><div class="jiffypost">
            <p id="leadinPreviewArea"><?php // echo $leadintext; ?></p>
            <div id="embedlyPreviewArea" style="overflow: hidden;"></div>
            <input type="hidden" id="linktype" name="linktype" value="" />
            <input type="hidden" id="provider_name" name="provider_name" value="" />
            <input type="hidden" id="provider_url" name="provider_url" value="" />
            <input type="hidden" id="custom_description" name="custom_description" value="<?php echo esc_attr( $custom_description ); ?>" />
            <input type="hidden" id="embedlyarea" name="embedlyarea" />
            <input type="hidden" id="hide_image" name="hide_image" value="<?php echo esc_attr( $hide_image ); ?>">
        </div></div>

    <?php
        $post_custom_keys = get_post_custom( $post->ID );
        foreach ( $post_custom_keys as $key => $value ) {
            $pubkey = substr( $key, 1 );
            if ( strpos( $key, '_embedly_' ) === 0 ) {
                printf( 
                    '<input type="hidden" class="embedly_meta" ' .
                    'id="%s" name="%s" value="%s" />', 
                    $pubkey, $pubkey, esc_attr( $value[ 0 ] )
                );
            }
        }
    }


    function bookmarklet_box( $post_id ) {
        // Borrowed from get_shortcut_link() in wp-includes/link-template.php
	$link = "javascript:
			var d=document,
			w=window,
			e=w.getSelection,
			k=d.getSelection,
			x=d.selection,
			s=(e?e():(k)?k():(x?x.createRange().text:0)),
			f='" . admin_url('post-new.php') . "',
			l=d.location,
			e=encodeURIComponent,
			u=f+'?post_type=jiffypost&u='+e(l.href)+'&t='+e(d.title)+'&s='+e(s)+'&v=1';
			a=function(){if(!w.open(u,'t','toolbar=1,resizable=1,scrollbars=1,status=1,width=1020,height=640'))l.href=u;};
			if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();
			void(0)";

	$link = str_replace( array("\r", "\n", "\t"),  '', $link );

        echo "To add the bookmarklet, drag the following link to your browser's toolbar: ";
        printf( '<a href="%s" id="jiffyBookmarklet" title="Jiffy This">Jiffy This</a>', $link );
    }


    function modify_teeny_mce_buttons( $buttons ) {
        if ( 'jiffypost' != get_post_type() )
            return $buttons;

        return array( 'bold', 'italic', 'strikethrough', 'link','unlink' ); 
    }
    
	


    function save_post( $post_id ) {
        if ( 'jiffypost' != get_post_type() )
            return;

        // XXX: some of these fields can be purged from here and
        // the various metaboxes above now that the entire embedly
        // response is being automatically stored as metadata
        $fields = array( 'navis_embed_url', 'leadintext', 'embedlyarea',
           'linktype', 'provider_name', 'provider_url', 'via_name',
           'via_url', 'oembedData', 'custom_description', 'hide_image'
        );
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $data = $_POST[ $field ];
                update_post_meta( $post_id, '_' . $field, $data );
            }
        }

        foreach ( $_POST as $field => $value ) {
            if ( strpos( $field, 'embedly' ) === 0 ) {
                $unesc = urldecode( $value );
                update_post_meta( $post_id, '_' . $field, $unesc );
            } 
        }
    }


    function insert_post_content( $data ) {
        if ( 'jiffypost' != get_post_type() )
            return $data;

        $content = '';
        if ( isset( $_POST[ 'leadintext' ] ) ) {
            $content = '<p>' . $_POST[ 'leadintext' ] . '</p>';
        }

        if ( isset( $_POST[ 'embedlyarea' ] ) ) {
            $content .= $_POST[ 'embedlyarea' ];
        }

        $data[ 'post_content' ] = $content;
        return $data;
    }


    function provide_embedly_config() {
        if ( 'jiffypost' != get_post_type() )
            return;

        $embedly_api_key = get_option( 'embedly_api_key' );
        $max_width       = get_option( 'embed_size_w' );
        $max_height      = get_option( 'embed_size_h' );
    ?>
        <script>
            <?php if ( $embedly_api_key ): ?>
                EMBEDLY_API_KEY = '<?php echo $embedly_api_key; ?>';
            <?php endif; ?>
            <?php if ( $max_width ): ?>
                MAX_EMBED_WIDTH = <?php echo $max_width; ?>;
            <?php endif; ?>
            <?php if ( $max_height ): ?>
                MAX_EMBED_HEIGHT = <?php echo $max_height; ?>;
            <?php endif; ?>
        </script>
    <?php
    }


    function init_settings() {
        add_settings_section(
            'navis_jiffy_post_settings', 'Navis Jiffy Post settings', 
            array( &$this, 'settings_callback' ), 'navis_jp' 
        );

        add_settings_field( 
            'embedly_api_key', 'Embedly API Key', 
            array( &$this, 'embedly_key_callback' ), 'navis_jp', 
            'navis_jiffy_post_settings' 
        );
        
        register_setting( 'navis_jp', 'embedly_api_key' );
    }


    function settings_callback() { }

    function embedly_key_callback() {
        $option = get_option( 'embedly_api_key' );
        echo "<input type='text' value='$option' name='embedly_api_key' style='width: 300px;' />"; 
    }

    function add_options_page() {
        add_options_page( 'Jiffy Posts', 'Jiffy Posts', 'manage_options',
                          'navis_jp', array( &$this, 'options_page' ) );
    }
    
    function options_page() {
    ?>
        <div>
            <h2>Navis Jiffy Post</h2>
            <p>If you don't have an embed.ly API key, visit <a href="http://embed.ly/">embed.ly</a> to sign up for a free key.</p>
            <form action="options.php" method="post">
                <?php settings_fields( 'navis_jp' ); ?>
                <?php do_settings_sections( 'navis_jp' ); ?>

                <input name="Submit" type="submit" value="Save Changes" style="margin: 20px 0 0 10px;"/>
            </form>
        </div>
    <?php
    }
    
    function add_to_query( $query ) {
        
        if ( is_admin() || 
             is_single() || 
             is_page() || 
             $query->get('suppress_filters') ) {
            return;
        }
        
        $supported = $query->get( 'post_type' );
        
        if ( !$supported || $supported == 'post' )
            $supported = array( 'post', 'jiffypost' );
        
        elseif ( is_array( $supported ) )
            array_push( $supported, 'jiffypost' );
        $query->set( 'post_type', $supported );
        // error_log('$supported = '.print_r($supported, true));            
    }

}

// add custom post type to the main loop
//add_filter( 'pre_get_posts', 'jp_get_posts' );
/***
    function jp_get_posts( $query ) {
	$var = false;
	if (isset($query->query_vars['suppress_filters'])){
      $var = $query->query_vars['suppress_filters'];
	}
	if ( is_home() && false ==$var ){
      $query->set( 'post_type', array( 'post', 'jiffypost') );
    }
	return $query;
  }
***/


// check to see if the embedly API key has been set
function jiffy_notice_embedly() {
		echo '<div class="error">';
		echo '<p>', sprintf(__('Please enter your <a href="%s">embedly API key</a> to create a Jiffy Post', 'argo'), admin_url('options-general.php?page=navis_jp')), '</p>';
		echo '</div>';        
}

if (get_option('embedly_api_key') == null) { 
	add_action('admin_notices', 'jiffy_notice_embedly');
}

	/* Flush rewrite rules on activation 

	if ( is_admin() && $_GET['activate'] && $_SERVER['SCRIPT_NAME'] == '/wp-admin/plugins.php' ) {

		add_action( 'init', 'flush_rewrite_rules', 12 );
	} */

new Navis_Jiffy_Posts;

