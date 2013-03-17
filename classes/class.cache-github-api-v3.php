<?php
/**
*
*/
class Cache_Github_Api_V3
{

	protected $github_username;


	/**
	* Constructor
	*/
	function __construct( $username )
	{

		$this->github_username = $username;
	} // __construct()


	/**
	* Check If New Widget User
	*/
	protected function is_new_user()
	{
		$key = 'github_username' . $this->widget_id;
		$new_username = ( !empty( $this->github_username ) ) ? $this->github_username : '';
		$current_username = get_option( $key, FALSE );
		if ( !$current_username OR $current_username !== $new_username ) {
			update_option( $key,  $new_username );
			return TRUE;
		}

		return FALSE;
	} // is_new_user()


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
		// Overides the cache if there is a new user
		if ( $this->is_new_user() ) return FALSE;

		$last_update_time = $this->get_cache_time( $cache_key );
		$offset = ( is_null( $offset ) ) ? 30 * 60 * 60 : $offset; // Default Offset 30 minutes
		if ( $last_update_time AND $last_update_time > time() - $offset )
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