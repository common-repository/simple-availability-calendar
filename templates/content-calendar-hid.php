<?php $isAppAvail = SA_Calendar_Settings::instance()->sa_app_avail; ?>
<div id="sa-time-avail"<?php echo $isAppAvail ? ' class="sa-appavail"' : ''; ?> style="display: none;"><h2></h2><div class="sa-choose-time"></div></div>
<?php if ($isAppAvail): ?>
<?php $captcha = new ReallySimpleCaptcha19(); ?>
<?php $add_class = esc_attr(SA_Calendar_Settings::instance()->sa_style_modal); ?>
<?php $header = esc_html(SA_Calendar_Settings::instance()->sa_text_appheader); ?>
<?php $submit_text = esc_html(SA_Calendar_Settings::instance()->sa_text_appsubmit); ?>
<?php $fields = apply_filters( 'sa_appointment_fields', SA_Calendar_Utilities::get_fields() ); ?>
<div id="sa-modal" class="sa-modal sa-fade<?php if (!empty($add_class)) echo ' ' . $add_class; ?>" role="dialog">
	<div class="sa-modal-dialog">
		<div class="sa-modal-content">
			<div class="sa-modal-header">
				<h4 class="sa-modal-title"><?php echo $header; ?></h4>
			</div>
			<div class="sa-modal-body">
				<div>
					<label><?php echo __( 'Date & Time', 'sa-calendar' ); ?></label><br/>
					<label id="sa-modal-date"></label>
				</div>
				<?php echo $fields; ?>
				<div>
					<label for="sa-captcha"><?php echo __( 'Input text on the image:', 'sa-calendar' ); ?></label>
					<img src="" width="<?php echo $captcha->img_size[0]; ?>" height="<?php echo $captcha->img_size[1]; ?>" alt="CAPTCHA"><br/>
					<input id="sa-captcha" type="text" required="required">
					<i><?php echo __( 'Please, fill this field with correct value', 'sa-calendar' ); ?></i>
				</div>
			</div>
			<div class="sa-modal-footer">
				<button type="button" class="btn btn-default" name="sa-modal-book"><?php echo $submit_text; ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __( 'Close', 'sa-calendar' ); ?></button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>