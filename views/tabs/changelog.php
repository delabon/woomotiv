<?php 
use DLBV2\Helper;


$filepath = __DIR__ . '/../../readme.txt';

if( ! file_exists( $filepath ) ){
    return '';
}

$logcontent = nl2br( file_get_contents( $filepath ) );
return preg_replace('/[\w\W]*== Changelog ==/', '', $logcontent);
