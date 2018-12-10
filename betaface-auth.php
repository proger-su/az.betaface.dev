<?php
/*
  Plugin Name: Betaface Auth
  Description: WordPress Betaface Auth
  Author: Alina Zakharchenko
  Author URI:
  Version: 1.0
 */

defined( 'ABSPATH' ) or die( 'No direct access' );

class betafaceAuth {

	static $version = '1.0.1';
	private $actions = array(
		'register' => 'betaface_register',
		'login' => 'betaface_login'
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
		wp_enqueue_script( 'sweetalert', plugin_dir_url( __FILE__ ) . 'static/js/sweetalert.min.js', array(), '2.1.2', true );
		wp_enqueue_script( 'betaface-auth', plugin_dir_url( __FILE__ ) . 'static/js/scripts.js', array( 'jquery', 'webcam', 'sweetalert' ), self::$version, true );

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
		static $running = false;
		
		if($running) {
			return esc_html__('Only one auth form per page!', 'betaface-auth');
		}
		
		ob_start();
		require 'templates/form.php';
		$running = true;
		return ob_get_clean();
	}

	public function login() {
		require_once('inc/api.php');
		$api = new betaFaceApi();

		check_ajax_referer( 'betaface-auth-nonce', 'nonce' );

		$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
		$photo = filter_input( INPUT_POST, 'photo' );

		if ( is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'You are already logged in!', 'betaface-auth' ) );
		}

		if ( !$email || !$photo ) {
			wp_send_json_error( esc_html__( 'Email or photo is incorrect!', 'betaface-auth' ) );
		}

		$user = get_user_by( 'email', $email );

		if ( !$user ) {
			wp_send_json_error( esc_html__( 'This email not found!', 'betaface-auth' ) );
		}

		$user_login = $user->data->user_login;
		$user_id = $user->ID;
		
		$current = (int) get_user_meta( $user->ID, 'betaface-auth-user-photo', true );
		$path = get_attached_file( $current );
		$photo = self::saveImage( $photo, "login_image_for_{$user_login}", true );

		if ( !$path || !$photo ) {
			wp_send_json_error( esc_html__( 'Current photo not found! Use standard login form please!', 'betaface-auth' ) );
		}

		try {
			$matches = array();
			preg_match( '/([^\/]+)$/', get_home_url(), $matches );

			if ( isset( $matches[1] ) ) {
				$domain = $matches[1];
				$prepared_name = "{$user_login}@{$domain}";
			} else {
				wp_send_json_error( esc_html__( 'Cannot get site domain!', 'betaface-auth' ) );
			}

			$upload = $api->upload_face( $path, $prepared_name );
			$recognize = $api->recognize_faces( $photo, $domain );

			if ( !isset( $recognize[$prepared_name] ) ) {
				wp_send_json_error( esc_html__( 'Cannot recognize face! Something went wrong!', 'betaface-auth' ) );
			}

			$result = (float) $recognize[$prepared_name];
			$round = round( $result * 100 );

			if ( $round >= 80 ) {
				wp_set_auth_cookie( $user_id, true );
				wp_send_json_success();
			} else {
				wp_send_json_error( esc_html__( 'Cannot recognize face! Maybe you are another person!', 'betaface-auth' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	public function register() {
		check_ajax_referer( 'betaface-auth-nonce', 'nonce' );

		$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
		$photo = filter_input( INPUT_POST, 'photo' );

		if ( is_user_logged_in() ) {
			wp_send_json_error( esc_html__( 'You are already logged in!', 'betaface-auth' ) );
		}

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

		$id = self::saveImage( $photo, "register_image_for_{$user_name}" );

		if ( !$id ) {
			wp_send_json_error( esc_html__( 'Cannot add user photo!', 'betaface-auth' ) );
		}

		update_user_meta( $user_id, 'betaface-auth-user-photo', $id, true );

		// Authorize user
		wp_set_auth_cookie( $user_id, true );

		return wp_send_json_success();
	}

	static function saveImage( $base64_img, $title, $tmp = false ) {
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

		$file_path = $upload_dir['path'] . '/' . $hashed_filename;

		if ( $tmp ) {
			return $file_path;
		}

		return wp_insert_attachment( $attachment, $file_path );
	}

}

global $betafaceAuth;
$betafaceAuth = new betafaceAuth;
