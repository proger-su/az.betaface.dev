<form method="post" id="betaface-auth" class="search-form" action="<?php echo get_pagenum_link(); ?>">
	<?php wp_nonce_field( 'betaface-auth-nonce', 'betaface-auth-nonce' ); ?>
	<input type="email" class="search-field betaface-auth-email" placeholder="Your email" name="betaface-auth-email">
	<button type="submit" class="search-submit" name="betaface-auth-submit" value="1"><svg class="icon icon-search" aria-hidden="true" role="img"> <use href="#icon-envelope-o" xlink:href="#icon-search"></use> </svg></button>
</form>