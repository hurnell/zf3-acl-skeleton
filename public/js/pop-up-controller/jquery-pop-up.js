
$(function () {

    var canHide = true;
    /**
     * 
     * @param {boolean} emptyOnRemove whether to set the contents of the poput to '' when it closes
     * @returns {void}
     */
    $.fn.initPopup = function (emptyOnRemove) {
        $('span#close-popup-in-emergency').off('click').on('click', function () {
            $().addRemoveMask(false, true);
        });
        /**
         * 
         * @param {boolean} flag whether to show the popup (true) or remove it( false)
         * @param {boolean} hide whether to force remove mask
         * @returns {undefined}
         */
        $.fn.addRemoveMask = function (flag, hide)
        {
            canHide = hide === undefined ? canHide : hide;
            if (flag) {
                if ($('div#outer-popup-holder').hasClass('hide-popup-div')) {
                    $('div#outer-popup-holder').removeClass('hide-popup-div');
                }
                $('div#outer-popup-holder').css({height: $(document).height() + 'px'});
            } else if (canHide) {
                if (!$('div#outer-popup-holder').hasClass('hide-popup-div')) {
                    $('div#outer-popup-holder').addClass('hide-popup-div');
                    $('div#popup-inner').html('');
                }
            }
        };
        $.fn.callRemoveMask = function (event) {
            if ('outer-popup-holder' === event.target.id
                    || 'popup-holder' === event.target.id)
            {
                $().removeMask();
            }
        };
        $.fn.removeMask = function () {
            $().addRemoveMask(false);
            if (true === emptyOnRemove && canHide) {
                $().emptyPopup();
            }
        };
        /* accepted values for where = middle, top=default, bottom=later*/
        $.fn.showPopup = function (html, where, hidable) {
            if (undefined === hidable) {
                canHide = true;
            } else {
                canHide = hidable;
            }
            $().addRemoveMask(true);
            $('div#popup-inner').html(html);
            $().repositionPopup(where);
        };
        $.fn.repositionPopup = function (where) {
            $('div#popup-inner').css({marginLeft: -$('div#popup-inner').width() / 2 - parseInt($('div#popup-inner').css('padding-left'))});
            if ('middle' === where) {
                $('div#popup-inner').css({marginTop: $(window).height() / 2 + $(window).scrollTop() - $('div#popup-inner').height() / 2});
            } else {
                $('div#popup-inner').css({marginTop: +25});
                $("html, body").animate({scrollTop: 0}, "slow");
            }
        };
        /**
         * 
         * @param {string} where
         * @returns {void}
         */
        $.fn.preloadPopup = function (where) {
            canHide = false;
            $().showPopup('<div class="popup-preloader"><div class="popup-preloader-inner"></div></div>', where, false);
        };
        $.fn.emptyPopup = function () {
            $('div#popup-inner').html('');
        };
        $.fn.setPopupOuterCss = function (theCss) {
            $('div#outer-popup-holder').css(theCss);
        };

        $.fn.setPopupHolderCss = function (theCss) {
            $('div#popup-holder').css(theCss);
        };

        $.fn.setPopupInnerCss = function (theCss) {
            $('div#popup-inner').css(theCss);
        };
        $.fn.loadPopup = function (loader) {
            $('div#popup-inner').load(loader);
        };
        $.fn.refreshPopupContent = function (html) {
            $('div#popup-inner').html(html);
        };
        $('body').on('click', 'div#outer-popup-holder', $().callRemoveMask);
    };


});