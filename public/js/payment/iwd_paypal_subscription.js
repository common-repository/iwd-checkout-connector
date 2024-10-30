jQuery(
    function($) {

        IWD.PayPalSub = {

            params: {
                'plan_id': '',
                'checkoutPagePath': '',
                'grandTotalAmount': '',
                'shape': 'rect',
                'color': 'gold'
            },

            init: function() {
                if (window.paypal &&  jQuery('#paypal-button-container-s').length !== 0) {
                    jQuery('#paypal-button-container-s').html('');
                    let paypal = window.paypal;
                    paypal.Buttons({
                        style: {
                            shape: IWD.PayPalSub.params.shape,
                            color: IWD.PayPalSub.params.color,
                            layout: 'vertical',
                            label: 'subscribe'
                        },
                        createSubscription: function (data, actions) {
                            var obj =  {
                                /* Creates the subscription */
                                plan_id: IWD.PayPalSub.params.plan_id
                            }

                            if (IWD.PayPalSub.params.quantity_supported == 1) {
                                obj.quantity = $("input[name='quantity']").val();
                            }

                            return actions.subscription.create(obj);
                        },
                        onApprove: function (data, actions) {
                            jQuery('.modal-subscribe').show();
                            IWD.PayPalSub.addMassage();
                        }
                    }).render('#paypal-button-container-s');
                }


                return self;
            },

            addMassage(){
                jQuery('.site-content .col-full .woocommerce').html('');
                var html = "<div class='woocommerce-message' role='alert'>You have successfully subscribed</div>";
                jQuery('.site-content .col-full .woocommerce').html(html);
            }
        };

    }
);
