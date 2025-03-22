<?php
namespace Fkwd\Plug\Wcrfc;

/**
 * Class Base
 *
 * @package fkwdwcrfc/src
 */
class Base {

    /**
     * The single instance of the class.
     *
     * @var Class|null
     */
    private static $instances = [];

	/**
     * Get the single instance of the class.
     *
     * @return Class|null
     */
    public static function get_instance() {
        $class = get_called_class();

        if ( ! isset( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new $class();
        }

        add_action( 'init', [ self::$instances[ $class ], 'init' ] );

        return self::$instances[ $class ];
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Initializes the function.
     *
     * @return void
     */
    public function init() {

    }

	/**
     * Generate a random secure string consisting of alphanumeric characters.
     *
     * @return string The generated string.
     */
    public function fkwd_generate_random_string( $size = 9 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_string = '';

		for ($i = 0; $i < $size; $i++) {
			$random_byte = ord(random_bytes(1));
			$random_index = $random_byte % strlen($characters);
			$random_string .= $characters[$random_index];
		}

		return $random_string;
	}

    /**
     * Validates and sanitizes what should be string values
     *
     * Options available:
     * - type: alpha|alphanumeric|email|url|attribute|html
     * - length: int
     * - min_length: int
     * - max_length: int
     * - trim: bool
     * - lowercase: bool
     * - uppercase: bool
     * - strip_tags: bool
     * - comma_delimited: bool
     * - remove_quotes: bool
     * - convert_spaces: strip|string
     * - convert_returns: strip|windows|spaces
     *
     * @param string $string - string to clean
     * @param object $options - array of options
     * @return string|null - the cleaned up string, will return null if invalid for security
     */
    public function fkwd_clean_string( $string, $options = [] ) {
        $string = (string) $string;

        if( empty( $string ) ) {
            return false;
        }

        if( !is_string( $string ) ) {
            return false;
        }

        $string = $this->fkwd_clean_string_format( $string, $options );

        if( !$this->fkwd_clean_string_length( $string, $options ) ) {
            return false;
        }

        if( !$this->fkwd_clean_string_type( $string, $options ) ) {
            return false;
        }

        $string = $this->fkwd_clean_string_returns( $string, $options );

        if( isset( $options['remove_quotes'] ) && $options['remove_quotes'] == false ) {
            $string = str_replace( [ "\"",'"' ], "", $string );
        }

        if( !empty( $options['convert_spaces'] ) && is_string( $options['convert_spaces'] ) ) {
            if( $options['convert_spaces'] == 'strip' ) {
                $string = str_replace( " ", "", $string );
            } else {
                $string = str_replace( " ", $options['convert_spaces'], $string );
            }
        }

        return $string;
    }

    /**
     * Cleans the given string based on the provided options.
     *
     * @param string $string The string to be cleaned.
     * @param array $options An array of options for cleaning the string.
     *                       Possible options are:
     *                       - trim: Whether to trim the string or not. Default is false.
     *                       - lowercase: Whether to convert the string to lowercase or not. Default is false.
     *                       - uppercase: Whether to convert the string to uppercase or not. Default is false.
     *                       - strip_tags: Whether to strip HTML tags from the string or not. Default is false.
     * @return string The cleaned string.
     */
    private function fkwd_clean_string_format( $string, $options = [] )
    {
        if( isset( $options['trim'] ) && $options['trim'] == true )
        {
            $string = trim( $string );
        }

        if( isset( $options['lowercase'] ) && $options['lowercase'] == true )
        {
            $string = strtolower( $string );
        }

        if( isset( $options['uppercase'] ) && $options['uppercase'] == true )
        {
            $string = strtoupper( $string );
        }

        if( isset( $options['strip_tags'] ) && $options['strip_tags'] == true )
        {
            if( is_bool( $options['strip_tags'] ) ) {
                $string = strip_tags( $string );

                $string = wp_strip_all_tags( $string );
            }

            if( is_array( $options['strip_tags'] ) ) {
                $string = strip_tags( $string, $options['strip_tags'] );

                $string = wp_kses( $string, $options['strip_tags'] );
            }
        }

        return $string;
    }

    /**
     * Checks if the length of a string meets specified criteria.
     *
     * @param string $string The string to check.
     * @param array $options Optional parameters:
     *                      - length: The maximum allowed length of the string.
     *                      - min_length: The minimum required length of the string.
     *                      - max_length: The maximum allowed length of the string.
     * @return bool Returns true if the length of the string meets the specified criteria, false otherwise.
     */
    private function fkwd_clean_string_length( $string, $options = [] ) {
        if( isset( $options['length'] ) && $options['length'] > 0 ) {
            if( strlen( $string ) > $options['length'] ) {
                return false;
            }
        }

        if( isset( $options['min_length'] ) && $options['min_length'] > 0 ) {
            if( strlen( $string ) < $options['min_length'] ) {
                return false;
            }
        }

        if( isset( $options['max_length'] ) && $options['max_length'] > 0 ) {
            if( strlen( $string ) > $options['max_length'] ) {
                return false;
            }
        }

        return $string;
    }

    /**
     * Validates and cleans a string based on the given options.
     *
     * @param string $string The string to be validated and cleaned.
     * @param array $options An array of options to specify the type of validation and cleaning.
     *     - type (string): The type of validation and cleaning to be performed.
     * @return bool Returns true if the string is valid and cleaned, false otherwise.
     */
    private function fkwd_clean_string_type( $string, $options = [] ) {
        if( isset( $options['type'] ) && $options['type'] == 'alpha' ) {
            if( !preg_match( '/^[a-zA-Z-]+$/i', $string ) ) {
                return false;
            }
        }

        if( isset( $options['type'] ) && $options['type'] == 'alphanumeric' ) {
            if( !preg_match( '/^[a-zA-Z0-9-]+$/i', $string ) ) {
                return false;
            }
        }

        if( isset( $options['type'] ) && $options['type'] == 'email' ) {
            if( !filter_var( $string, FILTER_VALIDATE_EMAIL ) ) {
                return false;
            }
        }

        if( isset( $options['type'] ) && $options['type'] == 'url' ) {
            if( !filter_var( $string, FILTER_VALIDATE_URL ) ) {
                return false;
            }

            $string = filter_var( $string, FILTER_SANITIZE_URL );

            $string = esc_url($string);
        }

        if( isset( $options['type'] ) && $options['type'] == 'attribute' ) {
            $string = esc_attr( $string );
        }

        if( isset( $options['type'] ) && $options['type'] == 'html' ) {
            $string = esc_html( $string );
        }

        if( isset( $options['type'] ) && $options['type'] == 'date' ) {
            $string = date_i18n( 'Y-m-d H:i:s', strtotime( $string ) );
        }

        if( !isset( $options['type'] ) || empty( $options['type'] ) ) {
            $string = sanitize_text_field($string);
        }

        return $string;
    }

    /**
     * Cleans the given string by converting returns to spaces and removing bad characters.
     *
     * @param string $string The string to be cleaned.
     * @param array $options An array of options:
     *                       - comma_delimited: If set to true, the returns will be replaced with commas instead of spaces.
     *                       - convert_returns: If set to 'strip', the returns will be completely removed.
     *                                          If set to 'windows', the windows-based returns will be removed.
     *                                          If set to 'spaces', the returns will be converted to spaces.
     * @return string The cleaned string.
     */
    private function fkwd_clean_string_returns( $string, $options = [] ) {
       // default on how it handles returns, which is converting them to spaces
       $return_options = array( "\t", "\r\n", "\n", "\r" );
       $convert_returns = " ";

       // if this needs to be comma delimited, make sure those are all in before removing bad characters or returns
       if( isset( $options['comma_delimited'] ) && $options['comma_delimited'] == true ) {
           $string = str_replace( $return_options, ", ", $string );
       }

       // if its set to strip, remove entirely
       if( isset( $options['convert_returns'] ) ) {
           if( $options['convert_returns'] == 'strip' ) {
               $convert_returns = "";
           // if its set to windows, remove windows based returns
           } else if( $options['convert_returns'] == 'windows' ) {
               $return_options = "\r";
               $convert_returns = "";
           } else if( $options['convert_returns'] == 'spaces' ) {
               $convert_returns = " ";
           }
       }

       $string = str_replace( $return_options, $convert_returns, $string );

       return $string;
    }

    /**
     * Validates and sanitizes what should be some kind of number
     *
     * @param mixed $number - number to verify
     * @param int $max - the max number this can be, defaults to null
     * @param int $min - the min number this can be, defaults to null
     * @return $string - the cleaned up number, will return null if invalid for security
     *
     */
    public function fkwd_clean_number( $number, $max = NULL, $min = NULL ) {
        $options = [
            'options' => [
                'default'   => false,
            ]
        ];

        if( $max !== NULL ) {
            $options['options']['max_range'] = $max;
        }

        if( $min !== NULL ) {
            $options['options']['min_range'] = $min;
        }

        $number = (float) $number;

        $filtered_number = filter_var($number, FILTER_VALIDATE_FLOAT, $options);

        if ($filtered_number !== false && $filtered_number == (int)$filtered_number) {
            return (int)$filtered_number;
        }

        return $filtered_number;
    }

    /**
     * Verify user ID.
     *
     * @param mixed $user_id The user ID to be verified.
     * @return mixed The cleaned user ID.
     */
    public function fkwd_verify_user_id( $user_id = NULL ) {
        return $this->fkwd_clean_number( $user_id );
    }

    public function send_log( string $action, string $log, ?int $user_id = null ): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'fkwd_logs';
    
        $data = array(
            'action'     => $action,
            'log'        => $log,
            'created_at' => current_time('mysql'),
        );
    
        $format = array('%s', '%s', '%s');
    
        if ( !is_null( $user_id ) ) {
            $data = array_merge( array('user_id' => $user_id), $data );
            $format = array_merge( array('%d'), $format );
        }
    
        $result = $wpdb->insert( $table, $data, $format );
    
        if ( false === $result ) {
            error_log( FKWD_PLUGIN_WCRFC_NAMESPACE . ': Failed to insert log entry. Error: ' . $wpdb->last_error );
            return false;
        }
    
        return true;
    }    
}

add_action('init', function() {
    // instantiate the Base class within the 'init' action
    new Base();
});
