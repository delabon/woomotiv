<?php 

return '        
    .woomotiv-popup{
        background-color: '. woomotiv()->config->woomotiv_bg .';
    }

    .woomotiv-popup > p{
        color: '. woomotiv()->config->woomotiv_style_text_color .';
    }

    .woomotiv-popup > p strong {
        color: '. woomotiv()->config->woomotiv_style_strong_color .';
    }

    .woomotiv-close:focus,
    .woomotiv-close:hover,
    .woomotiv-close{
        color:'. woomotiv()->config->woomotiv_style_close_color .';
        background-color:'. woomotiv()->config->woomotiv_style_close_bg_color .';
    }

    .wmt-stars:before{
        color: '. woomotiv()->config->woomotiv_style_stars_color .';
    }

    .wmt-stars span:before{
        color: '. woomotiv()->config->woomotiv_style_stars_rated_color .';
    }

';
