<?php
// Function/Shortcode Instance
extract( $instance );

// Github API
$config = array( 'function_instance'	=>	$instance );
$github_api = new WPGRC_Github_API_v3( $config );
$widget_content = $github_api->widget_content();

if ( !empty( $widget_content ) AND !isset( $widget_content['error_msg'] ) ) {

	foreach ( $widget_content as $content) {
		extract( $content );
		extract( $octocat );
		$repo_text = explode( '/', $repo_title );
		$owner_name = $repo_text[0];
		$repo_name = $repo_text[1];

		$html .= '<!-- WPGRC WIDGET -->' . $nl;
		if ( $show_avatar == 'on' AND isset( $author_avatar_url ) ) {
			$html.= '<div class="wpgrc-widget pull-left">';
			$html.= '<div class="wpgrc-commit-octocat polaroid pull-left">';
			$html.= '<img class="pull-left" src="'.$author_avatar_url.'" alt="" width="'.intval( $octocat_width ).'" height="'.intval( $octocat_height ).'">';
			$html.= '</div> <!-- /.wpgrc-commit-octocat -->';
		} elseif ( $show_octocat ) {
			$html .= '<div class="wpgrc-widget pull-left">' . $nl;
			$html .= '<div class="wpgrc-commit-octocat polaroid pull-left">' . $nl;
			$html .= '<img class="pull-left" src="'.$octocat_image_url.'" alt="'.$octocat_name.'" width="'.intval( $octocat_width ).'" height="'.intval( $octocat_height ).'">' . $nl;
			$html .= '</div> <!-- /.wpgrc-commit-octocat -->' . $nl;
		} else {
			$html .= '<div class="wpgrc-widget no-octocat pull-left">' . $nl;
		}

		$html .= '<ul class="pull-left wpgrc-commit-info-wrap">' . $nl;
		$html .= '<li class="clear wpgrc-commit-repo-title">' . $nl;
		$html .= '<a href="https://github.com/'.$owner_name.'" class="wpgrc-repo-owner" target="_blank">'.$owner_name.'</a>' . $nl;
		$html .= '<span>/</span>' . $nl;
		$html .= '<a href="'.$repo_url.'" " class="wpgrc-repo-url" target="_blank">'.$repo_name.'</a>' . $nl;
		$html .= '</li>' . $nl;
		$html .= '<li class="clear wpgrc-commit-message pull-left">'.$message.'</li>' . $nl;
		$html .= '<li class="clear wpgrc-commit-author-wrap pull-left">' . $nl;
		$html .= '<span>Commited by:&nbsp;</span>' . $nl;
		$html .= '<a href="'.$author_url.'" class="wpgrc-commit-author" target="_blank">'.$author.'</a>' . $nl;
		$html .= '</li>' . $nl;
		$html .= '</ul> <!-- /. wpgrc-commit-info-wrap -->' . $nl;
		$html .= '</div> <!-- /.wpgrc-widget -->' . $nl;
		$html .= '<!-- END WPGRC WIDGET -->' . $nl;
	} // foreach()
} else {
	if ( isset( $widget_content['error_msg'] ) ) {
		$error_msg = $widget_content['error_msg'];
	} else {
		$error_msg = 'I am sorry, something went wrong when contacting GitHub please try again later.';
	} // if/else()
	$html .= '<!-- WPGRC WIDGET ERROR -->' . $nl;
	$html .= '<div class="wpgrc-error">'.$error_msg.'</div>' . $nl;
	$html .= '<!-- END WPGRC WIDGET ERROR -->' . $nl;
} // if/else(!empty($widget_content))