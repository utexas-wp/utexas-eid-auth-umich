<?php

/**
 * @file
 * Plugin management functions. */

/**
 * These functions are used to display messages on the dashboard. */
function utexas_missing_plugin_wp_saml__warning() {
?>
	<div class="notice notice-warning is-dismissible">
		<p><?php _e('UTexas EID authentication requires the WP SAML Auth plugin - please activate it.', 'sample-text-domain'); ?></p>
	</div>
<?php
}

/**
 * These functions are used to display messages on the dashboard. */
function utexas_activated_required_plugins__info() {
?>
	<div class="notice notice-info is-dismissible">
		<p><?php _e('Additional plugins required by UTexas WP SAML Auth were also activated: WP SAML Auth', 'wp-native-php-sessions', 'sample-text-domain'); ?></p>
	</div>
<?php
}

/**
 * Check if required plugins are active -- if not, display a warning message.
 */
function utexas_auth_check_plugins() {
	if (!is_plugin_active("wp-saml-auth/wp-saml-auth.php")) {
		add_action('admin_notices', 'utexas_missing_plugin_wp_saml__warning');
	}
}

add_action('admin_init', 'utexas_auth_check_plugins');

/**
 * Activate other required plugins when this plugin is activated.
 */
function utexas_wp_saml_auth_activate() {
	if (!is_plugin_active("wp-saml-auth/wp-saml-auth.php")) {

		add_action('admin_notices', 'utexas_activated_required_plugins__info');
		activate_plugins([("wp-saml-auth/wp-saml-auth.php")]);
	}
	if (!is_plugin_active("wp-native-php-sessions/pantheon-sessions.php")) {

		add_action('admin_notices', 'utexas_activated_required_plugins__info');
		activate_plugins([("wp-native-php-sessions/pantheon-sessions.php")]);
	}
}
