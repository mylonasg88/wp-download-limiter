<?php

class DownloadLimiter
{
  const META_KEY = 'daily_downloads';

  public static function can_download($user_id): bool
  {
    $downloads = get_user_meta($user_id, self::META_KEY, true);
    $today = date('Y-m-d');

    if (!is_array($downloads) || !isset($downloads['date']) || $downloads['date'] !== $today) {
      return true;
    }

    return ($downloads['count'] < self::get_limit());
  }

  /**
   * User can download x amount of any files per day. System stores it per user.
   *
   * @param $user_id
   * @return void
   */
  public static function register_download($user_id): void
  {
    $downloads = get_user_meta($user_id, self::META_KEY, true);
    $today = date('Y-m-d');

    if (!is_array($downloads) || $downloads['date'] !== $today) {
      $downloads = ['date' => $today, 'count' => 1];
    } else {
      $downloads['count']++;
    }

    update_user_meta($user_id, self::META_KEY, $downloads);
  }

  public static function remaining($user_id): int
  {
    $downloads = get_user_meta($user_id, self::META_KEY, true);
    $today = date('Y-m-d');

    if (!is_array($downloads) || $downloads['date'] !== $today) {
      return self::get_limit();
    }

    return max(0, self::get_limit() - $downloads['count']);
  }

  public static function get_limit(): int
  {
    return (int)get_option('ddl_daily_limit', 20);
  }
}
