/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 */

// @codingStandardsIgnoreFile
// phpcs:ignoreFile

define([
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/model/messageList',
    'mage/cookies'
], function ($, url, __, messageList) {
    'use strict';

    $.widget('qliro.pollPending', {
        waitingResponse: false,
        counter: 0,
        response: 'PENDING',
        htmlSnippet: null,
        errorMessage: null,
        orderId: null,

        _create: function () {
            this.debug('Initialize polling...');

            $('.qliroone-checkout-processed').trigger('processStart');

            this.poll().then(function() {
                $('.qliroone-checkout-processed').hide().trigger('processStop');
            }.bind(this));
        },

        makeAjaxRequest: function() {
            if (!this.waitingResponse) {
                this.waitingResponse = true;

                this.debug('Polling', this.options.pollPendingUrl);

                $.ajax({
                    url: this.options.pollPendingUrl,
                    method: 'POST'
                }).then(
                    function (data) {
                        this.debug('Polled data', data);

                        this.response = data.status || 'PENDING';

                        if (data.status === 'OK') {
                            this.orderId = data.orderIncrementId;
                            this.htmlSnippet = data.htmlSnippet;
                        }

                        this.waitingResponse = false;
                    }.bind(this),
                    function (response) {
                        var data = response.responseJSON || {};

                        this.debug('Poll has failed', data);

                        this.response = data.status || 'FAILED';
                        this.errorMessage = data.error;

                        this.waitingResponse = false;
                    }.bind(this)
                );
            }
        },

        poll: function() {
            return new Promise(function(resolve) {
                var polling = setInterval(function() {
                    switch(this.response) {
                        case 'OK':
                            this.debug('[DONE]');
                            clearInterval(polling);
                            resolve('DONE');
                            location = url.build('checkout/qliro/success');
                            break;
                        case 'FAILED':
                            this.debug('[FAILED]');
                            clearInterval(polling);
                            this.queueErrorMessage();
                            $.mage.cookies.clear('QOMR');
                            location = url.build('checkout/cart');
                            break;
                        case 'PENDING':
                        default:
                            this.debug('... still pending ...');
                            this.makeAjaxRequest();
                    }
                }.bind(this), 1000)
            }.bind(this))
        },

        debug: function(caption, data) {
            if (this.options.isDebug) {
                if (data) {
                    console.log(caption, data);
                } else {
                    console.log(caption);
                }
            }
        },

        queueErrorMessage: function() {
            messageList.addErrorMessage({ message: this.errorMessage });
        }
    });
});
