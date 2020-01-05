<?php
/**
 * Load plugin's files with check for installing it as a standalone plugin or
 * a module of a theme / plugin. If standalone plugin is already installed, it
 * will take higher priority.
 *
 * @package Meta Box
 */

/**
 * Plugin loader class.
 *
 * @package Meta Box
 */
class RWMB_Loader {
	/**
	 * Define plugin constants.
	 */
	protected function constants() {
		// Script version, used to add version for scripts and styles.
		define( 'RWMB_VER', '5.2.4' );

		list( $path, $url ) = self::get_path( dirname( dirname( __FILE__ ) ) );

		// Plugin URLs, for fast enqueuing scripts and styles.
		define( 'RWMB_URL', $url );
		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		// Plugin paths, for including files.
		define( 'RWMB_DIR', $path );
		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
	}

	/**
	 * Get plugin base path and URL.
	 * The method is static and can be used in extensions.
	 *
	 * @link http://www.deluxeblogtips.com/2013/07/get-url-of-php-file-in-wordpress.html
	 * @param string $path Base folder path.
	 * @return array Path and URL.
	 */
	public static function get_path( $path = '' ) {
		// Plugin base path.
		$path = wp_normalize_path( untrailingslashit( $path ) );

		if ( self::is_as_plugin() ) {
			$url = untrailingslashit( plugins_url( '', $path . '/' . basename( $path ) . '.php' ) );
		} else {
			$path_split = explode( '/' . get_template() . '/', trailingslashit( $path ) );
			$path_index = count( $path_split ) - 1;

			if ( self::is_as_child_theme() ) {
				$url = trailingslashit( get_stylesheet_directory_uri() ) . trim( $path_split[ $path_index ], '/' );
			} else {
				$url = trailingslashit( get_template_directory_uri() ) . trim( $path_split[ $path_index ], '/' );
			}
		}

		$path = trailingslashit( $path );
		$url  = trailingslashit( $url );

		return array( $path, $url );
	}

	/**
	 * Check if integrated as plugin.
	 *
	 * @return bool
	 */
	public static function is_as_plugin() {
		return self::is_as_theme() || self::is_as_child_theme() ? false : true;
	}

	/**
	 * Check if integrated as theme.
	 *
	 * @return bool
	 */
	public static function is_as_theme() {
		if ( defined( 'TEMPLATEPATH' ) && ! self::is_child_theme_active() && 0 === strpos( wp_normalize_path( __FILE__ ), wp_normalize_path( get_template_directory() ) ) ) {
			return true;
		}

		return ! self::is_child_theme_active() && false !== strpos( wp_normalize_path( __FILE__ ), '/' . get_template() . '/' );
	}

	/**
	 * Check if integrated as child theme.
	 *
	 * @return bool
	 */
	public static function is_as_child_theme() {
		if ( defined( 'STYLESHEETPATH' ) && self::is_child_theme_active() && 0 === strpos( wp_normalize_path( __FILE__ ), wp_normalize_path( get_stylesheet_directory() ) ) ) {
			return true;
		}

		return self::is_child_theme_active() && false !== strpos( wp_normalize_path( __FILE__ ), '/' . get_template() . '/' );
	}

	/**
	 * Whether a child theme is in use.
	 *
	 * @return bool
	 */
	public static function is_child_theme_active() {
		if ( ! defined( 'TEMPLATEPATH' ) || ! defined( 'STYLESHEETPATH' ) ) {
			return false;
		}

		return TEMPLATEPATH !== STYLESHEETPATH;
	}

	/**
	 * Bootstrap the plugin.
	 */
	public function init() {
		$this->constants();

		// Register autoload for classes.
		require_once RWMB_INC_DIR . 'autoloader.php';
		$autoloader = new RWMB_Autoloader();
		$autoloader->add( RWMB_INC_DIR, 'RW_' );
		$autoloader->add( RWMB_INC_DIR, 'RWMB_' );
		$autoloader->add( RWMB_INC_DIR . 'about', 'RWMB_' );
		$autoloader->add( RWMB_INC_DIR . 'fields', 'RWMB_', '_Field' );
		$autoloader->add( RWMB_INC_DIR . 'walkers', 'RWMB_Walker_' );
		$autoloader->add( RWMB_INC_DIR . 'interfaces', 'RWMB_', '_Interface' );
		$autoloader->add( RWMB_INC_DIR . 'storages', 'RWMB_', '_Storage' );
		$autoloader->add( RWMB_INC_DIR . 'helpers', 'RWMB_Helpers_' );
		$autoloader->add( RWMB_INC_DIR . 'update', 'RWMB_Update_' );
		$autoloader->register();

		// Plugin core.
		$core = new RWMB_Core();
		$core->init();

		// Validation module.
		new RWMB_Validation();

		$sanitizer = new RWMB_Sanitizer();
		$sanitizer->init();

		$media_modal = new RWMB_Media_Modal();
		$media_modal->init();

		// WPML Compatibility.
		$wpml = new RWMB_WPML();
		$wpml->init();

		// Update.
		$update_option  = new RWMB_Update_Option();
		$update_checker = new RWMB_Update_Checker( $update_option );
		$update_checker->init();
		$update_settings = new RWMB_Update_Settings( $update_checker, $update_option );
		$update_settings->init();
		$update_notification = new RWMB_Update_Notification( $update_checker, $update_option );
		$update_notification->init();

		if ( is_admin() ) {
			$about = new RWMB_About( $update_checker );
			$about->init();
		}

		// Public functions.
		require_once RWMB_INC_DIR . 'functions.php';
	}
}
