<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Miscellaneous functions for use across the plugin
 * @package Simply_Static
 */

/**
* Get the protocol used for the origin URL
* @return string http or https
*/
function sist_origin_scheme() {
	return is_ssl() ? 'https' : 'http';
}

/**
* Get the host for the origin URL
* @return string host (URL minus the protocol)
*/
function sist_origin_host() {
	return untrailingslashit( sist_strip_protocol_from_url( sist_origin_url() ) );
}

/**
 * Wrapper around home_url(). Useful for swapping out the URL during debugging.
 * @return string home URL
 */
function sist_origin_url() {
	return home_url();
}

/**
 * Wrapper around site_url(). Returns the URL used for the WP installation.
 * @return string home URL
 */
function sist_wp_installation_url() {
	return site_url();
}

/**
 * Echo the selected value for an option tag if the statement is true.
 * @return null
 */
function sist_selected_if( $statement ) {
	echo ( $statement == true ? 'selected="selected"' : '' );
}

/**
 * Truncate if a string exceeds a certain length (30 chars by default)
 * @return string
 */
function sist_truncate( $string, $length = 30, $omission = '...' ) {
	return ( strlen( $string ) > $length + 3 ) ? ( substr( $string, 0, $length ) . $omission ) : $string;
}

/**
 * Use trailingslashit unless the string is empty
 * @return string
 */
function sist_trailingslashit_unless_blank( $string ) {
	return $string === '' ? $string : trailingslashit( $string );
}

/**
 * Dump an object to error_log
 * @return string
 */
function sist_error_log( $object=null ){
	ob_start();
	var_dump( $object );
	$contents = ob_get_contents();
	ob_end_clean();
	error_log( $contents );
}

/**
 * Given a URL extracted from a page, return an absolute URL
 *
 * Takes a URL (e.g. /test) extracted from a page (e.g. http://foo.com/bar/) and
 * returns an absolute URL (e.g. http://foo.com/bar/test). Absolute URLs are
 * returned as-is. Exception: links beginning with a # (hash) are left as-is.
 *
 * A null value is returned in the event that the extracted_url is blank or it's
 * unable to be parsed.
 *
 * @param  string       $extracted_url   Relative or absolute URL extracted from page
 * @param  string       $page_url        URL of page
 * @return string|null                   Absolute URL, or null
 */
function sist_relative_to_absolute_url( $extracted_url, $page_url ) {

	$extracted_url = trim( $extracted_url );

	// we can't do anything with blank urls
	if ( $extracted_url === '' ) {
		return null;
	}

	// if we get a hash, e.g. href='#section-three', just return it as-is
	if ( strpos( $extracted_url, '#' ) === 0 ) {
		return $extracted_url;
	}

	// check for a protocol-less URL
	// (Note: there's a bug in PHP <= 5.4.7 where parsed URLs starting with //
	// are treated as a path. So we're doing this check upfront.)
	// http://php.net/manual/en/function.parse-url.php#example-4617
	if ( strpos( $extracted_url, '//' ) === 0 ) {

		// if this is a local URL, add the protocol to the URL
		if ( stripos( $extracted_url, '//' . sist_origin_host() ) === 0 ) {
			$extracted_url = substr_replace( $extracted_url, sist_origin_scheme() . '://', 0, 2 );
		}

		return $extracted_url;

	}

	$parsed_extracted_url = parse_url( $extracted_url );

	// parse_url can sometimes return false; bail if it does
	if ( $parsed_extracted_url === false ) {

		return null;

	}

	if ( isset( $parsed_extracted_url['host'] ) ) {

		return $extracted_url;

	} elseif ( isset( $parsed_extracted_url['scheme'] ) ) {

		// examples of schemes without hosts: java:, data:
		return $extracted_url;

	} else { // no host on extracted page (might be relative url)

		$path = isset( $parsed_extracted_url['path'] ) ? $parsed_extracted_url['path'] : '';

		$query = isset( $parsed_extracted_url['query'] ) ? '?' . $parsed_extracted_url['query'] : '';
		$fragment = isset( $parsed_extracted_url['fragment'] ) ? '#' . $parsed_extracted_url['fragment'] : '';

		// turn our relative url into an absolute url
		$extracted_url = phpUri::parse( $page_url )->join( $path . $query . $fragment );

		return $extracted_url;

	}
}

/**
 * Check if URL starts with same URL as WordPress installation
 *
 * Both http and https are assumed to be the same domain.
 * @param  string  $url URL to check
 * @return boolean      true if URL is local, false otherwise
 */
function sist_is_local_url( $url ) {
	return ( stripos( sist_strip_protocol_from_url( $url ), sist_origin_host() ) === 0 );
}

/**
 * Returns a URL w/o the query string or fragment (i.e. nothing after the path)
 * @param  string $url URL to remove query string/fragment from
 * @return string      URL without query string/fragment
 */
function sist_remove_params_and_fragment( $url ) {
	return preg_replace('/(\?|#).*/', '', $url);
}

/**
 * Converts a textarea into an array w/ each line being an entry in the array
 * @param  string $textarea Textarea to convert
 * @return array            Converted array
 */
function sist_string_to_array( $textarea ) {
	// using preg_split to intelligently break at newlines
	// see: http://stackoverflow.com/questions/1483497/how-to-put-string-in-array-split-by-new-line
	$lines =  preg_split( "/\r\n|\n|\r/", $textarea );
	array_walk( $lines, 'trim' );
	$lines = array_filter( $lines );
	return $lines;
}

/**
 * Remove the http/https protocol from a URL
 *
 * @param  string $url URL to remove protocol from
 * @return string      URL sans http/https protocol
 */
function sist_strip_protocol_from_url( $url ) {
	$pattern = '/^https?:\/\//';
	return preg_replace( $pattern, '', $url );
}

/**
 * Remove index.html/index.php from a URL
 *
 * @param  string $url URL to remove index file from
 * @return string      URL sans index file
 */
function sist_strip_index_filenames_from_url( $url ) {
	$pattern = '/index.(html?|php)$/';
	return preg_replace( $pattern, '', $url );
}

/**
 * Get the current datetime formatted as a string for entry into MySQL
 * @return string MySQL formatted datetime
 */
function sist_formatted_datetime() {
	return date( 'Y-m-d H:i:s' );
}

/**
 * Similar to PHP's pathinfo(), but designed with URLs in mind (instead of directories)
 *
 * Example:
 *   $info = sist_url_path_info( '/manual/en/function.pathinfo.php?test=true' );
 *     $info['dirname']   === '/manual/en/'
 *     $info['basename']  === 'function.pathinfo.php'
 *     $info['extension'] === 'php'
 *     $info['filename']  === 'function.pathinfo'
 * @param  string $path The URL path
 * @return [type]       [description]
 */
function sist_url_path_info( $path ) {
	$info = array(
		'dirname' => '',
		'basename' => '',
		'filename' => '',
		'extension' => ''
	);

	// remove anything in the path after '#' or '?'
	foreach ( array( '#', '?' ) as $character ) {
		$char_location = strpos( $path, $character );
		if ( $char_location !== false ) {
			$path = substr( $path, 0, $char_location );
		}
	}

	// everything after the last slash is the filename
	$last_slash_location = strrpos( $path, '/' );
	if ( $last_slash_location === false ) {
		$info['basename'] = $path;
	} else {
		$info['dirname'] = substr( $path, 0, $last_slash_location+1 );
		$info['basename'] = substr( $path, $last_slash_location+1 );
	}

	// finding the dot for the extension
	$last_dot_location = strrpos( $info['basename'], '.' );
	if ( $last_dot_location === false ) {
		$info['filename'] = $info['basename'];
	} else {
		$info['filename'] = substr( $info['basename'], 0, $last_dot_location );
		$info['extension'] = substr( $info['basename'], $last_dot_location+1 );
	}

	// substr sets false if it fails, we're going to reset those values to ''
	foreach ( $info as $name => $value ) {
		if ( $value === false ) {
			$info[ $name ] = '';
		}
	}

	return $info;
}

/**
 * Ensure there is a single trailing directory separator on the path
 * @param string $path File path to add trailing directory separator to
 */
function sist_add_trailing_directory_separator( $path ) {
	return sist_remove_trailing_directory_separator( $path ) . DIRECTORY_SEPARATOR;
}

/**
 * Remove all trailing directory separators
 * @param string $path File path to remove trailing directory separators from
 */
function sist_remove_trailing_directory_separator( $path ) {
	return rtrim( $path, DIRECTORY_SEPARATOR );
}

/**
 * Ensure there is a single leading directory separator on the path
 * @param string $path File path to add leading directory separator to
 */
function sist_add_leading_directory_separator( $path ) {
	return DIRECTORY_SEPARATOR . sist_remove_leading_directory_separator( $path );
}

/**
 * Remove all leading directory separators
 * @param string $path File path to remove leading directory separators from
 */
function sist_remove_leading_directory_separator( $path ) {
	return ltrim( $path, DIRECTORY_SEPARATOR );
}
