/**
 * RestockX admin scripts.
 *
 * @package RestockX
 */

(function () {
	'use strict';

	/**
	 * Subscribers page: row delete button submits the bulk form for one row.
	 */
	function initSubscriberDelete() {
		var form = document.getElementById( 'bulk-action-form' );
		if ( ! form ) {
			return;
		}

		var selector   = document.getElementById( 'bulk-action-selector' );
		var applyBtn   = form.querySelector( 'input[name="submit_bulk_action"]' );
		var confirmMsg = ( window.restockx_admin && window.restockx_admin.confirm_delete_subscriber ) || 'Are you sure you want to delete this subscriber?';

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
	}

	/**
	 * Email template page: confirm before resetting to the default template.
	 */
	function initTemplateReset() {
		var resetBtn = document.querySelector( 'input[name="reset_template"]' );
		if ( ! resetBtn ) {
			return;
		}

		var resetMsg = ( window.restockx_admin && window.restockx_admin.confirm_reset_template ) || 'Are you sure you want to reset the email template to its default content?';

		resetBtn.addEventListener( 'click', function ( event ) {
			if ( ! window.confirm( resetMsg ) ) {
				event.preventDefault();
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initSubscriberDelete();
		initTemplateReset();
	} );
})();
