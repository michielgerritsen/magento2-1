define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        $,
        Component,
        rendererList
    ) {
        'use strict';
        var defaultComponent = 'Mollie_Payment/js/view/payment/method-renderer/default';
        var idealComponent = 'Mollie_Payment/js/view/payment/method-renderer/ideal';
        var kbcComponent = 'Mollie_Payment/js/view/payment/method-renderer/kbc';
        var giftcardComponent = 'Mollie_Payment/js/view/payment/method-renderer/giftcard';
        var methods = [
            {type: 'mollie_methods_bancontact', component: defaultComponent},
            {type: 'mollie_methods_banktransfer', component: defaultComponent},
            {type: 'mollie_methods_belfius', component: defaultComponent},
            {type: 'mollie_methods_creditcard', component: defaultComponent},
            {type: 'mollie_methods_ideal', component: idealComponent},
            {type: 'mollie_methods_kbc', component: kbcComponent},
            {type: 'mollie_methods_paypal', component: defaultComponent},
            {type: 'mollie_methods_paysafecard', component: defaultComponent},
            {type: 'mollie_methods_sofort', component: defaultComponent},
            {type: 'mollie_methods_inghomepay', component: defaultComponent},
            {type: 'mollie_methods_giropay', component: defaultComponent},
            {type: 'mollie_methods_eps', component: defaultComponent},
            {type: 'mollie_methods_klarnapaylater', component: defaultComponent},
            {type: 'mollie_methods_klarnasliceit', component: defaultComponent},
            {type: 'mollie_methods_giftcard', component: giftcardComponent},
            {type: 'mollie_methods_przelewy24', component: defaultComponent}
        ];

        function canUseApplePay()
        {
            try {
                return window.ApplePaySession && window.ApplePaySession.canMakePayments();
            } catch (error) {
                console.warn('Error when trying to check Apple Pay:', error);
                return false;
            }
        }

        /**
         * Only add Apple Pay if the current client supports Apple Pay.
         */
        if (canUseApplePay()) {
            methods.push({
                type: 'mollie_methods_applepay',
                component: defaultComponent
            });
        }

        $.each(methods, function (k, method) {
            if (window.checkoutConfig.payment.isActive[method['type']]) {
                rendererList.push(method);
            }
        });

        return Component.extend({});
    }
);