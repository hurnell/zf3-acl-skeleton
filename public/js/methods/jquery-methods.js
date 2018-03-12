$(function () {
    $.fn.getFormData = function () {
        console.log('getFormData');
        var elements = this.find('input, select, textarea');
        console.log(elements);
        var data = {};
        for (var i = 0; i < elements.length; i++) {

            var element = $(elements[i]);
            var elementType = element.prop('nodeName');

            switch (elementType.toLowerCase()) {
                case 'input':
                    switch (true) {
                        case ('text' === element.attr('type') && element.hasClass('with-datepicker')) :
                            var d = new Date(element.val());// mysql date format
                            data[element.attr('name')] = $.datepicker.formatDate("yy-mm-dd", d);
                            break;
                        case('text' === element.attr('type')):
                        case('email' === element.attr('type')):
                            data[element.attr('name')] = element.val();
                            break;
                        case  ('hidden' === element.attr('type')):
                            data[element.attr('name')] = element.val();
                            break;
                        case  ('radio' === element.attr('type')) :
                            var name = element.attr('name');
                            data[name] = $('input[name=' + name + ']:checked').val();
                            break;
                        case  ('checkbox' === element.attr('type')):
                            var eName = element.attr('name');
                            if (eName.indexOf('[]') === -1) {
                                data[eName] = element.is(':checked') ? 1 : 0;
                            } else {
                                var name = eName.replace('[]', '');
                                if (element.is(':checked')) {
                                    if (typeof data[name] === 'undefined') {
                                        data[name] = [];
                                    }
                                    data[name][(data[name]).length] = element.is(':checked') ? 1 : 0;
                                }
                            }
                        default:
                            console.log('else');
                            console.log(element.attr('name'));
                            console.log(element.val());
                    }
                    break;
                case 'select':
                    data[element.attr('name')] = element.val();
                    break;
                case 'textarea':
                    data[element.attr('name')] = element.val();
                    console.log(element.attr('name'));
                    console.log(element.val());
                    break;
                default:
                    console.log(elementType);
            }
        }
        return data;
    };
    /**
     * Returns the present locale from the url
     * @returns {String}
     */
    $.fn.getLocale = function () {
        var parts = window.location.pathname.split('/');
        var locale = 'en_GB';
        if (parts.length > 1) {
            locale = parts[1];
        }
        return locale;
    };

    /**
     * Just reload what is there
     * @returns {void}
     */
    $.fn.justReload = function () {
        $(window).attr("location", $(window).attr("location"));
    };
    /**
     * Sets all datepickers locale
     * @returns {void}
     */
    $.fn.setDatepickerLanguage = function () {
        var locale = $().getLocale();
        var parts = locale.split('_');
        var language = parts[0];
        $.datepicker.setDefaults($.datepicker.regional[language]);
    };
    $().setDatepickerLanguage();
    /**
     * 
     * @param {string} elementClass
     * @param {boolean} add whether to add the class or remove it
     * @returns {void}
     */
    $.fn.addRemoveClass = function (elementClass, add) {
        if (add) {
            if (!this.hasClass(elementClass)) {
                this.addClass(elementClass);
            }
        } else {
            if (this.hasClass(elementClass)) {
                this.removeClass(elementClass);
            }
        }
    };

    $.fn.getIdFromElement = function (attribute, index) {
        var whole = this.attr(attribute);
        var parts = whole.split('-');
        if (parts.length > index) {
            return parts[index];
        }
        return '';
    };

    $.fn.getFinalRouteParam = function (path) {
        var pathname = (typeof path === 'undefined') ? window.location.pathname : path;
        var last = pathname.lastIndexOf('/');
        return pathname.substring(last + 1);
    };

});