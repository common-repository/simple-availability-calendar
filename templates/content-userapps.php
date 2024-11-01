<?php
	$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	if (is_admin())
		$page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
	$posts_per_page = SA_Calendar_Settings::instance()->sa_posts_per_page;
	$i = $posts_per_page * ($page - 1);

	$date = new DateTime('today', $utz);
	$now = $date->format('Y-m-d').' 0000';

	$args4All = array(
		'post_type'			=> SA_Calendar::APPOINTMENT_POST_TYPE,
		'post_status'		=> 'publish',
		'paged'				=> $page,
		'author'			=> $user_id,
		'posts_per_page'	=> $posts_per_page,
		'meta_key'			=> SA_Calendar::APPOINTMENT_DATETIME_META_NAME,
		'orderby'			=> 'meta_value',
		'order'				=> 'DESC',
	);
	$args4Curr = array(
		'post_type'			=> SA_Calendar::APPOINTMENT_POST_TYPE,
		'post_status'		=> 'publish',
		'paged'				=> $page,
		'author'			=> $user_id,
		'posts_per_page'	=> $posts_per_page,
		'meta_key'			=> SA_Calendar::APPOINTMENT_DATETIME_META_NAME,
		'orderby'			=> 'meta_value',
		'order'				=> 'ASC',
		'meta_query'		=> array(
								array(
									'key'     => SA_Calendar::APPOINTMENT_DATETIME_META_NAME,
									'value'   => $now,
									'compare' => '>=',
								),
							),
	);

	query_posts( $show_all ? $args4All : $args4Curr );

	if ( !have_posts() && !$show_all ) {
		$show_all = true;
		query_posts( $args4All );
	}

	$paginationArgs = array(
		'prev_text'	=> __('&laquo; Previous', 'sa-calendar'),
		'next_text'	=> __('Next &raquo;', 'sa-calendar'),
	);
	if ($show_all)
		$paginationArgs['add_args']	= array( 'show_all' => 1);

	if (is_admin()) {
		$paginationArgs['format'] = '?paged=%#%';
	}

	$superMode = is_admin() && is_super_admin();
	
	$page_link = SA_Calendar_Utilities::get_current_page_url();
	if ($superMode)
		$page_link = add_query_arg('user_id', $user_id, $page_link);
	
	$showCancel = $for_current_user || $superMode;
	$add_class = esc_attr(SA_Calendar_Settings::instance()->sa_style_userapps);
	$fields = SA_Calendar_Settings::instance()->get_fields();
?>
<div class="sa-div<?php if (!empty($add_class)) echo ' ' . $add_class; ?>">
<?php if ( have_posts() ) : ?>
	<form class="sa-ms-head-div" action="<?php echo ( $show_all ? remove_query_arg('show_all', $page_link) : add_query_arg('show_all', 1, $page_link) ); ?>" method="post">
		<button class="btn btn-default"><?php echo ( $show_all ? __('Show Current', 'sa-calendar') : __('Show All', 'sa-calendar') ); ?></button>
	</form>
	<div class="sa-ms-tbl-div">
	<table class="sa-ms-tbl">
	<tr>
		<th><?php echo __('#', 'sa-calendar'); ?></th>
		<th><?php echo __('Date & Time', 'sa-calendar'); ?></th>
		<?php foreach($fields as $name => $item): ?>
		<th><?php echo $item['title']; ?></th>
		<?php endforeach; ?>
	<?php if ($showCancel) : ?>
		<th><?php echo __('Cancel', 'sa-calendar'); ?></th>
	<?php endif; ?>
	</tr>
	<?php while ( have_posts() ) : the_post(); ?>
	<?php
		$post_id = get_the_ID();
		$date = get_post_meta($post_id, SA_Calendar::APPOINTMENT_DATETIME_META_NAME, true);
		$old = $date < $now;
		$date = explode(' ', $date);
		$dt4User = SA_Calendar_Utilities::convert_2user_datetime($user_id, $date[0], $date[1], SA_Calendar_Settings::instance()->sa_timezone_avail);
	?>
	<tr<?php echo $old ? ' class="sa-ms-old"' : ''; ?>>
		<td><?php echo ++$i; ?></td>
		<td><?php echo $dt4User; ?></td>
		<?php foreach($fields as $name => $item): ?>
		<?php $val = get_post_meta($post_id, SA_Calendar::APPOINTMENT_FIELD_META_NAME . mb_strtolower($name), true); ?>
		<td><?php echo esc_attr( $val ); ?></td>
		<?php endforeach; ?>
		<?php if ($showCancel) : ?>
		<td>
			<form method="post" action="<?php echo $page_link; ?>">
				<?php wp_nonce_field( SA_Calendar_Logic::nonceUserAppName, 'sa_sec' ); ?>
				<input type="hidden" name="saad[id]" value="<?php echo $post_id; ?>">
			<?php if ($superMode) : ?>
				<input type="hidden" name="saad[user_id]" value="<?php echo $user_id; ?>" />
			<?php endif; ?>
				<button type="submit" class="sa-btn-del"></button>
			</form>
		</td>
		<?php endif; ?>
	</tr>
	<?php endwhile; ?>
	</table>
	</div>
	<?php if ( is_admin() ): ?>
	<div class='tablenav-pages'>
		<?php the_posts_pagination( $paginationArgs ); ?>
	</div>
	<?php else: ?>
		<?php the_posts_pagination( $paginationArgs ); ?>
	<?php endif; ?>
	<?php wp_reset_query(); ?>
<?php else : ?>
	<div class="alert alert-warning">
		<?php echo __( 'No appointments yet', 'sa-calendar' )?>
	</div>
<?php endif; ?>
</div>