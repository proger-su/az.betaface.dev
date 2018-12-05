<?php
global $wp;
$current_user = wp_get_current_user();
if ( $current_user->exists() ) {
	?>
	<h4><?php esc_html_e( 'Hi, ', 'betaface-auth' ); ?> <strong><?php echo $current_user->user_login; ?></strong>!</h4>
	<p><a href="<?php echo wp_logout_url( home_url( $wp->request ) ); ?>"><?php esc_html_e( 'Logout', 'betaface-auth' ); ?></a></p>
	<?php return; ?>
<?php } ?>

<form method="post" id="betaface-auth" class="search-form" action="<?php echo get_pagenum_link(); ?>">
	<?php wp_nonce_field( 'betaface-auth-nonce', 'betaface-auth-nonce' ); ?>
	<input type="email" class="search-field betaface-auth-email" placeholder="<?php esc_attr_e( 'Your email', 'betaface-auth' ); ?>" name="betaface-auth-email">
	<button type="submit" class="search-submit" name="betaface-auth-submit" value="1">OK</button>
	<div id="betaface-auth-screen-wrap">
		<div class="buttons">
			<button class="close" type="button">C</button>
			<button class="login" type="button">L</button>
			<button class="register" type="button">R</button>
		</div>
		<div id="betaface-auth-screen"></div>
	</div>
</form>