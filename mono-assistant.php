<?php
/**
 * Plugin Name: Mono Assistant
 * Plugin URI: https://github.com/Codestag/mono-assistant
 * Description: A plugin to assist Mono theme in adding widgets.
 * Author: Codestag
 * Author URI: https://codestag.com
 * Version: 1.0
 * Text Domain: mono-assistant
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Mono
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mono_Assistant' ) ) :
	/**
	 * Mono Assistant Class
	 *
	 * @since 1.0
	 */
	class Mono_Assistant {

		/**
		 * Base instance var.
		 *
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Instance method.
		 *
		 * @since 1.0
		 */
		public static function register() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Mono_Assistant ) ) {
				self::$instance = new Mono_Assistant();
				self::$instance->define_constants();
				self::$instance->includes();
			}
		}

		/**
		 * Defined constants.
		 *
		 * @since 1.0
		 */
		public function define_constants() {
			$this->define( 'MA_VERSION', '1.0' );
			$this->define( 'MA_DEBUG', true );
			$this->define( 'MA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'MA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Define a constant.
		 *
		 * @param string $name
		 * @param string $value
		 * @since 1.0
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Get file includes.
		 *
		 * @since 1.0
		 */
		public function includes() {
			require_once MA_PLUGIN_PATH . 'includes/widgets/stag-widget.php';
			require_once MA_PLUGIN_PATH . 'includes/widgets/recent-posts.php';
			require_once MA_PLUGIN_PATH . 'includes/widgets/custom-recent-posts.php';
			require_once MA_PLUGIN_PATH . 'includes/widgets/static-content.php';
			require_once MA_PLUGIN_PATH . 'includes/widgets/featured-post.php';
		}
	}
endif;


/**
 * Plugin class instance.
 *
 * @since 1.0
 */
function mono_assistant() {
	return Mono_Assistant::register();
}

/**
 * Plugin activation notice for theme requirement.
 *
 * @since 1.0
 */
function mono_assistant_activation_notice() {
	echo '<div class="error"><p>';
	echo esc_html__( 'Mono Assistant requires Mono WordPress Theme to be installed and activated.', 'mono-assistant' );
	echo '</p></div>';
}

/**
 * Plugin activation check.
 *
 * @since 1.0
 */
function mono_assistant_activation_check() {
	$theme = wp_get_theme(); // gets the current theme
	if ( 'Mono' === $theme->name || 'Mono' === $theme->parent_theme ) {
		add_action( 'after_setup_theme', 'mono_assistant' );
	} else {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'mono_assistant_activation_notice' );
	}
}

// Plugin loads.
mono_assistant_activation_check();
