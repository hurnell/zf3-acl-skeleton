$(function () {
    $("ul#availableflags, ul#enabledflags").sortable({
        connectWith: ".connected-sortable",
        placeholder: "sortable-placeholder",
        receive: function (event, ui) {
            if (null !== ui.sender) {
                var itemId = $(ui.item).attr('id');
                var itemNewId;
                var changeType = '';
                //console.log($(ui.sender).attr('id'));
                // console.log($(ui.item).attr('id'));
                if ('availableflags' === $(ui.sender).attr('id')) {
                    itemNewId = itemId.replace('availableflags', 'enabledflags');
                    changeType = 'enable';
                } else if ('enabledflags' === $(ui.sender).attr('id')) {
                    itemNewId = itemId.replace('enabledflags', 'availableflags');
                    changeType = 'disable';
                }
                var parts = itemNewId.split('-');
                var postData = {
                    locale: parts[1],
                    change: changeType
                };
                $.ajax({
                    type: "POST",
                    data: postData,
                    url: 'ajax-update-available-languages',
                    success: function () {
                        $().justReload();
                    },
                    error: function (a, b, c) {
                        console.log(b + ": " + c);
                    }
                });
            }
        }

    }).disableSelection();
});