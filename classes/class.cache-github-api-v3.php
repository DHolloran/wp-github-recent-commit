<?php
/**
*
*/
class Cache_Github_Api_V3
{
	protected $github_user;
	protected $selected_repository_name;
	protected $widget_id;
	protected $refresh_interval;


	/**
	* Constructor
	*/
	function __construct( $config )
	{
		extract( $config );
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
			$this->refresh_interval = $cache_refresh_interval;
		} // if()

	} // __construct()


	/**
	* Check If New Widget User
	*/
	protected function is_new_user()
	{
		$key = 'github_username' . $this->widget_id;
		$new_username = ( !empty( $this->github_user ) ) ? $this->github_user : '';
		$current_username = get_option( $key, FALSE );
		if ( !$current_username OR $current_username !== $new_username ) {
			update_option( $key,  $new_username );
			return TRUE;
		}

		return FALSE;
	} // is_new_user()


		/**
	* Check If New Widget Repository
	*/
	protected function is_new_repository()
	{
		$key = 'github_repository_name' . $this->widget_id;
		$new_repo = ( !empty( $this->selected_repository_name ) ) ? $this->selected_repository_name : '';
		$current_repo = get_option( $key, FALSE );

		if ( $current_repo === FALSE OR $current_repo !== $new_repo ) {
			update_option( $key,  $new_repo );
			return TRUE;
		} // if()

		return FALSE;
	} // is_new_repository()


	/**
	* Update Cache
	*/
	protected function update_cache( $cache_key, $cache_content )
	{
		// Cache Content
		update_option( $cache_key, $cache_content );

		// Cache Time
		update_option( $cache_key . '_updated', time() );
	} // update_cache()


	/**
	* Get Cache
	*/
	function get_cache( $cache_key )
	{

		return get_option( $cache_key, FALSE );
	} // get_cache()


/**
* Use Cache
*/
	protected function use_cache( $cache_key, $offset = null )
	{
		// Overides the cache if there is a new user or repository
		if ( $this->is_new_user() OR $this->is_new_repository() ) return FALSE;

		$last_update_time = $this->get_cache_time( $cache_key );

		// User selected cache refresh rate
		if ( $this->refresh_interval != '' )
			$refresh_interval = ( floatval( $this->refresh_interval ) * 60 ) * 60 * 60;

		// Default cache refresh rate
		if ($this->refresh_interval == '' OR $refresh_interval == 0 )
			$refresh_interval = ( is_null( $offset ) ) ? 60 * 60 * 60 : $offset; // Default Offset 60 minutes

		if ( $last_update_time AND $last_update_time > time() - $refresh_interval )
			return TRUE;

		return FALSE;
	} // use_cache()


/**
* Set Cache Time
*/
	protected function set_cache_time( $cache_key )
	{
		update_option( $cache_key . '_updated', time() );
		return TRUE;
	} // set_update_time()


/**
* Get Cache Time
*/
	protected function get_cache_time( $cache_key )
	{
		return get_option( $cache_key . '_updated', FALSE );
	} // get_update_time()


} // class Cache_Github_Api_V3