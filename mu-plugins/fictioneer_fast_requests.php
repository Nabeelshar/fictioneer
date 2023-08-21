<?php
/**
 * Plugin Name: Fictioneer Fast Requests
 * Description: Skips plugins for faster requests.
 * Version: 1.0
 * Author: Tetrakern
 * Author URI: https://github.com/Tetrakern
 * Donate link: https://ko-fi.com/tetrakern
 * License: GNU General Public License v3.0 or later
 * License URI: http://www.gnu.org/licenses/gpl.html
 */

// Check if AJAX request
if (
  ( defined( 'DOING_AJAX' ) && DOING_AJAX ) &&
  isset( $_REQUEST['fcn_fast_ajax'] ) &&
  isset( $_REQUEST['action'] ) &&
  strpos( $_REQUEST['action'], 'fictioneer_ajax' ) === 0
) {
  add_filter( 'option_active_plugins', 'fictioneer_exclude_plugins' );
}

$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

// Check REST Request
if ( strpos( $request_uri, 'wp-json/fictioneer_rest/v1/fictioneer_' ) !== false ) {
  add_filter( 'option_active_plugins', 'fictioneer_exclude_plugins' );
}

/**
 * Filters the list of active plugins
 *
 * @since 1.0.0
 *
 * @param array $plugins  An array of active plugin paths.
 *
 * @return array Filtered array of active plugins.
 */

function fictioneer_exclude_plugins( $plugins ) {
  // Setup
  $allow_list = array(
    'fictioneer-email-subscriptions/fictioneer-email-subscriptions.php'
  );

  // Remove not allowed plugins
  foreach ( $plugins as $index => $plugin ) {
    if ( ! in_array( $plugin, $allow_list ) ) {
      unset( $plugins[ $index ] );
    }
  }

  // Continue filter
  return $plugins;
}