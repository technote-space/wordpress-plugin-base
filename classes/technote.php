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

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

/**
 * Class Technote
 * @property string $original_plugin_name
 * @property string $plugin_name
 * @property string $plugin_file
 * @property array $plugin_data
 * @property \Technote\Models\Define $define
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
 * @property \Technote\Models\Uninstall $uninstall
 */
class Technote {

	/** @var array of \Technote */
	private static $instances = array();

	/** @var bool $initialized */
	private $initialized = false;

	/** @var string $original_plugin_name */
	public $original_plugin_name;
	/** @var string $plugin_name */
	public $plugin_name;
	/** @var string $plugin_file */
	public $plugin_file;
	/** @var array $plugin_data */
	public $plugin_data;
	/** @var \Technote\Models\Define $define */
	public $define;
	/** @var \Technote\Models\Config $config */
	public $config;
	/** @var \Technote\Models\Setting $setting */
	public $setting;
	/** @var \Technote\Models\Option $option */
	public $option;
	/** @var \Technote\Models\Device $device */
	public $device;
	/** @var \Technote\Models\Minify $minify */
	public $minify;
	/** @var \Technote\Models\Filter $filter */
	public $filter;
	/** @var \Technote\Models\User $user */
	public $user;
	/** @var \Technote\Models\Post $post */
	public $post;
	/** @var \Technote\Models\Loader $loader */
	public $loader;
	/** @var \Technote\Models\Log $log */
	public $log;
	/** @var \Technote\Models\Input $input */
	public $input;
	/** @var \Technote\Models\Db $db */
	public $db;
	/** @var \Technote\Models\Uninstall $db */
	public $uninstall;

	/**
	 * Technote constructor.
	 *
	 * @param string $plugin_name
	 * @param string $plugin_file
	 */
	private function __construct( $plugin_name, $plugin_file ) {
		require_once __DIR__ . DS . 'traits' . DS . 'singleton.php';
		require_once __DIR__ . DS . 'interfaces' . DS . 'singleton.php';
		require_once __DIR__ . DS . 'models' . DS . 'define.php';

		$this->original_plugin_name = $plugin_name;
		$this->plugin_file          = $plugin_file;
		$this->plugin_name          = strtolower( $this->original_plugin_name );
		$this->define               = \Technote\Models\Define::get_instance( $this );

		add_action( 'init', function () {
			$this->initialize();
		}, 1 );
	}

	/**
	 * @param string $plugin_name
	 * @param string $plugin_file
	 *
	 * @return Technote
	 */
	public static function get_instance( $plugin_name, $plugin_file ) {
		if ( ! isset( self::$instances[ $plugin_name ] ) ) {
			self::$instances[ $plugin_name ] = new static( $plugin_name, $plugin_file );
		}

		return self::$instances[ $plugin_name ];
	}

	/**
	 * initialize
	 */
	private function initialize() {
		if ( $this->initialized ) {
			return;
		}
		$this->initialized = true;
		$this->setup_property();
		$this->setup_update();
		$this->setup_textdomain();
		$this->filter->do_action( 'app_initialized' );
	}

	/**
	 * setup property
	 */
	private function setup_property() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$this->plugin_data = get_plugin_data( $this->plugin_file );
		spl_autoload_register( array( $this, 'load_class' ) );

		$this->uninstall = \Technote\Models\Uninstall::get_instance( $this );
		$this->input     = \Technote\Models\Input::get_instance( $this );
		$this->config    = \Technote\Models\Config::get_instance( $this );
		$this->setting   = \Technote\Models\Setting::get_instance( $this );
		$this->option    = \Technote\Models\Option::get_instance( $this );
		$this->log       = \Technote\Models\Log::get_instance( $this );

		$this->device = \Technote\Models\Device::get_instance( $this );
		$this->minify = \Technote\Models\Minify::get_instance( $this );
		$this->user   = \Technote\Models\User::get_instance( $this );
		$this->post   = \Technote\Models\Post::get_instance( $this );
		$this->loader = \Technote\Models\Loader::get_instance( $this );
		$this->db     = \Technote\Models\Db::get_instance( $this );

		$this->filter = \Technote\Models\Filter::get_instance( $this );
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
	 * @param string $group
	 * @param bool $error
	 * @param bool $escape
	 */
	public function add_message( $message, $group = '', $error = false, $escape = true ) {
		if ( ! isset( $this->loader->admin ) ) {
			add_action( 'admin_notices', function () use ( $message, $group, $error, $escape ) {
				$this->loader->admin->add_message( $message, $group, $error, $escape );
			}, 9 );
		} else {
			$this->loader->admin->add_message( $message, $group, $error, $escape );
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

	/**
	 * @param $name
	 * @param $arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( preg_match( '#register_uninstall_(.+)$#', $name, $matches ) ) {
			$plugin_base_name = $matches[1];
			self::uninstall( $plugin_base_name );
		}
	}

	/**
	 * @param string $plugin_base_name
	 */
	private static function uninstall( $plugin_base_name ) {
		$app = self::find_plugin( $plugin_base_name );
		if ( ! isset( $app ) ) {
			return;
		}
		$app->initialize();
		$app->uninstall->uninstall();
	}

	/**
	 * @param string $plugin_base_name
	 *
	 * @return \Technote|null
	 */
	private static function find_plugin( $plugin_base_name ) {

		/** @var \Technote $instance */
		foreach ( self::$instances as $plugin_name => $instance ) {
			if ( $instance->define->plugin_base_name === $plugin_base_name ) {
				return $instance;
			}
		}

		return null;
	}
}

