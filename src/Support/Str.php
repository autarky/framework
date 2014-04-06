<?php
/**
 * This file is part of the Autarky package.
 *
 * (c) Andreas Lutro <anlutro@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Autarky\Support;

class Str
{
  public static function contains($haystack, $needle, $caseSensitive = true)
  {
    $fn = $caseSensitive ? 'strstr' : 'stristr';
    return $fn($haystack, $needle) !== false;
  }

  public static function containsAny($haystack, array $needles, $caseSensitive = true)
  {
    foreach($needles as $needle) {
      if (static::contains($haystack, $needle, $caseSensitive)) {
        return true;
      }
    }
    return false;
  }

  public static function containsAll($haystack, array $needles, $caseSensitive = true)
  {
    foreach($needles as $needle) {
      if (!static::contains($haystack, $needle, $caseSensitive)) {
        return false;
      }
    }
    return true;
  }
}