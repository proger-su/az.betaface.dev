<form method="post" id="betaface-auth" class="search-form" action="<?php echo get_pagenum_link(); ?>">
	<?php wp_nonce_field( 'betaface-auth-nonce', 'betaface-auth-nonce' ); ?>
	<input type="email" class="search-field betaface-auth-email" placeholder="Your email" name="betaface-auth-email">
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