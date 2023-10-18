<?php
/**
 * Plugin name: Vip Member for BuddyPress
 * Plugin URI:  https://github.com/Extensionforge/bp-vip-member
 * Description: This plugins allows admins to add a "Vip" badge for specific members.
 * Author:      Extensionforge.com
 * Author URI:  https://extensionforge.com/
 * Version:     1.0.0
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bp-vip-member
 * Domain Path: /languages
 *
 * @package Vip Member for BuddyPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BP_VIP_MEMBER_VERSION',         '1.0.0' );
define( 'BP_VIP_MEMBER_PLUGIN_FILE',     __FILE__ );
define( 'BP_VIP_MEMBER_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'BP_VIP_MEMBER_PLUGIN_DIR_URL',  plugin_dir_url( __FILE__ ) );
define( 'BP_VIP_MEMBER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once 'inc/class-bp-vip-member.php';
require_once 'admin/class-bp-vip-member-admin.php';

register_activation_hook( __FILE__, array( BP_Vip_Member::class, 'create_admin_verification_request_email' ) );

/**
 * Show notice if BuddyPress is not installed
 */
function bp_vip_member_dependency_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			// translators: placeholders are opening and closing <a> tag, linking to BuddyPress plugin
			printf( esc_html__( 'Vip Member for BuddyPress requires %1$sBuddyPress%2$s to be installed and activated.', 'bp-vip-member' ), '<a href="https://wordpress.org/plugins/buddypress/" target="_blank">', '</a>' );
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'bp_vip_member_dependency_notice' );

/**
 * Load plugin.
 */
function bp_vip_member_loaded() {
	// Remove admin notice if plugin is able to load
	remove_action( 'admin_notices', 'bp_vip_member_dependency_notice' );

	global $bp_vip_member;
	$bp_vip_member = new BP_Vip_Member();

	global $bp_vip_member_admin;
	$bp_vip_member_admin = new BP_Vip_Member_Admin();

	do_action( 'bp_vip_member_loaded' );
}
add_action( 'bp_include', 'bp_vip_member_loaded' );
