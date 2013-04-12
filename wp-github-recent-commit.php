<?php
/**
 * @package WP_Github_Recent_Commit
 * @version 1.1.1
 */
/*
Plugin Name: WP Github Recent commit
Plugin URI: http://dholloran.github.com/wp-github-recent-commit/
Description: Wordpress widget that grabs a random Octocat from the Octodex and the latest commit from a public GitHub repository.
Author: Dan Holloran
Version: 1.1.1
Author URI: http://danholloran.com/
*/


/**
* Handles Activation/Deactivation/Install
*/
require_once "classes/class.wpgrc-init.php";
register_activation_hook( __FILE__, array( 'WPGRC_Init', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'WPGRC_Init', 'on_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'WPGRC_Init', 'on_uninstall' ) );

/**
* WPGRC Widget
*/
require_once "classes/class.cache-github-api-v3.php";
require_once "classes/class.github-api-v3.php";
require_once "widget/github-widget.php";
function wpgrc_widgets_init()
{
	register_widget( 'WP_Github_Recent_Commit_Widget' );
}
add_action('widgets_init', 'wpgrc_widgets_init');

/**
* WPGRC Shortcode
*/
function wpgrc_shortcode( $atts ) {
	return get_wpgrc( $atts );
}
add_shortcode( 'wpgrc','wpgrc_shortcode' );


/**
* WPGRC Function
*/
function wpgrc( $args )
{
	echo get_wpgrc( $args );
}


/**
* Get WPGRC
*/
function get_wpgrc( $args )
{
	$defaults = array(
		'id'								=>	"1",
		'username'					=>	'',
		'repository'				=>	"",
		'refresh_interval'	=>	"0.5",
		'show_octocat'			=>	"true",
		'octocat_width'			=>	"100",
		'octocat_height'		=>	"100"
	);
	$instance = shortcode_atts( $defaults, $args );
	$html = '';
	$nl = "\n";
	include "views/view-github-widget.php";
	return $html;
}

