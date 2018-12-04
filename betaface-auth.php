<?php
/*
  Plugin Name: Betaface Auth
  Description: WordPress Betaface Auth
  Author: Sergey Pererva
  Author URI: http://proger.su
  Version: 1.0
 */

defined( 'ABSPATH' ) or die( 'No direct access' );

class betafaceAuth {

	static $version = '1.0.0';
	private $actions = array(
		'register' => 'betaface_register'
	);

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqScripts' ) );

		add_action( 'show_user_profile', array( $this, 'addProfilePhotoField' ) );
		add_action( 'edit_user_profile', array( $this, 'addProfilePhotoField' ) );

		add_shortcode( 'betaface-auth', array( $this, 'renderAuthForm' ) );

		foreach ( $this->actions as $key => $action ) {
			add_action( "wp_ajax_{$action}", array( $this, $key ) );
			add_action( "wp_ajax_nopriv_{$action}", array( $this, $key ) );
		}
	}

	public function enqScripts() {
		wp_enqueue_style( 'betaface-auth', plugin_dir_url( __FILE__ ) . 'static/css/styles.css', array(), self::$version );
		wp_enqueue_script( 'webcam', plugin_dir_url( __FILE__ ) . 'static/js/webcam.js', array(), '1.0.25', true );
		wp_enqueue_script( 'betaface-auth', plugin_dir_url( __FILE__ ) . 'static/js/scripts.js', array( 'jquery', 'webcam' ), self::$version, true );

		wp_localize_script( 'betaface-auth', 'betafaceAuthConfig', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'actions' => $this->actions
		) );
	}

	public function addProfilePhotoField( $user ) {
		?>
		<h3><?php esc_html_e( 'Personal Information', 'betaface-auth' ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="year_of_birth"><?php esc_html_e( 'User photo', 'betaface-auth' ); ?></label></th>
				<td>
					<?php
					$photo = (int) get_user_meta( $user->ID, 'betaface-auth-user-photo', true );
					$url = wp_get_attachment_url( $photo );

					if ( $url ) {
						?>
						<img src="<?php echo esc_url( $url ); ?>" alt="<?php esc_attr_e( 'User photo', 'betaface-auth' ); ?>" width="auto" height="100">
						<?php
					}
					?>
				</td>
			</tr>
		</table>
		<?php
	}

	public function renderAuthForm() {
		ob_start();
		require 'templates/form.php';
		return ob_get_clean();
	}

	public function authHandler() {
		require_once('inc/api.php');
		$api = new betaFaceApi();
	}

	public function register() {
		check_ajax_referer( 'betaface-auth-nonce', 'nonce' );

		$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
		$photo = filter_input( INPUT_POST, 'photo' );

		if ( !$email || !$photo ) {
			wp_send_json_error( esc_html__( 'Email or photo is incorrect!', 'betaface-auth' ) );
		}

		$random_password = wp_generate_password( 6 );
		$user_name = explode( '@', $email );
		$user_name = $user_name[0];
		$user_id = wp_create_user( $user_name, $random_password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( $user_id->get_error_message() );
		}

		add_filter( 'wp_mail_content_type', function($content_type) {
			return "text/html";
		} );

		wp_mail( $email, get_bloginfo( 'name' ) . ' (' . esc_html( 'New registration', 'betaface-auth' ) . ')', "<p><strong>" . esc_html__( 'Your password', 'betaface-auth' ) . ":</strong> {$random_password}</p>" );

		$id = self::saveImage( $photo, 'Nigga' );

		if ( !$id ) {
			wp_send_json_error( esc_html__( 'Cannot add user photo!', 'betaface-auth' ) );
		}

		update_user_meta( $user_id, 'betaface-auth-user-photo', $id, true );

		return wp_send_json_success();
	}

	static function saveImage( $base64_img, $title ) {
		// Upload dir.
		$upload_dir = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;

		$img = str_replace( 'data:image/jpeg;base64,', '', $base64_img );
		$img = str_replace( ' ', '+', $img );
		$decoded = base64_decode( $img );
		$filename = strtolower( $title . '.jpeg' );
		$file_type = 'image/jpeg';
		$hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

		// Save the image in the uploads directory.
		$upload_file = file_put_contents( $upload_path . $hashed_filename, $decoded );

		$attachment = array(
			'post_mime_type' => $file_type,
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $upload_dir['url'] . '/' . basename( $hashed_filename )
		);

		return wp_insert_attachment( $attachment, $upload_dir['path'] . '/' . $hashed_filename );
	}

}

global $betafaceAuth;
$betafaceAuth = new betafaceAuth;
