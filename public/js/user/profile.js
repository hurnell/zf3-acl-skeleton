$(function () {
    $().initPopup(true);
    $('div#profile-div').on('click', 'span#update-full-name, span#update-email', function () {
        $.ajax({
            type: 'GET',
            url: $(this).attr('url'),
            success: function (data) {
                $().showPopup(data['view'], 'middle');
            }
        });
    });

    $('div#profile-div').on('click', 'span#update-photograph', function () {
        $().initResizeImage(120, 160, 300, 400, true, 'div#outer-popup-holder');
        $.ajax({
            type: 'GET',
            url: $(this).attr('url'),
            success: function (data) {
                $().showPopup(data['view'], 'middle');
            }
        });
    });
    $('div#popup-inner').on('click', 'input#submit-update-profile', function (event) {
        event.preventDefault();
        var form = $(this).closest('form');
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.getFormData(),
            success: function (data) {
                if (true === data['success']) {
                    $().justReload();
                } else {
                    $().refreshPopupContent(data['view']);
                }
            }
        });
        console.log(form.getFormData());
        return false;
    });

});
