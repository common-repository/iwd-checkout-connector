<?php if (!empty($label)) ?>
    <tr>
        <th class="woocommerce-table__product-name product-name">
            <?= $field_label ?>
        </th>
        <th class="woocommerce-table__product-table product-total">
            <?= is_array($field_value) ? '' : $field_value ?>
        </th>
    </tr>

<?php
if (is_array($field_value))
    foreach ($field_value as $option_label => $option_val) { ?>
        <tr>
            <th class="woocommerce-table__product-name product-name">
                <?= $option_label ?>
            </th>
            <th class="woocommerce-table__product-table product-total">
                <?= parseOption($option_val) ?>
            </th>
        </tr>
    <?php } ?>