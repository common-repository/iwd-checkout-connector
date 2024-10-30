jQuery(
	function($) {

		IWD        = {};
		IWD.PayPal = {

			params: {
				'containerId': '',
				'checkoutPagePath': '',
				'grandTotalAmount': '',
				'btnShape': 'rect',
				'btnColor': 'gold',
				'creditStatus': 1
			},

			init: function() {
				if (window.paypal) {
					let paypal = window.paypal,
						clickedFundingSource = 'paypal';

					paypal.Buttons(
						{
							fundingSource: IWD.PayPal.params.creditStatus == 1 ? '' : 'paypal',
							style: {
								layout: 'horizontal',
								size: 'responsive',
								shape: IWD.PayPal.params.btnShape,
								color: IWD.PayPal.params.btnColor,
								height: 43,
								fundingicons: false,
								tagline: false,
							},

							onClick: function(data)  {
								clickedFundingSource = data.fundingSource;
							},

							createOrder: function(data, actions) {
								return actions.order.create(
									{
										purchase_units: [{
											amount: {
												value: IWD.PayPal.params.grandTotalAmount
											}
										}]
									}
								);
							},

							onApprove: function(data) {
								window.location.href = IWD.PayPal.params.checkoutPagePath
									+ '?paypal_order_id=' + data.orderID + '&paypal_funding_source=' + clickedFundingSource;
							}
						}
					).render( '#' + IWD.PayPal.params.containerId );
				}
				return self;
			},
		};

	}
);
