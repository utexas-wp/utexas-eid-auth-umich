# utexas-eid-auth

This is a WordPress plugin that provides configuration for using the OneLogin library to sign in using Enterprise Authentication.

## Testing integration a WordPress site with OneLogin
1. Begin the integration process by requesting integration following the instructions at https://ut.service-now.com/sp?id=kb_article&number=KB0019336
1. Download the latest version of `utexas-eid-auth` at https://github.austin.utexas.edu/eis1-wcs/utexas-eid-auth/archive/refs/heads/master.zip
1. Go the site's `/wp-admin/plugin-install.php` and choose "Upload plugin"
1. Upload the zip file you downloaded.
1. Activate the plugin.
1. First attempt to sign in before an account has been provisioned `/wp-login.php?action=wp-saml-auth`. This is a configuration default that can be changed if necessary. Verify that an account is **not** automatically provisioned ("No WordPress user exists for your account. Please contact your administrator.")
1. Provision an EID-based account for yourself `terminus wp <site>.<env> -- user create <EID> <EID>@eid.utexas.edu --role=administrator`
1. Now attempt to sign in and confirm you can authenticate `/wp-login.php?action=wp-saml-auth`

## Overriding configuration on a specific site

Options defined in `wpsa-options.php` are the defaults for UT sites on Pantheon. If for some reason you must override anything set there, create your own mini-plugin by renaming `utexas-eid-auth-overrides.php.inc` to `utexas-eid-auth-overrides.php` and making relevant configuration changes. You must then activate this plugin for its changes to take effect.

- **auto_provision**: (default: `false`). For sites that should automatically create accounts from successful EID authentication, this should be changed to `true`.
- **permit_wp_login**: (default: `false`). **Changing this configuration option is currently not supported. The current design of `utexas-eid-auth` only allows SSO sign-in.**
- **allowRepeatAttributeName**: MUST be set to true (allow). The OneLogin SAML library includes a validation check for duplicate attribute names in the Authorization Response. The IAM team's SAML response includes two attributes with `FriendlyName="utexasEduPersonAffiliation"` . To avoid this being flagged as invalid, configuration of `samlauth.authentication` needs to include `security_allow_repeat_attribute_name: true` , which passes the value to the underlying library's configuration for `allowRepeatAttributeName`.

- Additional configuration options can be found in:
  - https://github.com/pantheon-systems/wp-saml-auth?tab=readme-ov-file#installation
  - https://github.com/SAML-Toolkits/php-saml/blob/master/advanced_settings_example.php





