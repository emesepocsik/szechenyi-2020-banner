jQuery(document).ready(function ($) {
    $('.choose-image').click(function (e) {
        e.preventDefault();
        var imageUploader = wp.media({
            'title': szechenyi_admin.title,
            'button': {
                'text': szechenyi_admin.button
            },
            'multiple': false
        }).on('select', function () {
            var attachment = imageUploader.state().get('selection').first().toJSON();
            $('#banner_image').val(attachment.url);
            $('#preview_image').attr('src', attachment.url);
        }).open();
    });
    $('.remove-image').click(function (e) {
        e.preventDefault();
        $('#banner_image').val('');
        $('#preview_image').attr('src', '');
        $('#preview_image').remove();
    });
});