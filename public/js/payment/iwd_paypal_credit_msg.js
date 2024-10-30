jQuery(
    function($) {

        IWD.PayPalMsg = {

            params: {
                'grand_total_amount': '',
                'logo_type': 'primary',
                'logo_position': 'left',
                'text_color': 'black',
                'status': 1
            },

            init: function() {
                if (window.paypal) {
                    let paypal = window.paypal,
                        logoConfig = {type: IWD.PayPalMsg.params.logo_type},
                        amount = IWD.PayPalMsg.params.grand_total_amount;

                    if(IWD.PayPalMsg.params.logo_type === 'alternative' || IWD.PayPalMsg.params.logo_type === 'primary') {
                        logoConfig.position = IWD.PayPalMsg.params.logo_position;
                    }
                    if($('.iwd-paypal-credit-msg').closest(".iwd-cart-sidebar-wrapper").length > 0) {
                        amount = IWD.PayPalMsg.params.grand_total_amount;
                    }

                    if(amount > 0 &&  IWD.PayPalMsg.params.status == 1) {
                        window.paypal.Messages({
                            amount: amount,
                            pageType: 'cart',
                            style: {
                                layout: 'text',
                                logo: logoConfig
                            },
                            text: {
                                color: IWD.PayPalMsg.params.text_color
                            }
                        }).render('.iwd-paypal-credit-msg');
                    }
                }
                return self;
            },
        };

    }
);
