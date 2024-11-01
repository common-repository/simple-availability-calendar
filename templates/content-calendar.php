<?php
	$date = new DateTime('today', $utz);
	$dateThis = new DateTime('first day of this month', $utz);
	$dateThis->setTime(0,0,0);
	$dateNext = new DateTime('first day of next month', $utz);
	$dateNext->setTime(0,0,0);
	
	$sw = SA_Calendar_Settings::instance()->sa_first_week_day;

	$dow = $dateThis->format('N') - 1 >= $sw ? $dateThis->format('N') - 1 - $sw : 7 - $dateThis->format('N');
	$tmpDate = clone $dateThis;
	$tmpDate->sub(new DateInterval('P'.$dow.'D'));
	$add_class = SA_Calendar_Settings::instance()->sa_style_calendar;
?>
<div class="sa-div<?php if (!empty($add_class)) echo ' ' . $add_class; ?>" data-uid="<?php echo $user_id; ?>" data-date="<?php echo $date->format('Y-m-01'); ?>" data-ctype="cal">
<div class="sa-subdiv">
<div>
	<div name="sa-cal-leftar" class="sa-leftar sa-bg-small" style="display:none;"></div>
	<div name="sa-cal-rightar" class="sa-rightar sa-bg-small"></div>
	<b><?php echo SA_Calendar_Utilities::get_cal_header($date); ?></b>
</div>
<table class="sa-calendar">
	<thead>
	<tr>
	<?php for($i = 0; $i < 7; $i++): ?>
		<th><?php echo SA_Calendar_Utilities::get_week_day_shortname(($i + $sw) % 7); ?></th>
	<?php endfor; ?>
	</tr>
	</thead>
	<tbody>
	<?php for($wn = 0; $wn < 6; $wn++): ?>
	<?php if ($tmpDate < $dateNext): ?>
	<tr>
		<?php for($i = 0; $i < 7; $i++): ?>
		<?php 	if ($tmpDate < $dateThis || $tmpDate >= $dateNext): ?>
		<td></td>
		<?php	else: ?>
		<td class="<?php echo $tmpDate > $date && SA_Calendar_Schedule::has_times($user_id, $tmpDate->format('Y-m-d'), ($i + $sw) % 7) ? 'sa-on' : 'sa-off'; ?>"><?php echo $tmpDate->format('j'); ?></td>
		<?php	endif; ?>
		<?php	$tmpDate->modify('+1 day'); ?>
		<?php endfor; ?>
	</tr>
	<?php else: ?>
	<tr style="display:none"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
	<?php endif; ?>
	<?php endfor; ?>
</tbody></table>

</div>
</div>