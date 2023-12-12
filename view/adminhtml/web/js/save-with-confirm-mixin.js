define([
    'jquery'
], function ($) {
    'use strict';
    var mixin = {
        /**
         * Check is it need to show confirmation popup
         *
         * @returns {Boolean}
         */
        _needConfirm: function () {
            var result = this._super();
            if (result) {
                $('body').trigger('processStop');
            }
            return result;
        }
    };
    return function (target) {
        $.widget('mage.saveWithConfirm', target, mixin);
        return $.mage.saveWithConfirm;
    };
});
