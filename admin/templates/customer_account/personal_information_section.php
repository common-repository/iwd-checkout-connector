<?php
foreach ($fields as $label => $value) {
    if (empty($value)) continue;

    $dataValue = is_array($value) ? '' : $value;
    if (!empty($label))
        echo '<address>
                <p class="woocommerce-customer-details--email ddd"><span>' . $label . ': </span>' . $dataValue . '</p>
            </address>';

    if (is_array($value)) {
        foreach ($value as $la => $val) {
            echo '<address>
                <p class="woocommerce-customer-details--email ddd"><span>' . __($la) . ': </span> ' . parseOption($val) . '</p>
            </address>';
        }
    }
}