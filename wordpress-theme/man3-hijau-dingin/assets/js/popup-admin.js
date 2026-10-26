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
        title: 'Pilih / unggah gambar poster popup',
        button: { text: 'Pakai sebagai poster' },
        library: { type: 'image' },
        multiple: false
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $('#man3_popup_image_id').val(att.id);
        var url = (att.sizes && (att.sizes.medium_large || att.sizes.large || att.sizes.medium))
          ? (att.sizes.medium_large || att.sizes.large || att.sizes.medium).url
          : att.url;
        $('#man3_popup_image_preview').html(
          '<img src="' + url + '" alt="" style="max-width:min(520px,100%);height:auto;border-radius:10px;border:1px solid #c3c4c7;box-shadow:0 4px 16px rgba(0,0,0,.08);" />'
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
