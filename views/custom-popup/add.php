<div class="dlb_modal woomotiv-custom-popup-modal">

    <header>

        <h3><?php esc_html_e('Custom Popup', 'woomotiv') ?></h3>

        <div class="_buttons">

            <button class="dlb_button _blue woomotiv_modal_save"><?php esc_html_e('Save', 'woomotiv') ?></button>
            <button class="dlb_button woomotiv_modal_close"><?php esc_html_e('Close', 'woomotiv') ?></button>

        </div>
        
    </header>

    <div class="_content">

        <form>
                
            <div class="dlb_input_wrapper dlb_image_upload_container">

                <img src="<?php echo esc_url(woomotiv()->url) ?>/img/150.png">

                <button class="dlb_button _blue woomotiv_upload_image">Upload Image</button>
                
                <input type="hidden" class="dlb_input" name="image_id">

            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php esc_html_e('Content', 'woomotiv') ?></h3>

                <textarea placeholder="Content.." class="dlb_input" name="content"></textarea>

                <p>
                    <?php esc_html_e('Use {} to make a specific word or sentence font bold.', 'woomotiv') ?>
                    <br>
                    <?php esc_html_e('Ex: Use this coupon code {CP20OFF} to get 20% off.', 'woomotiv') ?>
                </p>
            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php esc_html_e('Url', 'woomotiv') ?></h3>

                <input type="text" placeholder="https://delabon.com" class="dlb_input" name="link">

            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php esc_html_e('Expiry Date', 'woomotiv') ?></h3>

                <input type="text" placeholder="03/03/2021" class="dlb_input dlb_datepicker" name="expiry_date">

            </div>

        </form>

    </div>

</div>