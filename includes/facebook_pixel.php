<?php

if (!defined('ABSPATH')) die;

/**
 * Facebook Pixel
 *
 * Provides Facebook Pixel event functionality.
 *
 * @since      1.0.0
 */

class LogicHop_Facebook_Pixel {

	/**
	 * Core functionality & logic class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;

	/**
	 * Plugin version
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      integer    $version    Core functionality & logic.
	 */
	public $version;

	/**
	 * Facebook Pixel URL
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pixel_url    URL
	 */
	private $pixel_url;

	/**
	 * Parameters to hash before sending
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $hashed_params    Array of variable names
	 */
	private $hashed_params;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.0.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic		= $logic;
		$this->version		= '2.0.1';
		$this->pixel_url 	= 'https://www.facebook.com/tr?id=';
		$this->user_data	= array (
									'em', // Email
									'fn', // First Name
									'ln', // Last Name
									'ph', // Phone
									'ge', // Gender
									'db', // Date of Birth
									'ct', // City
									'st', // State
									'zp'  // ZIP
								);
	}

	/**
	 * Check if Facebook Pixel ID has been set
	 *
	 * @since    	1.0.0
	 * @return      boolean     If facebook_pixel_id is set
	 */
	public function active () {
		return $this->logic->get_option('facebook_pixel_id');
	}

	/**
	 * Check if Facebook Pixel embed option is enabled
	 *
	 * @since    	1.0.0
	 * @return      boolean     If facebook_pixel_embed is set
	 */
	public function embedEnabled () {
		return $this->logic->get_option('facebook_pixel_embed');
	}

	/**
	 * Displays Facebook Pixel metabox on Goal editor
	 *
	 * @since    	1.0.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string					Echos metabox form
	 */
	public function goal_tag_display ($post) {

		if ( ! $this->logic->addon_active('facebook-pixel') ) {
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('Logic Hop Facebook Pixel Events is currently disabled.', 'logichop'),
					sprintf(__('Logic Hop Facebook Pixel Events requires a <a href="%s" target="_blank">Logic Hop Business Plan</a> or higher.', 'logichop'),
						'https://logichop.com/get-started/?ref=addon-facebook-pixel'
						)
				);
			return;
		}

		$values	= get_post_custom($post->ID);
		$track 	= isset($values['logichop_goal_fbp_track']) ? esc_attr($values['logichop_goal_fbp_track'][0]) : '';
		$event	= isset($values['logichop_goal_fbp_event']) ? esc_attr($values['logichop_goal_fbp_event'][0]) : '';
		$data 	= isset($values['logichop_goal_fbp_data']) ? esc_attr($values['logichop_goal_fbp_data'][0]) : '';

		if ($this->logic->facebook_pixel->active()) {
			printf('<div>
						<p>
							<select id="logichop_goal_fbp_track" name="logichop_goal_fbp_track" class="logichop_fbp">
								<option value="">Don\'t Track</option>
								<option value="track" %s>Track</option>
								<option value="trackCustom" %s>Track Custom</option>
							</select>
						</p>
						<div id="logichop_fbp_events"></div>
						<div id="logichop_fbp_fields"></div>
						<input type="hidden" id="logichop_goal_fbp_event" name="logichop_goal_fbp_event" value="%s">
						<input type="hidden" id="logichop_goal_fbp_data" name="logichop_goal_fbp_data" value="%s">
						<p>
							<a href="#" class="logichop_fbp_clear">Clear</a>
						</p>
					</div>',
					($track == 'track') ? 'selected' : '',
					($track == 'trackCustom') ? 'selected' : '',
					$event,
					$data
				);
		} else {
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('Facebook Pixel is currently disabled.', 'logichop'),
					sprintf(__('To enable, add a valid Facebook Pixel ID on the <a href="%s">Settings page</a>.', 'logichop'),
							admin_url('admin.php?page=logichop-settings')
						)
				);
		}
	}

	/**
	 * Send Tracking Event to Facebook Pixel
	 *
	 * @since   	1.0.0
	 * @param		integer     $id         Post ID
	 * @param      	$values		array     	WordPress get_post_custom()
	 * @return      object     				Tracking response
	 */
	public function track_event ($id, $values) {
		if (!$this->active()) return false;

		$track = $event = $data = false;
		if (isset($values['logichop_goal_fbp_track'][0]) && $values['logichop_goal_fbp_track'][0] != '') {
			$track 	= $values['logichop_goal_fbp_track'][0];
			$event 	= ($values['logichop_goal_fbp_event'][0] != '') ? $values['logichop_goal_fbp_event'][0] : false;
			$data 	= ($values['logichop_goal_fbp_data'][0] != '') ? $values['logichop_goal_fbp_data'][0] : false;
		}

		if (!$event || !$data) return false;

		$facebook_pixel = sprintf('%s%s&ev=%s',
									$this->pixel_url,
									$this->active(),
									$event
								);

		$json = json_decode($data);

		foreach ($json as $key => $value) {
			if ($value) {
				$prefix = 'cd';
				if (!is_array($value)) {
					$value = trim($this->logic->get_liquid_value($value));
				} else {
					$value = implode($value, ',');
				}
				if (in_array($key, $this->user_data)) {
					$prefix = 'ud';
					$value = hash('sha256', trim($value));
				}
				$facebook_pixel .= sprintf('&%s[%s]=%s',
											$prefix,
											$key,
											urlencode($value)
										);
			}
		}

		$response = wp_remote_get($facebook_pixel);
		return $response;
	}
}
