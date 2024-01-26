<?php

// =============================================================================
// LEGACY CLEANUP
// =============================================================================

/**
 * Clean up legacy fields, files, and settings
 *
 * This only runs on hardcoded cutoff dates, once. It will only run again if the
 * date in this function is changed on an update.
 *
 * @since 5.8.7
 */

function fictioneer_legacy_cleanup() {
  $cutoff = 202419100000; // YYYYMMDDHHMM
  $last_cleanup = absint( get_option( 'fictioneer_last_cleanup' ) );

  // Sitemap cleanup
  if ( $last_cleanup < 202401130000 ) {
    $old_sitemap = ABSPATH . '/sitemap.xml';

    if ( file_exists( $old_sitemap ) ) {
      unlink( $old_sitemap );
    }

    flush_rewrite_rules();
  }

  // Purge theme caches after update
  if ( $last_cleanup < 202419100000 ) {
    fictioneer_delete_transients_like( 'fictioneer_' );
  }

  // Remember cleanup
  update_option( 'fictioneer_last_cleanup', $cutoff );
}
add_action( 'init', 'fictioneer_legacy_cleanup', 99 );

// =============================================================================
// THEME DEACTIVATION
// =============================================================================

/**
 * Clean up when the theme is deactivated
 *
 * Always deletes all theme-related Transients in the database. If the option to
 * delete all settings and theme mods is activated, these will be removed as
 * well but otherwise preserved.
 *
 * @since 4.7.0
 * @since 5.7.4 - Updated to use SQL queries.
 *
 * @global wpdb $wpdb  WordPress database object.
 */

function fictioneer_theme_deactivation() {
  global $wpdb;

  // Delete all theme Transients
  fictioneer_delete_transients_like( 'fictioneer_' );

  // Only continue if the user wants to delete all options/mods
  if ( get_option( 'fictioneer_delete_theme_options_on_deactivation', false ) ) {
    // Remove theme mods
    remove_theme_mods();

    // SQL to delete all options starting with "fictioneer_"
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'fictioneer_%'" );

    // SQL to delete all user meta starting with "fictioneer_"
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'fictioneer_%'" );

    // SQL to delete all comment meta starting with "fictioneer_"
    $wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE meta_key LIKE 'fictioneer_%'");

    // Reset user roles
    remove_role( 'fcn_moderator' );
  }
}
add_action( 'switch_theme', 'fictioneer_theme_deactivation' );

?>
