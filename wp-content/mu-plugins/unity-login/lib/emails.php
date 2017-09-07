<?php
/**
 * Customize emails sent from website
 *
 */
add_filter('wp_mail_from', function($email) {
  return 'no-reply@firstvotenc.org';
});

add_filter('wp_mail_from_name', function($name) {
  return 'First Vote NC';
});
