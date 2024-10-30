<?php
foreach ($fields as $label => $value) {
    if (empty($value)) continue;

    $dataValue = is_array($value) ? '' : $value;
    if (!empty($label))
        echo '<tr class="shipping " data-order_item_id="">
                            <td class=""></td>
                            <td class="name">' . $label . '</td>
                            <td class="line_cost" width="1%" colspan="3">' . $dataValue . '</td>
                            <td class="wc-order-edit-line-item">
                            </td>
                        </tr>';

    if (is_array($value)) {
        foreach ($value as $la => $val) {
            echo '<tr class="shipping " data-order_item_id="">
                            <td class=""></td>
                            <td class="name">' . $la . '</td>
                            <td class="line_cost" width="1%" colspan="3">' . parseOption($val) . '</td>
                            <td class="wc-order-edit-line-item">
                            </td>
                        </tr>';
        }
    }
}