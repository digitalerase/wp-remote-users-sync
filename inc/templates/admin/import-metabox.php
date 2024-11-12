<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="wprus-container" data-postbox_class="wprus-users wprus-togglable">
	<table class="form-table">
		<tr>
			<th>
			</th>
			<td>
				<label for=""><?php esc_html_e( 'Force imported users to this role', 'wprus' ); ?></label>
				<select id="wprus_roles_import_select" class="wprus-select" multiple>
				<?php if ( ! empty( $roles ) ) : ?>
					<?php foreach ( $roles as $user_role ) : ?>
						<option value="<?php echo esc_attr( $user_role ); ?>"><?php echo esc_html( $user_role ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
				</select>
				<div class="import-users-options">
					<input type="checkbox" id="wprus_import_users_pass_option" value="0"><label><?php esc_html_e( 'Keep existing password', 'wprus' ); ?></label>
				</div>
			</td>
		</tr>
		<tr id="wprus_import_file_dropzone">
			<th>
				<label for="wprus_import_file"><?php esc_html_e( 'Users File', 'wprus' ); ?></label>
			</th>
			<td>
				<input class="input-file hidden" type="file" id="wprus_import_file" value=""><label for="wprus_import_file" class="button"><?php esc_html_e( 'Upload File', 'wprus' ); ?></label> <input type="text" id="wprus_import_file_filename" placeholder="wprus-user-export-xxxx-xx-xx-xx-xx-xx.dat" value="" disabled=""> <input type="button" value="<?php esc_html_e( 'Import', 'wprus' ); ?>" class="button button-primary" id="wprus_import_file_trigger" disabled=""><div class="spinner"></div>
				<p class="howto">
					<?php esc_html_e( 'Requires a file previously exported with WP Remote Users Sync', 'wprus' ); ?>
				</p>
			</td>
		</tr>
	</table>
	<div id="wprus_import_results">
		<div class="summary"></div>
		<ul class="errors"></ul>
	</div>
</div>
