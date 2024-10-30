<?php
foreach ($fields as $label => $value) {
    if (empty($value)) continue;

    $dataValue = is_array($value) ? '' : $value;
    if (!empty($label))
        echo '<tr>
				<td class="label"><b>' . __($label) . ':</b></td>
				<td width="1%"></td>
				<td class="total">' . $dataValue . '</td>
			</tr>';

    if (is_array($value)) {
        foreach ($value as $la => $val) {
            echo '<tr>
				<td class="label"><b>' . __($la) . ':</b></td>
				<td width="1%"></td>
				<td class="total">' . parseOption($val) . '</td>
			</tr>';
        }
    }
}