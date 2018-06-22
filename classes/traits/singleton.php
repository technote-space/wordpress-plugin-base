<?php
/**
 * Technote Traits Singleton
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Singleton
 * @package TechnoteTraits
 * @property \Technote $app
 */
trait Singleton {

	/** @var array */
	private static $instances = array();

	/** @var array */
	private static $slugs = array();

	/** @var \Technote */
	protected $app;

	/** @var string */
	public $class_name;

	/** @var \ReflectionClass */
	public $reflection;

	/**
	 * Singleton constructor.
	 *
	 * @param \Technote $app
	 * @param \ReflectionClass $reflection
	 */
	private function __construct( \Technote $app, $reflection ) {
		$this->init( $app, $reflection );
	}

	/**
	 * @param \Technote $app
	 * @param \ReflectionClass $reflection
	 */
	protected function init( \Technote $app, $reflection ) {
		$this->app        = $app;
		$this->reflection = $reflection;
		$this->class_name = $reflection->getName();
	}

	/**
	 * initialize
	 */
	protected abstract function initialize();

	/**
	 * @param \Technote $app
	 *
	 * @return \Technote\Traits\Singleton
	 */
	public static function get_instance( \Technote $app ) {
		$class = get_called_class();
		if ( false === $class ) {
			$class = get_class();
		}
		try {
			$reflection = new \ReflectionClass( $class );

			if ( ! isset( self::$instances[ $app->plugin_name ][ $class ] ) ) {
				$instance = new static( $app, $reflection );
				$instance->initialize();
				if ( $instance instanceof \Technote\Interfaces\Uninstall && $app->uninstall ) {
					$app->uninstall->add_uninstall( array( $instance, 'uninstall' ) );
				}
				self::$instances[ $app->plugin_name ][ $class ] = $instance;
			}

			return self::$instances[ $app->plugin_name ][ $class ];
		} catch ( \Exception $e ) {
		}

		return null;
	}

	/**
	 * @param string $config_name
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function get_slug( $config_name, $suffix ) {

		if ( ! isset( self::$slugs[ $this->app->plugin_name ][ $config_name ] ) ) {
			$default = $this->app->plugin_name . $suffix;
			$slug    = $this->app->get_config( 'slug', $config_name, $default );
			if ( empty( $slug ) ) {
				$slug = $default;
			}
			self::$slugs[ $this->app->plugin_name ][ $config_name ] = $slug;
		}

		return self::$slugs[ $this->app->plugin_name ][ $config_name ];
	}

	/**
	 * @param string $tag
	 * @param string $method
	 * @param string $priority
	 * @param string $accepted_args
	 */
	public function add_filter( $tag, $method, $priority, $accepted_args ) {
		add_filter( $tag, function () use ( $method ) {
			$this->$method( ...func_get_args() );
		}, $priority, $accepted_args );
	}

	/**
	 * @return string
	 */
	protected function get_file_slug() {
		$class    = get_class( $this );
		$exploded = explode( '\\', $class );
		$slug     = end( $exploded );

		return strtolower( $slug );
	}
}
