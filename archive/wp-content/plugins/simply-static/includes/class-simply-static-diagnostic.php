<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Simply Static Diagnostic class
 *
 * Checks to ensure that the user's server and WP installation meet a set of
 * minimum requirements.
 * @package Simply_Static
 */
class Simply_Static_Diagnostic {

	/** @const */
    protected static $min_version = array(
		'php' => '5.3.0',
		'curl' => '7.15.0'
	);

	protected $description = array(
		'URLs' => array(
			array( 'function' => 'is_destination_host_a_valid_url' )
		),
		'Filesystem' => array(
			array( 'function' => 'is_temp_files_dir_readable' ),
			array( 'function' => 'is_temp_files_dir_writeable' )
		),
		'WordPress' => array(
			array( 'function' => 'is_permalink_structure_set' )
		),
		'MySQL' => array(
			array( 'function' => 'user_can_delete' ),
			array( 'function' => 'user_can_insert' ),
			array( 'function' => 'user_can_select' ),
			array( 'function' => 'user_can_create' ),
			array( 'function' => 'user_can_alter' ),
			array( 'function' => 'user_can_drop' )
		),
		'PHP' => array(
			array( 'function' => 'php_version' ),
			array( 'function' => 'has_curl' )
		)
	);

	public $results = array();

	/**
	 * Stores options for the archive manager using Simply_Static_Options
	 * @var Simply_Static_Options
	 */
	protected $options = null;

	public function __construct( $options ) {
		$this->options = $options;

		if ( $this->options->get( 'delivery_method' ) == 'local' ) {
			$local_dir = $this->options->get( 'local_dir' );
			$this->description['Filesystem'][] = array(
				'function' => 'is_local_dir_writeable'
			);
		}

		$additional_urls = sist_string_to_array( $this->options->get( 'additional_urls' ) );
		foreach ( $additional_urls as $url ) {
			$this->description['URLs'][] = array(
				'function' => 'is_additional_url_valid',
				'param' => $url
			);
		}

		$additional_files = sist_string_to_array( $this->options->get( 'additional_files' ) );
		foreach ( $additional_files as $file ) {
			$this->description['Filesystem'][] = array(
				'function' => 'is_additional_file_valid',
				'param' => $file
			);
		}

		foreach ( $this->description as $title => $tests ) {
			$this->results[ $title ] = array();
			foreach ( $tests as $test ) {
				$param = isset( $test['param'] ) ? $test['param'] : null;
				$result = $this->$test['function']( $param );

				if ( ! isset( $result['message'] ) ) {
					$result['message'] = $result['test'] ? __( 'OK', Simply_Static::SLUG ) : __( 'FAIL', Simply_Static::SLUG );
				}

				$this->results[ $title ][] = $result;
			}
		}
	}

	public function is_destination_host_a_valid_url() {
		$destination_scheme = $this->options->get( 'destination_scheme' );
		$destination_host = $this->options->get( 'destination_host' );
		$destination_url = $destination_scheme . '://' . $destination_host;
		$label = sprintf( __( 'Checking if Destination URL <code>%s</code> is valid', Simply_Static::SLUG ), $destination_url );
		return array(
			'label' => $label,
			'test' => filter_var( $destination_url, FILTER_VALIDATE_URL ) !== false
		);
	}

	public function is_additional_url_valid( $url ) {
		$label = sprintf( __( 'Checking if Additional URL <code>%s</code> is valid', Simply_Static::SLUG ), $url );
		if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			$test = false;
			$message = __( 'Not a valid URL', Simply_Static::SLUG );
		} else if ( ! sist_is_local_url( $url ) ) {
			$test = false;
			$message = __( 'Not a local URL', Simply_Static::SLUG );
		} else {
			$test = true;
			$message = null;
		}

		return array(
			'label' => $label,
			'test' => $test,
			'message' => $message
		);
	}

	public function is_additional_file_valid( $file ) {
		$label = sprintf( __( 'Checking if Additional File/Dir <code>%s</code> is valid', Simply_Static::SLUG ), $file );
		if ( stripos( $file, get_home_path() ) !== 0 && stripos( $file, WP_PLUGIN_DIR ) !== 0 && stripos( $file, WP_CONTENT_DIR ) !== 0 ) {
			$test = false;
			$message = __( 'Not a valid path', Simply_Static::SLUG );
		} else if ( ! is_readable( $file ) ) {
			$test = false;
			$message = __( 'Not readable', Simply_Static::SLUG );;
		} else {
			$test = true;
			$message = null;
		}

		return array(
			'label' => $label,
			'test' => $test,
			'message' => $message
		);
	}

	public function is_permalink_structure_set() {
		$label = __( 'Checking if WordPress permalink structure is set', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => strlen( get_option( 'permalink_structure' ) ) !== 0
		);
	}

	public function is_temp_files_dir_readable() {
		$temp_files_dir = $this->options->get( 'temp_files_dir' );
		$label = sprintf( __( "Checking if web server can read from Temp Files Directory: <code>%s</code>", Simply_Static::SLUG ), $temp_files_dir );
		return array(
			'label' => $label,
			'test' => is_readable( $temp_files_dir )
		);
	}

	public function is_temp_files_dir_writeable() {
		$temp_files_dir = $this->options->get( 'temp_files_dir' );
		$label = sprintf( __( "Checking if web server can write to Temp Files Directory: <code>%s</code>", Simply_Static::SLUG ), $temp_files_dir );
		return array(
			'label' => $label,
			'test' => is_writable( $temp_files_dir )
		);
	}

	public function is_local_dir_writeable() {
		$local_dir = $this->options->get( 'local_dir' );
		$label = sprintf( __( "Checking if web server can write to Local Directory: <code>%s</code>", Simply_Static::SLUG ), $local_dir );
		return array(
			'label' => $label,
			'test' => is_writable( $local_dir )
		);
	}

	public function user_can_delete() {
		$label = __( 'Checking if MySQL user has <code>DELETE</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'delete' )
		);
	}

	public function user_can_insert() {
		$label = __( 'Checking if MySQL user has <code>INSERT</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'insert' )
		);
	}

	public function user_can_select() {
		$label = __( 'Checking if MySQL user has <code>SELECT</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'select' )
		);
	}

	public function user_can_create() {
		$label = __( 'Checking if MySQL user has <code>CREATE</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'create' )
		);
	}

	public function user_can_alter() {
		$label = __( 'Checking if MySQL user has <code>ALTER</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'alter' )
		);
	}

	public function user_can_drop() {
		$label = __( 'Checking if MySQL user has <code>DROP</code> privilege', Simply_Static::SLUG );
		return array(
			'label' => $label,
			'test' => Simply_Static_Sql_Permissions::instance()->can( 'drop' )
		);
	}

	public function php_version() {
		$label = sprintf( __( 'PHP version >= PHP %s', Simply_Static::SLUG ), self::$min_version['php'] );
		return array(
			'label' => $label,
			'test' => version_compare( phpversion(), self::$min_version['php'], '>=' ),
			'message'  => phpversion(),
		);
	}

	public function has_curl() {
		$label = __( 'Checking for cURL support', Simply_Static::SLUG );

		if ( is_callable( 'curl_version' ) ) {
			$version = curl_version();
			$test = version_compare( $version['version'], self::$min_version['curl'], '>=' );
			$message = $version['version'];
		} else {
			$test = false;
			$message = null;
		}

		return array(
			'label' => $label,
			'test' => $test,
			'message'  => $message,
		);
	}

}
