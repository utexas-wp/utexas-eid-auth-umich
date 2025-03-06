# utexas-eid-auth

This is a WordPress plugin that provides configuration for using the OneLogin library to sign in using Enterprise Authentication.

## Testing integration a WordPress site with OneLogin
1. For the target WordPress site, run the "Push SAML data" job: https://github.austin.utexas.edu/eis1-wcs/pantheon-stewardship-tasks/actions/workflows/pantheon-site-saml-conf-push.yml
1. Download the latest version of `utexas-eid-auth` at https://github.austin.utexas.edu/eis1-wcs/utexas-eid-auth/archive/refs/heads/master.zip
2. Go the site's `/wp-admin/plugin-install.php` and choose "Upload plugin"
3. Upload the zip file you downloaded.
4. "Activate" the plugin.
6. First attempt to sign in before an account has been provisioned `/wp-login.php?action=wp-saml-auth`. This is a configuration default that can be changed if necessary. Verify that an account is **not** automatically provisioned ("No WordPress user exists for your account. Please contact your administrator.")
3. Provision an EID-based account for yourself `terminus wp <site>.<env> -- user create <EID> <EID>@eid.utexas.edu --role=administrator`
4. Now attempt to sign in and confirm you can authenticate `/wp-login.php?action=wp-saml-auth`

## Configuration notes
All pertinent configuration for the OneLogin library is found in `wpsa-options.php`. A few callouts:

- **auto_provision**: (default: FALSE). For sites that should automatically create accounts from successful EID authentication, this should be changed to TRUE.
- **permit_wp_login**: (default: FALSE). To allow any local WordPress password sign in, set to TRUE.
- **allowRepeatAttributeName**: Must be set to true (allow). The OneLogin SAML library includes a validation check for duplicate attribute names in the Authorization Response. The IAM team's SAML response includes two attributes with `FriendlyName="utexasEduPersonAffiliation"` . To avoid this being flagged as invalid, configuration of `samlauth.authentication` needs to include `security_allow_repeat_attribute_name: true` , which passes the value to the underlying library's configuration for `allowRepeatAttributeName`.
- Additional configuration options can be found in:
  -  https://github.com/pantheon-systems/wp-saml-auth?tab=readme-ov-file#installation
  - https://github.com/SAML-Toolkits/php-saml/blob/master/advanced_settings_example.php

## Overriding configuration on a specific site
Otions defined in `wpsa-options.php` are the defaults for UT sites on Pantheon. If for some reason you want to override anything set here, create your own mini-plugin. See `utexas-eid-auth-overrides.php.example`.

