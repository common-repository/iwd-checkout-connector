<?php
foreach ($fields as $label => $value) {
    if (empty($value)) continue;

    $dataValue = is_array($value) ? '' : $value;
    if (!empty($label))
        echo '<p><strong>' . __($label) . ':</strong> ' . $dataValue . '</p>';

    if (is_array($value)) {
        foreach ($value as $la => $val) {
            echo '<p><strong>' . __($la) . ':</strong> ' . parseOption($val) . '</p>';
        }
    }
}