<?php
/**
 * @package WP_Github_Recent_Commit
 * @version 1.0.0
 */
/*
Plugin Name: WP Github Recent commit
Plugin URI: http://dholloran.github.com/wp-github-recent-commit/
Description: Wordpress widget that grabs a random Octocat from the Octodex and the latest commit from a public GitHub repository.
Author: Dan Holloran
Version: 1.0.0
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
* Setup Widget
*/
require_once "classes/class.cache-github-api-v3.php";
require_once "classes/class.github-api-v3.php";
require_once "widget/github-widget.php";
function wpgrc_widgets_init()
{
	register_widget( 'WP_Github_Recent_Commit_Widget' );
}
add_action('widgets_init', 'wpgrc_widgets_init');