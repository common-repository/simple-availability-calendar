<?php
	$action = SA_Calendar_Utilities::get_current_page_url();
	$timezones = SA_Calendar_Utilities::timezone_list();
	$add_class = esc_attr(SA_Calendar_Settings::instance()->sa_style_schedule);
	$timeFormat = SA_Calendar_Settings::instance()->sa_time_format;
?>
<form method="post" action="<?php echo $action ?>"<?php if (!empty($add_class)) echo ' class="' . $add_class . '"'; ?>>
	<?php wp_nonce_field( SA_Calendar_Logic::nonceScheduleName, 'sa_sec' ); ?>
<?php if (is_admin() && is_super_admin()) : ?>
	<input type="hidden" name="sas[user_id]" value="<?php echo $user_id; ?>" />
<?php endif; ?>
<div class="sa-avail">
	<input type="hidden" name="sas[f]" value="1" />
	<input type="checkbox" id="sa-avail-schedule" name="sas[avail]" value="1"<?php echo $schedule->avail ? ' checked="checked"' : ''; ?>><label for="sa-avail-schedule"><?php echo __('Yes, I am available', 'sa-calendar'); ?></label><br/>
</div>

<?php if (SA_Calendar_Settings::instance()->sa_timezone_avail) : ?>
<div class="sa-tz">
	<label for="sa-my-timezone"><?php echo __('My time zone:', 'sa-calendar'); ?></label>&nbsp;
	<select id="sa-my-timezone" name="sas[timezone]">
	<?php foreach($timezones as $key=>$val): ?>
		<option value="<?php echo $key; ?>"<?php if ($key == $user_timezone) echo ' selected="selected"'; ?>><?php echo $val; ?></option>
	<?php endforeach; ?>
	</select>
</div>
<?php endif; ?>
<div class="sa-header">
	<h2><?php echo __( 'Available Times', 'sa-calendar' ); ?></h2>
</div>
<div class="sa-tbl" id="sa-week-schedule">
	<?php for($wd = 0; $wd < 7; $wd++): ?>
	<div class="sa-tbl-row">
		<div class="sa-tbl-th">
			<?php echo SA_Calendar_Utilities::get_week_day_name($wd); ?>
		</div>
		<div class="sa-tbl-td">
<?php SA_Calendar_Utilities::print_select( $schedule, $wd, $timeFormat); ?>
		</div>
	</div>
	<?php endfor; ?>
</div>
<div class="sa-submit-cont"><button class="btn btn-default"><?php echo __( 'Save schedule', 'sa-calendar' ); ?></button></div>
</form>
