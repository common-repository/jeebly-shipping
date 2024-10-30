<?php
/**
 * The functions used all over the place in this plugin.
 *
 * @link       https://shipsy.io/
 * @since      1.0.3
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/admin
 */

/**
 * TODO: Find a neat way of importing the endpoints
 *      instead of just calling a function again and again.
 *      Although we are using `require_once` but still.
 *
 * TODO: Cache db calls everywhere.
 */

/**
 * Function to get the endpoint url
 *
 * @param string      $api THe name of the api for which to get the endpoint.
 * @param string|null $org_id The organisation for which to fetch url.
 * @return string
 */
function shipsy_get_endpoint( string $api, string $org_id = null ): string {
	require SHIPSY_ECONNECT_PATH . 'config/settings.php';
	// TODO: Why is it not working when we use `require_once`?

	// For backward compatibility, and to prevent changes where calling this function.
	if ( is_null( $org_id ) ) {
		$org_id = shipsy_get_org_id();
	}

	$integration_url  = $BASE_URL;   // phpcs:ignore
	// phpcs:ignore
	if ( ! is_null( $PROJECTX_INTEGRATION_CONFIG ) && array_key_exists( $org_id, $PROJECTX_INTEGRATION_CONFIG ) ) {
		$integration_url = $PROJECTX_INTEGRATION_CONFIG[ $org_id ]; // phpcs:ignore
	}

	return $integration_url . $ENDPOINTS[ $api ]; // phpcs:ignore
}

/**
 * Function to get TTL for cookies.
 *
 * @return int
 */
function shipsy_get_cookie_ttl(): int {
	require SHIPSY_ECONNECT_PATH . 'config/settings.php';

	// Ignore all caps casing.
	return time() + $COOKIE_TTL;    // phpcs:ignore
}

/**
 * Function to sanitize arrays.
 *
 * @param array $input The input to sanitize.
 * @return array
 */
function shipsy_sanitize_array( array $input ): array {
	// Initialize the new array that will hold the sanitized values.
	$new_input = array();

	// Loop through the input and recursively sanitize each of the values.
	foreach ( $input as $key => $val ) {
		if ( is_array( $val ) ) {
			$new_input[ $key ] = shipsy_sanitize_array( $val );
		} else {
			$new_input[ $key ] = sanitize_text_field( $val );
		}
	}
	return $new_input;
}

/**
 * Function to parse error response.
 *
 * @param array $error Array containing error message.
 * @return mixed|string
 */
function shipsy_parse_response_error( array $error ) {
	if ( 401 === $error['statusCode'] ) {
		return 'Authentication error! Please log in again.';
	}
	return $error['message'];
}

/**
 * Function to validate consignment address (i.e, during syncing).
 *
 * @param array $consignment The consignment to sync.
 * @return array
 */
function shipsy_validate_consignment_addresses( array $consignment ): array {
	$ends        = array( 'origin', 'destination' );
	$end_details = array( 'name', 'number', 'alt-number', 'line-1', 'line-2', 'pincode', 'city', 'state', 'country' );

	foreach ( $ends as $end ) {
		foreach ( $end_details as $end_detail ) {
			$key = $end . '-' . $end_detail;
			if ( ! isset( $consignment[ $key ] ) ) {
				$consignment[ $key ] = '';
			}
		}
	}

	return $consignment;
}

/**
 * Function to validate addresses.
 *
 * @param array $addresses The addresses of customer.
 * @return array
 */
function shipsy_validate_customer_addresses( array $addresses ): array {
	$address_types   = array( 'forwardAddress', 'reverseAddress', 'exceptionalReturnAddress', 'returnAddress' );
	$address_details = array( 'name', 'phone', 'alternate_phone', 'address_line_1', 'address_line_2', 'pincode', 'city', 'state' );

	foreach ( $address_types as $address_type ) {
		if ( ! isset( $addresses[ $address_type ] ) ) {
			$addresses[ $address_type ] = array();
		}

		foreach ( $address_details as $address_detail ) {
			if ( ! isset( $addresses[ $address_type ][ $address_detail ] ) ) {
				$addresses[ $address_type ][ $address_detail ] = '';
			}
		}
	}
	return $addresses;
}

/**
 * Function to get AWB number.
 *
 * @param array $synced_orders Array of order ids if synced orders.
 * @return mixed
 */
function shipsy_get_awb_number( array $synced_orders ) {
	$headers = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);

	$data_to_send_json = wp_json_encode( array( 'customerReferenceNumberList' => $synced_orders ) );
	$args              = array(
		'body'        => $data_to_send_json,
		'timeout'     => '10',
		'redirection' => '10',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);
	$request_url       = shipsy_get_endpoint( 'AWB_NUMBER_API' );
	$response          = wp_remote_post( $request_url, $args );
	$result            = wp_remote_retrieve_body( $response );

	return json_decode( $result, true );
}


/**
 * Function to get Virtual Series.
 *
 * @return mixed
 */
function shipsy_get_virtual_series() {
	$headers     = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);
	$args        = array(
		'headers' => $headers,
	);
	$request_url = shipsy_get_endpoint( 'VSERIES_API' );
	$response    = wp_remote_get( $request_url, $args );
	$result      = wp_remote_retrieve_body( $response );

	return json_decode( $result, true );
}

/**
 * Function to get customer addresses.
 *
 * @return mixed
 */
function shipsy_get_addresses() {
	$headers     = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);
	$args        = array(
		'timeout'     => '10',
		'redirection' => '10',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);
	$request_url = shipsy_get_endpoint( 'SHOP_DATA_API' );
	$response    = wp_remote_post( $request_url, $args );
	$result      = wp_remote_retrieve_body( $response );

	return json_decode( $result, true );
}

/**
 * Function to configure plugin (i.e, user login with settings).
 *
 * @param array $post_request_params Form values.
 * @return void
 */
function shipsy_config( array $post_request_params ) {
	$post_request_params['org_id'] = strtolower( $post_request_params['org_id'] );

	$headers            = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => $post_request_params['org_id'],
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
	);
	$data_to_send_array = array(
		'username' => $post_request_params['user-name'],
		'password' => $post_request_params['password'],
	);
	$data_to_send_json  = wp_json_encode( $data_to_send_array );
	$args               = array(
		'body'        => $data_to_send_json,
		'timeout'     => '10',
		'redirection' => '10',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);
	$request_url        = shipsy_get_endpoint( 'REGISTER_SHOP_API', $post_request_params['org_id'] );

	$response              = wp_remote_post( $request_url, $args );
	$result                = wp_remote_retrieve_body( $response );
	$result_data           = json_decode( $result, true );
	$notifications         = array();
	$notifications['page'] = 'shipsy-configuration';

	if ( array_key_exists( 'data', $result_data ) ) {
		if ( array_key_exists( 'access_token', $result_data['data'] ) ) {
			$access_token             = $result_data['data']['access_token'];
			$notifications['success'] = 'Configuration is successful';
			setcookie( 'access_token', $access_token, shipsy_get_cookie_ttl() );

			// if registration is successful store the org-id in cookies.
			shipsy_set_org_id( $post_request_params['org_id'] );
			shipsy_set_download_label_option(
				( array_key_exists( 'download_label_option', $post_request_params ) &&
				$post_request_params['download_label_option'] ) ? 1 : 0
			);
		}
		if ( array_key_exists( 'customer', $result_data['data'] ) &&
			array_key_exists( 'id', $result_data['data']['customer'] ) &&
			array_key_exists( 'code', $result_data['data']['customer'] ) ) {
			$customer_id   = $result_data['data']['customer']['id'];
			$customer_code = $result_data['data']['customer']['code'];
			setcookie( 'cust_id', $customer_id, shipsy_get_cookie_ttl() );
			setcookie( 'cust_code', $customer_code, shipsy_get_cookie_ttl() );
		}
	} else {
		// remove cookies if already set from previous config.
		shipsy_remove_cookie( 'cust_id' );
		shipsy_remove_cookie( 'cust_code' );
		shipsy_remove_cookie( 'access_token' );

		// set org_id even if auth failed so that if user tries to access other pages,
		// request is sent to correct PX instance.
		shipsy_set_org_id( $post_request_params['org_id'] );

		$notifications['failure'] = $result_data['error']['message'];
	}
	wp_safe_redirect( add_query_arg( $notifications, admin_url( 'admin.php' ) ) );

}

/**
 * Function to update address.
 *
 * @param array $post_request_params Form values.
 * @return void
 */
function shipsy_update_addresses( array $post_request_params ) {
	$headers = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);

	if ( isset( $post_request_params['useForwardCheck'] ) && 'true' === $post_request_params['useForwardCheck'] ) {
		$use_forward_address = true;
		$reverse_address     = array(
			'name'            => $post_request_params['forward-name'],
			'phone'           => $post_request_params['forward-phone'],
			'alternate_phone' => $post_request_params['forward-alt-phone'] ?? '',
			'address_line_1'  => $post_request_params['forward-line-1'],
			'address_line_2'  => $post_request_params['forward-line-2'],
			'pincode'         => $post_request_params['forward-pincode'],
			'city'            => $post_request_params['forward-city'],
			'state'           => $post_request_params['forward-state'],
			'country'         => $post_request_params['forward-country'],
		);
	} else {
		$use_forward_address = false;
		$reverse_address     = array(
			'name'            => $post_request_params['reverse-name'],
			'phone'           => $post_request_params['reverse-phone'],
			'alternate_phone' => $post_request_params['reverse-alt-phone'] ?? '',
			'address_line_1'  => $post_request_params['reverse-line-1'],
			'address_line_2'  => $post_request_params['reverse-line-2'],
			'pincode'         => $post_request_params['reverse-pincode'],
			'city'            => $post_request_params['reverse-city'],
			'state'           => $post_request_params['reverse-state'],
			'country'         => $post_request_params['reverse-country'],
		);
	}
	$data_to_send_array = array(
		'forwardAddress'           => array(
			'name'            => $post_request_params['forward-name'],
			'phone'           => $post_request_params['forward-phone'],
			'alternate_phone' => $post_request_params['forward-alt-phone'] ?? '',
			'address_line_1'  => $post_request_params['forward-line-1'],
			'address_line_2'  => $post_request_params['forward-line-2'],
			'pincode'         => $post_request_params['forward-pincode'],
			'city'            => $post_request_params['forward-city'],
			'state'           => $post_request_params['forward-state'],
			'country'         => $post_request_params['forward-country'],
		),
		'reverseAddress'           => $reverse_address,
		'useForwardAddress'        => $use_forward_address,
		'exceptionalReturnAddress' => array(
			'name'            => $post_request_params['exp-return-name'],
			'phone'           => $post_request_params['exp-return-phone'],
			'alternate_phone' => $post_request_params['exp-return-alt-phone'] ?? '',
			'address_line_1'  => $post_request_params['exp-return-line-1'],
			'address_line_2'  => $post_request_params['exp-return-line-2'],
			'pincode'         => $post_request_params['exp-return-pincode'],
			'city'            => $post_request_params['exp-return-city'],
			'state'           => $post_request_params['exp-return-state'],
			'country'         => $post_request_params['exp-return-country'],
		),
	);

	// Return address is required iff the Ord Id is 1.
	if ( shipsy_get_org_id() === '1' ) {
		$data_to_send_array['returnAddress'] = array(
			'name'            => $post_request_params['return-name'],
			'phone'           => $post_request_params['return-phone'],
			'alternate_phone' => $post_request_params['return-alt-phone'] ?? '',
			'address_line_1'  => $post_request_params['return-line-1'],
			'address_line_2'  => $post_request_params['return-line-2'],
			'pincode'         => $post_request_params['return-pincode'],
			'city'            => $post_request_params['return-city'],
			'state'           => $post_request_params['return-state'],
			'country'         => $post_request_params['return-country'],
		);
	}

	$data_to_send_json = wp_json_encode( $data_to_send_array );

	$args        = array(
		'body'        => $data_to_send_json,
		'timeout'     => '10',
		'redirection' => '10',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);
	$request_url = shipsy_get_endpoint( 'UPDATE_ADDRESS_API' );
	$response    = wp_remote_post( $request_url, $args );
	$result      = wp_remote_retrieve_body( $response );
	$array2      = json_decode( $result, true );

	$notifications         = array();
	$notifications['page'] = 'shipsy-setup';
	if ( is_array( $array2 ) ) {
		if ( array_key_exists( 'success', $array2 ) ) {
			if ( $array2['success'] ) {
				$notifications['success'] = 'Setup is Successful';
			}
		} else {
			$notifications['failure'] = $array2['error']['message'];
		}
	}
	wp_safe_redirect( add_query_arg( $notifications, admin_url( 'admin.php' ) ) );

}

/**
 * Function to remove cookie.
 *
 * @param string $key The key for which to remove cookie.
 * @return boolean
 */
function shipsy_remove_cookie( string $key ): bool {
	if ( isset( $_COOKIE[ $key ] ) ) {
		unset( $_COOKIE[ $key ] );
		setcookie( $key, null );
		return true;
	}
	return false;
}

/**
 * Function to get customer id from cookie.
 *
 * @return string|null
 */
function shipsy_get_cust_id(): ?string {
	return isset( $_COOKIE['cust_id'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['cust_id'] ) ) : null;
}

/**
 * Function to get access token from cookie.
 *
 * @return string|null
 */
function shipsy_get_access_token(): ?string {
	return isset( $_COOKIE['access_token'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['access_token'] ) ) : null;
}

/**
 * Function to get customer code from cookie.
 *
 * @return string|null
 */
function shipsy_get_cust_code(): ?string {
	return isset( $_COOKIE['cust_code'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['cust_code'] ) ) : null;
}

/**
 * Function to get shop url.
 *
 * @return string|void
 */
function shipsy_get_shop_url() {
	return get_bloginfo( 'wpurl' );
}

/**
 * Function to save organization id to cookie.
 *
 * @param string $org_id Organisation id to store in cookie.
 * @return void
 */
function shipsy_set_org_id( string $org_id ) {
	setcookie( 'org_id', $org_id, shipsy_get_cookie_ttl() );
}

/**
 * Function to get organization id from cookie.
 *
 * @return string|null
 */
function shipsy_get_org_id(): ?string {
	return isset( $_COOKIE['org_id'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['org_id'] ) ) : null;
}

/**
 * Function to save download label option setting to cookie.
 *
 * @param int $val Settings variable to decide whether to show download label option in WooCommerce.
 * @return void
 */
function shipsy_set_download_label_option( int $val ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'options';
	$exists = shipsy_get_download_label_option();
	// phpcs:disable
	if ( is_null( $exists ) ) {
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `$table_name` (option_name, option_value) VALUES (%s, %s)",
				array( 'shipsy_download_label_option', $val )
			)
		);

	} else {
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name` SET option_value=%s WHERE option_name=%s",
				array( $val, 'shipsy_download_label_option' )
			)
		);
	}
	// phpcs:enable
}

/**
 * Function to get download label option setting from cookie.
 *
 * @return string|null
 */
function shipsy_get_download_label_option(): ?string {
	global $wpdb;
	$table_name = $wpdb->prefix . 'options';

	// phpcs:disable
	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT `option_value` FROM `$table_name` WHERE option_name=%s",
			'shipsy_download_label_option'
		)
	);
	// phpcs:enable
}

/**
 * Function to save synced order details to DB.
 *
 * @param array $data The order details to write in db.
 * @return void
 */
function shipsy_add_sync_track( array $data ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sync_track_order';

	// phpcs:disable
	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO `$table_name` (orderId, shipsy_refno) VALUES (%s, %s)",
			array( $data['orderId'], $data['shipsy_refno'] )
		)
	);
	// phpcs:enable
}

/**
 * Function to get reference number from DB.
 *
 * @param string $order_id The order id for which to fetch details from db.
 * @return string|null
 */
function shipsy_get_ref_no( string $order_id ): ?string {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sync_track_order';

	// phpcs:disable
	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT `shipsy_refno` FROM `$table_name` WHERE orderId=%s",
			$order_id
		)
	);
	// phpcs:enable
}

/**
 * FUnction to get tracking url stored in db.
 *
 * @param string $order_id The order id for which to fetch tracking url from db.
 * @return string|null
 */
function shipsy_get_tracking_url( string $order_id ): ?string {
	global $wpdb;
	$table_name = $wpdb->prefix . 'sync_track_order';

	// phpcs:disable
	return $wpdb->get_var(
		$wpdb->prepare(
			"SELECT `track_url` FROM `$table_name` WHERE orderId=%s",
			$order_id
		)
	);
	// phpcs:enable
}

/**
 * Function to add tracking url for an order in db.
 *
 * @param string $order_id The order id for which to add tracking url.
 * @return bool
 */
function shipsy_add_tracking_url( string $order_id ): bool {
	global $wpdb;
	$headers            = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);
	$data['cust_refno'] = $order_id;
	$data_to_send_json  = wp_json_encode( array( 'customerReferenceNumberList' => array( $data['cust_refno'] ) ) );
	$args               = array(
		'body'        => $data_to_send_json,
		'timeout'     => '10',
		'redirection' => '10',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);
	$request_url        = shipsy_get_endpoint( 'TRACKING_API' );
	$response           = wp_remote_post( $request_url, $args );
	$result             = wp_remote_retrieve_body( $response );
	$array2             = json_decode( $result, true );
	if ( ! empty( $array2['data'] ) && $array2['success'] ) {
		$table_name = $wpdb->prefix . 'sync_track_order';

		$track_url = $array2['data'][ $order_id ];
		// phpcs:disable
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name` SET track_url=%s WHERE orderId=%s",
				array( $track_url, $order_id )
			)
		);
		// phpcs:enable
		return true;
	} else {
		return false;
	}
}

/**
 * Function for bulk label download.
 *
 * @param array $order_ids The order ids for which to download labels.
 * @return array|void
 */
function shipsy_bulk_label_download( array $order_ids ) {
	// TODO: Can we turn this function into an internal API?
	require SHIPSY_ECONNECT_PATH . 'config/settings.php';

	$order_ids = shipsy_clean_order_ids( $order_ids );

	$ref_nos = array();

	foreach ( $order_ids as $order_id ) {
		$ref_nos[] = shipsy_get_ref_no( sanitize_text_field( $order_id ) );
	}

	$headers = array(
		'Content-Type'    => 'application/json',
		'organisation-id' => shipsy_get_org_id(),
		'shop-origin'     => 'wordpress',
		'shop-url'        => shipsy_get_shop_url(),
		'customer-id'     => shipsy_get_cust_id(),
		'access-token'    => shipsy_get_access_token(),
	);

	$data_to_send_json = wp_json_encode(
		array(
			'consignmentIds'    => $ref_nos,
			'isReferenceNumber' => true,
		)
	);
	$args              = array(
		'body'        => $data_to_send_json,
		'timeout'     => '50',
		'redirection' => '50',
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => $headers,
	);

	// Ignore all caps casing.
	$request_url = shipsy_get_endpoint( 'BULK_LABEL_API' );
	$response    = wp_remote_post( $request_url, $args );
	$result      = json_decode( $response['body'], true );

	$notifications              = array();
	$notifications['post_type'] = 'shop_order';

	if ( $result && array_key_exists( 'error', $result ) ) {
		if ( array_key_exists( 'message', $result['error'] ) ) {
			$notifications['failure'] = $result['error']['message'];
		} else {
			$notifications['failure'] = 'Cannot fetch labels, please try again later';
		}
	} else {
		try {
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$wpfs = new WP_Filesystem_Direct( null );

			$upload_dir = wp_upload_dir();
			$dir_path   = $upload_dir['basedir'] . '/shipsy-labels-dir';

			if ( ! empty( $upload_dir['basedir'] ) ) {
				if ( ! file_exists( $dir_path ) ) {
					wp_mkdir_p( $dir_path );
				}
			}

			$pdf_path = $dir_path . '/shipsy-labels.pdf';
			$wpfs->delete( $pdf_path, false, 'f' );
			$wpfs->put_contents( $pdf_path, $response['body'], 0777 );

			$notifications['success'] = 'Successfully fetched labels for orders: ' . implode( ', ', $order_ids );
			$url                      = SHIPSY_ECONNECT_URL . 'assets/pdf/shipsy-labels.pdf';
			$redirect                 = add_query_arg( $notifications, admin_url( 'edit.php' ) );

			/*
			 * TODO: Can there be some better way to handle this. That is can we open download in new tab and also
			 *	     redirect the current page with the respective message
			 */

			header( 'Content-type: application/x-file-to-save' );
			header( 'Content-Disposition: attachment; filename=' . basename( $url ) );
			readfile( $pdf_path ); // phpcs:ignore
			die;

		} catch ( Exception $ex ) {
			$notifications['failure'] = $ex->getMessage();
		}
	}
	return $notifications;
}

/**
 * Function to convert string of order_ids to array( order_id ).
 *
 * @param mixed $order_id String of order id(s).
 * @return array
 */
function shipsy_clean_order_ids( $order_id ): array {

	if ( is_array( $order_id ) ) {
		$orders = array();
		foreach ( $order_id as $id ) {
			$orders[] = sanitize_text_field( wp_unslash( $id ) );
		}
		$order_id = $orders;
	} else {
		$order_id = array( sanitize_text_field( wp_unslash( $order_id ) ) );
	}

	return $order_id;
}

/**
 * Slugify a string.
 *
 * @param string $text String to slugify.
 * @return string
 */
function shipsy_slugify( string $text ): string {
	// Strip html tags.
	$text = wp_strip_all_tags( $text );
	// Replace non letter or digits by -.
	$text = preg_replace( '~[^\pL\d]+~u', '-', $text );
	// Transliterate.
	setlocale( LC_ALL, 'en_US.utf8' );
	$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );
	// Remove unwanted characters.
	$text = preg_replace( '~[^-\w]+~', '', $text );
	// Trim.
	$text = trim( $text, '-' );
	// Remove duplicate -.
	$text = preg_replace( '~-+~', '-', $text );
	// Lowercase.
	$text = strtolower( $text );
	// Check if it is empty.
	if ( empty( $text ) ) {
		return 'n-a'; }
	// Return result.
	return $text;
}
