<?php 

return '        
    .woomotiv-popup{
        background-color: '. woomotiv()->config->woomotiv_bg .';
    }

    .woomotiv-popup .woomotiv-content p{
        color: '. woomotiv()->config->woomotiv_style_text_color .';
    }

    .woomotiv-popup .woomotiv-content p strong {
        color: '. woomotiv()->config->woomotiv_style_strong_color .';
    }

    .woomotiv-close{
        fill:'. woomotiv()->config->woomotiv_style_close_color .';
    }

    .wmt-stars:before{
        color: '. woomotiv()->config->woomotiv_style_stars_color .';
    }

    .wmt-stars span:before{
        color: '. woomotiv()->config->woomotiv_style_stars_rated_color .';
    }

';
