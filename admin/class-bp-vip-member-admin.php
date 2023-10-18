<?php
/**
 * Class BP_Vip_Member_Admin
 *
 * @author  extensionforge.com
 * @since   1.0.0
 * @package bp-vip-member/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BP_Vip_Member_Admin
 *
 * @author extensionforge.com
 * @package bp-vip-member/admin
 */
class BP_Vip_Member_Admin {

	/**
	 * The class managing the plugin meta box.
	 *
	 * @var BP_Vip_Member_Meta_Box
	 */
	public $meta_box;

	/**
	 * The class managing the plugin settings
	 *
	 * @var BP_Vip_Member_Settings
	 */
	public $settings;

	/**
	 * BP_Vip_Member_Admin constructor.
	 */
	public function __construct() {
		$this->meta_box = require 'meta-box/class-bp-vip-member-meta-box.php';
		$this->settings = require 'settings/class-bp-vip-member-settings.php';

		add_action( 'admin_enqueue_scripts',                                  array( $this, 'enqueue_scripts'                      ), 10, 0 );

		/**
		 * Handle "Vip" column
		 */
		add_filter( 'manage_users_columns',                                   array( $this, 'add_vip_column'                  ), 10, 1 );
		add_filter( 'manage_users_custom_column',                             array( $this, 'vip_column_content'              ), 10, 3 );
		add_action( 'wp_ajax_bp_vip_member_toggle',                      array( $this, 'toggle_vip_member'               ), 10, 0 );

		/**
		 * Handle bulk actions
		 */
		add_filter( 'bulk_actions-users',                                     array( $this, 'register_users_bulk_actions'          ), 10, 1 );
		add_filter( 'handle_bulk_actions-users',                              array( $this, 'handle_users_bulk_actions'            ), 10, 3 );
		add_action( 'admin_notices',                                          array( $this, 'users_bulk_action_notice'             ), 10, 0 );

		/**
		 * Handle verification requests
		 */
		add_action( 'admin_notices',                                          array( $this, 'new_requests_notice'                  ), 10, 0 );
		add_action( 'wp_ajax_bp_vip_member_dismiss_new_requests_notice', array( $this, 'dismiss_new_requests_notice'          ), 10, 0 );
		add_filter( 'views_users',                                            array( $this, 'add_verification_requests_users_view' ), 99, 1 );
		add_action( 'users_list_table_query_args',                            array( $this, 'filter_verification_requests_users'   ), 10, 1 );

		/**
		 * User vip / unvip event
		 */
		add_action( 'update_user_meta',                                       array( $this, 'before_update_user_meta'              ), 10, 4 );
		add_action( 'set_user_role',                                          array( $this, 'after_update_user_role'               ), 10, 3 );
		add_action( 'set_object_terms',                                       array( $this, 'after_update_object_terms'            ), 10, 6 );
	}

	/**
	 * Enqueue scripts in admin
	 */
	public function enqueue_scripts() {
		// Make sure these are loaded
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Enqueue admin script
		wp_enqueue_script( 'bp-vip-member-admin', BP_VIP_MEMBER_PLUGIN_DIR_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), BP_VIP_MEMBER_VERSION, true );
		wp_localize_script( 'bp-vip-member-admin', 'bpVipMemberAdmin', array(
			'ajaxUrl'                     => admin_url( 'admin-ajax.php' ),
			'vipTooltip'             => esc_html__( 'Click to unverify', 'bp-vip-member' ),
			'vipByRoleTooltip'       => esc_html__( 'User belongs to a vip role', 'bp-vip-member' ),
			'vipByMemberTypeTooltip' => esc_html__( 'User belongs to a vip member type', 'bp-vip-member' ),
			'unvipTooltip'           => esc_html__( 'Click to verify', 'bp-vip-member' ),
		) );

		// Enqueue dependencies except the main frontend script
		global $bp_vip_member;
		$bp_vip_member->enqueue_scripts();
		wp_dequeue_script( 'bp-vip-member' );
	}

	/**
	 * Add new column in users table.
	 *
	 * @param array $columns The users admin columns.
	 *
	 * @return array $columns The users admin columns.
	 */
	public function add_vip_column( $columns ) {
		$columns['bp-vip-member'] = esc_html__( 'Vip', 'bp-vip-member' );
		return $columns;
	}

	/**
	 * Add vip column content.
	 *
	 * @param string $output The column output.
	 * @param array $column_name The current column name.
	 * @param int $user_id The current user id.
	 *
	 * @return string The column output.
	 */
	public function vip_column_content( $output, $column_name, $user_id ) {
		if ( 'bp-vip-member' === $column_name ) {
			/** @var $bp_vip_member BP_Vip_Member */
			global $bp_vip_member;

			$button_class = 'bp-vip-member-toggle';

			if ( $bp_vip_member->is_user_vip( $user_id ) ) {
				$output .= $bp_vip_member->get_vip_badge();

				if ( $bp_vip_member->is_user_vip_by_role( $user_id ) ) {
					$button_class .= ' bp-vip-by-role';
				}
				else if ( $bp_vip_member->is_user_vip_by_member_type( $user_id ) ) {
					$button_class .= ' bp-vip-by-member-type';
				}
			}
			else {
				$output .= $bp_vip_member->get_unvip_badge();
			}

			$output = '<a href="#" class="' . esc_attr( $button_class ) .'" data-user-id="' . esc_attr( $user_id ) . '" data-bp-vip-member-toggle-nonce="' . esc_attr( wp_create_nonce( 'bp-vip-member-toggle' ) ) . '">' . $output . '</a>';
		}

		return $output;
	}

	/**
	 * Ajax action to toggle the vip status of a user
	 */
	public function toggle_vip_member() {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp-vip-member-toggle' ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		if ( empty( $_POST['userId'] ) ) {
			wp_send_json_error( 'missing_user_id' );
		}

		$user_id      = intval( $_POST['userId'] );
		$was_vip = boolval( get_user_meta( $user_id, $this->meta_box->meta_keys['vip'], true ) );

		update_user_meta( $user_id, $this->meta_box->meta_keys['vip'], ! $was_vip );

		global $bp_vip_member;
		$output = $was_vip ? $bp_vip_member->get_unvip_badge() : $bp_vip_member->get_vip_badge();

		wp_send_json_success( $output );
	}

	/**
	 * Add a bulk action for users
	 *
	 * @param array $bulk_actions The list of bulk actions
	 *
	 * @return array The list of bulk actions
	 */
	public function register_users_bulk_actions( $bulk_actions ) {
		$bulk_actions['bp-vip-member_verify']   = esc_html__( 'Verify', 'bp-vip-member' );
		$bulk_actions['bp-vip-member_unverify'] = esc_html__( 'Unverify', 'bp-vip-member' );
		return $bulk_actions;
	}

	/**
	 * Handle the "verify" bulk actions for users
	 *
	 * @param string $redirect_to The redirection url after the bulk action is processed
	 * @param string $doaction The bulk action being processed
	 * @param array $user_ids The ids of the users being processed
	 *
	 * @return string The redirection url after the bulk action is processed
	 */
	public function handle_users_bulk_actions( $redirect_to, $doaction, $user_ids ) {
		if ( 'bp-vip-member_verify' === $doaction ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, $this->meta_box->meta_keys['vip'], true );
			}

			$redirect_to = add_query_arg( 'bp-vip-member_bulk_vip', count( $user_ids ), $redirect_to );
		}
		elseif ( 'bp-vip-member_unverify' === $doaction ) {
			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, $this->meta_box->meta_keys['vip'], false );
			}

			$redirect_to = add_query_arg( 'bp-vip-member_bulk_unvip', count( $user_ids ), $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Display success message after processing users bulk action
	 */
	public function users_bulk_action_notice() {
		if ( ! empty( $_GET['bp-vip-member_bulk_vip'] ) ) {
			$vip_count = intval( $_GET['bp-vip-member_bulk_vip'] );

			printf( '<div id="message" class="updated fade"><p>' . _n( 'Vip %s user.', 'Vip %s users.', $vip_count, 'bp-vip-member' ) . '</p></div>', $vip_count );
		}

		if ( ! empty( $_GET['bp-vip-member_bulk_unvip'] ) ) {
			$vip_count = intval( $_GET['bp-vip-member_bulk_unvip'] );

			printf( '<div id="message" class="updated fade"><p>' . _n( 'Unvip %s user.', 'Unvip %s users.', $vip_count, 'bp-vip-member' ) . '</p></div>', $vip_count );
		}
	}

	/**
	 * Display a notice when there are new verification requests
	 */
	public function new_requests_notice() {
		// Bail if verification requests are disabled
		if ( ! $this->settings->get_option( 'enable_verification_requests' ) ) {
			return;
		}

		$new_requests = get_transient( 'bp_vip_member_new_requests' );
		if ( ! empty( $new_requests ) ) :
			// Clear transient and bail if we are on the requests view
			if ( ! empty( $_GET['view'] ) && $_GET['view'] === 'bp_vip_member_requests' ) {
				delete_transient( 'bp_vip_member_new_requests' );
				return;
			}

			$new_requests_count = count( $new_requests ); ?>

			<div class="notice notice-info is-dismissible bp-vip-member-new-requests-notice">
				<p>
					<span>
						<?php printf( _n( '%1$s user have recently requested to be vip.', '%1$s users have recently requested to be vip.', $new_requests_count, 'bp-vip-member' ), $new_requests_count ); ?>
					</span>
					<a href="<?php echo esc_url( admin_url( 'users.php?view=bp_vip_member_requests' ) ) ?>" class="button button-primary">
						<?php esc_html_e( 'View requests', 'bp-vip-member' ); ?>
					</a>
				</p>
			</div>

		<?php endif;
	}

	/**
	 * Ajax action to dismiss the new requests notice
	 */
	public function dismiss_new_requests_notice() {
		if ( ! is_admin() || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		delete_transient( 'bp_vip_member_new_requests' );

		wp_send_json_success();
	}

	/**
	 * Add a verification requests view in the user table to filter users who have requested to be vip
	 *
	 * @param array $views Array of users table views
	 *
	 * @return array Modified array of users table views
	 */
	public function add_verification_requests_users_view( $views ) {
		// Bail if verification requests are disabled
		if ( ! $this->settings->get_option( 'enable_verification_requests' ) ) {
			return $views;
		}

		$class = '';

		if ( isset( $_GET['view'] ) && $_GET['view'] === 'bp_vip_member_requests' ) {
			$views['all'] = str_replace( 'class="current"', '', $views['all'] );
			$class = 'current';
		}

		$query_args = array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => 'bp_vip_member_verification_request',
					'value' => 'pending',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => $this->meta_box->meta_keys['vip'],
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => $this->meta_box->meta_keys['vip'],
						'value'   => true,
						'compare' => '!=',
					),
				),
			),
			'count_total' => true,
			'fields'      => 'ID',
		);

		// Exclude vip roles
		$vip_roles = $this->settings->get_option( 'vip_roles' );
		if ( ! empty( $vip_roles ) ) {
			$query_args['role__not_in'] = $vip_roles;
		}

		// Exclude vip member types
		$vip_member_types = $this->settings->get_option( 'vip_member_types' );
		if ( ! empty( $vip_member_types ) ) {
			$users_vip_by_member_type = bp_core_get_users( array( 'type' => 'alphabetical', 'member_type__in' => $vip_member_types ) );
			if ( ! empty( $users_vip_by_member_type['users'] ) ) {
				$query_args['exclude'] = array_map( function( $user ) { return $user->ID; }, $users_vip_by_member_type['users'] );
			}
		}

		$verification_requests = new WP_User_Query( $query_args );

		$url  = add_query_arg( 'view', 'bp_vip_member_requests', admin_url( 'users.php' ) );
		$text = sprintf( esc_html__( 'Verification Requests %s', 'bp-vip-member' ), '<span class="count">(' . number_format_i18n( $verification_requests->get_total() ) . ')</span>' );

		$views['bp_vip_member_requests'] = sprintf( '<a href="%1$s" class="%2$s">%3$s</a>', esc_url( $url ), $class, $text );

		return $views;
	}

	/**
	 * Filter user table for the "Verification Requests" view
	 *
	 * @param array $query_args Array of user query args
	 *
	 * @return array Modified query args
	 */
	public function filter_verification_requests_users( $query_args ) {
		// Bail if not on the requests view, or verification requests are disabled
		if (  empty( $_GET['view'] ) || $_GET['view'] !== 'bp_vip_member_requests' || ! $this->settings->get_option( 'enable_verification_requests' ) ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['meta_query'][] = array(
			'relation' => 'AND',
			array(
				'key'   => 'bp_vip_member_verification_request',
				'value' => 'pending',
			),
			array(
				'relation' => 'OR',
				array(
					'key'     => $this->meta_box->meta_keys['vip'],
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => $this->meta_box->meta_keys['vip'],
					'value'   => true,
					'compare' => '!=',
				),
			),
		);

		// Exclude vip roles
		$vip_roles = $this->settings->get_option( 'vip_roles' );
		if ( ! empty( $vip_roles ) ) {
			if ( empty( $query_args['role__not_in'] ) ) {
				$query_args['role__not_in'] = array();
			}

			$query_args['role__not_in'] = array_unique( array_merge( $query_args['role__not_in'], $vip_roles ) );
		}

		// Exclude vip member types
		$vip_member_types = $this->settings->get_option( 'vip_member_types' );
		if ( ! empty( $vip_member_types ) ) {
			$users_vip_by_member_type = bp_core_get_users( array( 'type' => 'alphabetical', 'member_type__in' => $vip_member_types ) );
			if ( ! empty( $users_vip_by_member_type['users'] ) ) {
				if ( empty( $query_args['exclude'] ) ) {
					$query_args['exclude'] = array();
				}

				$query_args['exclude'] = array_unique( array_merge( $query_args['exclude'], array_map( function( $user ) { return $user->ID; }, $users_vip_by_member_type['users'] ) ) );
			}
		}

		return $query_args;
	}

	/**
	 * Maybe trigger the vip status updated event when the vip meta changed
	 *
	 * @param int $meta_id ID of the meta that just is about to change
	 * @param int $user_id ID of the user who's meta is about to change
	 * @param string $meta_key Meta key
	 * @param mixed $meta_value Meta value
	 */
	public function before_update_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
		if ( $meta_key !== $this->meta_box->meta_keys['vip'] ) {
			return;
		}

		global $bp_vip_member;

		$vip_by_role        = $bp_vip_member->is_user_vip_by_role( $user_id );
		$vip_by_member_type = $bp_vip_member->is_user_vip_by_member_type( $user_id );

		// Bail if user is already vip through other means
		if ( $vip_by_role || $vip_by_member_type ) {
			return;
		}

		$is_vip  = $meta_value == true;
		$was_vip = $bp_vip_member->is_user_vip_by_meta( $user_id );

		if ( $is_vip && ! $was_vip ) {
			do_action( 'bp_vip_member_vip_status_updated', $user_id, 'vip' );
		} else if ( ! $is_vip && $was_vip ) {
			do_action( 'bp_vip_member_vip_status_updated', $user_id, 'unvip' );
		}
	}

	/**
	 * Maybe trigger the vip status updated event when a vip role is being given or removed
	 *
	 * @param int $user_id ID of the user who's role just changed
	 * @param string $role Slug of the new role that was given to the user
	 * @param array $old_roles Old roles that the user had
	 */
	public function after_update_user_role( $user_id, $role, $old_roles ) {
		$vip_roles = $this->settings->get_option( 'vip_roles' );

		// Bail if there are no vip roles
		if ( empty( $vip_roles ) ) {
			return;
		}

		global $bp_vip_member;
		$vip_by_meta        = $bp_vip_member->is_user_vip_by_meta( $user_id );
		$vip_by_member_type = $bp_vip_member->is_user_vip_by_member_type( $user_id );

		// Bail if user is already vip through other means
		if ( $vip_by_meta || $vip_by_member_type ) {
			return;
		}

		$has_vip_role = in_array( $role, $vip_roles );
		$had_vip_role = ! empty( array_intersect( $old_roles, $vip_roles ) );

		if ( $has_vip_role && ! $had_vip_role ) {
			do_action( 'bp_vip_member_vip_status_updated', $user_id, 'vip' );
		} else if ( ! $has_vip_role && $had_vip_role ) {
			do_action( 'bp_vip_member_vip_status_updated', $user_id, 'unvip' );
		}
	}

	/**
	 * Maybe trigger the vip status updated event when a vip member type is being given or removed
	 *
	 * @param int    $object_id  Object ID.
	 * @param array  $terms      An array of object terms.
	 * @param array  $tt_ids     An array of term taxonomy IDs.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether to append new terms to the old terms.
	 * @param array  $old_tt_ids Old array of term taxonomy IDs.
	 */
	public function after_update_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( function_exists( 'bp_get_member_type_tax_name' ) && $taxonomy !== bp_get_member_type_tax_name() ) {
			return;
		}

		$vip_member_types = $this->settings->get_option( 'vip_member_types' );

		// Bail if there are no vip member types
		if ( empty( $vip_member_types ) ) {
			return;
		}

		global $bp_vip_member;
		$vip_by_meta = $bp_vip_member->is_user_vip_by_meta( $object_id );
		$vip_by_role = $bp_vip_member->is_user_vip_by_role( $object_id );

		// Bail if user is already vip through other means
		if ( $vip_by_meta || $vip_by_role ) {
			return;
		}

		$old_term_slugs = array();
		if ( ! empty( $old_tt_ids ) ) {
			$old_term_slugs = get_terms( array(
				'taxonomy'         => $taxonomy,
				'term_taxonomy_id' => $old_tt_ids,
				'fields'           => 'slugs',
			) );
		}

		$has_vip_member_type = ! empty( array_intersect( $terms, $vip_member_types ) );
		$had_vip_member_type = ! empty( array_intersect( $old_term_slugs, $vip_member_types ) );

		if ( $has_vip_member_type && ! $had_vip_member_type ) {
			do_action( 'bp_vip_member_vip_status_updated', $object_id, 'vip' );
		} else if ( ! $has_vip_member_type && $had_vip_member_type ) {
			do_action( 'bp_vip_member_vip_status_updated', $object_id, 'unvip' );
		}
	}
}
