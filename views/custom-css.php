<?php 

return '        
    .woomotiv-popup{
        background-color: '. woomotiv()->config->woomotiv_bg .';
    }

    .woomotiv-popup[data-size="small"]>p,
    .woomotiv-popup > p{
        color: '. woomotiv()->config->woomotiv_style_text_color .';
        font-size: '. woomotiv()->config->woomotiv_font_size .'px;
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

    @media screen and ( max-width: 576px ){

        .woomotiv-popup[data-size="small"]>p,
        .woomotiv-popup > p{
            font-size: '. woomotiv()->config->woomotiv_font_size_mobile .'px;
        }        
    }
';
