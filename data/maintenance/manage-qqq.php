<?php
/**
 * Ensure that the qqq file has all keys in en.json and no others.
 */

$en_file = __DIR__ . '/../i18n/en.json';
$qqq_file = __DIR__ . '/../i18n/qqq.json';

$en = json_decode( file_get_contents( $en_file ), true );
$qqq = json_decode( file_get_contents( $qqq_file ), true );

foreach ( $en as $key => $value ) {
	if ( !isset( $qqq[$key] ) ) {
		$qqq[$key] = 'TODO: needs description';
	}
}

foreach ( $qqq as $key => $value ) {
	if ( !isset( $en[$key] ) ) {
		unset( $qqq[$key] );
	}
}

$meta = $qqq['@metadata'];
unset( $qqq['@metadata'] );
ksort( $qqq );

$qqq = array_merge( array( '@metadata' => $meta ), $qqq );
file_put_contents( $qqq_file, json_encode( $qqq, JSON_PRETTY_PRINT ) );
