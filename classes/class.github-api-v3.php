<?php
/**
* Handles retrieving data from the GitHub v3 API
* @author Dan Holloran
*/
class WPGRC_Github_API_v3 extends Cache_Github_Api_V3
{
	protected $github_url;
	protected $github_user;
	protected $flush_cache;
	protected $widget_id;
	protected $selected_repository_name;
	protected $commit_count;
	protected $show_user_avatar;

/*
* Constructor
*/
	public function __construct( $config ) {
		parent::__construct( $config );

		extract( $config );
		$this->github_url = 'https://api.github.com';
		$this->flush_cache = FALSE;

		// Function/Shortcode Settings
		if ( isset( $function_instance ) ) {
			extract( $function_instance );
			$this->github_user = strtolower( $username );
			$this->widget_id = $id;
			$this->selected_repository_name = $repository;
		} // if()

		// Widget Settings
		if ( isset( $widget_instance ) ) {
			extract( $widget_instance );
			extract( $widget_args );
			$this->github_user = strtolower( $github_username );
			$this->widget_id = $widget_id;
			$this->selected_repository_name = $github_repository_name;
			$this->commit_count = ( isset( $commit_count ) AND is_numeric( $commit_count ) ) ? intval( $commit_count ) : 1;
			$this->show_user_avatar = ( isset( $show_user_avatar ) AND $show_user_avatar != '' ) ? true : false;
		} // if()

	} // __construct()


/*
* Execute Request
*/
	public function widget_content()
	{
		// Repos
		if ( $this->selected_repository_name != '' ) {
			$repo_names = array();
			$repo_names[]	=	$this->selected_repository_name;
		} else {
			$repo_names = $this->get_repos();
		} // if/else()
		// Commits
		if( $repo_names ) {
			$commits = $this->get_commits( $repo_names );
		} else {
			return array( 'error_msg' => "No repositories could be found for the user name {$this->github_user}, please check that the user name is correct and try again" );
		} // if/else()

		// Latest Commits
		if ( !empty( $commits ) AND isset( $commits[0] ) AND $this->commit_count === 1 ) {
			$latest_commit_key = $this->get_latest_commit_key( $commits );
			if ( isset( $latest_commit_key ) )
				return $this->build_widget_output_array( array( $commits[$latest_commit_key] ) );
		} elseif ( !empty( $commits ) ){
			return $this->build_widget_output_array( $commits );
		} else {
			if ( $this->selected_repository_name != '' ) {
				return array( 'error_msg' => "No commits could be found for repository {$this->selected_repository_name} owned by user {$this->github_user}, please check that the repository name is correct and try again" );
			} else {
				return array( 'error_msg' => "No commits could be found for repositories owned by user {$this->github_user}, please check that the user name is correct and try again" );
			} // if/else()
		} // if/elseif/else()


		return array();
	} // widget_content()


/*
* Get Repositories
*/
	protected function get_repos()
	{
		$cache_key = 'wpgrc_repos_' . $this->widget_id;
		$offset = 60 * 60 * 60; // 1 hour
		if ( $this->use_cache( $cache_key, $offset ) ) {
			$repo_names = $this->get_cache( $cache_key );
		} else {
			$get_repos = wp_remote_get( "{$this->github_url}/users/{$this->github_user}/repos");
			$repos = json_decode( wp_remote_retrieve_body( $get_repos ) );
			if( !$this->validate_response( $repos, $cache_key ) ) return FALSE;

			$repo_names = array();
			foreach ( $repos as $repo ) {
				array_push( $repo_names, $repo->name );
			} // foreach()
			$this->update_cache( $cache_key, $repo_names );
		} // if/else()

		return $repo_names;
	} // get_repos()


/*
* Get Commits
*/
	protected function get_commits( $repo_names )
	{
		if ( empty( $repo_names ) ) return FALSE;

		$cache_key = 'wpgrc_commits_' . $this->widget_id;
		if ( $this->use_cache( $cache_key ) ) {
			$commits = $this->get_cache( $cache_key );
		} else {
			$commits = array();
			// Build array of commits
			foreach ( $repo_names as $repo_name ) {
				$get_commits = wp_remote_get( "{$this->github_url}/repos/{$this->github_user}/{$repo_name}/commits?page=1&per_page=100");
				$repo_commits = json_decode( wp_remote_retrieve_body( $get_commits ), TRUE );
				if( !$this->validate_response( $repo_commits, $cache_key ) ) return FALSE;
				if ( !empty( $repo_commits ) ) {
					for ($i=0; $i < $this->commit_count; $i++) {
						if ( isset( $repo_commits[$i] ) ) {
							$last_commit = $repo_commits[$i];
							array_push( $commits, $last_commit );
						} // if()
					} // fo()
				} // if()
			} // foreach()

			$this->update_cache( $cache_key, $commits );
		} // if/else()

		return $commits;
	} // get_commits()



	/**
	* Validate Response
	*/
	protected function validate_response( $response, $cache_key )
	{
		if ( !empty( $response->message ) ) {
			$this->update_cache( $cache_key, FALSE );
			return FALSE;
		}

		return TRUE;
	} // validate_response( $response )


	/*
	* Get Latest Commit Array Key
	*/
	protected function get_latest_commit_key( $commits )
	{
		// Make sure we have something to work with
		if ( empty( $commits ) ) return FALSE;

		$latest_commit_dates = array();
		for ($i=0; $i < count( $commits ); $i++) {
			$commit = $commits[$i];
			$latest_commit_dates[$i] = strtotime( $commit['commit']['author']['date'] );
		} //for()

		$value = max( $latest_commit_dates );
		$key = array_search( $value, $latest_commit_dates );
		return $key;
	} // get_latest_commit_key($commits)



/*
* Build Widget Output Array
*/
	protected function build_widget_output_array( $commits )
	{
		if ( empty( $commits ) ) return FALSE;
		$commits_info = array();
		foreach ( $commits as $commit ) {
			$commit_info = array();
			$commit_info['author'] = $commit['author']['login'];
			$commit_info['author_email'] = $commit['commit']['author']['email'];
			$commit_info['author_url'] = $commit['author']['html_url'];
			$commit_info['message'] = $commit['commit']['message'];
			$commit_info['repo_url'] = str_replace( array( 'api.', 'repos/', 'commits/', $commit['sha']), '', $commit['url'] );
			$commit_info['repo_title'] = rtrim( str_replace( array( 'https://github.com/' ), '', $commit_info['repo_url'] ), '/' );
			$commit_info['octocat'] = $this->get_random_octocat();
			$commits_info[] = $commit_info;
		}
		return $commits_info;
	} // build_widget_output_array()


/*
* Get Octocats
*/
	function get_octocats()
	{
		// URL location of your feed
		$feedUrl = 'http://feeds.feedburner.com/Octocats?format=xml';
		$feedContent = "";

		// Fetch feed from URL
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $feedUrl);
		curl_setopt($curl, CURLOPT_TIMEOUT, 3);
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, FALSE);

		// FeedBurner requires a proper USER-AGENT...
		curl_setopt($curl, CURL_HTTP_VERSION_1_1, TRUE);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3");

		$feedContent = curl_exec($curl);
		curl_close($curl);

		$octocats = array();
		// Did we get feed content?
		if( $feedContent && !empty( $feedContent ) ) {
			$feedXml = @simplexml_load_string($feedContent);
			if( $feedXml ) {
				foreach ( $feedXml->entry as $entry ) {
					$octocat = array();
					$octocat['octocat_name'] = (string)$entry->title;
					$img_attrs = $entry->content->div->a->img->attributes();
					$octocat['octocat_image_url'] = (string)$img_attrs[0];
					$octocats[] = $octocat;
				} // foreach()
			} // if($feedXml)
		} // if($feedContent && !empty($feedContent))

		return $octocats;
	} // get_octocats()


/*
* Get Random Octocat
*/
	protected function get_random_octocat()
	{
		$cache_key = 'wpgrc_octocats_' . $this->widget_id;
		$offset = 24 * 60 * 60; // Once a day

		if ( $this->use_cache( $cache_key, $offset ) ) {
			$octocats = $this->get_cache( $cache_key );
		} else {
			$octocats = $this->get_octocats();
			$this->update_cache( $cache_key, $octocats );
		} // if/else()

		// Select Random Octocat
		if ( !empty( $octocats ) ) {
			$random = rand( 0, count( $octocats ) - 1 );
			return $octocats[$random];
		} // if()

		return array();
	} // get_random_octocat()

} // END class WPGRC_Github_API_v3