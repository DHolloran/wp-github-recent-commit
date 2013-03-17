<?php
/**
*
*/
class WPGRC_Github_API_v3 extends Cache_Github_Api_V3
{
	protected $github_url;
	protected $github_user;
	protected $flush_cache;
	protected $widget_id;

/*
* Constructor
*/
	public function __construct( $username, $widget_id ) {
		parent::__construct();
		$this->github_url = 'https://api.github.com';
		$this->github_user = $username;
		$this->flush_cache = FALSE;
		$this->widget_id;
	} // __construct()


/*
* Execute Request
*/
	public function widget_content()
	{
		$repo_names = $this->get_repos();
		if( $repo_names ) $commits = $this->get_commits( $repo_names );
		if ( isset( $commits ) AND $commits ) $latest_commit_key = $this->get_latest_commit_key( $commits );
		if ( isset( $latest_commit_key ) ) return $this->build_widget_output_array( $commits[$latest_commit_key] );
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
				if( !$this->validate_response( $commits, $cache_key ) ) return FALSE;
				$get_commits = wp_remote_get( "{$this->github_url}/repos/{$this->github_user}/{$repo_name}/commits?page=1&per_page=1");
				$repo_commits = json_decode( wp_remote_retrieve_body( $get_commits ), TRUE );
				if ( !empty( $repo_commits ) ) {
					$last_commit = $repo_commits[0];
					array_push( $commits, $repo_commits[0] );
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
	protected function build_widget_output_array( $commit )
	{
		if ( empty( $commit ) ) return FALSE;
		$commit_info = array();
		$commit_info['author'] = $commit['author']['login'];
		$commit_info['author_email'] = $commit['commit']['author']['email'];
		$commit_info['author_url'] = $commit['author']['html_url'];
		$commit_info['message'] = $commit['commit']['message'];
		$commit_info['repo_url'] = str_replace( array( 'api.', 'repos/', 'commits/', $commit['sha']), '', $commit['url'] );
		$commit_info['repo_title'] = rtrim( str_replace( array( 'https://github.com/' ), '', $commit_info['repo_url'] ), '/' );
		$commit_info['octocat'] = $this->get_random_octocat();
		return $commit_info;
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
			$random = rand( 0, count( $octocats ) );
			return $octocats[$random];
		} // if()

		return array();
	} // get_random_octocat()

} // END class WPGRC_Github_API_v3