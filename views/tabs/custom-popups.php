<?php 

use WooMotiv\Framework\HTML;
use function WooMotiv\convert_timezone;
use function WooMotiv\upgrade_notice;

global $wpdb;

$pagenum = empty( $_GET['pagenum'] ) ? 1 : (int)$_GET['pagenum'];
$pagenum = $pagenum === 0 ? 1 : $pagenum;
$per_page = 5;
$offset = ($pagenum-1) * $per_page; 
$total_pages = ceil( get_option( 'woomotiv_total_custom_popups', 0 ) / $per_page);

# pagination output
$pagination_url = admin_url( 'admin.php?page=woomotiv&tab=custom-popups&pagenum=' );
$pageprev = $pagenum - 1;
$pagenext = $pagenum + 1;

$pagination = "
    <ul class='dlb_pagination'>
";

if( $pagenum > 1 ){
    $pagination .= "
        <li><a href='{$pagination_url}1'>First</a></li>
        <li><a href='{$pagination_url}{$pageprev}'>Prev</a></li>
    ";
}

if( $pagenum < $total_pages ){
    $pagination .= "
        <li><a href='{$pagination_url}{$pagenext}'>Next</a></li>
        <li><a href='{$pagination_url}{$total_pages}'>Last</a></li>
    ";
}

$pagination .= "
    </ul>
";

# items output
$table = $wpdb->prefix.'woomotiv_custom_popups';
$results = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT $offset, $per_page", OBJECT );
$output = '';

foreach ( $results as $item ) {

    $image = '<img src="'.woomotiv()->url.'/img/150.png">';

    if( $item->image_id ){

        $src = wp_get_attachment_image_src( $item->image_id );

        $image = '<img src="'.$src[0].'">';
    }

    $expiry_date = convert_timezone( $item->date_ends );

    $output .= "
        <div class='woomotiv_custom_item' data-id='{$item->id}'>
        
            {$image}
            
            <div>
                <p>{$item->content}</p>
                <span class='dashicons dashicons-admin-links' style='font-size: 13px;'></span>
                {$item->link}
            </div>

            <div>
                ".__('Expiry Date', 'woomotiv').": <br>{$expiry_date->format('F d, Y')}<br>

                <button class='dlb_button _green woomotiv_custom_popup_edit'>".__('Edit', 'woomotiv')."</button>
                <br>
                <button class='dlb_button _red woomotiv_custom_popup_delete'>".__('Delete', 'woomotiv')."</button>
            </div>

        </div>
    ";

}

return

HTML::P(
    HTML::button( __('Add Custom Popup', 'woomotiv' ), array( 
        'class'         =>  'dlb_button woomotiv-add-custom-popups', 
        'value'         => woomotiv()->config->woomotiv_content_content,
        'data-nonce'    => wp_create_nonce('woomotiv'),
        'id'            => 'woomotiv-add-custom-popup'
    ))
)

.HTML::div( $output, array(
    'class' => 'woomotiv_custom_items'
))

.HTML::div( $pagination )

;

