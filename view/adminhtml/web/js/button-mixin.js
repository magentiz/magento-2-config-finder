define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    var buttonMixin = {
        _isSaveEvent: function()
        {
            if (this.options.event !== "save" && this.options.event !== "saveAndContinueEdit") {
                return false;
            }
            if (!this.options.target || $(this.options.target).length === 0 || !$(this.options.target).is("form#edit_form")) {
                return false;
            }
            if (this.options.eventData && this.options.eventData.ignore_loader) {
                return false;
            }
            return true;
        },
        /**
         * Bind handler on button click.
         * @protected
         */
        _bind: function () {
            this._super();
            if (this._isSaveEvent()) {
                $(this.options.target).on('invalid-form', function() {
                    $('body').trigger('processStop');
                    if (this.loaderTimeoutHandler) {
                        clearTimeout(this.loaderTimeoutHandler);
                        this.loaderTimeoutHandler = 0;
                    }
                });
            }
        },
        /**
         * Button click handler.
         * @protected
         */
        _click: function () {
            if (this._isSaveEvent()) {
                $("body").trigger("processStart");
                if (this.loaderTimeoutHandler) {
                    clearTimeout(this.loaderTimeoutHandler);
                    this.loaderTimeoutHandler = 0;
                }
                if (this.options.eventData && this.options.eventData.loader_timeout) {
                    this.loaderTimeoutHandler = setTimeout(() => {
                        $('body').trigger('processStop');
                    }, +this.options.eventData.loader_timeout);
                }
            }
            this._super();
        }
    };
    return function (targetButton) {
        $.widget('ui.button', targetButton, buttonMixin);
        return $.ui.button;
    };
});
