<?php
/**
 * The file contains settings to be used by the plugin.
 *
 * @link       https://shipsy.io/
 * @since      1.0.3
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/admin
 */

//phpcs:disable
require 'base.php';

$BASE_URL = 'https://app.shipsy.in';
$PROJECTX_INTEGRATION_CONFIG = array(
	'1' => 'https://dtdcapi.shipsy.io'
);


$ORGANISATION = 'Jeebly';
$ORIGIN_COUNTRY = 'AE';
$DOMESTIC = True;

/*
Unset the local variables after use, or else they will leak into the files where
we include this file
*/
unset( $API );  // phpcs:ignore
unset( $URL );  // phpcs:ignore
//phpcs:enable