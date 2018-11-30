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
		
		$email = filter_input(INPUT_POST, 'email');
	}

}

global $betafaceAuth;
$betafaceAuth = new betafaceAuth;
