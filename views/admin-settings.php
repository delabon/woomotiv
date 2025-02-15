<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	
	<h1>
		Woomotiv 
		<span class="page-title-action">Version <?php echo esc_html(woomotiv()->version) ?></span>
		<a target="_blank" href="https://delabon.com/documentation/24/index.html" class="page-title-action"><?php esc_html_e('Documentation', 'woomotiv') ?></a>
	</h1>

	<br><br>
	
	<div class="dlb_panel" data-tab="<?php echo esc_attr($currentTab['slug']) ?>" >

        <?= $navMarkup; // phpcs:ignore ?>
		
		<div class="dlb_panel_content">
			<form class="dlb_form" method="post" enctype="multipart/form-data">

				<?= $currentTabContent; // phpcs:ignore ?>
				
				<p class="submit">					
					<button name="save" class="button-primary" type="submit">
						<?php esc_html_e( 'Save changes', 'woomotiv' ); ?>
					</button>
		
				</p>

				<input type="hidden" name="woomotiv_nonce" value="<?php echo esc_attr(wp_create_nonce('woomotiv')) ?>">

			</form>
		</div>

	</div>
</div>
