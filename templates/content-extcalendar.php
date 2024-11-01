<?php
	$tmpDate = new DateTime('today', $utz);
	$times_type = array();
	for ($daynum = 0; $daynum < 7; $daynum++){
		$times_type[] = SA_Calendar_Schedule::get_times_type($user_id, $tmpDate->format('Y-m-d'), $tmpDate->format('N') - 1);
		$tmpDate->modify('+1 day');
	}

	$rows = array();
	$curFrame = 0;
	$timeFrame = SA_Calendar_Settings::instance()->sa_time_frame;
	$timeFormat = SA_Calendar_Settings::instance()->sa_time_format;
	while ($curFrame < 24 * 60) {
		$frameTxt = SA_Calendar_Utilities::minutes_2_time($curFrame, $timeFormat);
		$cells = array($frameTxt);
		for ($daynum = 0; $daynum < 7; $daynum++){
			$cellStyle = '';
			$time_type = SA_Calendar_Schedule::Type_Off;
			if (isset($times_type[$daynum][$curFrame]))
				$time_type = $times_type[$daynum][$curFrame];

			if ($time_type == SA_Calendar_Schedule::Type_App) {
				if ($for_current_user)
					$cellStyle = ' class="sa-exc-app"';
			}
			else if ($time_type == SA_Calendar_Schedule::Type_Free) {
				if ($for_current_user || $daynum > 0)
					$cellStyle = ' class="sa-exc-on"';
			}
			$cells[] = $cellStyle;
		}
		$rows[$curFrame] = $cells;
		$curFrame += $timeFrame;
	}

	if (!$for_current_user) {
		foreach(array_keys($rows) as $rowKey) {
			$hasVal = false;
			for($j = 1; $j < count($rows[$rowKey]); $j++)
				if ($rows[$rowKey][$j] != '') {
					$hasVal = true;
					break;
				}
			if ($hasVal)
				break;
			else
				unset($rows[$rowKey]);
		}
		foreach(array_reverse(array_keys($rows)) as $rowKey) {
			$hasVal = false;
			for($j = 1; $j < count($rows[$rowKey]); $j++)
				if ($rows[$rowKey][$j] != '') {
					$hasVal = true;
					break;
				}
			if ($hasVal)
				break;
			else
				unset($rows[$rowKey]);
		}
	}
	$date = new DateTime('today', $utz);
	$headerText = esc_html(SA_Calendar_Utilities::get_extcal_header($date));
	$isAppAvail = SA_Calendar_Settings::instance()->sa_app_avail && !$for_current_user;
	$add_class = esc_attr(SA_Calendar_Settings::instance()->sa_style_extcalendar);
?>
<div class="sa-div<?php if (!empty($add_class)) echo ' ' . $add_class; ?>" data-uid="<?php echo $user_id; ?>" data-date="<?php echo $date->format('Y-m-d'); ?>" data-ctype="ext">
<table class="sa-exc<?php echo $for_current_user ? '' : '-us'; echo $isAppAvail ? ' sa-appavail' : ''; ?>">
	<colgroup>
		<col width="16%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
		<col width="12%" />
	</colgroup>
	<thead>
	<tr>
		<th colspan="8">
			<div name="sa-cal-leftar" class="sa-leftar" style="display:none;"></div>
			<div name="sa-cal-rightar" class="sa-rightar"></div>
			<span><?php echo $headerText; ?></span><br>
		<?php if (SA_Calendar_Settings::instance()->sa_timezone_avail): ?>
			<?php if ($for_current_user): ?>
			<i><?php echo __('All times listed are in your timezone', 'sa-calendar'); ?><br><?php echo SA_Calendar_Utilities::format_timezone_name($utz->getName()); ?></i>
			<?php else: ?>
			<i><?php echo __('All times listed are in user`s timezone', 'sa-calendar'); ?><br><?php echo SA_Calendar_Utilities::format_timezone_name($utz->getName()); ?></i>
			<?php endif; ?>
		<?php endif; ?>
		</th>
	</tr>
	<tr>
		<th class="sa-exc-tlh"><?php if (SA_Calendar_Settings::instance()->sa_timezone_avail) echo 'GMT ' . $date->format('P'); ?></th>
	<?php for($i = 0; $i < 7; $i++): ?>
		<th class="sa-exc-dayh"><span><?php echo SA_Calendar_Utilities::get_week_day_shortname($date->format('N') - 1) . '</span><br><span>' . $date->format('j'); ?></span></th>
	<?php 	$date->modify('+1 day'); ?>
	<?php endfor; ?>
	</tr>
	</thead>
	<tbody>
	<?php if(count($rows) > 0): ?>
		<?php foreach($rows as $key => $row) : ?>
	<tr data-time="<?php echo $key; ?>">
		<td><?php echo $row[0]; ?></td>
		<td<?php echo $row[1]; ?>></td>
		<td<?php echo $row[2]; ?>></td>
		<td<?php echo $row[3]; ?>></td>
		<td<?php echo $row[4]; ?>></td>
		<td<?php echo $row[5]; ?>></td>
		<td<?php echo $row[6]; ?>></td>
		<td<?php echo $row[7]; ?>></td>
	</tr>
		<?php endforeach; ?>
	<?php else: ?>
	<tr>
		<td colspan="8"><?php echo __('No available date and time', 'sa-calendar'); ?></td>
	</tr>
	<?php endif; ?>
</tbody></table>
</div>