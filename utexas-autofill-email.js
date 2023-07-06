jQuery( function() {
  jQuery("#user_login").blur(function(e) {
    var username = jQuery(this).val();
    if (username) {
      jQuery("#email").val( username + "@eid.utexas.edu");
    }
  });
});

