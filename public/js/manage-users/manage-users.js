$(function () {
    var userId = $('ul#user-roles').attr('uid');
    $("#possible-roles, #user-roles").sortable({
        connectWith: ".connected_sortable",
        placeholder: "sortable-placeholder",
        cancel: '.unsortable',
        receive: function (event, ui) {
            var postData = {
                user_id: userId
            };
            if (null !== ui.sender) {
                if ('user-roles' === $(ui.sender).attr('id')) {
                    postData.role_id = $(ui.item).attr('id');
                    postData.type = 'remove';
                } else if ('possible-roles' === $(ui.sender).attr('id')) {
                    postData.role_id = $(ui.item).attr('id');
                    postData.type = 'add';
                }


                $.ajax({
                    type: "POST",
                    url: '../ajax-update-user-role-membership',
                    data: postData
                });
            }
        }
    }).disableSelection();

        
    $('td.suspend-user button').on('click', function () {
        var userId = $(this).closest('td').attr('id');
        var row = $(this).closest('tr');
        var button = $(this);
        var present = $(this).attr('present');
        var opposite = $(this).attr('opposite');
        var suspended = row.hasClass('suspended-user');
        $.ajax({
            type: "POST",
            url: 'ajax-toggle-suspension-user-by-id/' + userId,
            success: function (data) {
                suspended ? row.removeClass('suspended-user').addClass('active-user') : row.addClass('suspended-user').removeClass('active-user');
                button.html(opposite.trim());
                button.attr('opposite', present.trim());
                button.attr('present', opposite.trim());
            }
        });
    });
    $('td.delete-user button').on('click', function () {
        var userId = $(this).closest('td').attr('id');
        var row = $(this).closest('tr');
        var response = confirm($(this).attr('warning'));
        if (response) {
            $.ajax({
                type: "POST",
                url: 'ajax-delete-user-by-id/' + userId,
                success: function (data) {
                    row.hide();
                }
            });
        }
    });
});