<?php
/**
 * Class BP_Vip_Member_Settings
 *
 * @author  extensionforge
 * @since   1.0.0
 * @package bp-vip-member/admin/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BP_Vip_Member_Settings' ) ) :
	/**
	 * Class BP_Vip_Member_Settings
	 *
	 * @author extensionforge
	 * @package bp-vip-member/admin/settings
	 */
	class BP_Vip_Member_Settings {

		/**
		 * Option page slug.
		 *
		 * @var string
		 */
		private $page_slug = 'bp-vip-member';

		/**
		 * Option group slug.
		 *
		 * @var string
		 */
		private $option_group = 'bp_vip_member';

		/**
		 * Options default values.
		 *
		 * @var array
		 */
		private $defaults = array();

		/**
		 * BP_Vip_Member_Settings constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_option_page' ) );
			add_action( 'admin_init', array( $this, 'page_init'       ) );

			if ( ! bp_core_do_network_admin() ) {
				add_filter( 'bp_core_get_admin_tabs', array( $this, 'add_tab_in_buddypress_settings' ), 10, 1 );
				add_action( 'bp_admin_head',          array( $this, 'remove_settings_submenu_link'   ), 999   );
			}

			$this->defaults = array(
				"{$this->option_group}_vip_roles"                    => array(),
				"{$this->option_group}_vip_member_types"             => array(),
				"{$this->option_group}_enable_verification_requests"      => false,
				"{$this->option_group}_badge_shape"                       => 'circle',
				"{$this->option_group}_badge_color"                       => '#1DA1F2',
				"{$this->option_group}_tooltip_content"                   => esc_html__( 'Vip', 'bp-vip-member' ),
				"{$this->option_group}_display_unvip_badge"          => 0,
				"{$this->option_group}_unvip_badge_shape"            => 'circle',
				"{$this->option_group}_unvip_badge_color"            => '#DD9933',
				"{$this->option_group}_unvip_tooltip_content"        => esc_html__( 'Unvip', 'bp-vip-member' ),
				"{$this->option_group}_enable_vip_notification"      => false,
				"{$this->option_group}_vip_notification_content"     => esc_html__( 'Congratulations, your profile is now vip !', 'bp-vip-member' ),
				"{$this->option_group}_enable_unvip_notification"    => false,
				"{$this->option_group}_unvip_notification_content"   => esc_html__( 'Sorry, your profile has been unvip', 'bp-vip-member' ),
				"{$this->option_group}_display_badge_in_activity_stream"  => 1,
				"{$this->option_group}_display_badge_in_profile_username" => 1,
				"{$this->option_group}_display_badge_in_profile_fullname" => 0,
				"{$this->option_group}_display_badge_in_members_lists"    => 1,
				"{$this->option_group}_display_badge_in_bp_widgets"       => 0,
				"{$this->option_group}_display_badge_in_messages"         => 0,
				"{$this->option_group}_display_badge_in_bbp_topics"       => 1,
				"{$this->option_group}_display_badge_in_bbp_replies"      => 1,
				"{$this->option_group}_display_badge_in_rtmedia"          => 1,
				"{$this->option_group}_display_badge_in_wp_comments"      => 1,
				"{$this->option_group}_display_badge_in_wp_posts"         => 1,
			);
		}

		/**
		 * Add options page.
		 */
		public function add_option_page() {
			$hook = add_options_page(
				esc_html__( 'Vip Member Settings', 'bp-vip-member' ),
				esc_html__( 'Vip Member for BuddyPress', 'bp-vip-member' ),
				'manage_options',
				$this->page_slug,
				array( $this, 'render_settings_page' )
			);

			if ( ! bp_core_do_network_admin() ) {
				add_action( "admin_head-$hook", 'bp_core_modify_admin_menu_highlight' );
			}
		}

		/**
		 * Options page callback.
		 */
		public function render_settings_page() {
			wp_enqueue_style( 'glider.js', BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/css/vendor/glider.min.css', array(), '1.7.4' );
			wp_enqueue_style( 'bp-vip-member-settings', BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/css/settings.css', array(), BP_VIP_MEMBER_VERSION );
			wp_enqueue_script( 'glider.js', BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/js/vendor/glider.min.js', array(), '1.7.4', true );
			wp_enqueue_script( 'bp-vip-member-settings', BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/js/settings.js', array( 'glider.js', 'jquery' ), BP_VIP_MEMBER_VERSION, true );
			?>

			<div class="wrap">

				<h1><?php esc_html_e( 'Vip Member Settings', 'bp-vip-member' ); ?></h1>

				

				<div class="bp-vip-member-settings-container">

					<form class="bp-vip-member-settings-form" method="post" action="options.php">
						<?php
						// This prints out all hidden setting fields
						settings_fields( $this->option_group );
						do_settings_sections( $this->page_slug );
						submit_button();
						?>
					</form>

					<div class="bp-vip-member-settings-sidebar">

						<br /><br />

						<div class="extensionforge-promo">
							<a target="_blank" href="https://extensionforge.com">extensionforge.com</a>
						</div>

					</div>

				</div>
			</div>

			<?php
		}

		/**
		 * Get an option with default value.
		 *
		 * @param string $option_name The requested option name.
		 *
		 * @return mixed The requested option.
		 */
		public function get_option( $option_name ) {
			// Prefix $option_name if not already prefixed
			if ( substr( $option_name, 0, strlen( $this->option_group ) ) !== $this->option_group ) {
				$option_name = $this->option_group . '_' . $option_name;
			}

			return get_option( $option_name, $this->defaults[ $option_name ] );
		}

		/**
		 * Register and add settings and settings fields.
		 */
		public function page_init() {
			$settings = array(
				"{$this->option_group}_verification_section"     => array(
					'title'  => esc_html__( 'Verification Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_vip_roles"               => array(
							'label'       => esc_html__( 'Vip Roles', 'bp-vip-member' ),
							'description' => esc_html__( 'Automatically grant vip badge to the following roles:', 'bp-vip-member' ),
							'type'        => 'multi_checkbox',
							'options'     => $this->get_role_options(),
						),
						"{$this->option_group}_vip_member_types"        => array(
							'label'       => esc_html__( 'Vip Member Types', 'bp-vip-member' ),
							'description' => empty( $this->get_member_type_options() ) ? esc_html__( 'There are no member types. You need to create a member type to use this option.', 'bp-vip-member' ) : esc_html__( 'Automatically grant vip badge to the following member types:', 'bp-vip-member' ),
							'type'        => 'multi_checkbox',
							'options'     => $this->get_member_type_options(),
						),
						"{$this->option_group}_enable_verification_requests" => array(
							'label'       => esc_html__( 'Enable Verification Requests', 'bp-vip-member' ),
							'description' => esc_html__( 'Display a button on user profiles to allow users to send a verification request. The requests will then be displayed for admins in the "Verification Requests" view in the users table.', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_vip_badge_section"     => array(
					'title'  => esc_html__( 'Vip badge', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_badge_shape" => array(
							'label'             => esc_html__( 'Vip Badge Shape', 'bp-vip-member' ),
							'type'              => 'radio_image',
							'is_badge_selector' => true,
							'badge_type'        => 'vip',
							'options'           => array(
								'circle'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-circle.svg',
								'square'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-square.svg',
								'diamond' => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-diamond.svg',
								'hexagon' => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-hexagon.svg',
								'spiky'   => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-spiky.svg',
								'wavy'    => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-wavy.svg',
								'shield'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-shield.svg',
								'blob'    => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-blob.svg',
							),
						),
						"{$this->option_group}_badge_color" => array(
							'label' => esc_html__( 'Vip Badge Color', 'bp-vip-member' ),
							'type'  => 'color',
						),
						"{$this->option_group}_tooltip_content" => array(
							'label'       => esc_html__( 'Vip Badge Tooltip Content', 'bp-vip-member' ),
							'description' => esc_html__( 'Content of the tooltip displayed when hovering on a vip badge. Empty for no tooltip.', 'bp-vip-member' ),
							'type'        => 'text',
						),
					),
				),
				"{$this->option_group}_unvip_badge_section"     => array(
					'title'  => esc_html__( 'Unvip Badge', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_unvip_badge" => array(
							'label'       => esc_html__( 'Display Unvip Badge', 'bp-vip-member' ),
							'description' => esc_html__( 'Display an unvip badge for users who are not vip.', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_unvip_badge_shape" => array(
							'label'             => esc_html__( 'Unvip Badge Shape', 'bp-vip-member' ),
							'type'              => 'radio_image',
							'is_badge_selector' => true,
							'badge_type'        => 'unvip',
							'options'           => array(
								'circle'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-circle.svg',
								'square'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-square.svg',
								'diamond' => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-diamond.svg',
								'hexagon' => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-hexagon.svg',
								'spiky'   => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-spiky.svg',
								'wavy'    => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-wavy.svg',
								'shield'  => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-shield.svg',
								'blob'    => BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/images/mask-blob.svg',
							),
						),
						"{$this->option_group}_unvip_badge_color" => array(
							'label' => esc_html__( 'Unvip Badge Color', 'bp-vip-member' ),
							'type'  => 'color',
						),
						"{$this->option_group}_unvip_tooltip_content" => array(
							'label'       => esc_html__( 'Unvip Badge Tooltip Content', 'bp-vip-member' ),
							'description' => esc_html__( 'Content of the tooltip displayed when hovering on a unvip badge. Empty for no tooltip.', 'bp-vip-member' ),
							'type'        => 'text',
						),
					),
				),
				"{$this->option_group}_notifications_section"     => array(
					'title'  => esc_html__( 'Notifications', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_enable_vip_notification" => array(
							'label' => esc_html__( 'Enable Vip Notification', 'bp-vip-member' ),
							'description' => esc_html__( 'Send a BuddyPress notification to the user when they get vip.', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_vip_notification_content" => array(
							'label'       => esc_html__( 'Vip Notification Content', 'bp-vip-member' ),
							'description' => esc_html__( 'Content of the notification sent when a user gets vip.', 'bp-vip-member' ),
							'type'        => 'textarea',
						),
						"{$this->option_group}_enable_unvip_notification" => array(
							'label' => esc_html__( 'Enable Unvip Notification', 'bp-vip-member' ),
							'description' => esc_html__( 'Send a BuddyPress notification to the user when their vip status gets revoked.', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_unvip_notification_content" => array(
							'label'       => esc_html__( 'Unvip Notification Content', 'bp-vip-member' ),
							'description' => esc_html__( 'Content of the notification sent when a user gets unvip.', 'bp-vip-member' ),
							'type'        => 'textarea',
						),
					),
				),
				"{$this->option_group}_activity_section"  => array(
					'title'  => esc_html__( 'Activities Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_activity_stream" => array(
							'label'       => esc_html__( 'Display in Activities', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in Activity Stream', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_profile_section"   => array(
					'title'  => esc_html__( 'Profiles Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_profile_username" => array(
							'label'       => esc_html__( 'Display in Username', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in Profile Username', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_display_badge_in_profile_fullname" => array(
							'label'       => esc_html__( 'Display in Fullname', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in Profile Fullname', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_directory_section" => array(
					'title'  => esc_html__( 'Directories Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_members_lists" => array(
							'label'       => esc_html__( 'Display in Directories', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in Members Lists', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_bp_widget_section" => array(
					'title'  => esc_html__( 'Widgets Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_bp_widgets" => array(
							'label'       => esc_html__( 'Display in BP Widgets', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in BuddyPress Widgets', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_message_section" => array(
					'title'  => esc_html__( 'Messages Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_messages" => array(
							'label'       => esc_html__( 'Display in Messages', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in Private Messages', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_bbp_section"       => array(
					'title'  => esc_html__( 'bbPress Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_bbp_topics" => array(
							'label'       => esc_html__( 'Display in Topics', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in BBPress Topics', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_display_badge_in_bbp_replies" => array(
							'label'       => esc_html__( 'Display in Replies', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in BBPress Replies', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_rtmedia_section"   => array(
					'title'  => esc_html__( 'rtMedia Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_rtmedia" => array(
							'label'       => esc_html__( 'Display in rtMedia views', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in rtMedia lightbox and single media views', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
				"{$this->option_group}_wp_section"        => array(
					'title'  => esc_html__( 'WordPress Settings', 'bp-vip-member' ),
					'fields' => array(
						"{$this->option_group}_display_badge_in_wp_comments" => array(
							'label'       => esc_html__( 'Display in Comments', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in WordPress Comments', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
						"{$this->option_group}_display_badge_in_wp_posts" => array(
							'label'       => esc_html__( 'Display in Posts', 'bp-vip-member' ),
							'description' => esc_html__( 'Display Vip Badge in WordPress Posts', 'bp-vip-member' ),
							'type'        => 'checkbox',
						),
					),
				),
			);

			foreach ( $settings as $section_id => $section ) {

				// Don't show bbPress settings if bbPress isn't activated
				if (
					"{$this->option_group}_bbp_section" === $section_id && ! function_exists( 'bbpress' ) || // Don't show bbPress settings if bbPress isn't activated
					"{$this->option_group}_rtmedia_section" === $section_id && ! function_exists( 'rtmedia' ) || // Don't show rtMedia settings if rtMedia isn't activated
					"{$this->option_group}_message_section" === $section_id && class_exists( 'BP_Better_Messages' ) // Don't show messages settings if BP Better Messages is activated
				) {
					continue;
				}

				add_settings_section(
					$section_id, // ID
					$section['title'], // Title
					'__return_false', // Callback
					$this->page_slug // Page
				);

				foreach ( $section['fields'] as $field_id => $field ) {
					register_setting(
						$this->option_group, // Option group
						$field_id, // Option name
						array( $this, "sanitize_{$field['type']}" ) // Sanitize
					);

					add_settings_field(
						$field_id, // ID
						$field['label'], // Title
						array( $this, "render_{$field['type']}_field" ), // Callback
						$this->page_slug, // Page
						$section_id, // Section
						array(
							'id'                => $field_id,
							'description'       => ! empty( $field['description'] ) ? $field['description'] : '',
							'options'           => ! empty( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array(),
							'is_badge_selector' => ! empty( $field['is_badge_selector'] ),
							'badge_type'        => ! empty( $field['badge_type'] ) ? $field['badge_type'] : 'vip',
						) // Callback args
					);
				}
			}
		}

		/**
		 * Sanitize checkbox field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return int The sanitized checkbox value
		 */
		public function sanitize_checkbox( $input ) {
			return ! empty( $input ) ? 1 : 0;
		}

		/**
		 * Sanitize multi checkbox field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return array The sanitized checkbox value
		 */
		public function sanitize_multi_checkbox( $input ) {
			if ( ! is_array( $input ) ) {
				return array();
			}

			return array_map( 'sanitize_text_field', $input );
		}

		/**
		 * Sanitize color field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return string The sanitized color
		 */
		public function sanitize_color( $input ) {
			return sanitize_hex_color( $input );
		}

		/**
		 * Sanitize radio image field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return string The sanitized radio image value
		 */
		public function sanitize_radio_image( $input ) {
			return sanitize_text_field( $input );
		}

		/**
		 * Sanitize text field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return string The sanitized text
		 */
		public function sanitize_text( $input ) {
			return sanitize_text_field( $input );
		}

		/**
		 * Sanitize textarea field.
		 *
		 * @param mixed $input Contains the setting value.
		 *
		 * @return string The sanitized text
		 */
		public function sanitize_textarea( $input ) {
			return wp_kses_post( $input );
		}

		/**
		 * Render a checkbox field.
		 *
		 * @param array $args Field args.
		 */
		public function render_checkbox_field( $args ) {
			if ( empty( $args['id'] ) ) {
				return;
			}

			$checked = ! empty( $this->get_option( $args['id'] ) ) ? 'checked' : '';
			?>
			<input type="checkbox" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" <?php echo esc_attr( $checked ); ?> />
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['description'] ); ?></label>
			<?php endif;
		}

		/**
		 * Render a multi checkbox field.
		 *
		 * @param array $args Field args.
		 */
		public function render_multi_checkbox_field( $args ) {
			if ( empty( $args['id'] ) || ! isset( $args['options'] ) ) {
				return;
			}

			if ( ! empty( $args['description'] ) ) : ?>
				<p><?php echo wp_kses_post( $args['description'] ); ?></p>
			<?php endif;

			foreach ( $args['options'] as $value => $label ) : ?>
				<br>
				<label>
					<input type="checkbox" <?php checked( in_array( esc_attr( $value ), $this->get_option( $args['id'] ) ), true ); ?> name="<?php echo esc_attr( $args['id'] ); ?>[]" value="<?php echo esc_attr( $value ); ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach;
		}

		/**
		 * Render a radio image field.
		 *
		 * @param array $args Field args.
		 */
		public function render_radio_image_field( $args ) {
			if ( empty( $args['id'] ) || ! isset( $args['options'] ) ) {
				return;
			}

			if ( ! empty( $args['description'] ) ) : ?>
				<p><?php echo wp_kses_post( $args['description'] ); ?></p>
 			<?php endif; ?>

			<ul id="input_<?php echo esc_attr( $args['id'] ); ?>" class="bp-vip-member-radio-image-control<?php echo ! empty( $args['is_badge_selector'] ) ? ' bp-vip-member-badge-selector' : ''; ?>">
				<?php foreach ( $args['options'] as $value => $image ) : ?>
					<li>
						<input type="radio" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" id="<?php echo esc_attr( $args['id'] . $value ); ?>" <?php checked( $this->get_option( $args['id'] ), esc_attr( $value ) ); ?> />
						<label for="<?php echo esc_attr( $args['id'] . $value ); ?>">
							<?php if ( ! empty( $args['is_badge_selector'] ) ) : ?>
								<span style="--bp-vip-members-<?php echo esc_attr( $args['badge_type'] ); ?>-badge-shape: url('<?php echo esc_url( $image ); ?>')" class="bp-<?php echo esc_attr( $args['badge_type'] ); ?>-badge"></span>
							<?php else : ?>
								<img src="<?php echo esc_url( $image ); ?>">
							<?php endif; ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php
		}

		/**
		 * Render a color field.
		 *
		 * @param array $args Field args.
		 */
		public function render_color_field( $args ) {
			if ( empty( $args['id'] ) ) {
				return;
			}

			?>
			<input type="text" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $this->get_option( $args['id'] ) ); ?>" class="color-picker" />
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['description'] ); ?></label>
			<?php endif;
		}

		/**
		 * Render a text field.
		 *
		 * @param array $args Field args.
		 */
		public function render_text_field( $args ) {
			if ( empty( $args['id'] ) ) {
				return;
			}

			?>
			<input type="text" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $this->get_option( $args['id'] ) ); ?>" />
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></label>
			<?php endif;
		}

		/**
		 * Render a textarea field.
		 *
		 * @param array $args Field args.
		 */
		public function render_textarea_field( $args ) {
			if ( empty( $args['id'] ) ) {
				return;
			}

			?>
			<textarea id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" rows="2"><?php echo wp_kses_post( $this->get_option( $args['id'] ) ); ?></textarea>
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<br>
				<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></label>
			<?php endif;
		}

		/**
		 * Add new tab to BuddyPress settings.
		 *
		 * @param array $tabs The array of tabs.
		 *
		 * @return array The modified array of tabs.
		 */
		public function add_tab_in_buddypress_settings( $tabs ) {
			$tabs['bp_vip_member'] = array(
				'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-vip-member' ), 'options-general.php' ) ),
				'name' => __( 'Vip Member', 'bp-vip-member' ),
			);

			return $tabs;
		}

		/**
		 * Remove submenu link from the Settings menu.
		 */
		public function remove_settings_submenu_link() {
			remove_submenu_page( 'options-general.php', $this->page_slug );
		}

		/**
		 * Get an array of role options where each key is the role slug and each value is the role name
		 *
		 * @return array Array of role options
		 */
		private function get_role_options() {
			$roles        = get_editable_roles();
			$role_options = array();
			foreach ( $roles as $role_slug => $role ) {
				$role_options[ $role_slug ] = $role['name'];
			}

			return $role_options;
		}

		/**
		 * Get an array of role options where each key is the role slug and each value is the role name
		 *
		 * @return array Array of role options
		 */
		private function get_member_type_options() {
			$member_types        = bp_get_member_types( array(), 'objects' );
			$member_type_options = array();
			foreach ( $member_types as $member_type_slug => $member_type ) {
				$member_type_options[ $member_type_slug ] = $member_type->labels['name'];
			}

			return $member_type_options;
		}
	}

endif;

return new BP_Vip_Member_Settings();
