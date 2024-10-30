<?php

/**
 * Failed order template
 */

defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);

$cart_totals = unserialize($session_data['cart_totals']);
$customer_data = unserialize($session_data['customer']);
$currency_symbol = IWD_CONNECTOR()->cart->getCart($updated_session)['currency_symbol'];
$grand_total = $cart_totals['total'];
$subtotal = $cart_totals['subtotal'];
?>
    <p><?php printf('We have received your payment, but there was a problem creating your order. Please contact our support service'); ?></p>

    <table cellspacing="0" cellpadding="6"
           style="width: 100%; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif"
           border="1">
        <thead>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Customer’s Payment/Transaction Creation Datetime:</th>
            <td style="text-align: left"><?php echo current_datetime()->format('Y-m-d H:i:s');; ?></td>
        </tr>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Order Creation Failed Date:</th>
            <td style="text-align: left"><?php echo current_datetime()->format('Y-m-d H:i:s');; ?></td>
        </tr>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Customer’s email:</th>
            <td style="text-align: left"><?php echo $customer_data['email']; ?></td>
        </tr>
        <tr>
            <th scope="col" style="text-align: left">Product</th>
            <th scope="col" style="text-align: left">Quantity</th>
            <th scope="col" style="text-align: left">Price</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach (IWD_CONNECTOR()->cart->getCartItems($session_data) as $cart) { ?>
            <tr>
                <td style="text-align: left; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word">
                    <?php echo $cart['name']; ?> (<?php echo $cart['sku']; ?>)
                    <?php if ($cart['options']) { ?>
                        <ul class="xfmc1">
                            <?php foreach ($cart['options'] as $option) { ?>
                                <li><strong class="xfmc7"
                                            style="float:left;margin-right:.25em;clear:both;"><?php echo $option['label'] ?>
                                        :</strong>
                                    <p><?php echo $option['value'] ?></p></li>

                            <?php } ?>
                        </ul>
                    <?php } ?>
                </td>
                <td style="text-align: left; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif">
                    <?php echo $cart['qty']; ?>
                </td>
                <td style="text-align: left; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif">
                    <span><span><?= $currency_symbol ?></span><?php echo number_format($cart['price'], 2, '.', ''); ?></span></td>

            </tr>
        <?php } ?>

        </tbody>
        <tfoot>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Shipping:</th>
            <td class="xfmc2" style="text-align:left;"><span class="xfmc7 xfmc8"><span
                            class="xfmc9"><?= $currency_symbol ?></span><?php echo IWD_CONNECTOR()->shipping->getSelectedShipping($session_data)['amount']; ?></span>&nbsp;<small
                        class="xfmc10"> <?php echo IWD_CONNECTOR()->shipping->getSelectedShipping($session_data)['carrier_title']; ?></small>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Payment method:</th>
            <td style="text-align: left"><?php echo $apiData->payment_method_title; ?></td>
        </tr>
        <?php if (isset($apiData->transactions)) { ?>
            <tr>
                <th scope="row" colspan="2" style="text-align: left">Transaction:</th>
                <th style="text-align: left"><?= $apiData->transactions->capture ? $apiData->transactions->capture->id : $apiData->transactions->authorization->id; ?></th>
            </tr>
        <?php } ?>
        <tr>
            <th scope="row" colspan="2" style="text-align: left">Total:</th>
            <td style="text-align: left"><span><span><?= $currency_symbol ?></span><?= $grand_total ?></span></td>
        </tr>
        </tfoot>
    </table>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ($additional_content) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
    echo "\n\n----------------------------------------\n\n";
}


/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
