<?php
/*
Plugin Name: CDN Enabler Replace Content
Text Domain: cdn-enabler-replace-content
Description: This plugin requires CDN ENABLER Plugin allows you to replace multiple contents and having full control to rewrite your content to your cdn.
Version: 1.1.0
Author: Jundell Agbo
Author URI: https://profiles.wordpress.org/jundellagbo/
License: GPLv2 or later
*/

// include vendor libraries :)
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// boot carbon fields
use Carbon_Fields\Container;
use Carbon_Fields\Field;
add_action( 'after_setup_theme', 'cdn_enabler_replace_content_crb_load' );
function cdn_enabler_replace_content_crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}

// display notice if CDN Enabler is not installed
function cdn_enabler_replace_content_notice_plugin_requirements() {
    if( ! defined('CDN_ENABLER_FILE') ):
        echo '<div class="notice notice-error">
            <p>Please install <a href="https://wordpress.org/plugins/cdn-enabler/" target="_blank">CDN Enabler</a> to Enable CDN Enabler Replace Content plugin.</p>
        </div>';
    endif;
}
add_action('admin_notices', 'cdn_enabler_replace_content_notice_plugin_requirements');

// settings in admin allows you to enter your replace content
add_action( 'carbon_fields_register_fields', 'cdn_enabler_replace_content_carbon_fields_settings' );
function cdn_enabler_replace_content_carbon_fields_settings() {
    Container::make( 'theme_options', 'CDN Enabler Replace Contents' )
    ->set_page_parent( 'options-general.php' )
    ->add_fields( array(
        Field::make( 'complex', 'cdn_enabler_contents_to_replace', __( '' ) )
        ->set_collapsed( false )
        ->add_fields( array(
            Field::make( 'textarea', 'cdn_enabler_replace_from', 'Content' )->set_width( 50 ),
            Field::make( 'textarea', 'cdn_enabler_replace_to', 'Replace To' )->set_width( 50 )
        ))
        ->set_header_template( 'Replace your content using these settings below.' )
    ));
}

// process for rewriting contents here using cdn_enabler_contents_after_rewrite filter after theme setup
add_action( 'setup_theme', 'cdn_enabler_contents_after_theme', 10, 2 ); 
function cdn_enabler_contents_after_theme() {
    // apply filter for rewrites
    if( defined('CDN_ENABLER_FILE') ):
        add_filter( 'cdn_enabler_contents_after_rewrite', 'cdn_enabler_replace_content_filter_cdn_enabler_contents_after_rewrite' ); 
    endif;
}


function cdn_enabler_replace_content_filter_cdn_enabler_contents_after_rewrite( $rewritten_contents ) {
    $contents_to_rewrite = carbon_get_theme_option( 'cdn_enabler_contents_to_replace' );
    foreach( $contents_to_rewrite as $rewriting ) {
        // not necessary to replace them if they are the same value, LOL :)
        // we disregard if from content is empty
        if( (isset($rewriting['cdn_enabler_replace_from']) && !empty($rewriting['cdn_enabler_replace_from'])) && ($rewriting['cdn_enabler_replace_from'] != $rewriting['cdn_enabler_replace_to']) ) {
            // replace contents after rewrite
            $rewritten_contents = str_replace( $rewriting['cdn_enabler_replace_from'], $rewriting['cdn_enabler_replace_to'], $rewritten_contents );
        }
    }
    return $rewritten_contents;
}