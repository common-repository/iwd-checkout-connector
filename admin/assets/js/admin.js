jQuery(
	function($) {
		let IWD;
		IWD       = {};
		IWD.Admin = {
			connection_test_url:'/wp-json/iwd-checkout/check-connection',
			integration_key: '#iwd_connector_integration_key',
			secret_key: '#iwd_connector_secret_key',
			button: '#iwd-connector-connection-test',
			help_text: '.error_massage',
			iwd_site_url: iwd_site_url,

			init: function() {
				$( IWD.Admin.button ).on( 'click', this.connectionTest.bind( this ) );
			},
			connectionTest: function(e) {
				e.preventDefault();
				this.block();
				let data = { api_key: $( IWD.Admin.integration_key ).val(), api_secret: $( IWD.Admin.secret_key ).val()};
				$.ajax(
					{
						url: IWD.Admin.iwd_site_url + IWD.Admin.connection_test_url,
						dataType: 'json',
						method: 'POST',
						data: JSON.stringify( data ),
						success: function (response) {
							IWD.Admin.unblock();
							IWD.Admin.parseResponse( response );
						},
						error: function (response) {
							alert( 'Requested page not found. [404]' );
						}
					}
				);
			},
			parseResponse: function (response) {
				$( IWD.Admin.button ).removeClass( 'error' );
				$( IWD.Admin.button ).removeClass( 'success' );
				if (response.error === true) {
					$( IWD.Admin.button ).addClass( 'error' );
					$( IWD.Admin.help_text ).html( response.help_text );
				}
				$( IWD.Admin.button ).addClass( 'success' );
				$( IWD.Admin.button ).text( response.massage );

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
		IWD.Admin.init();
	}
);
