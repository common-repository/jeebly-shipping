<?php
/**
 * The file to view orders in public end.
 *
 * @link       https://shipsy.io/
 * @since      1.0.3
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/admin
 */

/**
 * The file to view orders in public end.
 */

require_once SHIPSY_ECONNECT_PATH . 'config/settings.php';

if ( $tracking_items ) : ?>
	<table>
		<thead>
			<tr>
				<th class="courier"><span class="nobr"><?php esc_html_e( 'Courier' ); ?></span></th>
				<th class="tracking-number"><span class="nobr"><?php esc_html_e( 'Tracking Number' ); ?></span></th>
				<th class="tracking-url"><?php esc_html_e( 'Actions' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $tracking_items as $key => $tracking_item ) {
				?>
				<tr class="tracking">
					<td class="courier" data-title="<?php esc_attr_e( 'Courier' ); ?>">
						<?php echo esc_html( $ORGANISATION ); // phpcs:ignore ?>
					</td>
					<td class="tracking-number" data-title="<?php esc_attr_e( 'Tracking Number' ); ?>">
						<?php echo esc_html( $tracking_item->shipsy_refno ); ?>
					</td>
					<?php if ( $tracking_item->track_url ) { ?>
						<td class="tracking-url" style="text-align: center;">
							<a target="_blank" href="<?php echo esc_url( $tracking_item->track_url ); ?>"><button>Track Order</button></a>
						</td>
					<?php } ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php
endif; ?>
