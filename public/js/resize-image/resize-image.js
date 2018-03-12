$(function () {
    $.fn.initResizeImage = function (imageWidth, imageHeight, containerWidth, containerHeight, removePopupOnSuccess, holder) {
        var droppedFile;
        var fileName;
        var rotation = 0;
        var w;
        var h;
        var ex;
        var pl;
        var pt;
        var divHeight;
        var divWidth;
        var ratio;
        var l;
        var targetIn;
        var t;
        var allowedIds = ['image-container-div', 'initial-view', 'upload-icon', 'file-chooser', 'drag-here-span', 'drag-and-drop-image-form', 'inner-holder', 'file-chooser', 'file-chooser-paragraph'];
        var isAdvancedUpload = function ()
        {
            var div = document.createElement('div');
            return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
        }();
        if (isAdvancedUpload) {


            addUploadClickHandler();
            addShowMaskHandler();
            addRotateImageHandler();
            addCreateImageHandler();
            var droppedFiles = false;
            $().initPopup(true);


            initialiseDragAndDrop();
            function initialiseDragAndDrop() {
                $('html').off('drag dragstart dragend dragover dragenter dragleave drop').on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                    var target = $(e.target).attr('id');
                    if (target !== 'image-holder') {
                        e.preventDefault();
                        if (allowedIds.indexOf(target) !== -1) {
                            e.stopPropagation();
                        }
                    }
                })
                        .on('dragover dragenter', function (e) {
                            targetIn = $(e.target).attr('id');
                            if ('drag-and-drop-image-form' === targetIn || allowedIds.indexOf(targetIn) !== -1) {
                                if (!$(holder + ' div#initial-view').hasClass('is-dragover')) {
                                    $(holder + ' div#initial-view').addClass('is-dragover');
                                }
                            }
                        })
                        .on('dragleave dragend drop', function (e) {
                            var targetOut = $(e.target).attr('id');
                            if ('drag-and-drop-image-form' === targetOut) {
                                $(holder + ' div#initial-view').removeClass('is-dragover');
                            }
                        })
                        .off('click', 'span.drag-and-drop-span').on('click', 'span.drag-and-drop-span', function (event) {
                    event.preventDefault();
                })
                        .on('drop', function (e) {
                            droppedFiles = null;
                            if (allowedIds.indexOf($(e.target).attr('id')) !== -1) {
                                droppedFiles = e.originalEvent.dataTransfer.files;
                                droppedFile = droppedFiles[0];
                                if (checkFileType(droppedFile)) {
                                    renderImage(droppedFile);
                                }
                            }
                        });
            }
            function renderImage(file) {
                // generate a new FileReader object
                var reader = new FileReader();
                // inject an image with the src url
                reader.onload = function (event) {
                    var image = new Image();
                    image.src = reader.result;
                    fileName = file.name;
                    var fileUrl = event.target.result;
                    image.onload = function () {
                        w = image.width;
                        h = image.height;
                        l = 0;
                        t = 0;
                        pl = 0;
                        pt = 0;
                        ratio = 1;
                        divHeight = $(holder + ' div#image-container-div').height();
                        divWidth = $(holder + ' div#image-container-div').width();
                        if (w > h) {
                            ratio = w / divWidth;
                            t = (divHeight - h / ratio) / 2 + 'px';
                            $(holder + ' div#image-container-div img').css({width: divWidth + 'px'});
                            $(holder + ' div#image-container-div div#image-holder').css({top: t, left: 0});
                        } else {
                            ratio = h / divHeight;
                            l = (divWidth - w / ratio) / 2 + 'px';
                            $(holder + ' div#image-container-div div#image-holder').css({top: 0, left: l});
                            $(holder + ' div#image-container-div img').css({height: divHeight + 'px'});
                        }
                        $(holder + ' svg#upload-icon').addClass('hidden-upload-icon');
                        $(holder + ' p#file-chooser-paragraph').addClass('hidden-file-chooser-paragraph');
                        $(holder + ' div#image-container-div').removeClass('hidden-image-container-div');
                        $(holder + ' div#image-container-div img').attr('src', fileUrl);
                        initialiseDraggable();
                        initialiseSlider();
                    };

                };

                // when the file is read it triggers the onload event above.
                reader.readAsDataURL(file);
            }
            function addUploadClickHandler() {
                $(holder).off('click', 'span#file-chooser, svg#upload-icon').on('click', 'span#file-chooser, svg#upload-icon', function (event) {
                    $(holder + ' input#file-input').trigger('click');
                });
                $(holder).off('change', 'input#file-input').on('change', 'input#file-input', function (event) {
                    droppedFiles = $(this)[0].files;
                    droppedFile = droppedFiles[0];
                    renderImage(droppedFile);
                });
            }
            function addShowMaskHandler() {
                $(holder).off('click', 'div#left-button').on('click', 'div#left-button', function () {
                    var buttonText = '';
                    $(holder + ' div.rectangle-mask').each(function () {
                        if ($(this).hasClass('rectangle-mask-hidden')) {
                            $(holder + ' div#image-container-div').addClass('image-container-black');
                            $(this).removeClass('rectangle-mask-hidden');
                            buttonText = $(holder + ' div#left-button').attr('tranlation-hide');
                        } else {
                            $(this).addClass('rectangle-mask-hidden');
                            $(holder + ' div#image-container-div').removeClass('image-container-black');
                            buttonText = $(holder + ' div#left-button').attr('tranlation-show');
                        }
                    });
                    $(holder + ' div#left-button').html(buttonText);
                });
            }
            function addRotateImageHandler() {
                $(holder).off('click', 'div#rotate-button').on('click', 'div#rotate-button', function () {
                    rotation++;
                    rotation = rotation % 4;
                    $(holder + ' div#image-container-div img').css({
                        '-ms-transform': 'rotate(' + 90 * rotation + 'deg)',
                        '-webkit-transform': 'rotate(' + 90 * rotation + 'deg)',
                        'transform': 'rotate(' + 90 * rotation + 'deg)'
                    });
                });
            }
            function addCreateImageHandler() {
                $(holder).off('click', 'div#right-button').on('click', 'div#right-button', function () {
                    var pos = $(holder + ' div#image-container-div div#image-holder img').position();
                    var pos2 = $(holder + ' div#image-container-div div#image-holder').position();
                    var ajaxData = new FormData();
                    ajaxData.append('file', droppedFile);
                    ajaxData.append('file-name', fileName);
                    ajaxData.append('dst_x', 0);
                    ajaxData.append('dst_y', 0);
                    ajaxData.append('src_x', ((containerWidth - imageWidth) / 2 - pos.left - pos2.left) * ratio);
                    ajaxData.append('src_y', ((containerHeight - imageHeight) / 2 - pos.top - pos2.top) * ratio);
                    ajaxData.append('dst_w', imageWidth);
                    ajaxData.append('dst_h', imageHeight);
                    ajaxData.append('src_w', imageWidth * ratio);
                    ajaxData.append('src_h', imageHeight * ratio);
                    ajaxData.append('ratio', ratio);
                    ajaxData.append('rotation', rotation * 90);

                    console.log('outside');
                    $.ajax({
                        url: $('form#drag-and-drop-image-form').attr('action'),
                        type: 'POST',
                        data: ajaxData,
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        complete: function () {

                        },
                        success: function (data) {
                            console.log('start');
                            if (data.success === true) {
                                $('div#photo-div img#child-photo').attr('src', data.location);
                                if (true === removePopupOnSuccess) {
                                    $().removeMask();
                                    $().justReload();
                                }
                                $('div#photo-div').addRemoveClass('hide-div', false);
                            } else {
                                var errors = (data.errors).join('<br />');
                                $('p#error-messages').html(errors);
                            }
                        },
                        error: function () {
                            // Log the error, show an alert, whatever works for you
                        }
                    });
                });
            }
            function initialiseDraggable() {
                $(holder + ' div#image-container-div div#image-holder').draggable({
                    drag: function () {
                        var pos = $(holder + ' div#image-container-div div#image-holder').position();
                        pl = pos.left + $(holder + ' div#image-container-div div#image-holder').width() / 2 - divWidth / 2;
                        pt = pos.top + $(holder + ' div#image-container-div div#image-holder').height() / 2 - divHeight / 2;
                    }
                });
            }
            function initialiseSlider() {
                $(holder + ' #vertical-slider').slider({
                    orientation: "vertical",
                    range: "min",
                    min: 0,
                    max: 400,
                    value: 100 / ratio,
                    slide: function (event, ui) {
                        ratio = 100 / ui.value;
                        t = ((divHeight - h / ratio) / 2 + pt) + 'px';
                        l = ((divWidth - w / ratio) / 2 + pl) + 'px';
                        $(holder + ' div#image-container-div img').css({height: h / ratio + 'px', width: w / ratio + 'px'});
                        $(holder + ' div#image-container-div div#image-holder').css({top: t, left: l});
                    }
                });
            }

            function checkFileType(droppedFile) {
                var allowed = $('form#drag-and-drop-image-form').attr('allowed');
                var parts = allowed.split(' ');
                var types = [];
                for (var i = 0; i < parts.length; i++) {
                    types[i] = 'image/' + parts[i];
                }
                var yes = types.indexOf(droppedFile.type) > -1;
                var message = yes ? '' : $('form#drag-and-drop-image-form').attr('error-message');
                $('p#error-messages').html(message);
                return yes;
            }
        } else {
            alert('you need to use a modern browser');
        }
    };

});