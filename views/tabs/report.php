<?php

use  WooMotiv\Framework\HTML ;
use function  WooMotiv\days_in_month ;
use function  WooMotiv\get_statistics ;
use function  WooMotiv\upgrade_notice ;
/** Only Premium */
if ( wmv_fs()->is_free_plan() ) {
    return HTML::div( upgrade_notice() );
}