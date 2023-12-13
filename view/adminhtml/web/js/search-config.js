/**
 * Copyright © Magentiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 * This script is a Pop-up content on site
 */
define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/calendar'
], function ($, registry, modal, alert) {

    $.widget('mage.searchConfig', {
        options: {
            baseUrl: '',
            resultText: '#search-config-results',
            itemNavTab: 'a.admin__page-nav-link.item-nav'
        },

        showText: false,
        resultText: null,
        formElement: null,
        itemNavTab: null,

        _create: function() {
            var self = this;
            this.resultText = $(self.options.resultText);
            this.itemNavTab = $(self.options.itemNavTab)

            $(document).ready(function () {
                self._bindClick();
                self._enableSubmitButton();
            });

        },

        _bindClick: function () {
            var self = this;

            self.element.on('click', 'button.primary.show-text', function () {
               self.showText = true;
            });

            self.element.on('click', 'button.primary.clear', function (e) {
                e.preventDefault();
                self.showText = false;
                self.element.find('input#search-input').val('');
                self._clearSearchResult();
            });

            self.element.on('submit', function (e) {
                e.preventDefault();
                self._clearSearchResult();
                self._ajaxSubmit($(this));
            })
        },

        _ajaxSubmit: function (form) {
            var self = this;

            $.ajax({
                url: form.attr('action'),
                data: form.serializeArray(),
                beforeSend: function () {
                    self._disableSubmitButton();
                    $('body').trigger('processStart');
                },
                success: function (res) {
                    if(! res.error) {
                        self.itemNavTab.removeClass('found');
                        var html = '<ul>';
                        res.each(function (tab) {
                            var navEl = $('#' + tab.tabId);
                            if (navEl.length) {
                                navEl.parents('.config-nav-block').removeClass('_hide').addClass('_show');
                                navEl.parents('ul.items').show();
                                navEl.addClass('found');
                            }

                            if (self.showText) {
                                html += '<li><strong>'+tab.name+'</strong>: <a href="'+tab.url+'">'+tab.description+'</a></li>';
                            }
                        })
                        html += '</ul>';
                        if (self.showText) {
                            self.resultText.html(html);
                        }
                    }

                },
                complete: function () {
                    $('body').trigger('processStop');
                    self.showText = false;
                    self._enableSubmitButton();
                }
            })
        },

        _clearSearchResult: function () {
            this.resultText.empty();
            var tabEl = $('.config-nav-block._show').not('._tab_active');
            tabEl.removeClass('_show').addClass('_hide');
            tabEl.find('ul.items').hide();
            this.itemNavTab.removeClass('found');
        },

        _enableSubmitButton: function () {
            this.element.find('.search-btn button').attr('disabled', false);
        },

        _disableSubmitButton: function () {
            this.element.find('.search-btn button').attr('disabled', true);
        },

        _validateForm: function(form) {
            return form.validation().validation('isValid');
        }
    });

    return $.mage.searchConfig;
});
