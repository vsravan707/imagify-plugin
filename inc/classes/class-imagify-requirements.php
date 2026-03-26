<?php

use Imagify\User\User;

defined('ABSPATH') || die('Cheatin’ uh?');

/**
 * Class used to check that Imagify has everything it needs.
 *
 * @since  1.7.1
 * @author Grégory Viguier
 */
class Imagify_Requirements
{
	/**
	 * Cache the test results.
	 *
	 * @var    object
	 * @access protected
	 * @since  1.7.1
	 * @author Grégory Viguier
	 */
	protected static $supports = [];


	/** ----------------------------------------------------------------------------------------- */
	/** SERVER ================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Test for cURL.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function supports_curl($reset_cache = false)
	{
		if ($reset_cache || ! isset(self::$supports['curl'])) {
			self::$supports['curl'] = function_exists('curl_init') && function_exists('curl_exec');
		}

		return self::$supports['curl'];
	}

	/**
	 * Test for imageMagick and GD.
	 * Similar to _wp_image_editor_choose(), but allows to test for multiple mime types at once.
	 *
	 * @since  1.7.1
	 * @access public
	 * @see    _wp_image_editor_choose()
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function supports_image_editor($reset_cache = false)
	{
		if (! $reset_cache && isset(self::$supports['image_editor'])) {
			return self::$supports['image_editor'];
		}

		require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
		require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
		require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

		self::$supports['image_editor'] = false;

		$args = [
			'path'       => IMAGIFY_PATH . 'assets/images/imagify-logo.png',
			'mime_types' => imagify_get_mime_types('image'),
			'methods'    => Imagify_Attachment::get_editor_methods(),
		];

		/** This filter is documented in /wp-includes/media.php. */
		$implementations = apply_filters('wp_image_editors', ['WP_Image_Editor_Imagick', 'WP_Image_Editor_GD']);

		foreach ($implementations as $implementation) {
			if (! call_user_func([$implementation, 'test'], $args)) {
				continue;
			}

			foreach ($args['mime_types'] as $mime_type) {
				if (! call_user_func([$implementation, 'supports_mime_type'], $mime_type)) {
					continue 2;
				}
			}

			if (array_diff($args['methods'], get_class_methods($implementation))) {
				continue;
			}

			self::$supports['image_editor'] = true;
			break;
		}

		return self::$supports['image_editor'];
	}

	/**
	 * Test for the uploads directory.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function supports_uploads($reset_cache = false)
	{
		if (! $reset_cache && isset(self::$supports['uploads'])) {
			return self::$supports['uploads'];
		}

		self::$supports['uploads'] = Imagify_Filesystem::get_instance()->get_upload_basedir();

		if (self::$supports['uploads']) {
			self::$supports['uploads'] = Imagify_Filesystem::get_instance()->is_writable(self::$supports['uploads']);
		}

		return self::$supports['uploads'];
	}

	/**
	 * Test if external requests are blocked for Imagify.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function is_imagify_blocked($reset_cache = false)
	{
		self::$supports['imagify_blocked'] = false;
		return false;
	}


	/** ----------------------------------------------------------------------------------------- */
	/** IMAGIFY BACKUP DIRECTORIES ============================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Test for the attachments backup directory.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function attachments_backup_dir_is_writable($reset_cache = false)
	{
		if ($reset_cache || ! isset(self::$supports['attachment_backups'])) {
			self::$supports['attachment_backups'] = imagify_backup_dir_is_writable();
		}

		return self::$supports['attachment_backups'];
	}

	/**
	 * Test for the custom folders backup directory.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function custom_folders_backup_dir_is_writable($reset_cache = false)
	{
		if ($reset_cache || ! isset(self::$supports['custom_folder_backups'])) {
			self::$supports['custom_folder_backups'] = Imagify_Custom_Folders::backup_dir_is_writable();
		}

		return self::$supports['custom_folder_backups'];
	}


	/** ----------------------------------------------------------------------------------------- */
	/** IMAGIFY API ============================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Determine if the Imagify API is available by checking the API version.
	 * The result is cached for 3 minutes.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function is_api_up($reset_cache = false)
	{
		self::$supports['api_up'] = true;
		return true;
	}

	/**
	 * Test for the Imagify API key validity.
	 * A positive result is cached for 1 year.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool
	 */
	public static function is_api_key_valid($reset_cache = false)
	{
		self::$supports['api_key_valid'] = true;
		return true;
	}

	/**
	 * Test for the Imagify account quota.
	 *
	 * @since  1.7.1
	 * @since  1.9.9 Return false when the API cannot be reached.
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $reset_cache True to get a fresh value.
	 * @return bool              True when over quota. False otherwise, even when the API cannot be reached.
	 */
	public static function is_over_quota($reset_cache = false)
	{
		self::$supports['over_quota'] = false;
		return false;
	}


	/** ----------------------------------------------------------------------------------------- */
	/** CLASS CACHE ============================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Reset a test cache.
	 *
	 * @since  1.7.1
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $cache_key Cache key.
	 */
	public static function reset_cache($cache_key)
	{
		unset(self::$supports[$cache_key]);

		$transients = [
			'api_up'        => 'imagify_check_api_version',
			'api_key_valid' => 'imagify_check_licence_1',
		];

		if (isset($transients[$cache_key]) && get_site_transient($transients[$cache_key])) {
			delete_site_transient($transients[$cache_key]);
		}
	}
}
