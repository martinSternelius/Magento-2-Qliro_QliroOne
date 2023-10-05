/**
 * Copyright © Qliro AB. All rights reserved.
 * See LICENSE.txt for license details.
 *
 * Remove template rendering for this component
 */

define([], function () {
    'use strict';

    return function (shippingFunction) {
        var result = {};
        if (window.checkoutConfig.qliro.enabled) {
            result = {defaults: {template: ''}};
        }
        return shippingFunction.extend(result);
    }
});

