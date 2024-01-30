define([
    'jquery',
    'Qliro_QliroOne/js/model/config',
    'mage/url',
    'mage/storage',
], function ($, qliroConfig, mageUrl) {
    'use strict';

    const action = function(countryId) {
        const url = mageUrl.build(qliroConfig.countrySelector.updateCountryUrl);

        return $.post(
            url + '?token=' + qliroConfig.securityToken, 
            JSON.stringify({
                countryId: countryId,
            })
        ).done(function(response) {
            if ('OK' === response.status) {
                window.location.reload();
            }
        });
    }

    return action;
});
