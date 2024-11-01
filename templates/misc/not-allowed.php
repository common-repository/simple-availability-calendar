<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="alert alert-warning">
	<?php echo esc_attr__( 'You are not allowed to access this page.', 'sa-calendar' ); ?>

	<?php if ( ! empty( $message ) ) : ?>
		<?php echo $message;  ?>
	<?php endif; ?>
</div>