=== Plugin Name ===
Contributors: dholloran
Donate link: http://danholloran.com/
Tags: github,commit,octocat
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Widget that grabs a random Octocat from the GitHub octodex and the latest commit from the master branch of a public GitHub repository.

== Description ==
Widget that grabs a random Octocat from the GitHub octodex and the latest commit from the master branch of a public GitHub repository.  It can be used as a normal widget by going to Apperance > Widgets, by adding the shortcode `[wpgrc username="username"]` in your post editor, or by adding `<?php wpgrc( array( 'username' => 'username' ); ?>`.  You can checkout a live demo of all three [here][demo](http://demo.danholloran.com/github-commit-widget-demo/) and get more information about all three under the installation tab.
**Plugin Home Page:** [http://dholloran.github.com/wp-github-recent-commit/](http://dholloran.github.com/wp-github-recent-commit/)
If you have any issues please submit an [issue](https://github.com/DHolloran/wp-github-recent-commit/issues/new) or fix it/submit a pull request I will try to handle it ASAP. You an also contact me at [support@danholloran.com](mailto:support@danholloran.com).

== Installation ==
1. Upload `wp-github-recent-commit` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

**Using the widget**  
1. Go to Appearance > Widgets drag the WP Github Recent Commit widget to the sidebar area of your choice  
2. Fill in your GitHub username and set the rest of the options

**Using the function**  
1. Place this code in your template file  
2. Add a user name and set any other options  
`if ( function_exists('wpgrc') ) {  
	wpgrc( array(  
		'id'				=>	"1", // optional, used for caching purposes
		'username'			=>	'', // required, it just won't work with out it
		'repository'		=>	"", // optional, if not it will just be the last commit from all repos for the username
		'refresh_interval'	=>	"0.5", // optional default 0.5hrs aka 30min
		'show_octocat'		=>	"true", // optional boolean default true
		'octocat_width'		=>	"100", // optional int default 100
		'octocat_height'	=>	"100" // optional int default 100
	) );
}`

**Using the shortcode**  
1. Place in your admin editor `[wpgrc id="1" username="" repository="" refresh_interval="0.5" show_octocat="true" octocat_width="100" octocat_height="100" ]`  
2. Add a user name and set any other options  

== Frequently Asked Questions ==
None so far... If you have any issues please submit an [issue](https://github.com/DHolloran/wp-github-recent-commit/issues/new) or fix it/submit a pull request I will try to handle it ASAP. You an also contact me at [support@danholloran.com](mailto:support@danholloran.com).

== Screenshots ==
1. screenshot1.png
2. screenshot2.png

== Changelog ==

= 1.0 =
Initial Release

= 1.0.1 =
Fixed issue with the owner Repo link having incorrect link.

= 1.1.0 =
Added WPGRC shortcode
Added WPGRC template function
Added cache refresh rate control
Added ability to choose one repository to pull commits from

== Upgrade Notice ==

= 1.0 =
Initial Release

= 1.0.1 =
Fixed issue with the owner Repo link having incorrect link.

= 1.1.0 =
WPGRC now with shortcode and template function.