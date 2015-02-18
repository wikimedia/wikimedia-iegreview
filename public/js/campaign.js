(function ( $, document, window ) {
  $(document).ready(function() {

    /**
     * Toggle all checkboxes in sync with the toggle-all checkbox
     */
    $('#toggle-all').click(function(event) {
      $( '.reviewer-group input[type="checkbox"]' ).prop('checked', this.checked );
    });

  });
})( jQuery, document, window );
