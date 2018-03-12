$(function () {
    $().tableSorter('table.table');
    /*$('a#edit-translation-google-link').on('click', function (event) {
        event.preventDefault();
        var href = $(this).attr('href');
        $().initPopup();

        $.ajax({
            type: "POST",
            data: {uri: href},
            url: '/en_GB/translation/ajax-get-google-translation',
            success: function (data) {
                console.log(data.result);
                $().showPopup( data.result );
            }
        });

    });//*/
});