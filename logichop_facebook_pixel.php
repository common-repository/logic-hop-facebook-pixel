<?php

	/*
		Plugin Name: Logic Hop Facebook Pixel Add-on
		Plugin URI:	https://logichop.com/docs/facebook-pixel
		Description: Enables Facebook Pixel event tracking for Logic Hop
		Author: Logic Hop
		Version: 3.0.2
		Author URI: https://logichop.com
	*/

	if (!defined('ABSPATH')) die;

	if ( is_admin() ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'logichop/logichop.php' ) && ! is_plugin_active( 'logic-hop/logichop.php' ) ) {
			add_action( 'admin_notices', 'logichop_facebook_pixel_plugin_notice' );
		}
	}

	function logichop_facebook_pixel_plugin_notice () {
		$message = sprintf(__('The Logic Hop Facebook Pixel requires the Logic Hop plugin. Please download and activate the <a href="%s" target="_blank">Logic Hop plugin</a>.', 'logichop'),
							'http://wordpress.org/plugins/logic-hop/'
						);

		printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
	}

	require_once 'includes/facebook_pixel.php';

	/**
	 * Plugin activation/deactviation routine to clear Logic Hop transients
	 *
	 * @since    2.0.1
	 */
	function logichop_facebook_pixel_activation () {
		delete_transient( 'logichop' );
  }
	register_activation_hook( __FILE__, 'logichop_facebook_pixel_activation' );
	register_deactivation_hook( __FILE__, 'logichop_facebook_pixel_activation' );

	/**
	 * Register admin notices
	 *
	 * @since    2.0.1
	 */
	function logichop_facebook_pixel_admin_notice () {
		global $logichop;

		$message = '';

		if ( ! $logichop->logic->addon_active('facebook-pixel') ) {
			$message = sprintf(__('The Logic Hop Facebook Pixel Add-on requires a <a href="%s" target="_blank">Logic Hop License Key or Data Plan</a>.', 'logichop'),
							'https://logichop.com/get-started/?ref=addon-facebook-pixel'
						);
		}

		if ( $message ) {
			printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
		}
	}
	add_action( 'logichop_admin_notice', 'logichop_facebook_pixel_admin_notice' );

	/**
	 * Plugin page links
	 *
	 * @since    1.0.0
	 * @param    array		$links			Plugin links
	 * @return   array  	$new_links 		Plugin links
	 */
	function logichop_plugin_action_links_facebook_pixel ($links) {
		$new_links = array();
        $new_links['settings'] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://logichop.com/docs/facebook-pixel', 'Instructions' );
 		$new_links['deactivate'] = $links['deactivate'];
 		return $new_links;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'logichop_plugin_action_links_facebook_pixel');

	/**
	 * Initialize functionality
	 *
	 * @since    1.0.0
	 */
	function logichop_facebook_pixel_init () {
		global $logichop;

		if ( isset( $logichop->logic ) ) {
			$logichop->logic->facebook_pixel = new LogicHop_facebook_pixel($logichop->logic);
		}
	}
	add_action('logichop_integration_init', 'logichop_facebook_pixel_init');

	/**
	 * Handle event tracking
	 *
	 * @since    1.0.0
	 * @param    integer	$id		Goal ID
	 * @return   boolean   	Event tracked
	 */
	function logichop_check_track_event_facebook_pixel ($id, $values) {
		global $logichop;
		return $logichop->logic->facebook_pixel->track_event($id, $values);
	}
	add_filter('logichop_check_track_event', 'logichop_check_track_event_facebook_pixel', 10, 2);

	/**
	 * Generate client meta data
	 *
	 * @since    1.0.0
	 * @param    array		$integrations	Integration names
	 * @return   array    	$integrations	Integration names
	 */
	function logichop_facebook_pixel_client_meta ($integrations) {
		$integrations[] = 'facebook-pixel';
		return $integrations;
	}
	add_filter('logichop_client_meta_integrations', 'logichop_facebook_pixel_client_meta');

	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 * @param    array		$settings	Settings parameters
	 * @return   array    	$settings	Settings parameters
	 */
	function logichop_settings_register_facebook_pixel ($settings) {
		$settings['facebook_pixel_id'] = array (
							'name' 	=> __('Facebook Pixel ID', 'logichop'),
							'meta' 	=> __('Enable Facebook Pixel event tracking. <a href="https://logichop.com/docs/facebook-pixel" target="_blank">Instructions</a>', 'logichop'),
							'type' 	=> 'text',
							'label' => '',
							'opts'  => null
						);
		$settings['facebook_pixel_embed'] = array (
							'name' 	=> __('Facebook Pixel', 'logichop'),
							'meta' 	=> __('Include Facebook Pixel Javascript include on every page.', 'logichop'),
							'type' 	=> 'checkbox',
							'label' => 'Enable Javascript Embed',
							'opts'  => null
						);
		return $settings;
	}
	add_filter('logichop_settings_register', 'logichop_settings_register_facebook_pixel');

	/**
	 * Validate settings
	 *
	 * @since    1.0.0
	 * @param    string		$key		Settings key
	 * @return   string    	$result		Error object
	 */
	function logichop_settings_validate_facebook_pixel ($validation, $key, $input) {
		global $logichop;

		if ($key == 'facebook_pixel_id' && $input[$key] != '') {
			if (!preg_match('/^[0-9]+$/i', strval($input[$key]))) {
         		$validation->error = true;
         		$validation->error_msg = '<li>Invalid Facebook Pixel ID</li>';
         	}
         }
	}
	add_filter('logichop_settings_validate', 'logichop_settings_validate_facebook_pixel', 10, 3);

	/**
	 * Add goal metabox
	 *
	 * @since    1.0.0
	 */
	function logichop_configure_metabox_facebook_pixel () {
		global $logichop;

		add_meta_box(
				'logichop_goal_facebook_pixel_event',
				__('Facebook Pixel Event', 'logichop'),
				array($logichop->logic->facebook_pixel, 'goal_tag_display'),
				array('logichop-goals'),
				'normal',
				'low'
			);
	}
	add_action('logichop_configure_metaboxes', 'logichop_configure_metabox_facebook_pixel');

	/**
	 * Save event data
	 *
	 * @since    1.0.0
	 * @param    integer	$post_id	WP post ID
	 */
	function logichop_event_save_facebook_pixel ($post_id) {
		if (isset($_POST['logichop_goal_fbp_track'])) 	update_post_meta($post_id, 'logichop_goal_fbp_track', wp_kses($_POST['logichop_goal_fbp_track'],''));
		if (isset($_POST['logichop_goal_fbp_event'])) 	update_post_meta($post_id, 'logichop_goal_fbp_event', wp_kses($_POST['logichop_goal_fbp_event'],''));
		if (isset($_POST['logichop_goal_fbp_data'])) 	update_post_meta($post_id, 'logichop_goal_fbp_data', wp_kses($_POST['logichop_goal_fbp_data'],''));
	}
	add_action('logichop_event_save', 'logichop_event_save_facebook_pixel');

	/**
	 * Output FB Pixel script in wp_head()
	 *
	 * @since    1.0.0
	 */
	function logichop_embed_facebook_pixel () {
		global $logichop;

		if ( ! $logichop ) return;

		$pixel_id = $logichop->logic->facebook_pixel->active();

		if ( $pixel_id && $logichop->logic->facebook_pixel->embedEnabled() ) {

			printf('<!-- Facebook Pixel Code -->
							<script>
							  !function(f,b,e,v,n,t,s)
							  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
							  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
							  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';
							  n.queue=[];t=b.createElement(e);t.async=!0;
							  t.src=v;s=b.getElementsByTagName(e)[0];
							  s.parentNode.insertBefore(t,s)}(window, document,\'script\',
							  \'https://connect.facebook.net/en_US/fbevents.js\');
							  fbq(\'init\', \'%s\');
							  fbq(\'track\', \'PageView\');
							</script>
							<noscript><img height="1" width="1" style="display:none"
							  src="https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1"
							/></noscript>
							<!-- End Facebook Pixel Code -->',
							$pixel_id,
							$pixel_id
					);
		}
	}
	add_action( 'wp_head', 'logichop_embed_facebook_pixel' );

	/**
	 * Enqueue admin styles
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_styles_facebook_pixel ($hook) {
		global $logichop;
		if (in_array($hook, array('post.php', 'post-new.php'))) {
			$css_path = sprintf('%sadmin/logichop_facebook_pixel_goals.css', plugin_dir_url( __FILE__ ));
			wp_enqueue_style( 'logichop_facebook_pixel', $css_path, array(), $logichop->logic->facebook_pixel->version, 'all' );
		}
	}
	add_action('logichop_admin_enqueue_styles', 'logichop_admin_enqueue_styles_facebook_pixel');

	/**
	 * Enqueue admin scripts
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_scripts_facebook_pixel ($hook, $post_type) {
		global $logichop;
		if ($post_type == 'logichop-goals') {
			$js_path = sprintf('%sadmin/logichop_facebook_pixel_goals.js', plugin_dir_url( __FILE__ ));
			wp_enqueue_script( 'logichop_facebook_pixel', $js_path, array( 'jquery' ), $logichop->logic->facebook_pixel->version, false );
		}
	}
	add_action('logichop_admin_enqueue_scripts', 'logichop_admin_enqueue_scripts_facebook_pixel', 10, 2);
