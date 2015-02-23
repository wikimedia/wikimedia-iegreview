(function( $, document, window ) {
  $(document).ready(function() {
    $('.langlist').each(function() {
      var $list = $(this),
        $select = $(document.createElement('select'))
          .addClass('form-control')
          .addClass('input-sm')
          .change(function() {
            window.location.href = $(this).find('option:selected').val();
          })
          .insertBefore($list.hide());
      $('>li a', this).each(function() {
        var $this = $(this);
        $(document.createElement('option'))
          .val(this.href)
          .html($this.html())
          .prop('selected',$this.attr('class')==='selected')
          .appendTo($select);
      });
      $list.remove();
    });
    $('.langlabel').show();

    /**
     * Clamped-width.
     * Usage:
     *  <div data-clampedwidth=".myParent">This long content will force clamped width</div>
     *
     * Author: LV
     * See: http://stackoverflow.com/a/17243766/8171
     */
    $('[data-clampedwidth]').each(function () {
      var $e = $(this),
          $parentPanel = $($e.data('clampedwidth')),
          resizeFn = function () {
            var sideBarNavWidth = $parentPanel.width() -
              parseInt($e.css('paddingLeft'), 10) -
              parseInt($e.css('paddingRight'), 10) -
              parseInt($e.css('marginLeft'), 10) -
              parseInt($e.css('marginRight'), 10) -
              parseInt($e.css('borderLeftWidth'), 10) -
              parseInt($e.css('borderRightWidth'), 10);
            $e.css('width', sideBarNavWidth);
          };
      resizeFn();
      $(window).resize(resizeFn);
    });

    /**
     * HTML5 validation scroll with fixed header fix.
     * Author: Devin McInnis
     * See: http://stackoverflow.com/a/14968319
     */
    $('input, select').on('invalid', function () {
        var headerHeight = 60,
            $w = $(window),
            vTop = $w.scrollTop(),
            vBottom = vTop + $w.height(),
            invalid_el = $(':invalid').first().offset().top - headerHeight;
        if ( invalid_el < vTop + headerHeight ||
            invalid_el > vBottom
        ) {
            $('html, body').scrollTop(invalid_el);
        }
    });

  });
})( jQuery, document, window );
