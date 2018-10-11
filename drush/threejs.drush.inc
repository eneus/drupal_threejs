<?php

/**
 * @file
 * Drush integration for ThreeJS.
 */

/**
 * The ThreeJS plugin URI.
 */
define('THREEJS_DOWNLOAD_URL', 'https://github.com/mrdoob/three.js/archive/dev.zip');
define('THREEJS_DOWNLOAD_PREFIX', 'threejs-');

/**
 * Implements hook_drush_command().
 */
function threejs_drush_command() {
  $items = [];

  // The key in the $items array is the name of the command.
  $items['threejs-plugin'] = [
    'callback' => 'drush_threejs_plugin',
    'description' => dt('Download and install the ThreeJS plugin.'),
  // No bootstrap.
    'bootstrap' => DRUSH_BOOTSTRAP_DRUSH,
    'arguments' => [
      'path' => dt('Optional. A path where to install the ThreeJS plugin. If omitted Drush will use the default location.'),
    ],
    'aliases' => ['webgl'],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function threejs_drush_help($section) {
  switch ($section) {
    case 'drush:threejs-plugin':
      return dt('Download and install the ThreeJS plugin from GitHub, default location is /libraries.');
  }
}

/**
 * Implements drush_MODULE_post_pm_enable().
 */
function drush_threejs_post_pm_enable() {
  $modules = func_get_args();
  if (in_array('threejs', $modules)) {
    drush_threejs_plugin();
  }
}

/**
 * Command to download the ThreeJS plugin.
 */
function drush_threejs_plugin() {
  $args = func_get_args();
  if (!empty($args[0])) {
    $path = $args[0];
  }
  else {
    $path = 'libraries';
  }

  // Create the path if it does not exist.
  if (!is_dir($path)) {
    drush_op('mkdir', $path);
    drush_log(dt('Directory @path was created', ['@path' => $path]), 'notice');
  }

  // Set the directory to the download location.
  $olddir = getcwd();
  chdir($path);

  // Download the zip archive.
  if ($filepath = drush_download_file(THREEJS_DOWNLOAD_URL)) {
    $filename = basename($filepath);
    $dirname = THREEJS_DOWNLOAD_PREFIX . ltrim(basename($filepath, '.zip'), 'v');

    // Remove any existing ThreeJS plugin directory.
    if (is_dir($dirname) || is_dir('three.js')) {
      drush_delete_dir($dirname, TRUE);
      drush_delete_dir('three.js', TRUE);
      drush_log(dt('A existing ThreeJS plugin was deleted from @path', ['@path' => $path]), 'notice');
    }

    // Decompress the zip archive.
    drush_tarball_extract($filename);

    // Change the directory name to "three.js" if needed.
    if ($dirname != 'three.js') {
      drush_move_dir($dirname, 'three.js', TRUE);
      $dirname = 'three.js';
    }
  }

  if (is_dir($dirname)) {
    drush_log(dt('ThreeJS plugin has been installed in @path', ['@path' => $path]), 'success');
  }
  else {
    drush_log(dt('Drush was unable to install the ThreeJS plugin to @path', ['@path' => $path]), 'error');
  }

  // Set working directory back to the previous working directory.
  chdir($olddir);
}
