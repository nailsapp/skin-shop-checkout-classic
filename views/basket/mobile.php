<div class="row">
    <div class="col-xs-12 visible-xs visible-sm">
        <?php

        echo empty($readonly) ? '<h1>Your Basket</h1>' : '<hr /><p><strong>Your Order</strong></p>';

        $bPriceExcludeTax       = appSetting('price_exclude_tax', 'nailsapp/module-shop');
        $bOmitVariantTaxPricing = shopSkinSetting('omit_variant_tax_pricing', 'checkout');

        foreach ($items as $item) {

            ?>
            <div class="row bordered-row">
                <?php

                if (!empty($item->variant->featured_img)) {

                    $featuredImg = $item->variant->featured_img;

                } elseif (!empty($item->product->featured_img)) {

                    $featuredImg = $item->product->featured_img;

                } else {

                    $featuredImg = false;
                }

                if ($featuredImg) {

                    echo '<div class="col-xs-3">';

                        $url = cdnCrop($featuredImg, 175, 175);
                        echo img(array('src' => $url, 'class' => 'img-thumbnail'));

                    echo '</div>';
                    $mainColWidth = 7;

                } else {

                    $mainColWidth = 10;
                }

                ?>
                <div class="col-xs-<?=$mainColWidth?>">
                    <?php

                    // --------------------------------------------------------------------------

                    //  Label
                    echo anchor($item->product->url, '<strong>' . $item->product->label . '</strong>');

                    if ($item->variant->label !== $item->product->label) {

                        echo '<br />';
                        echo '<em>' . $item->variant->label . '</em>';
                    }

                    // --------------------------------------------------------------------------

                    //  To order?
                    if ($item->variant->stock_status == 'TO_ORDER') {

                        echo '<p class="text-muted">';
                            echo '<small>';
                                echo '<em>Lead time: ' . $item->variant->lead_time . '</em>';
                            echo '</small>';
                        echo '</p>';
                    }

                    // --------------------------------------------------------------------------

                    //  Collection Only
                    if ($item->variant->shipping->collection_only) {

                        echo '<div class="alert alert-warning alert-mini">';
                            echo '<strong>Note:</strong> Collection only.';
                        echo '</div>';
                    }

                    ?>
                    <div>
                    <?php

                    if ($bPriceExcludeTax) {

                        echo '<strong class="variant-unit-price-ex-tax-' . $item->variant->id . '">';
                            echo $item->price->user_formatted->value_ex_tax;
                        echo '</strong>';

                        if (!$bOmitVariantTaxPricing && $item->price->user->value_tax > 0) {

                            echo '<br />';
                            echo '<small class="text-muted">';
                                echo '<span class="variant-unit-price-inc-tax-' . $item->variant->id . '">';
                                    echo $item->price->user_formatted->value_inc_tax;
                                echo '</span>';
                                echo ' inc. tax';
                            echo '</small>';
                        }

                    } else {

                        echo '<span class="variant-unit-price-inc-tax-' . $item->variant->id . '">';
                            echo $item->price->user_formatted->value_inc_tax;
                        echo '</span>';

                        if (!$bOmitVariantTaxPricing && $item->price->user->value_tax > 0) {

                            echo '<br />';
                            echo '<small class="text-muted">';
                                echo '<span class="variant-unit-price-ex-tax-' . $item->variant->id . '">';
                                    echo $item->price->user_formatted->value_ex_tax;
                                echo '</span>';
                                echo ' ex. tax';
                            echo '</small>';
                        }
                    }

                    ?>
                    </div>
                </div>
                <div class="col-xs-2 text-center">
                <?php

                if (empty($readonly)) {

                    /**
                     * Determine whether the user can increment the product. In order to be
                     * incrementable there must:
                     * - Be sufficient stock (or unlimited)
                     * - not exceed any limit imposed by the product type
                     */

                    if (is_null($item->variant->quantity_available)) {

                        //  Unlimited quantity
                        $sufficient = true;

                    } elseif ($item->quantity < $item->variant->quantity_available) {

                        //  Fewer than the quantity available, user can increment
                        $sufficient = true;

                    } else {

                        $sufficient = false;
                    }

                    if (empty($item->product->type->max_per_order)) {

                        //  Unlimited additions allowed
                        $notExceed = true;

                    } elseif ($item->quantity < $item->product->type->max_per_order) {

                        //  Not exceeded the maximum per order, user can increment
                        $notExceed = true;

                    } else {

                        $notExceed = false;
                    }

                    if ($sufficient && $notExceed) {

                        echo anchor(
                            $shop_url . 'basket/increment?variant_id=' . $item->variant->id,
                            '<div class="basket-incrementer">
                                <span class="glyphicon glyphicon-plus-sign text-muted"></span>
                            </div>'
                        );
                    }
                }

                echo '<span class="variant-quantity-' . $item->variant->id . '">';
                    echo number_format($item->quantity);
                echo '</span>';


                if (empty($readonly)) {

                    echo anchor(
                        $shop_url . 'basket/decrement?variant_id=' . $item->variant->id,
                        '<div class="basket-incrementer">
                            <span class="glyphicon glyphicon-minus-sign text-muted"></span>
                        </div>'
                    );
                }

                ?>
                </div>
            </div>
        <?php

        }

        ?>
        <div class="bordered-row">
            <div class="row padded-row">
                <div class="col-xs-12">
                    <div class="pull-left">Sub Total</div>
                    <div class="pull-right">
                        <b><?=$totals->user_formatted->item?></b>
                    </div>
                </div>
            </div>
            <div class="row padded-row">
                <div class="col-xs-12">
                    <div class="pull-left">
                        Shipping
                    </div>
                    <div class="pull-right">
                        <?php

                        if (empty($readonly)) {

                            echo form_open('shop/basket/set_shipping');

                            ?>
                            <select name="shipping_option">
                            <?php

                            foreach ($shippingOptions as $oOption) {

                                /**
                                 * If a shipping option is defined, use that one, if not fall back to the default.
                                 */
                                if (!empty($basket->shipping->option)) {
                                    $sSelected = $oOption->slug == $basket->shipping->option ? 'selected' : '';
                                } else {
                                    $sSelected = $oOption->default ? 'selected' : '';
                                }

                                ?>
                                <option value="<?=$oOption->slug?>" <?=$sSelected?>>
                                    <?=$oOption->cost_formatted?> - <?=$oOption->label?>
                                </option>
                                <?php
                            }

                            ?>
                            </select>
                            <?php

                            echo form_close();

                        } else {

                            echo $totals->user_formatted->shipping;
                        }

                        ?>
                    </div>
                </div>
            </div>
            <?php

            if (!empty($basket) && $basket->shipping->option === 'COLLECTION') {

                $aAddress   = array();
                $aAddress[] = appSetting('warehouse_addr_addressee', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_line1', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_line2', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_town', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_postcode', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_state', 'nailsapp/module-shop');
                $aAddress[] = appSetting('warehouse_addr_country', 'nailsapp/module-shop');
                $aAddress   = array_filter($aAddress);

                if (!empty($aAddress)) {

                    $sMapUrl = 'https://www.google.com/maps/?q=' . urlencode(implode(', ', $aAddress));

                    ?>
                    <div class="row padded-row">
                        <div class="col-xs-12">
                            <p class="small alert alert-info">
                                Collect from:
                                <br /><?=anchor($sMapUrl, implode(', ', $aAddress), 'target="_blank"')?>
                            </p>
                        </div>
                    </div>
                    <?php
                }
            }

            ?>
            <div class="row padded-row">
                <div class="col-xs-12">
                    <div class="pull-left">
                        <?php

                        if ($bPriceExcludeTax) {

                            echo 'Tax';

                        } else {

                            echo 'Tax <small class="text-muted">(Included)</small>';
                        }

                        ?>
                    </div>
                    <div class="pull-right">
                        <b><?=$totals->user_formatted->tax_combined?></b>
                    </div>
                </div>
            </div>
            <?php

            if (!empty($totals->base->grand_discount)) {

                ?>
                <div class="row padded-row success-row">
                    <div class="col-xs-12">
                        <div class="pull-left">
                            Discount
                        </div>
                        <div class="pull-right">
                            <b>-<?=$totals->user_formatted->grand_discount?></b>
                        </div>
                    </div>
                </div>
                <?php

            }

            ?>
            <div class="row padded-row shaded-row">
                <div class="col-xs-12">
                    <div class="pull-left">
                        Total
                    </div>
                    <div class="pull-right">
                        <b><?=$totals->user_formatted->grand?></b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
