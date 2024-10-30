<?php
/**
 * Shipsy plugin config page.
 *
 * @link       https://shipsy.io/
 * @since      1.0.3
 *
 * @package    Shipsy_Econnect
 * @subpackage Shipsy_Econnect/admin/partials
 */

/** Shipsy plugin config page. */

?>

<div class="container forms-container">
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data"
		id="config-form">
		<input type="hidden" name="action" value="on_config_submit"/>
		<div class="row">
			<div class="col-md-6 config-form">
				<h3>Configure</h3>
				<div class="form-group">
					<input type="text" class="form-control" required="true" id="forward-name" name="user-name"
						placeholder="Username *" value=""/>
				</div>
				<div class="form-group">
					<input type="password" class="form-control" id="forward-line-1" required="true" name="password"
						placeholder="Password *" value=""/>
				</div>
				<div class="form-group">
					<input type="text" class="form-control" placeholder="Organization Id *" required="true"
						id="forward-org-id" name="org_id" value=""/>
				</div>
				<div class="form-group">
					<button type="submit" class="btnSubmit" form="config-form" value="Save">Save</button>
				</div>
			</div>

			<div class="col-md-6 settings-form">
				<h3>Settings</h3>
				<div class="block" style="margin-top:20px; float: left">
					<label style="width: 100%">
						<input type="checkbox" name="download_label_option" value="true"
							id="download_label_option">
						Enable download shipping label in orders.
					</label>
				</div>
			</div>
		</div>
	</form>
</div>
