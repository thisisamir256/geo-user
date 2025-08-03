jQuery(document).ready(function($) {

	// Uploading files
	var file_frame;

	jQuery.fn.upload_csv_file = function( button ) {
		var button_id = button.attr('id');
		var field_id = button_id.replace( '_button', '' );

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
		    text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
		  var attachment = file_frame.state().get('selection').first().toJSON();
		  jQuery("#"+field_id).val(attachment.id);
		  jQuery("#csvfilediv a").attr('href',attachment.url);
		  jQuery( '#csvfilediv a' ).show();
		  jQuery( '#' + button_id ).attr( 'id', 'remove_csv_file_button' );
		  jQuery( '#remove_csv_file_button' ).text( 'Remove listing image' );
		});

		// Finally, open the modal
		file_frame.open();
	};

	jQuery('#csvfilediv').on( 'click', '#upload_csv_file_button', function( event ) {
		event.preventDefault();
		jQuery.fn.upload_csv_file( jQuery(this) );
	});

	jQuery('#csvfilediv').on( 'click', '#remove_csv_file_button', function( event ) {
		event.preventDefault();
		jQuery( '#upload_csv_file' ).val( '' );
		jQuery( '#upload_csv_file_button').show();
		jQuery( '#csvfilediv a' ).attr( 'href', '' );
		jQuery( '#csvfilediv a' ).hide();
		jQuery( this ).attr( 'id', 'upload_csv_file_button' );
		jQuery( '#upload_csv_file_button' ).text( 'Set listing image' );
	});

});
