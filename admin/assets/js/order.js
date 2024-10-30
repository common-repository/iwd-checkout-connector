jQuery(
	function($) {
		let IWD;
		IWD       = {};
		IWD.Order = {

			params: iwd_order_action_params,
			button_capture: '.iwd-capture-items',
			button_void: '.iwd-void-items',
			button_ship: '.iwd-ship-items',

			init: function() {
				$( IWD.Order.button_void ).on( 'click', this.void );
				$( IWD.Order.button_capture ).on( 'click', this.capture );
				$( IWD.Order.button_ship ).on( 'click', this.ship );
			},
			void: function (e) {
				e.preventDefault();
				IWD.Order.block();

				let result = confirm( IWD.Order.params.void.message );
				if (result === false) {
					IWD.Order.unblock();
					return;
				}
				let data = { order_id: IWD.Order.params.order_id };
				$.ajax(
					{
						url:  IWD.Order.params.void.url,
						dataType: 'json',
						method: 'POST',
						data: data,
						success: function (response) {
							IWD.Order.unblock();
							IWD.Order.parseResponse( response );
						},
						error: function () {
							alert( 'Requested page not found. [404]' );
						}
					}
				);
			},
			capture: function (e) {
				e.preventDefault();
				IWD.Order.block();

				let result = confirm( IWD.Order.params.capture.message );
				if (result === false) {
					IWD.Order.unblock();
					return;
				}
				let data = { order_id: IWD.Order.params.order_id };
				$.ajax(
					{
						url:  IWD.Order.params.capture.url,
						dataType: 'json',
						method: 'POST',
						data: data,
						success: function (response) {
							IWD.Order.parseResponse( response );
						},
						error: function () {
							alert( 'Requested page not found. [404]' );
						}
					}
				);
			},
			ship: function (e) {
				e.preventDefault();

				$('select#order_status').val('wc-shipped');
				$('form#post').trigger('submit');
			},
			parseResponse: function (response) {
				if (response.code === 'error' || response.code === "invalid_data") {
					try {
						let error = $.parseJSON( response.message );
						IWD.Order.unblock();
						alert( error.details[0].description );
					} catch (error) {
						IWD.Order.unblock();
						alert( response.message );
					}
				} else {
					location.reload();
				}

			},
			block: function() {
				$( '.wrap' ).block(
					{
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					}
				);
			},
			unblock: function() {
				$( '.wrap' ).unblock();
			}

		};
		IWD.Order.init();
	}
);
