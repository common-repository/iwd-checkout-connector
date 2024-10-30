<form class="woocommerce-checkout" style="display: none">
    <input type="email" name="billing_email" id="billing_email" style="display: none">
</form>

<iframe id="iwdCheckoutFrame"
        src="<?php echo esc_url(IWD_CONNECTOR()->iframe->getFrameUrl()); ?>"
        frameborder="0"
        scrolling="no"
        style="width: 100%; height: 837px"></iframe>

<script>
	iFrameResize({ log: false }, '#iwdCheckoutFrame');
	jQuery(document).ready(function () {
		var iframe =  document.getElementById('iwdCheckoutFrame');

		// Remove paypal_order_id param from url
		history.replaceState && history.replaceState(
			null, '', location.pathname
                + location.search
                    .replace(/[\?&]paypal_order_id=[^&]+/, '')
                    .replace(/[\?&]paypal_funding_source=[^&]+/, '')
                    .replace(/^&/, '?')
		);

		let changeUrlAction = function(event) {
			if (event.data.changeUrlAction === 'edit_cart') {
				window.location.href = '<?php echo esc_url(IWD_CONNECTOR()->helper->getEditCartUrl()); ?>';
			}
			else if (event.data.changeUrlAction === 'authenticate') {
				var data = { username: event.data.login, password: event.data.password };
				jQuery.ajax({
					dataType : "json",
					method: "POST",
					url: '<?php echo esc_url(IWD_CONNECTOR()->helper->getLoginUrl()); ?>',
					data: JSON.stringify(data)
				}).done(function (response) {
					if (response.errors) {
						sendMessage(response.message);
					} else {
						location.reload();
					}
				}).fail(function () {
					var msg =  '<?php __( 'Could not authenticate. Please try again later' ); ?>';
					sendMessage(msg);
				});
			}
			else if(event.data.changeUrlAction === 'reset_pass'){
				window.location.href = '<?php echo esc_url(IWD_CONNECTOR()->helper->getResetPasswordUrl()); ?>';
			}
		};

		var sendMessage = function(msg) {
			iframe.contentWindow.postMessage({
				'action': 'sendMassage',
				'message': msg
			}, '*');
		};

		let actionSuccess = function(event) {
			if (event.data.successPage === undefined && event.data.actionSuccess) {
                jQuery.ajax({
                    dataType : "json",
                    method: "POST",
                    url: '<?php echo esc_url(IWD_CONNECTOR()->helper->getSuccessUrl()); ?>',
                    data: JSON.stringify(event.data.actionSuccess)
                }).done(function (response) {
                    window.location.href = response;
                })
			} else if(event.data.successPage) {
                window.location.href = event.data.successPage;
            }
		};

        let updateEmail = function (event) {
            if (event.data.updateEmail) {
                jQuery('#billing_email').val(event.data.updateEmail);
                jQuery('#billing_email').trigger('change').trigger('blur');
            }
        };

		if (window.addEventListener) {
			window.addEventListener("message", changeUrlAction, false);
			window.addEventListener("message", actionSuccess, false);
            window.addEventListener("message", updateEmail, false);
		} else if (window.attachEvent) {
			window.attachEvent("onmessage", changeUrlAction);
			window.attachEvent("onmessage", actionSuccess);
		}

        /**
         * Init GA and Send Data to Iframe
         */
        function  initGaScript () {
            let self = this,
                gaClientId = 0,
                frameWindow = iframe;

            if(window.ga && ga.loaded) {
                ga(function (tracker) {
                    gaClientId = tracker.get('clientId');
                })
            }

            if(frameWindow.dataset.loaded === 'true') {
                sendGaClientId(gaClientId);
            }
            else {
                frameWindow.onload = function () {
                    sendGaClientId(gaClientId);
                };
            }
        }

        function  sendGaClientId (gaClientId) {
            iframe.contentWindow.postMessage({
                'gaClientId': gaClientId
            }, '*');
        }

        initGaScript();
	});


</script>
