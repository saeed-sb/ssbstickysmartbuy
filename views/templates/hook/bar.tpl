{* SSB Sticky Smart Buy Bar *}
<div class="ssb-bar{if $ssb_shadow} ssb-has-shadow{/if}"
     data-ssb-bar
     aria-hidden="true"
     style="--ssb-bar-bg: {$ssb_bar_bg|escape:'html':'UTF-8'};
            --ssb-button-bg: {$ssb_button_bg|escape:'html':'UTF-8'};
            --ssb-button-color: {$ssb_button_color|escape:'html':'UTF-8'};
            --ssb-button-icon-color: {$ssb_button_icon_color|escape:'html':'UTF-8'};
            --ssb-title-color: {$ssb_title_color|escape:'html':'UTF-8'};
            --ssb-current-price-color: {$ssb_current_price_color|escape:'html':'UTF-8'};
            --ssb-regular-price-color: {$ssb_regular_price_color|escape:'html':'UTF-8'};
            --ssb-discount-badge-bg: {$ssb_discount_badge_bg|escape:'html':'UTF-8'};
            --ssb-discount-badge-color: {$ssb_discount_badge_color|escape:'html':'UTF-8'};
            --ssb-button-radius: {$ssb_button_radius|intval}px;
            --ssb-bottom-spacing: {$ssb_bottom_spacing|intval}px;">
    <div class="ssb-info">
        {if $ssb_show_name}
            <div class="ssb-name">{$ssb_product_name|escape:'html':'UTF-8'}</div>
        {/if}

        {if $ssb_show_price}
            <div class="ssb-price" aria-live="polite">
                {if $ssb_show_discount_prices}
                    <span class="ssb-regular-price{if !$ssb_product_has_discount || !$ssb_product_regular_price} ssb-is-hidden{/if}"
                          data-ssb-regular-price>{$ssb_product_regular_price|escape:'html':'UTF-8'}</span>
                {/if}

                <span class="ssb-current-price" data-ssb-current-price>{$ssb_product_price|escape:'html':'UTF-8'}</span>

                {if $ssb_show_discount_percentage}
                    <span class="ssb-discount-badge{if !$ssb_product_has_discount || !$ssb_product_discount_label} ssb-is-hidden{/if}"
                          data-ssb-discount-badge>{$ssb_product_discount_label|escape:'html':'UTF-8'}</span>
                {/if}
            </div>
        {/if}
    </div>

    <button type="button" class="ssb-btn" data-ssb-add-to-cart aria-disabled="false">
        {if $ssb_button_icon_enabled && $ssb_button_icon_position == 'before'}
            {include file="module:ssbstickysmartbuy/views/templates/hook/icon.tpl" icon=$ssb_button_icon}
        {/if}

        <span class="ssb-btn-text">{$ssb_button_text|escape:'html':'UTF-8'}</span>

        {if $ssb_button_icon_enabled && $ssb_button_icon_position == 'after'}
            {include file="module:ssbstickysmartbuy/views/templates/hook/icon.tpl" icon=$ssb_button_icon}
        {/if}
    </button>
</div>
