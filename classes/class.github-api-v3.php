<?php
/**
*
*/
class DH_Github_API_v3
{
	protected $github_url;
	protected $github_user;
	protected $flush_cache;
	protected $widget_id;

/*
* Constructor
*/
	public function __construct( $username, $widget_id ) {
		$this->github_url = 'https://api.github.com';
		$this->github_user = $username;
		$this->flush_cache = false;
		$this->widget_id;
	} // __construct()


/*
* Execute Request
*/
	public function widget_content()
	{
		$repo_names = $this->get_repos();
		$commits = $this->get_commits( $repo_names );
		$latest_commit_key = $this->get_latest_commit_key( $commits );
		return $this->build_widget_output_array( $commits[$latest_commit_key] );
	} // widget_content()


/*
* Get Repositories
*/
	protected function get_repos()
	{
		$cache_key = 'github_repos_' . $this->widget_id;
		$offset = 60 * 60 * 60; // 1 hour

		if ( $this->use_cache( $cache_key, $offset ) ) {
			$repo_names = $this->get_cache( $cache_key );
		} else {
			$get_repos = wp_remote_get( "{$this->github_url}/users/{$this->github_user}/repos");
			$repos = json_decode( wp_remote_retrieve_body( $get_repos ) );
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
		$cache_key = 'github_commits_' . $this->widget_id;
		if ( $this->use_cache( $cache_key ) ) {
			$commits = $this->get_cache( $cache_key );
		} else {
			$commits = array();
			// Build array of commits
			foreach ( $repo_names as $repo_name ) {
				$get_commits = wp_remote_get( "{$this->github_url}/repos/{$this->github_user}/{$repo_name}/commits?page=1&per_page=1");
				$repo_commits = json_decode( wp_remote_retrieve_body( $get_commits ), true );
				if ( !empty( $repo_commits ) ) {
					$last_commit = $repo_commits[0];
					array_push( $commits, $repo_commits[0] );
				} // if()
			} // foreach()

			$this->update_cache( $cache_key, $commits );
		} // if/else()

		return $commits;
	} // get_commits()


/*
* Get Latest Commit Array Key
*/
	protected function get_latest_commit_key( $commits )
	{
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
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);

		// FeedBurner requires a proper USER-AGENT...
		curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
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
		$cache_key = 'github_octocats_' . $this->widget_id;
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


/*
* Update Cache
*/
	protected function update_cache( $cache_key, $cache_content )
	{
		// Cache Content
		update_option( $cache_key, $cache_content );

		// Cache Time
		update_option( $cache_key . '_updated', time() );

	} // update_cache()


/*
* Get Cache
*/
	function get_cache( $cache_key )
	{
		return get_option( $cache_key, false );
	}


/*
* Use Cache
*/
	protected function use_cache( $cache_key, $offset = null )
	{
		$last_update_time = $this->get_cache_time( $cache_key );
		$offset = ( is_null( $offset ) ) ? 30 * 60 * 60 : $offset; // Default Offset 30 minutes
		if ( $last_update_time AND $last_update_time > time() - $offset )
			return TRUE;

		return FALSE;
	} // use_cache($cache_key)


/*
* Set Cache Time
*/
	protected function set_cache_time( $cache_key )
	{
		update_option( $cache_key . '_updated', time() );
		return true;
	} // set_update_time()


/*
* Get Cache Time
*/
	protected function get_cache_time( $cache_key )
	{
		return get_option( $cache_key . '_updated', false );
	} // get_update_time()


} // END class DH_Github_API_v3