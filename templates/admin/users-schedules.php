<?php
	$current_user = wp_get_current_user();
	$user_name = $current_user->display_name;
	$user_id = $current_user->ID;
	if (is_super_admin()) {
		$user_id = isset($_GET['user_id']) && ctype_digit($_GET['user_id']) ? intval($_GET['user_id']) : 0;
		$hidGET = '';
		foreach($_GET as $key => $val) {
			if ($key != 'user_id')
				$hidGET .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
		}
		$users = get_users( array( 'orderby' => 'display_name', 'fields' => array( 'ID', 'display_name', 'user_login' ) ) );
		$opts = '';
		foreach($users as $user) {
			$sel = '';
			if ($user->ID == $user_id) {
				$sel = ' selected="selected"';
				$user_name = $user->display_name;
			}
			$opts .= sprintf('<option value="%d"%s>%s (%s)</option>', $user->ID, $sel, $user->display_name, $user->user_login);
		}
		if ($user_id == 0 && count($users) > 0) {
			$user_id = $users[0]->ID;
			$user_name = $users[0]->display_name;
		}
	}
?>
<div class="wrap">
<?php if (is_super_admin()): ?>
<h1><?php echo __('Users schedules', 'sa-calendar'); ?></h1>
<form method="get" action="<?php menu_page_url('sa_calendar_schedule') ?>">
	<?php echo $hidGET; ?>
	<label for="ddlUsers"><?php echo __('User', 'sa-calendar'); ?>:&nbsp</label><select id="ddlUsers" name="user_id" style="min-width: 300px" onchange="this.form.submit()"><?php echo $opts; ?></select>
</form>
<?php endif; ?>
<?php if ($user_id > 0): ?>
		<h2 style="margin-top: 50px"><?php echo sprintf(__('Schedule of %s', 'sa-calendar'), $user_name); ?></h2>
		<div style="max-width: 600px">
		<?php echo do_shortcode('[sa_calendar_schedule user_id="' . $user_id . '"]'); ?>
		</div>
<?php endif; ?>
</div>