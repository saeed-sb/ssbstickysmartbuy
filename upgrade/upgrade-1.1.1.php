<?php
if (!defined('_PS_VERSION_')) { exit; }

/** Code-only release; preserve existing configuration, data and pending operations. */
function upgrade_module_1_1_1($module)
{
    return (string) ($module->name ?? '') === 'ssbstickysmartbuy';
}
