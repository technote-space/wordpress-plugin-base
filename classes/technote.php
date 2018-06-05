<?php
/**
 * Technote
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

/**
 * Class Technote
 * @package Technote
 * @property string $original_plugin_name
 * @property string $plugin_name
 * @property string $plugin_file
 * @property array $plugin_data
 * @property Models\Define $define
 * @property \Technote\Models\Config $config
 * @property \Technote\Models\Setting $setting
 * @property \Technote\Models\Option $option
 * @property \Technote\Models\Device $device
 * @property \Technote\Models\Minify $minify
 * @property \Technote\Models\Filter $filter
 * @property \Technote\Models\User $user
 * @property \Technote\Models\Post $post
 * @property \Technote\Models\Loader $loader
 * @property \Technote\Models\Log $log
 * @property \Technote\Models\Input $input
 * @property \Technote\Models\Db $db
 */
class Technote {

	/** @var array */
	private static $instances = array();

	/**
	 * Technote constructor.
	 *
	 * @param string $plugin_name
	 * @param string $plugin_file
	 */
	private function __construct( $plugin_name, $plugin_file ) {
		require_once dirname( __FILE__ ) . DS . 'traits' . DS . 'singleton.php';
		require_once dirname( __FILE__ ) . DS . 'interfaces' . DS . 'singleton.php';
		require_once dirname( __FILE__ ) . DS . 'models' . DS . 'define.php';

		add_action( 'init', function () use ( $plugin_name, $plugin_file ) {
			$this->initialize( $plugin_name, $plugin_file );
		}, 1 );
	}

	/**
	 * @param string $plugin_name
	 * @param string $plugin_file
	 *
	 * @return Technote
	 */
	public static function get_instance( $plugin_name, $plugin_file ) {
		if ( ! isset( static::$instances[ $plugin_name ] ) ) {
			static::$instances[ $plugin_name ] = new static( $plugin_name, $plugin_file );
		}

		return static::$instances[ $plugin_name ];
	}

	/**
	 * @param string $plugin_name
	 * @param string $plugin_file
	 */
	private function initialize( $plugin_name, $plugin_file ) {
		$this->setup_property( $plugin_name, $plugin_file );
		$this->setup_update();
		$this->setup_textdomain();
	}

	/**
	 * @param string $plugin_name
	 * @param string $plugin_file
	 */
	private function setup_property( $plugin_name, $plugin_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->original_plugin_name = $plugin_name;
		$this->plugin_name          = strtolower( $plugin_name );
		$this->plugin_file          = $plugin_file;
		$this->plugin_data          = get_plugin_data( $this->plugin_file );
		$this->define               = Models\Define::get_instance( $this );
		spl_autoload_register( array( $this, 'load_class' ) );

		$this->input   = Models\Input::get_instance( $this );
		$this->config  = Models\Config::get_instance( $this );
		$this->setting = Models\Setting::get_instance( $this );
		$this->option  = Models\Option::get_instance( $this );
		$this->log     = Models\Log::get_instance( $this );

		$this->device = Models\Device::get_instance( $this );
		$this->minify = Models\Minify::get_instance( $this );
		$this->user   = Models\User::get_instance( $this );
		$this->post   = Models\Post::get_instance( $this );
		$this->loader = Models\Loader::get_instance( $this );
		$this->db     = Models\Db::get_instance( $this );

		$this->filter = Models\Filter::get_instance( $this );
	}

	/**
	 * setup update checker
	 */
	private function setup_update() {
		$update_info_file_url = $this->get_config( 'config', 'update_info_file_url' );
		if ( ! empty( $update_info_file_url ) ) {
			\Puc_v4_Factory::buildUpdateChecker(
				$update_info_file_url,
				$this->plugin_file,
				$this->plugin_name
			);
		}
	}

	/**
	 * setup textdomain
	 */
	private function setup_textdomain() {
		$text_domain = $this->get_config( 'config', 'text_domain' );
		if ( ! empty( $text_domain ) ) {
			load_plugin_textdomain( $this->get_config( 'config', 'text_domain' ), false, $this->define->plugin_languages_dir );
		}
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function translate( $value ) {
		$text_domain = $this->get_config( 'config', 'text_domain' );
		if ( ! empty( $text_domain ) ) {
			$value = __( $value, $text_domain );
		}

		return $value;
	}

	/**
	 * @param string $name
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_config( $name, $key, $default = null ) {
		return $this->config->get( $name, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get_option( $key, $default = '' ) {
		return $this->option->get( $key, $default );
	}

	/**
	 * @param null|string $capability
	 *
	 * @return bool
	 */
	public function user_can( $capability = null ) {
		return $this->user->user_can( $capability );
	}

	/**
	 * @param mixed $message
	 */
	public function log( $message ) {
		if ( $message instanceof \Exception ) {
			$this->log->log( $message->getMessage() );
			$this->log->log( $message->getTraceAsString() );
		} else {
			$this->log->log( $message );
		}
	}

	/**
	 * @param string $message
	 */
	public function add_error( $message ) {
		if ( ! isset( $this->loader->admin ) ) {
			add_action( 'admin_notices', function () use ( $message ) {
				$this->loader->admin->add_error( $message );
			}, 9 );
		} else {
			$this->loader->admin->add_error( $message );
		}
	}

	/**
	 * @param string $message
	 */
	public function add_message( $message ) {
		if ( ! isset( $this->loader->admin ) ) {
			add_action( 'admin_notices', function () use ( $message ) {
				$this->loader->admin->add_message( $message );
			}, 9 );
		} else {
			$this->loader->admin->add_message( $message );
		}
	}

	/**
	 * @param $class
	 *
	 * @return bool
	 */
	public function load_class( $class ) {

		$class = ltrim( $class, '\\' );
		$dir   = null;
		if ( preg_match( "#^{$this->define->plugin_namespace}#", $class ) ) {
			$class = preg_replace( "#^{$this->define->plugin_namespace}#", '', $class );
			$dir   = $this->define->plugin_classes_dir;
		} elseif ( preg_match( "#^{$this->define->lib_namespace}#", $class ) ) {
			$class = preg_replace( "#^{$this->define->lib_namespace}#", '', $class );
			$dir   = $this->define->lib_classes_dir;
		}

		if ( isset( $dir ) ) {

			$class = ltrim( $class, '\\' );
			$class = strtolower( $class );
			$path  = $dir . DS . str_replace( '\\', DS, $class ) . '.php';
			if ( is_readable( $path ) ) {
				require_once $path;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 */
	public function get_page_slug( $file ) {
		return basename( $file, '.php' );
	}
}

