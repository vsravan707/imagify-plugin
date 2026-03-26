<?php

/**
 * Disable WordPress.org updates for this fork (prevents overwriting local changes).
 *
 * @package Imagify
 */

defined('ABSPATH') || exit;

/**
 * Plugin basename for this install (e.g. imagify/imagify.php).
 *
 * @return string
 */
function imagify_fork_plugin_basename()
{
	static $basename;

	if (isset($basename)) {
		return $basename;
	}

	$basename = plugin_basename(IMAGIFY_FILE);

	return $basename;
}

/**
 * Remove this plugin from the update_plugins transient so no upgrade is offered.
 *
 * @param object|false $value Transient value.
 * @return object|false
 */
function imagify_fork_strip_plugin_update($value)
{
	if (! is_object($value) || empty($value->response) || ! is_array($value->response)) {
		return $value;
	}

	$key = imagify_fork_plugin_basename();

	if (isset($value->response[$key])) {
		unset($value->response[$key]);
	}

	return $value;
}

add_filter('site_transient_update_plugins', 'imagify_fork_strip_plugin_update');
add_filter('transient_update_plugins', 'imagify_fork_strip_plugin_update');

/**
 * Never auto-update this plugin.
 *
 * @param bool|null $update Whether to update.
 * @param object    $item   Plugin update offer.
 * @return bool|null
 */
function imagify_fork_disable_auto_update($update, $item)
{
	if (is_object($item) && isset($item->plugin) && imagify_fork_plugin_basename() === $item->plugin) {
		return false;
	}

	return $update;
}

add_filter('auto_update_plugin', 'imagify_fork_disable_auto_update', 10, 2);
