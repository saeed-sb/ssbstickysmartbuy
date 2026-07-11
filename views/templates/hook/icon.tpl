<span class="ssb-btn-icon ssb-icon-{$icon|escape:'html':'UTF-8'}" aria-hidden="true">
    {if $icon == 'cart'}
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
            <path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L20 7H6"/>
            <circle cx="10" cy="20" r="1.25"/>
            <circle cx="18" cy="20" r="1.25"/>
        </svg>
    {elseif $icon == 'bag'}
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
            <path d="M5 8h14l-1 12H6L5 8Z"/>
            <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
        </svg>
    {elseif $icon == 'plus'}
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
            <path d="M12 5v14M5 12h14"/>
        </svg>
    {elseif $icon == 'arrow'}
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
            <path d="M5 12h14M14 7l5 5-5 5"/>
        </svg>
    {/if}
</span>
