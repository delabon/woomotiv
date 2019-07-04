<?php

use  WooMotiv\Framework\HTML ;
use function  WooMotiv\convert_timezone ;
use function  WooMotiv\upgrade_notice ;
/** Only Premium */
if ( wmv_fs()->is_free_plan() ) {
    return HTML::div( upgrade_notice() );
}