<?php
extract( $args );
extract($instance);
$octocat_height = ( isset( $octocat_size_height ) AND is_numeric( $octocat_size_height ) ) ? floor( $octocat_size_height ) : 100;
$octocat_width = ( isset( $octocat_size_width ) AND is_numeric( $octocat_size_width ) ) ? floor( $octocat_size_width ) : 100;
$github_api = new DH_Github_API_v3( strtolower( $github_username ), $widget_id );
$widget_content = $github_api->widget_content();
extract( $widget_content );
extract( $octocat );
$repo_text = explode( '/', $repo_title );
$owner_name = $repo_text[0];
$repo_name = $repo_text[1];
?>

<!-- GITHUB LAST COMMIT WIDGET -->
<div class="github-last-commit pull-left">
	<div class="github-commit-octocat polaroid pull-left">
		<img class="pull-left" src="<?php echo $octocat_image_url; ?>" alt="<?php echo $octocat_name; ?>" width="<?php echo $octocat_width; ?>" height="<?php echo $octocat_height; ?>">
	</div>
	<div class="pull-left github-commit-info-wrap">
		<div class="clear github-commit-repo-title">
			<a href="https://github.com/<?php echo $owner_name; ?>" target="_blank"><?php echo $owner_name; ?></a><span>/</span><a href="<?php echo $repo_url; ?>" target="_blank"><?php echo $repo_name; ?></a>
		</div>
		<div class="clear github-commit-message pull-left"><?php echo $message; ?></div>
		<div class="clear github-commit-author-wrap pull-left">
			<span class="pull-left">Commited by:&nbsp;</span>
			<a href="<?php echo $author_url; ?>" class="pull-left github-commit-author" target="_blank"><?php echo $author; ?></a>
			<div class="github-commit-avatar pull-left"></div>
		</div>
	</div>
</div><!-- END LAST COMMIT WIDGET -->