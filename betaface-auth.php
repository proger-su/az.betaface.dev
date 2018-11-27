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
	private $action = 'betaface_auth_handler';

	function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqScripts' ) );

		add_shortcode( 'betaface-auth', array( $this, 'renderAuthForm' ) );

		add_action( "wp_ajax_{$this->action}", array( $this, 'authHandler' ) );
		add_action( "wp_ajax_nopriv_{$this->action}", array( $this, 'authHandler' ) );
	}

	public function enqScripts() {
		wp_enqueue_style( 'betaface-auth', plugin_dir_url( __FILE__ ) . 'static/css/styles.css', array(), self::$version );
		wp_enqueue_script( 'webcam', plugin_dir_url( __FILE__ ) . 'static/js/webcam.js', array(), '1.0.25', true );
		wp_enqueue_script( 'betaface-auth', plugin_dir_url( __FILE__ ) . 'static/js/scripts.js', array( 'jquery', 'webcam' ), self::$version, true );

		wp_localize_script( 'betaface-auth', 'betafaceAuthConfig', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'action' => $this->action
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

}

global $betafaceAuth;
$betafaceAuth = new betafaceAuth;
