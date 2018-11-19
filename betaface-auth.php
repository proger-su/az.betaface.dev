<?php

/*
  Plugin Name: Betaface Auth
  Description: WordPress Betaface Auth
  Author: Sergey Pererva
  Author URI: http://proger.su
  Version: 1.0
 */

defined('ABSPATH') or die('No direct access');

class betafaceAuth {

	function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'enqScripts'));

		add_shortcode('betaface-auth', array($this, 'renderAuthForm'));

		add_action('wp_ajax_betaface_auth_handler', array($this, 'authHandler'));
		add_action('wp_ajax_nopriv_betaface_auth_handler', array($this, 'authHandler'));
	}

	public function enqScripts() {
		wp_enqueue_style('betaface-auth', plugin_dir_url(__FILE__) . 'static/css/styles.css');
		wp_enqueue_script('betaface-auth', plugin_dir_url(__FILE__) . 'static/js/scripts.js', array('jquery'));
	}

	public function renderAuthForm() {
		return '';
	}

	public function authHandler() {
		
	}
}

global $betafaceAuth;
$betafaceAuth = new betafaceAuth;