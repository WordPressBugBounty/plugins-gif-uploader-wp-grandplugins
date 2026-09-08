<?php

namespace GPLSCore\GPLS_PLUGIN_WGR;

/**
 * Plugin Name:  WP GIF Uploader
 * Description:  The plugin offers uploading GIF and create sub-sizes without losing animation.
 * Author:       GrandPlugins
 * Author URI:   https://profiles.wordpress.org/grandplugins/
 * Plugin URI:   https://grandplugins.com/product/wp-gif-editor/
 * Domain Path:  /languages
 * Requires PHP: 7.0
 * Tested up to: 7.1
 * Text Domain:  wp-gif-editor
 * Std Name:     gpls-wgr-wp-gif-editor
 * Version:      1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_WGR\Core;
use GPLSCore\GPLS_PLUGIN_WGR\Settings;
use GPLSCore\GPLS_PLUGIN_WGR\GIF_Base;
use GPLSCore\GPLS_PLUGIN_WGR\GIF_Creator;
use GPLSCore\GPLS_PLUGIN_WGR\GIF_Editor;
use GPLSCore\GPLS_PLUGIN_WGR\GIF_Post;

if ( ! class_exists( __NAMESPACE__ . '\GPLS_WGR_WP_GIF_Editor' ) ) :


	/**
	 * Exporter Main Class.
	 */
	class GPLS_WGR_WP_GIF_Editor {

		/**
		 * Single Instance
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Plugin Info
		 *
		 * @var array
		 */
		private static $plugin_info;

		/**
		 * Debug Mode Status
		 *
		 * @var bool
		 */
		protected $debug = false;

		/**
		 * Core Object
		 *
		 * @var object
		 */
		private static $core;

		/**
		 * Settings Class Object.
		 *
		 * @var object
		 */
		public $settings;

		/**
		 * Singular init Function.
		 *
		 * @return Object
		 */
		public static function init() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Core Actions Hook.
		 *
		 * @return void
		 */
		public static function core_actions( $action_type ) {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
			self::$core = new Core( self::$plugin_info );
			if ( 'activated' === $action_type ) {
				self::$core->plugin_activated();
			} elseif ( 'deactivated' === $action_type ) {
				self::$core->plugin_deactivated();
			} elseif ( 'uninstall' === $action_type ) {
				self::$core->plugin_uninstalled();
			}
		}

		/**
		 * Plugin Activated Hook.
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::setup_plugin_info();
			if ( is_plugin_active( 'wp-gif-editor/gpls-wgr-wp-gif-editor.php' ) ) {
				deactivate_plugins( 'wp-gif-editor/gpls-wgr-wp-gif-editor.php' );
			}

			self::core_actions( 'activated' );
			register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WGR_WP_GIF_Editor', 'plugin_uninstalled' ) );
		}

		/**
		 * Plugin Deactivated Hook.
		 *
		 * @return void
		 */
		public static function plugin_deactivated() {
			self::setup_plugin_info();
			self::core_actions( 'deactivated' );
			GIF_Base::deactivated();

		}

		/**
		 * Plugin Installed hook.
		 *
		 * @return void
		 */
		public static function plugin_uninstalled() {
			self::setup_plugin_info();
			self::core_actions( 'uninstall' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			self::setup_plugin_info();
			$this->load_languages();
			$this->includes();

			self::disable_duplicate();

			self::$core     = new Core( self::$plugin_info );
			$this->settings = new Settings( self::$core, self::$plugin_info );

			GIF_Base::init( self::$plugin_info );
			GIF_Creator::init( self::$plugin_info );
			GIF_Editor::init( self::$plugin_info, self::$core );
			GIF_Post::init( self::$plugin_info, self::$core );

			self::funnel();
		}

		/**
		 * What the GIFs in this library actually weigh.
		 *
		 * filesize() per attachment is cheap on a small library and wasteful on
		 * a large one, so the scan is capped and the answer kept for a day.
		 *
		 * @return array
		 */
		public static function gif_weight() {
			$cached = get_transient( 'gpls_wgr_gif_weight' );

			if ( is_array( $cached ) ) {
				return $cached;
			}

			$ids = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_mime_type' => 'image/gif',
					'post_status'    => 'inherit',
					'numberposts'    => 500,
					'fields'         => 'ids',
				)
			);

			$heavy = 0;
			$bytes = 0;

			foreach ( $ids as $id ) {
				$file = get_attached_file( $id );

				if ( ! $file || ! file_exists( $file ) ) {
					continue;
				}

				$size   = (int) filesize( $file );
				$bytes += $size;

				if ( $size > MB_IN_BYTES ) {
					$heavy++;
				}
			}

			$weight = array(
				'gifs'  => count( $ids ),
				'heavy' => $heavy,
				'mb'    => (int) round( $bytes / MB_IN_BYTES ),
			);

			set_transient( 'gpls_wgr_gif_weight', $weight, DAY_IN_SECONDS );

			return $weight;
		}

		/**
		 * Contextual upgrade prompts on the media screens.
		 *
		 * Every number below is read from the person's own library, so each
		 * prompt is a true statement about their site rather than an advert.
		 *
		 * @return void
		 */
		private static function funnel() {
			if ( ! class_exists( 'GPLS_Funnel' ) ) {
				return;
			}

			\GPLS_Funnel::boot(
				array(
					'slug'       => 'gif-uploader-wp-grandplugins',
					'name'       => 'WP GIF Uploader',
					'textdomain' => 'wp-gif-editor',
					'cap'        => 'upload_files',
					'screens'    => array(
						'upload',     // Media Library, list and grid.
						'attachment', // Single attachment edit.
						'media_page_' . self::$plugin_info['options_page'],
					),
					// A callable, so nothing here translates until admin_notices.
					// This plugin boots on plugins_loaded, and translating that
					// early trips WordPress 6.7's "translation loading was
					// triggered too early" notice.
					'offers'     => function () {
						return array(
							array(
								'id'         => 'heavy_gifs',
								'product'    => 'wp-gif-editor',
								'when'       => function () {
									$weight = self::gif_weight();

									// One heavy GIF is a choice. Three is a pattern.
									return $weight['heavy'] >= 3 ? $weight : false;
								},
								'stat'       => '{heavy}',
								'stat_label' => esc_html__( 'over 1 MB', 'wp-gif-editor' ),
								'title'      => esc_html__( '{heavy} of your GIFs are over 1 MB each', 'wp-gif-editor' ),
								'body'       => esc_html__( 'A GIF downloads and starts playing the moment the page loads, all of it, whether or not the reader ever scrolls that far. Your {gifs} GIFs come to about {mb} MB. Pro can show the first frame as a still image and load the animation only once the page is ready, or on hover, or on click.', 'wp-gif-editor' ),
								'cta'        => esc_html__( 'See what Pro adds', 'wp-gif-editor' ),
							),
							array(
								'id'         => 'gif_subsizes',
								'product'    => 'image-sizes-controller',
								'when'       => function () {
									$weight = self::gif_weight();
									$sizes  = count( wp_get_registered_image_subsizes() );

									if ( $sizes < 6 || $weight['gifs'] < 10 ) {
										return false;
									}

									return array(
										'sizes' => $sizes,
										'gifs'  => $weight['gifs'],
										'files' => $sizes * $weight['gifs'],
									);
								},
								'stat'       => '{sizes}',
								'stat_label' => esc_html__( 'image sizes', 'wp-gif-editor' ),
								'title'      => esc_html__( 'Each GIF you upload becomes {sizes} animated copies', 'wp-gif-editor' ),
								'body'       => esc_html__( 'Keeping the animation in every subsize is this plugin\'s job, but your theme and plugins register {sizes} image sizes, so {gifs} GIFs turn into roughly {files} animated files on disk. Most sites display three or four sizes. Image Sizes Controller switches off the rest.', 'wp-gif-editor' ),
								'cta'        => esc_html__( 'See Image Sizes Controller', 'wp-gif-editor' ),
							),
							array(
								'id'         => 'edit_gifs',
								'product'    => 'wp-gif-editor',
								'when'       => function () {
									$weight = self::gif_weight();

									return $weight['gifs'] >= 5 ? $weight : false;
								},
								'stat'       => '{gifs}',
								'stat_label' => esc_html__( 'GIFs', 'wp-gif-editor' ),
								'title'      => esc_html__( 'WordPress will not let you edit these {gifs} GIFs', 'wp-gif-editor' ),
								'body'       => esc_html__( 'Crop, scale or rotate an animated GIF in the built-in image editor and it comes back as one still frame. Pro applies crop, scale, rotate and flip to GIFs with every frame intact, adds image or text watermarks, and can build a new GIF out of images you already have.', 'wp-gif-editor' ),
								'cta'        => esc_html__( 'See what Pro adds', 'wp-gif-editor' ),
							),
						);
					},
				)
			);
		}

		/**
		 * Disable Duplicate Free/Pro.
		 *
		 * @return void
		 */
		private static function disable_duplicate() {
			if ( ! empty( self::$plugin_info['duplicate_base'] ) && self::is_plugin_active( self::$plugin_info['duplicate_base'] ) ) {
				deactivate_plugins( 'gif-uploader-wp-grandplugins/gpls-wgr-wp-gif-uploader.php' );
			}
		}

		/**
		 * Is Plugin Active.
		 *
		 * @param string $plugin_basename
		 * @return boolean
		 */
		private static function is_plugin_active( $plugin_basename ) {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
			return is_plugin_active( $plugin_basename );
		}

		/**
		 * Includes Files
		 *
		 * @return void
		 */
		public function includes() {
			require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/Funnel.php';
		}

		/**
		 * Load languages Folder.
		 *
		 * @return void
		 */
		public function load_languages() {
			load_plugin_textdomain( self::$plugin_info['text_domain'], false, self::$plugin_info['path'] . 'languages/' );
		}

		/**
		 * Set Plugin Info
		 *
		 * @return array
		 */
		public static function setup_plugin_info() {
			$plugin_data = get_file_data(
				__FILE__,
				array(
					'Version'     => 'Version',
					'Name'        => 'Plugin Name',
					'URI'         => 'Plugin URI',
					'SName'       => 'Std Name',
					'text_domain' => 'Text Domain',
				),
				false
			);

			self::$plugin_info = array(
				'id'             => 802,
				'basename'       => plugin_basename( __FILE__ ),
				'version'        => $plugin_data['Version'],
				'name'           => $plugin_data['SName'],
				'text_domain'    => $plugin_data['text_domain'],
				'file'           => __FILE__,
				'plugin_url'     => $plugin_data['URI'],
				'public_name'    => $plugin_data['Name'],
				'path'           => trailingslashit( plugin_dir_path( __FILE__ ) ),
				'url'            => trailingslashit( plugin_dir_url( __FILE__ ) ),
				'options_page'   => $plugin_data['SName'] . '-settings-tab',
				'localize_var'   => str_replace( '-', '_', $plugin_data['SName'] ) . '_localize_data',
				'type'           => 'pro',
				'general_prefix' => 'gpls-plugins-general-prefix',
				'classes_prefix' => 'gpls-wgr',
				'review_link'    => 'https://wordpress.org/plugins/gif-uploader-wp-grandplugins/#reviews',
				'pro_link'       => 'https://grandplugins.com/product/wp-gif-editor/?utm_source=free&utm_medium=pro_btn&utm_content=gif-uploader-wp-grandplugins',
				'duplicate_base' => 'wp-gif-editor/gpls-wgr-wp-gif-editor.php',
			);
		}

		/**
		 * Define Constants
		 *
		 * @param string $key
		 * @param string $value
		 * @return void
		 */
		public function define( $key, $value ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
			}
		}

	}

	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\GPLS_WGR_WP_GIF_Editor', 'init' ), 10 );
	register_activation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WGR_WP_GIF_Editor', 'plugin_activated' ) );
	register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WGR_WP_GIF_Editor', 'plugin_deactivated' ) );

endif;
