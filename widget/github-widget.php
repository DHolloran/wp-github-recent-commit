<?php
/**
 * Github widget
 */
class WP_Github_Recent_Commit_Widget extends WP_Widget {
	private $fields = array(
		'title'              				=> 'Title (optional)',
		'github_username'    				=> 'Github Username (required)',
		'github_repository_name'		=>	'Name of Repository (optional)',
		'show_octocat'      				=> 'Show Random Octocat (optional)',
		'octocat_size_width'  			=> 'Octocat Width (default: 100px)',
		'octocat_size_height'				=> 'Octocat Height (default: 100px)'
	);

	/**
	 * Constructor
	 */
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_dh_github_widget', 'description' => __( 'Displays your latest GitHub commit from a public repository.', 'roots' ) );

		$this->WP_Widget( 'widget_dh_github_widget', __( 'WP Github Recent Commit', 'roots' ), $widget_ops );
		$this->alt_option_name = 'widget_dh_github_widget';

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
		if ( is_active_widget( false, false, $this->id_base, true ) )
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_files' ) );
	} // __construct()


	/**
	 * Widget
	 */
	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_dh_github_widget', 'widget' );

		if ( !is_array( $cache ) ) $cache = array();

		if ( !isset( $args['widget_id'] ) ) $args['widget_id'] = null;

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract( $args, EXTR_SKIP );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( '', 'roots' ) : $instance['title'], $instance, $this->id_base );

		foreach ( $this->fields as $name => $label ) {
			if ( !isset( $instance[$name] ) ) { $instance[$name] = ''; }
		}

		echo $before_widget;

		if ( $title )
			echo $before_title, $title, $after_title;

		require_once "views/view-github-widget.php";
		echo $after_widget;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( 'widget_dh_github_widget', $cache, 'widget' );
	} // widget()


	/**
	* Update
	*/
	function update( $new_instance, $old_instance ) {
		$instance = array_map( 'strip_tags', $new_instance );

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );

		if ( isset( $alloptions['widget_dh_github_widget'] ) )
			delete_option( 'widget_dh_github_widget' );

		return $instance;
	} // update


	/**
	* Flush Widget Cache
	*/
	function flush_widget_cache() {

		wp_cache_delete( 'widget_dh_github_widget', 'widget' );
	} // flush_widget_cache()


	/**
	* Form
	*/
	function form( $instance ) {
		foreach ( $this->fields as $name => $label ) {
			${$name} = isset( $instance[$name] ) ? esc_attr( $instance[$name] ) : ''; ?>
		<p>

			<?php if ( $name != 'show_octocat' ): ?>
				<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"><?php _e( "{$label}:", 'roots' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" type="text" value="<?php echo ${$name}; ?>">
			<?php else: ?>

				<?php $checked = ( ${$name} == 'on' ) ? 'checked="checked"': ''; ?>
				<input id="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $name ) ); ?>" type="checkbox" value="on"<?php echo $checked; ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( $name ) ); ?>"><?php _e( "{$label}", 'roots' ); ?></label>
			<?php endif ?>

		</p>
		<?php
		} // foreach
	} // form()


	/**
	* Enqueue Files
	*/
	function enqueue_files()
	{
		wp_enqueue_style( 'wpgrc_plugin_css', plugins_url( 'assets/css/wpgrc-plugin.css' , dirname(__FILE__) ) );
	} // enqueue_files()

} // WP_Github_Recent_Commit_Widget
