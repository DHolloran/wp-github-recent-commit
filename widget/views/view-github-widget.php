<?php

// Widget Instance
extract( $args );
extract($instance);
$octocat_height = ( isset( $octocat_size_height ) AND is_numeric( $octocat_size_height ) ) ? floor( $octocat_size_height ) : 100;
$octocat_width = ( isset( $octocat_size_width ) AND is_numeric( $octocat_size_width ) ) ? floor( $octocat_size_width ) : 100;

// Github API
$github_api = new WPGRC_Github_API_v3( strtolower( $github_username ), $widget_id );
$widget_content = $github_api->widget_content();
if ( !empty( $widget_content ) ) {
	extract( $widget_content );
	extract( $octocat );
	$repo_text = explode( '/', $repo_title );
	$owner_name = $repo_text[0];
	$repo_name = $repo_text[1];
	?>

	<!-- WPGRC WIDGET -->
	<?php if ( $show_octocat == 'on' ): ?>
	<div class="wpgrc-widget pull-left">
		<div class="wpgrc-commit-octocat polaroid pull-left">
			<img class="pull-left" src="<?php echo $octocat_image_url; ?>" alt="<?php echo $octocat_name; ?>" width="<?php echo $octocat_width; ?>" height="<?php echo $octocat_height; ?>">
		</div> <!-- /.wpgrc-commit-octocat -->
	<?php else: ?>
	<div class="wpgrc-widget no-octocat pull-left">
	<?php endif ?>

		<ul class="pull-left wpgrc-commit-info-wrap">
			<li class="clear wpgrc-commit-repo-title">
				<a href="https://wpgrc.com/<?php echo $owner_name; ?>" class="wpgrc-repo-owner" target="_blank"><?php echo $owner_name; ?></a>
				<span>/</span>
				<a href="<?php echo $repo_url; ?>" " class="wpgrc-repo-url" target="_blank"><?php echo $repo_name; ?></a>
			</li>
			<li class="clear wpgrc-commit-message pull-left"><?php echo $message; ?></li>
			<li class="clear wpgrc-commit-author-wrap pull-left">
				<span>Commited by:&nbsp;</span>
				<a href="<?php echo $author_url; ?>" class="wpgrc-commit-author" target="_blank"><?php echo $author; ?></a>
			</li>
		</ul> <!-- /. wpgrc-commit-info-wrap -->
	</div> <!-- /.wpgrc-widget -->
	<!-- END WPGRC WIDGET -->
<?php } else { ?>
	<!-- WPGRC WIDGET ERROR -->
	<div class="wpgrc-error">I am sorry, something went wrong when contacting GitHub please try again later.</div>
	<!-- END WPGRC WIDGET ERROR -->
<?php } // if/else(!empty($widget_content))