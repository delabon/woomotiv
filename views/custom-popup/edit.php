<div class="dlb_modal woomotiv-custom-popup-modal">

    <header>

        <h3><?php _e('Custom Popup', 'woomotiv') ?></h3>

        <div class="_buttons">

            <button class="dlb_button _blue woomotiv_modal_save"><?php _e('Save', 'woomotiv') ?></button>
            <button class="dlb_button woomotiv_modal_close"><?php _e('Close', 'woomotiv') ?></button>

        </div>
        
    </header>

    <div class="_content">

        <form>
                
            <input type="hidden" class="dlb_input" name="id" value="<?php echo $result->id ?>">

            <div class="dlb_input_wrapper dlb_image_upload_container">

                <img src="<?php echo $image_url ?>">

                <button class="dlb_button _blue woomotiv_upload_image"><?php _e('Upload Image','woomotiv')?></button>
                
                <input type="hidden" class="dlb_input" name="image_id" value="<?php echo $result->image_id ?>">

            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php _e('Content', 'woomotiv') ?></h3>

                <textarea placeholder="Content.." class="dlb_input" name="content"><?php echo $result->content ?></textarea>

                <p>
                    <?php _e('Use {} to make a specific word or sentence font bold.', 'woomotiv') ?>
                    <br>
                    <?php _e('Ex: Use this coupon code {CP20OFF} to get 20% off.', 'woomotiv') ?>
                </p>
            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php _e('Url', 'woomotiv') ?></h3>

                <input type="text" placeholder="https://delabon.com" class="dlb_input" name="link" value="<?php echo $result->link ?>">

            </div>

            <div class="dlb_input_wrapper">

                <h3 class="dlb_input_title"><?php _e('Expiry Date', 'woomotiv') ?></h3>

                <input type="text" placeholder="03/03/2021" class="dlb_input dlb_datepicker" name="expiry_date" value="<?php echo $expiry_date->format('F d, Y') ?>">

            </div>

        </form>

    </div>

</div>