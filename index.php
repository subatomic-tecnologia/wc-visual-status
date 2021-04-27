<?php
/**
 * Plugin Name: WooCommerce Visual Status by Subatomic
 * Description: A visual way of showing your WooCommerce order statuses on the client's dashboard
 * Author: Subatomic
 * Author URI: https://subatomic.rocks/
 * Plugin URI: https://github.com/subatomic-rocks/wc-visual-status/
 * Version: 0.1.0
 * Requires at least: 5.5
 * Tested up to: 5.7
 * WC requires at least: 5.2
 * WC tested up to: 5.2.2
 * Requires PHP: 7.0 
 * License: BSD-3-Clause
 * License URI: http://opensource.org/licenses/BSD-3-Clause
 */

// Loads the relevant plugin classes here
require_once( __DIR__ . '/src/Core.php' );

// No direct access here, please :)
\Subatomic\WordPress\WooCommerceVisualStatus\Core::preventDirectAccess();

// Boots the plugin.
\Subatomic\WordPress\WooCommerceVisualStatus\Core::boot( 'res', plugin_dir_url( __FILE__ ), plugin_dir_path( __FILE__ ) );