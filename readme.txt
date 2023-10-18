=== Vip Member for BuddyPress ===
Contributors: extensionforge
Tags: buddypress, bp, vip, member, community, badge
Requires at least: 5.4
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Verify your BuddyPress members and display a VIP badge on the front-end.

== Description ==
This plugin allows you to verify your BuddyPress members individually or based on WP roles. You can also allow members to request verification directly from their member profile.

Vip members will have a VIP badge displayed on the front-end, and you can choose to display an badge for those who haven't been verified yet.

A dedicated settings tab allows you to choose where you want to display the badges:
+ Activities
+ Profiles
+ Member Lists (e.g.: member directory)
+ BuddyPress widgets
+ Private Messages
+ bbPress Forums
+ WordPress Comments and Posts

As well as other settings like the badge color or tooltip.

This plugin is also compatible with [BP Better Message](https://wordpress.org/plugins/bp-better-messages/)

This plugin requires [BuddyPress](https://wordpress.org/plugins/buddypress/).

== Installation ==
= AUTOMATIC INSTALLATION =
Automatic installation is the easiest option — WordPress will handles the file transfer, and you won’t need to leave your web browser. To do an automatic install of Vip Member for BuddyPress, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “Vip Member for BuddyPress,” then click “Search Plugins.” Once you’ve found us, you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by clicking “Install Now,” and WordPress will take it from there.

= MANUAL INSTALLATION =
Manual installation method requires downloading the Vip Member for BuddyPress plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= UPDATING =
Automatic updates should work smoothly, but we still recommend you back up your site.

== Frequently Asked Questions ==
= How do I mark a member as verified? =
+ From your admin panel go to *Users > All Users*.
+ Click on the badge in the "Vip" column to verify or unverify your members.
+ You can also verify members from their extended profile tab.

= How do I verify / unverify multiple users at once? =
+ From your admin panel go to *Users > All Users*.
+ Check all the users you want to verify or unverify.
+ In the *Bulk Actions* dropdown, choose "Verify" or "Unverify".
+ Click "Apply".

= Can I automatically verify members with "x" role or member type? =
+ Yes, visit the settings page located in *Settings > BuddyPress*, in the *Vip Member* tab.
+ Check all the roles and/or member types that should be automatically verified.
+ All members belonging to the roles or member types you chose will be displayed as verified users.

= How do I choose where the verified badge is displayed? =
+ From your admin panel go to *Settings > BuddyPress*.
+ Open the *Vip Member* tab.
+ Check the places where you want the badge displayed and click "Save Changes".

= How can I display a list of all my verified members? =
+ You can use a filtering plugin like BP Profile Search to allow filtering your directory on whether a user is verified or not.
+ You can also create a "Vip" member type, make sure to check the "Has Directory View" option, and set that new member type to be automatically verified in our Vip Members settings.

= How do I change the style of the verified badge (tooltip, color, shape, ...)? =
You can easily change the color of the badge via a color picker in the settings page.
The settings are located in *Settings > BuddyPress* in the *Vip Member* tab.

== Screenshots ==
1. BuddyPress members directory showing some verified users
2. BuddyPress profile page showing a verified user and user activities
3. BuddyPress extended profile showing the "Mark this member as verified" checkbox
4. Plugin settings page

== Changelog ==
= 1.0 =
* Initial release.
