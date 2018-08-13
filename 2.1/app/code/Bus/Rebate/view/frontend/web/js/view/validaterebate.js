define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Bus_Rebate/js/model/validaterebate'
    ],
    function (Component, additionalValidators, yourValidator) {
        'use strict';
        additionalValidators.registerValidator(yourValidator);
        return Component.extend({});
    }
);
