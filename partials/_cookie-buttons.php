<?php
/**
 * Partial: Cookie Buttons
 *
 * Renders buttons to reset your consent and clear all cookies.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 3.0
 */


// No direct access!
defined( 'ABSPATH' ) OR exit;

?>

<div class="cookies spacing-top spacing-bottom ">
  <button type="button" data-click="reset-consent" class="button"><?php _e( 'Reset Consent', 'fictioneer' ); ?></button>
  <button type="button" data-click="clear-cookies" data-message="<?php _e( 'Cookies and local storage have been cleared. To keep it that way, you should leave the site.', 'fictioneer' ); ?>" class="button"><?php _e( 'Clear Cookies', 'fictioneer' ); ?></button>
</div>
