(function ($) {
  function reindexRows($wrap) {
    $wrap.find('[data-slide-row]').each(function (i) {
      $(this)
        .find('input, select, textarea')
        .each(function () {
          var name = $(this).attr('name');
          if (!name) return;
          $(this).attr('name', name.replace(/slides\[\d+\]/, 'slides[' + i + ']'));
        });
    });
    $wrap.attr('data-next-index', $wrap.find('[data-slide-row]').length);
  }

  function openPicker($row) {
    var frame = wp.media({
      title: 'Pilih / unggah foto slide informasi',
      button: { text: 'Pakai foto' },
      library: { type: 'image' },
      multiple: false
    });
    frame.on('select', function () {
      var att = frame.state().get('selection').first().toJSON();
      var url =
        att.sizes && (att.sizes.medium || att.sizes.medium_large || att.sizes.thumbnail)
          ? (att.sizes.medium || att.sizes.medium_large || att.sizes.thumbnail).url
          : att.url;
      $row.find('[data-image-id]').val(att.id);
      $row.find('[data-thumb]').html('<img src="' + url + '" alt="" />');
    });
    frame.open();
  }

  $(function () {
    var $wrap = $('#man3_slide_rows');
    if (!$wrap.length) return;

    $wrap.on('click', '[data-pick-image]', function (e) {
      e.preventDefault();
      openPicker($(this).closest('[data-slide-row]'));
    });

    $wrap.on('click', '[data-remove-row]', function (e) {
      e.preventDefault();
      var $rows = $wrap.find('[data-slide-row]');
      if ($rows.length <= 1) {
        var $row = $rows.first();
        $row.find('[data-image-id]').val('0');
        $row.find('[data-thumb]').html('<span class="placeholder">Kosong</span>');
        $row.find('input[type=text], input[type=url]').val('');
        $row.find('input[type=checkbox]').prop('checked', true);
        return;
      }
      $(this).closest('[data-slide-row]').remove();
      reindexRows($wrap);
    });

    $wrap.on('click', '[data-move-up]', function (e) {
      e.preventDefault();
      var $row = $(this).closest('[data-slide-row]');
      var $prev = $row.prev('[data-slide-row]');
      if ($prev.length) {
        $row.insertBefore($prev);
        reindexRows($wrap);
      }
    });

    $wrap.on('click', '[data-move-down]', function (e) {
      e.preventDefault();
      var $row = $(this).closest('[data-slide-row]');
      var $next = $row.next('[data-slide-row]');
      if ($next.length) {
        $row.insertAfter($next);
        reindexRows($wrap);
      }
    });

    $('#man3_slide_add').on('click', function (e) {
      e.preventDefault();
      var tpl = document.getElementById('man3_slide_row_tpl');
      if (!tpl) return;
      var i = parseInt($wrap.attr('data-next-index') || '0', 10);
      if (isNaN(i)) i = $wrap.find('[data-slide-row]').length;
      var html = tpl.innerHTML.replace(/__i__/g, String(i));
      $wrap.append(html);
      $wrap.attr('data-next-index', String(i + 1));
      reindexRows($wrap);
    });
  });
})(jQuery);
