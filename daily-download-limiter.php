<?php
/**
 * Plugin Name: Daily Download Limiter
 * Description: Limits logged-in users to 20 downloads per day.
 * Version: 1.0
 * Author: George Mylonas
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/includes/DownloadLimiter.php';

add_action('template_redirect', function () {
  if (!is_user_logged_in()) return;

  if (preg_match('#/mp-files/([^/]+)\.pdf/?$#', $_SERVER['REQUEST_URI'], $matches)) {
    $user_id = get_current_user_id();
    $dailyLimit = get_option('ddl_daily_limit', 20);

    if (!DownloadLimiter::can_download($user_id)) {
      wp_die("⛔ You’ve reached your daily download limit ($dailyLimit). Please try again tomorrow.");
    }

    // Log the download
    DownloadLimiter::register_download($user_id);
  }
});

add_action('admin_menu', function () {
  add_options_page(
    'Daily Download Limit Settings',
    'Download Limit',
    'manage_options',
    'daily-download-limiter',
    'ddl_settings_page'
  );
});

function ddl_settings_page()
{
  ?>
  <div class="wrap">
    <h1>Daily Download Limit</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('ddl_settings_group');
      do_settings_sections('daily-download-limiter');
      submit_button();
      ?>
    </form>
  </div>
  <?php
}

add_action('admin_init', function () {
  register_setting('ddl_settings_group', 'ddl_daily_limit');

  add_settings_section(
    'ddl_settings_section',
    'Settings',
    null,
    'daily-download-limiter'
  );

  add_settings_field(
    'ddl_daily_limit_field',
    'Max Downloads Per Day',
    'ddl_daily_limit_input',
    'daily-download-limiter',
    'ddl_settings_section'
  );
});

function ddl_daily_limit_input()
{
  $value = get_option('ddl_daily_limit', 20);
  echo '<input type="number" name="ddl_daily_limit" value="' . esc_attr($value) . '" min="1" />';
}
