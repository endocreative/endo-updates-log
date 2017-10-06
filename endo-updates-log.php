<?php
/**
 * Plugin Name: Endo Updates Log
 * Plugin URI: http://www.endocreative.com
 * Description: Store a log entry every time a plugin or WordPress core is updated
 * Version: 1.0.0
 * Author: Endo Creative
 * Author URI: http://www.endocreative.com
 * Text Domain: endo-updates-log
 * License: GPL2
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-endo-updates-log.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_endo_updates_log() {
	$plugin = new Endo_Updates_Log();
	$plugin->run();
}
run_endo_updates_log();