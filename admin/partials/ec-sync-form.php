<?php
/**
 * Shipsy order sync form page.
 *
 * @link       https://shipsy.io/
 * @since      1.0.3
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/admin/partials
 */

/** Shipsy order sync form page. */

require_once SHIPSY_ECONNECT_PATH . 'admin/helper/helper.php';

/**
 * TODO: Handle nonce verification.
 */
// phpcs:ignore
if ( ! isset( $_GET['orderid'] ) ) {
	?>
	<div class="alert alert-danger" role="alert"><?php echo esc_html( 'Order id not found!' ); ?></div>
	<?php
	return;
}

// phpcs:ignore
$get       = shipsy_sanitize_array( $_GET );
$order_id  = $get['orderid'];
$order_ids = shipsy_clean_order_ids( $order_id );

$response                   = shipsy_get_addresses();
$all_addresses              = $response['data'];
$forward_address            = ( array_key_exists( 'forwardAddress', $all_addresses ) ) ? $all_addresses['forwardAddress'] : array();
$reverse_address            = ( array_key_exists( 'reverseAddress', $all_addresses ) ) ? $all_addresses['reverseAddress'] : array();
$exceptional_return_address = ( array_key_exists( 'exceptionalReturnAddress', $all_addresses ) ) ? $all_addresses['exceptionalReturnAddress'] : array();
$valid_service_types        = $all_addresses['serviceTypes'];

$form_ids = '';
foreach ( $order_ids as $ord_id ) {
	$form_ids = $form_ids . 'sync-form-' . $ord_id . ' ';
}
$form_ids      = sanitize_text_field( $form_ids );
$order_ids_str = implode( ',', $order_ids );

?>

<div id="sync-form-overlay" class="overlay"></div>
<div id="sync-form-spanner" class="spanner">
	<div class="loader"></div>
	<p>Syncing data, please be patient.</p>
</div>

<div class="container-fluid">
	<div class="pb-2 mt-4 mb-2 border-bottom">
		<h3>Sync Orders</h3>
	</div>
</div>

<?php

if ( array_key_exists( 'data', $response ) && ! empty( $response['data'] ) ) {
	foreach ( $order_ids as $order_id ) {

		$curr_order       = wc_get_order( $order_id );
		$customer_notes   = $curr_order->get_customer_note();
		$shipping_address = $curr_order->get_address( 'shipping' );
		?>

<div class="container-fluid">
	<div class="main-container-card" style="font-size: 0.8em; margin-right: 2em">
		<form id="<?php echo esc_attr( 'sync-form-' . $order_id ); ?>" class="form-horizontal">
<!--            <input type="hidden" name="action" value="on_sync_submit"/>-->
			<div class="row">
				<div class="col-12">
					<div class="form-group container-card" id="order-details" style="padding: 2%; margin: 1em 2em">
						<!--                <div class="header-style" style="width : 90% !important">-->
						<!--                    <span class="header-font">Order Details</span>-->
						<!--                </div>-->
						<div class="container">
							<div class="row">
								<div class="col-sm-2">
									<label for="textInput" class="label-font">Order Number<span
												class="required-text">*</span></label>

									<input type="text" required="true"
										value="<?php echo esc_attr( sanitize_text_field( $order_id ) ); ?>"
										id="customer-reference-number" name="customer-reference-number"
										class="form-control" readonly>
									<div class="orderText" style="color : red ; font-size : 10px; display:none"> Order
										Number is required
									</div>
								</div>
								<div class="col-sm-2">
									<label for="textInput" class="label-font">AWB Number</label>
									<input type="text" name="awb-number" id="awb-number" class="form-control" placeholder="Text input">
								</div>
								<div class="col-sm-2">
									<label for="select" class="label-font">Service Type<span
												class="required-text">*</span></label>
									<select class="custom-select" required="true" name="service-type"
											id="select-service-type">
										<?php foreach ( $valid_service_types as $service_type ) { ?>
											<?php if ( 'PREMIUM' === $service_type['name'] ) : ?>
												<option value="<?php echo esc_attr( $service_type['id'] ); ?>"
														selected=""><?php echo esc_html( $service_type['name'] ); ?></option>
											<?php else : ?>
												<option value="<?php echo esc_attr( $service_type['id'] ); ?>"><?php echo esc_html( $service_type['name'] ); ?></option>
											<?php endif; ?>
										<?php } ?>
									</select>
								</div>
								<div class="col-sm-2">
									<label for="select" class="label-font">Courier Type<span
												class="required-text">*</span></label>
									<select class="custom-select" required="true" name="courier-type"
											id="select-courier-type">
										<option value="NON-DOCUMENT" selected>NON-DOCUMENT</option>
										<option value="DOCUMENT">DOCUMENT</option>
									</select>
								</div>
								<div class="col-sm-2">
									<label for="select" class="label-font">Consignment Type<span class="required-text">*</span></label>
									<select class=" custom-select" required="true" name="consignment-type"
											id="select-consignment-type" onchange="onConsignmentTypeChangeHandler(<?php echo esc_html( $order_id ); ?>)">
										<option disabled selected value> -- select consignment type ---</option>
										<option value="forward" selected>FORWARD</option>
										<option value="reverse">REVERSE</option>
									</select>
								</div>
								<div class="col-sm-2">
									<label for="num-pieces" class="label-font">Number of Pieces<span
												class="required-text">*</span></label>
									<input type="number" id="num-pieces" required="true"
										oninput="this.value = Math.abs(this.value)" min="1" pattern="\d+"
										name="num-pieces" class="form-control" value="1" onkeyup="onNumPieceChangeHandler('<?php echo esc_html( $order_id ); ?>')">
									<div class="numpiecesError" style="color : red ; font-size : 10px;display:none">
										Value should be greater than 0
									</div>

									<div class="block form-group" style="margin: 4% 0 0 0; float left">
										<label for="useForwardCheck" style="width: 100%">
											<input type="checkbox" name="multiPieceCheck"  value="true" id="multiPieceCheck"
												onchange="onMultiPieceCheckChangeHandler('<?php echo esc_html( $order_id ); ?>')">
											All pieces same
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-6">
					<div class="form-group col-12 container-card" id="origin-details" style="margin: 1em 2em">
						<h5>Origin Details</h5>
						<div class="container">
							<div class="row">
								<div class="col-sm-4">
									<label for="origin-name" class="label-font">Name<span
												class="required-text">*</span></label>
									<input type="text" id="origin-name" required="true"
										name="origin-name" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['name'] ) ); ?>">
									<div class="origin-name-error" style="color : red ; font-size : 10px;display:none">
										Origin Name is required
									</div>
								</div>
								<div class="col-sm-4">
									<label for="origin-number" class="label-font">Phone Number<span
												class="required-text">*</span></label>
									<input type="tel" id="origin-number" required="true"
										name="origin-number" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['phone'] ) ); ?>">
									<div class="origin-number-error" style="color : red ; font-size : 10px;display:none">
										Phone number is required
									</div>
								</div>
								<div class="col-sm-4">
									<label for="origin-alt-number" class="label-font">Alternate Phone Number</label>
									<input type="tel" id="origin-alt-number"
										name="origin-alt-number" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['alternate_phone'] ) ); ?>">
									<div class="origin-alt-phone-error" style="color : red ; font-size : 10px;display:none">
										Invalid value for Alternate Phone Number
									</div>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-sm-6">
									<label for="origin-line-1" class="label-font">Address Line 1<span
												class="required-text">*</span></label>
									<input type="text" id="origin-line-1" required="true"
										name="origin-line-1" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['address_line_1'] ) ); ?>">
									<div class="origin-line-1-error" style="color : red ; font-size : 10px;display:none">
										Origin Address is required
									</div>

								</div>
								<div class="col-sm-6">
									<label for="origin-line-2" class="label-font">Address Line 2</label>
									<input type="text" id="origin-line-2"
										name="origin-line-2" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['address_line_2'] ) ); ?>">
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-sm-3">
									<label for="origin-city" class="label-font"> City</label>
									<input type="text" id="origin-city" name="origin-city"
										class="form-control" value="<?php echo esc_attr( sanitize_text_field( $forward_address['city'] ) ); ?>">
								</div>
								<div class="col-sm-3">
									<label for="origin-state" class="label-font">State</label>
									<input type="text" id="origin-state" name="origin-state"
										class="form-control" value="<?php echo esc_attr( sanitize_text_field( $forward_address['state'] ) ); ?>">
								</div>
								<div class="col-sm-3">
									<label for="origin-country" class="label-font">Country<span
												class="required-text">*</span></label>
									<input type="text" id="origin-country" required="true"
										name="origin-country" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['country'] ) ); ?>">
									<div class="origin-country-error" style="color : red ; font-size : 10px;display:none">
										Origin Country is required
									</div>

								</div>
								<div class="col-sm-3">
									<label for="origin-pincode">Pincode</label>
									<input type="text" id="origin-pincode"
										name="origin-pincode" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $forward_address['pincode'] ) ); ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="form-group container-card" id="destination-details" style="margin: 1em 2em">
						<h5>Destination Details</h5>
						<div class="container" style="margin-left : 0px">
							<div class="row">
								<div class="col-sm-4">
									<label for="destination-name" class="label-font">Name<span
												class="required-text">*</span></label>
									<input type="text" id="destination-name" required="true" name="destination-name"
										class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $shipping_address['first_name'] . ' ' . $shipping_address['last_name'] ) ); ?>">
									<div class="destination-name-error" style="color : red ; font-size : 10px;display:none">
										Destination Name is required
									</div>
								</div>
								<div class="col-sm-4">
									<label for="destination-number" class="label-font">Phone Number<span
												class="required-text">*</span></label>
									<input type="tel" id="destination-number" required="true"
										name="destination-number" class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $curr_order->get_billing_phone() ) ); ?>">
									<div class="destination-number-error" style="color : red ; font-size : 10px;display:none">
										Phone number is required
									</div>

								</div>
								<div class="col-sm-4">
									<label for="destination-alt-number" class="label-font">Alt Phone Number </label>
									<input type="tel" id="destination-alt-number"
										name="destination-alt-number"
										class="form-control" value="<?php echo esc_attr( sanitize_text_field( $curr_order->get_billing_phone() ) ); ?>">
									<div class="destination-alt-phone-error" style="color : red ; font-size : 10px;display:none">
										Invalid value for Alternate Phone Number
									</div>
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-sm-6">
									<label for="destination-line-1" class="label-font">Address Line 1<span
												class="required-text">*</span></label>
									<input type="text" id="destination-line-1" required="true" name="destination-line-1"
										class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $shipping_address['address_1'] ) ); ?>">
									<div class="destination-line-1-error" style="color : red ; font-size : 10px;display:none">
										Destination Address is required
									</div>

								</div>
								<div class="col-sm-6">
									<label for="destination-line-2" class="label-font">Address Line 2</label>
									<input type="text" id="destination-line-2" name="destination-line-2"
										class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $shipping_address['address_2'] ) ); ?>">
								</div>
							</div>
							<div class="row mt-3">
								<div class="col-sm-3">
									<label for="destination-city" class="label-font">City</label>
									<input type="text" id="destination-city" name="destination-city"
										class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $shipping_address['city'] ) ); ?>">
								</div>
								<div class="col-sm-3">
									<label for="destination-state" class="label-font">State</label>
									<input type="text" id="destination-state" name="destination-state"
										class="form-control"
										value="<?php echo esc_attr( sanitize_text_field( $shipping_address['state'] ) ); ?>">
								</div>
								<div class="col-sm-3">
									<label for="destination-country" class="label-font">Country<span
												class="required-text">*</span></label>
									<input type="text" id="destination-country" required="true"
										name="destination-country"
										class="form-control" value="<?php echo esc_attr( sanitize_text_field( $shipping_address['country'] ) ); ?>">
									<div class="destination-country-error" style="color : red ; font-size : 10px;display:none">
										Destination Country is required
									</div>

								</div>
								<div class="col-sm-3">
									<label for="destination-pincode" class="label-font">Pincode</label>
									<input type="text" id="destination-pincode"
										name="destination-pincode"
										class="form-control" value="<?php echo esc_attr( sanitize_text_field( $shipping_address['postcode'] ) ); ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-6">
					<div class="form-group col-12 container-card" id="payment-details" style="margin: 1em 2em">
						<h5>COD Details</h5>
						<div class="container">
							<div class="row">
								<div class="col-12">
									<label for="select" class="label-font">COD Collection Mode <span
												class="required-text">*</span></label>
									<select class="custom-select " name="cod-collection-mode" required="true"
											id="select-cod-collection-mode">
										<option value="<?php echo esc_attr( sanitize_text_field( $curr_order->get_payment_method() ) ); ?>"
											selected><?php echo esc_html( $curr_order->get_payment_method() ); ?></option>
									</select>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-12">
									<?php if ( 'cod' === $curr_order->get_payment_method() ) { ?>
										<label for="cod-amount" class="label-font">COD Amount <span
													class="required-text">*</span></label>
										<input type="number" value="<?php echo esc_attr( sanitize_text_field( $curr_order->get_total() ) ); ?>" id="cod-amount"
											required="true" name="cod-amount"
											oninput="this.value = Math.abs(this.value)"
											class="form-control   " readonly>
									<?php } else { ?>
										<label for="cod-amount" class="label-font">COD Amount <span
													class="required-text">*</span></label>
										<input type="number" value="0" id="cod-amount" required="true" name="cod-amount"
											class="form-control   " readonly>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="form-group container-card" id="piece-details" style="margin: 1em 2em">
						<h5>Piece Details</h5>
						<div class="container piece-details-div" id="piece-det">
							<?php
							$description    = array();
							$declared_value = 0;
							$order_items    = $curr_order->get_items();
							foreach ( $order_items as $key => $item ) {
								$description[]   = sanitize_text_field( (int) $item['quantity'] . ' ' . $item['name'] );
								$declared_value += sanitize_text_field( $item['total'] + $item['total_tax'] );
							}
							?>
							<div class="row mt-3" id="piece-detail-1">
								<div class="row">
									<div class="col-sm-6">
										<label for="textInput" class="label-font">Description<span
													class="required-text">*</span></label>
										<input type="text" name="description[]" required="true" id="description1"
											class="form-control    description-tag"
											value="<?php echo esc_attr( sanitize_text_field( implode( ', ', $description ) ) ); ?>">
										<div class="description1-error" style="color:red; font-size : 10px;display:none">
											Description is required
										</div>
									</div>
									<div class="col-sm-6">

										<label for="textInput" class="label-font">Weight(Kg)<span
													class="required-text">*</span></label>
										<input type="number" required="true" name="weight[]" oninput="check(this)"
											step="any" min="0" class="form-control" value="1">
									</div>
								</div>

								<div class="row mt-3">
									<div class="col-sm-2">
										<label for="textInput" class="label-font">Length<span class="required-text">*</span></label>
										<input type="number" name="length[]" required="true" oninput="this.value=Math.abs(this.value)"
											min="0" step="any" class="form-control" value="1">
									</div>
									<div class="col-sm-2">
										<label for="textInput" class="label-font">Breadth<span
													class="required-text">*</span></label>
										<input type="number" name="width[]" required="true" oninput="this.value=Math.abs(this.value)"
											min="0" step="any" class="form-control" value="1">
									</div>
									<div class="col-sm-2">
										<label for="textInput" class="label-font">Height <span
													class="required-text">*</span></label>
										<input type="number" required="true" name="height[]" oninput="this.value=Math.abs(this.value)"
											min="0" step="any" class="form-control" value="1">
									</div>
									<div class="col-sm-6">
										<label for="textInput" class="label-font">Declared Value <span
													class="required-text">*</span></label>
										<input type="number" name="declared-value[]" required="true" min="0" step="any"
											id="declared-value<?php echo esc_html( $order_id ); ?>"
											class="form-control" value="<?php echo esc_attr( sanitize_text_field( $declared_value ) ); ?>">
										<div class="declared-value1<?php echo esc_html( $order_id ); ?>-error" style="color : red ; font-size : 10px;display:none">
											Declared value required
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<div class="form-group container-card" id="order-details" style="padding: 2%; margin: 1em 2em">
						<!--                        <h5>Order Notes</h5>-->
						<div class="container">
							<div class="row">
								<div class="col-12">
									<label for="textInput" class="label-font">Customer Order Notes</label>

									<textarea required="true"
										id="customer-order-notes" name="notes"
										class="form-control" readonly><?php echo esc_textarea( sanitize_text_field( $customer_notes ) ); ?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</form>
	</div>
</div>

<?php } ?>

<div class="container-fluid" style="margin-left: 0; width: 80%">
	<button type="submit" id="softdataSubmitButton" data-toggle="tooltip" title="Save"
			class="btnSubmit btnBlue" onclick="onBulkSyncOrderHandler('<?php echo esc_html( $order_ids_str ); ?>');">Sync</button>
</div>

<?php } elseif ( array_key_exists( 'error', $response ) ) { ?>
	<div class="alert alert-danger" role="alert"><?php echo esc_html( shipsy_parse_response_error( $response['error'] ) ); ?></div>

<?php } else { ?>
	<div class="alert alert-danger"
		role="alert"><?php echo esc_html( $all_addresses['error'] ?? $valid_service_types['error'] ); ?></div>
	<?php
}
?>

<style>
	/* Chrome, Safari, Edge, Opera */
	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	/* Firefox */
	input[type=number] {
		-moz-appearance: textfield;
	}
</style>
