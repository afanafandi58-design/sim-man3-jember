(function ($) {
  $(function () {
    var frame;
    $('#man3_popup_image_pick').on('click', function (e) {
      e.preventDefault();
      if (frame) {
        frame.open();
        return;
      }
      frame = wp.media({
        title: 'Pilih gambar popup',
        button: { text: 'Gunakan gambar' },
        multiple: false
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $('#man3_popup_image_id').val(att.id);
        var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
        $('#man3_popup_image_preview').html(
          '<img src="' + url + '" alt="" style="max-width:280px;height:auto;border-radius:8px;" />'
        );
      });
      frame.open();
    });
    $('#man3_popup_image_clear').on('click', function (e) {
      e.preventDefault();
      $('#man3_popup_image_id').val('0');
      $('#man3_popup_image_preview').empty();
    });
  });
})(jQuery);
