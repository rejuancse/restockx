/**
 * RestockX admin scripts.
 *
 * @package RestockX
 */

(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'bulk-action-form' );
		if ( ! form ) {
			return;
		}

		var selector   = document.getElementById( 'bulk-action-selector' );
		var applyBtn   = form.querySelector( 'input[name="submit_bulk_action"]' );
		var confirmMsg = ( window.restockx_admin && restockx_admin.confirm_delete_subscriber ) || 'Are you sure you want to delete this subscriber?';

		form.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest( '.kebab.delete' );
			if ( ! btn ) {
				return;
			}

			event.preventDefault();

			if ( ! window.confirm( confirmMsg ) ) {
				return;
			}

			var row = btn.closest( 'tr' );
			var checkbox = row ? row.querySelector( 'input[name="notifications[]"]' ) : null;
			if ( ! checkbox ) {
				return;
			}

			// Delete only this row: clear any other selections first.
			form.querySelectorAll( 'input[name="notifications[]"]' ).forEach( function ( cb ) {
				cb.checked = false;
			} );
			checkbox.checked = true;

			if ( selector ) {
				selector.value = 'delete';
			}

			if ( applyBtn ) {
				applyBtn.click();
			}
		} );
	} );
})();
