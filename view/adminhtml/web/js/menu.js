
define('globalNavigationScroll', [
    'jquery'
], function ($) {
    'use strict';

    var win = $(window),
        subMenuClass = '.submenu',
        fixedClassName = '_fixed',
        menu = $('.menu-wrapper'),
        content = $('.page-wrapper'),
        menuItems = $('#nav').children('li'),
        winHeight,
        menuHeight = menu.height(),
        menuScrollMax = 0,
        submenuHeight = 0,
        contentHeight,
        winTop = 0,
        winTopLast = 0,
        scrollStep = 0,
        nextTop = 0;

    /**
     * Check if menu is fixed
     * @returns {Boolean}
     */
    function isMenuFixed() {
        return menuHeight < contentHeight && contentHeight > winHeight;
    }

    /**
     * Check if class exist than add or do nothing
     * @param {jQuery} el
     * @param {String} $class
     */
    function checkAddClass(el, $class) {
        if (!el.hasClass($class)) {
            el.addClass($class);
        }
    }

    /**
     * Check if class exist than remove or do nothing
     * @param {jQuery} el
     * @param {String} $class
     */
    function checkRemoveClass(el, $class) {
        if (el.hasClass($class)) {
            el.removeClass($class);
        }
    }

    /**
     * Calculate and apply menu position
     */
    function positionMenu() {

        //  Spotting positions and heights
        winHeight = win.height();
        contentHeight = content.height();
        winTop = win.scrollTop();
        scrollStep = winTop - winTopLast;

        if (isMenuFixed()) { // fixed menu cases

            checkAddClass(menu, fixedClassName);

            if (menuHeight > winHeight) { // smart scroll cases

                if (winTop > winTopLast) { //eslint-disable-line max-depth

                    menuScrollMax = menuHeight - winHeight;

                    nextTop < menuScrollMax - scrollStep ?
                        nextTop += scrollStep : nextTop = menuScrollMax;

                    menu.css('top', -nextTop);

                } else if (winTop <= winTopLast) { // scroll up

                    nextTop > -scrollStep ?
                        nextTop += scrollStep : nextTop = 0;

                    menu.css('top', -nextTop);

                }

            }

        } else { // static menu cases
            checkRemoveClass(menu, fixedClassName);
            menu.css('top', 'auto');
        }

        //  Save previous window scrollTop
        winTopLast = winTop;

    }

    positionMenu(); // page start calculation

    //  Change position on scroll
    win.on('scroll', function () {
        positionMenu();
    });

    win.on('resize', function () {

        winHeight = win.height();

        //  Reset position if fixed and out of smart scroll
        if (menuHeight < contentHeight && menuHeight <= winHeight) {
            menu.removeAttr('style');
            menuItems.off();
        }

    });

    //  Add event to menuItems to check submenu overlap
    menuItems.on('click', function () {

        var submenu = $(this).children(subMenuClass),
            delta,
            logo = $('.logo')[0].offsetHeight;

        submenuHeight = submenu.height();

        if (submenuHeight > menuHeight && menuHeight + logo > winHeight) {
            menu.height(submenuHeight - logo);
            delta = -menu.position().top;
            window.scrollTo(0, 0);
            positionMenu();
            window.scrollTo(0, delta);
            positionMenu();
            menuHeight = submenuHeight;
        }

        var top = isMenuFixed() ? nextTop + 'px' : $(window).scrollTop() + 'px';
        submenu.css('top', top);
    });

});
