<?php
/**
 * Class BP_Vip_Member_Meta_Box
 *
 * @author  extensionforge.com
 * @since   1.0.0
 * @package bp-vip-member/admin/meta-box
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BP_Vip_Member_Meta_Box' ) ) :
	/**
	 * Class BP_Vip_Member_Meta_Box.
	 *
	 * @author extensionforge.com
	 * @package bp-vip-member/admin/meta-box
	 */
	class BP_Vip_Member_Meta_Box {
		/**
		 * BP_Vip_Member_Meta_Box constructor.
		 */
		public function __construct() {
			$this->name         = 'bp_vip_member_meta_box';
			$this->nonce_name   = "{$this->name}_nonce";
			$this->nonce_action = plugin_basename( __FILE__ );

			$this->meta_keys = array(
				'vip' => 'bp_vip_member',
			);

			add_action( 'bp_members_admin_user_metaboxes', array( $this, 'add_meta_box'  ), 10 );
			add_action( 'bp_members_admin_update_user',    array( $this, 'save_meta_box' ), 10 );
		}

		/**
		 * Add meta box.
		 *
		 * @since 1.0.0
		 */
		public function add_meta_box() {
			if ( current_user_can( 'manage_options' ) ) {
				add_meta_box(
					$this->name,
					esc_html__( 'Verify Member', 'bp-vip-member' ),
					array( $this, 'render_meta_box' ), // callback
					get_current_screen()->id
				);
			}
		}


		/**
		 * Render meta box.
		 *
		 * @since 1.0.0
		 */
		public function render_meta_box() {
			if ( ! empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
				$user_id = intval( $_GET['user_id'] );
			} else {
				$user_id = get_current_user_id();
			}

			/** @var BP_Vip_Member $bp_vip_member */
			global $bp_vip_member;
			$is_vip_by_role        = $bp_vip_member->is_user_vip_by_role( $user_id );
			$is_vip_by_member_type = $bp_vip_member->is_user_vip_by_member_type( $user_id );
			$is_vip_by_meta        = $bp_vip_member->is_user_vip_by_meta( $user_id );
			?>
			<table class="form-table">
				<tbody>
				<tr class="<?php echo esc_attr( $this->meta_keys['vip'] ); ?>-wrap">
					<th scope="row"><?php esc_html_e( 'Vip Member', 'bp-vip-member' ); ?></th>
					<td>
						<?php if ( $is_vip_by_role ) : ?>
							<p><em><?php esc_html_e( 'This user belongs to a vip role. If you wish to unverify this user, please assign another role to them.', 'bp-vip-member' ); ?></em></p>
						<?php elseif ( $is_vip_by_member_type ) : ?>
							<p><em><?php esc_html_e( 'This user belongs to a vip member type. If you wish to unverify this user, please remove their member type or assign another member type to them.', 'bp-vip-member' ); ?></em></p>
						<?php else : ?>
							<label for="<?php echo esc_attr( $this->meta_keys['vip'] ); ?>">
								<input name="<?php echo esc_attr( $this->meta_keys['vip'] ); ?>" id="<?php echo esc_attr( $this->meta_keys['vip'] ); ?>" type="checkbox" <?php checked( $is_vip_by_meta, true ); ?>>
								<?php esc_html_e( 'Mark this member as vip', 'bp-vip-member' ); ?>
							</label>
						<?php endif; ?>
					</td>
				</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( $this->nonce_action, $this->nonce_name );
		}

		/**
		 * Save meta data.
		 *
		 * @since 1.0.0
		 */
		public function save_meta_box() {
			if ( $this->can_save() ) {
				if ( ! empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
					$user_id = intval( $_GET['user_id'] );
				} else {
					$user_id = get_current_user_id();
				}

				update_user_meta( $user_id, $this->meta_keys['vip'], ! empty( $_POST[ $this->meta_keys['vip'] ] ) );
			}
		}

		/**
		 * Check if meta box can be saved
		 *
		 * @return bool
		 */
		private function can_save() {
			return (
				isset( $_POST['save'] ) &&
				isset( $_POST[ $this->nonce_name ] ) &&
				wp_verify_nonce( $_POST[ $this->nonce_name ], $this->nonce_action )
			);
		}
	}

endif;

return new BP_Vip_Member_Meta_Box();
