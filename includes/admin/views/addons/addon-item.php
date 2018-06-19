<?php
/**
 * Single Add-on Item View
 * Used on Add-Ons browser screen
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;

$featured = ! empty( $addon['featured'] ) ? ' featured' : '';
// $featured = $addon['featured'] ? ' featured' : '';
// if ( isset( $addon['file'] ) ) {
// 	$status = $this->get_addon_status( $addon['file'] );
// 	$action = $this->get_addon_status_action( $status );
// }
?>
<li class="llms-add-on-item<?php echo $featured; ?>">
	<div class="llms-add-on">
		<a href="<?php echo esc_url( $addon['permalink'] ); ?>" class="llms-add-on-link">
			<header>
				<img alt="<?php echo $addon['title']; ?> Banner" src="<?php echo esc_url( $addon['image'] ); ?>">
				<h4><?php echo $addon['title']; ?></h4>
			</header>
			<section>
				<p><?php echo llms_trim_string( $addon['description'], 180 ); ?></p>
			</section>
			<?php if ( $addon['author']['name'] ) : ?>
			<footer>
				<?php // Translators: %s = Author Name ?>
				<span><?php printf( __( 'Author: %s', 'lifterlms' ), $addon['author']['name'] ); ?></span>
				<?php if ( $addon['author']['image'] ) : ?>
					<img alt="<?php echo esc_attr( $addon['author']['name'] ); ?> logo" src="<?php echo esc_url( $addon['author']['image'] ); ?>">
				<?php endif; ?>
			</footer>
			<?php endif; ?>
		</a>
		<?php if ( 'mine' === $AddOns->get_current_section() && in_array( $AddOns->get_addon_type( $addon ), array( 'plugin', 'theme' ) ) ) :
			$status = $AddOns->get_addon_status( $addon );
			?>
			<footer class="llms-status status--<?php echo $status; ?>">
				<span><?php printf( __( 'Status: %s', 'lifterlms' ), '<em>' . $AddOns->get_l10n( $status ) . '</em>' );?></span>
				<button class="llms-button-secondary small llms-addon-action" name="llms_addon" type="submit" value="<?php echo $addon['id']; ?>">
					<?php if ( 'none' === $status ) : ?>
						<i class="fa fa-cloud-download" aria-hidden="true"></i>
						<?php _e( 'Install', 'lifterlms' ); ?>
					<?php elseif ( 'active' === $status ): ?>
						<i class="fa fa-toggle-on" aria-hidden="true"></i>
						<?php _e( 'Deactivate', 'lifterlms' ); ?>
					<?php elseif ( 'inactive' === $status ): ?>
						<i class="fa fa-toggle-on" aria-hidden="true"></i>
						<?php _e( 'Activate', 'lifterlms' ); ?>
					<?php endif; ?>
				</button>
			</footer>
		<?php endif; ?>
	</div>
</li>
<?php


return;

?>
<button class="llms-add-on-button" name="llms-add-on-<?php echo $action; ?>"><?php echo $this->get_addon_status_l10n( $action ); ?></button>
