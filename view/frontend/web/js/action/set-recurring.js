define([
    'jquery',
    'Qliro_QliroOne/js/model/config',
    'mage/url'
], function ($, qliroConfig, mageUrl) {
    'use strict';

    const action = function(config) {
        const url = mageUrl.build(qliroConfig.recurringOrder.setRecurringUrl);

        return $.post(
            url + '?token=' + qliroConfig.securityToken, 
            JSON.stringify(config)
        );
    }

    return action;
});
