<?php
/**
 * Github widget
 */
class WP_Github_Recent_Commit_Widget extends WP_Widget {
	private $fields = array(
		'title'          => 'Title (optional)',
	);

	function __construct() {
		$widget_ops = array('classname' => 'widget_dh_github_widget', 'description' => __('Simple Github widget.', 'roots'));

		$this->WP_Widget('widget_dh_github_widget', __('Github Widget', 'roots'), $widget_ops);
		$this->alt_option_name = 'widget_dh_github_widget';

		add_action('save_post', array(&$this, 'flush_widget_cache'));
		add_action('deleted_post', array(&$this, 'flush_widget_cache'));
		add_action('switch_theme', array(&$this, 'flush_widget_cache'));
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_dh_github_widget', 'widget');

		if (!is_array($cache)) {
			$cache = array();
		}

		if (!isset($args['widget_id'])) {
			$args['widget_id'] = null;
		}

		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args, EXTR_SKIP);

		// $title = apply_filters('widget_title', empty($instance['title']) ? __('', 'roots') : $instance['title'], $instance, $this->id_base);

		foreach($this->fields as $name => $label) {
			if (!isset($instance[$name])) { $instance[$name] = ''; }
		}

		echo $before_widget;

		// if ($title) {
		//   echo $before_title, $title, $after_title;
		// }
		require_once "views/view-github-widget.php";
		echo $after_widget;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_dh_github_widget', $cache, 'widget');
	}

	function update($new_instance, $old_instance) {
		$instance = array_map('strip_tags', $new_instance);

		$this->flush_widget_cache();

		$alloptions = wp_cache_get('alloptions', 'options');

		if (isset($alloptions['widget_dh_github_widget'])) {
			delete_option('widget_dh_github_widget');
		}

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_dh_github_widget', 'widget');
	}

	function form($instance) {
		foreach($this->fields as $name => $label) {
			${$name} = isset($instance[$name]) ? esc_attr($instance[$name]) : '';
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id($name)); ?>"><?php _e("{$label}:", 'roots'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id($name)); ?>" name="<?php echo esc_attr($this->get_field_name($name)); ?>" type="text" value="<?php echo ${$name}; ?>">
		</p>
		<?php
		}
	}
}
