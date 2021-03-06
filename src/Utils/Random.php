<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;


/**
 * Secure random string generator.
 */
class Random
{

	/**
	 * Generate random string.
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function generate($length = 10, $charlist = '0-9a-z')
	{
		if ($length === 0) {
			return ''; // random_bytes and mcrypt_create_iv do not support zero length
		}

		$charlist = str_shuffle(preg_replace_callback('#.-.#', function ($m) {
			return implode('', range($m[0][0], $m[0][2]));
		}, $charlist));
		$chLen = strlen($charlist);

		if (PHP_VERSION_ID >= 70000) {
			$rand3 = random_bytes($length);
		}
		if (empty($rand3) && function_exists('openssl_random_pseudo_bytes')) {
			$rand3 = openssl_random_pseudo_bytes($length);
		}
		if (empty($rand3) && function_exists('mcrypt_create_iv')) {
			$rand3 = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
		}
		if (empty($rand3) && !defined('PHP_WINDOWS_VERSION_BUILD') && is_readable('/dev/urandom')) {
			$rand3 = file_get_contents('/dev/urandom', FALSE, NULL, -1, $length);
		}
		if (empty($rand3)) {
			static $cache;
			$rand3 = $cache ?: $cache = md5(serialize($_SERVER), TRUE);
		}

		$s = '';
		for ($i = 0; $i < $length; $i++) {
			if ($i % 5 === 0) {
				list($rand, $rand2) = explode(' ', microtime());
				$rand += lcg_value();
			}
			$rand *= $chLen;
			$s .= $charlist[($rand + $rand2 + ord($rand3[$i % strlen($rand3)])) % $chLen];
			$rand -= (int) $rand;
		}
		return $s;
	}

}
