<?php 
/*
Plugin Name: Combat-Adblock
Plugin URI: 
Description: Stop adblockers taking advantage of your premium content.
Author: James Kingsbury
Version: 0.9
Author URI: 
*/

	//setup shortcode so user can use enclosing tags to protect premium content
	//returns js specific to the admin options and this js either shows the user
	//a predefined adblocker warning(and blocks content), or alt if no adblovker 
	//is detected, no warning is shown
	function ca_shortcode_init(){
		function ca_shortcode($atts = [], $content = null){
			if(get_option('ca_alert_type')['type'] == 'radio-alert'){
				return ca_alert($content);
			}elseif(get_option('ca_alert_type')['type'] == 'radio-modal'){
				return ca_modal($content);
			}else{
				return ca_banner($content);
			}
		}
		add_shortcode('combat-adblock', 'ca_shortcode');
	}

	//returns js alert code with admin defined message
	function ca_alert($content){
		//executed if alert is wanted
		isset(get_option('ca_alert_text')['text']) ? $alert_text =  get_option('ca_alert_text')['text'] :  $alert_text = ca_default_alert_text();
		
		if(isset(get_option('ca_alert_redirect')['type'])){
			//add support for custom redirect
			!empty(get_option('ca_alert_redirect_url')['text']) ? $redirect_url =  get_option('ca_alert_redirect_url')['text'] :  $redirect_url = ca_default_redirect_url();
			return ca_alert_js($content, $alert_text, admin_url( 'admin-ajax.php'), true, $redirect_url);
		}else{
			return ca_alert_js($content, $alert_text, admin_url( 'admin-ajax.php'), false, '');
		}
	}

	//retunrn js banner code with prefedined html/css or default
	function ca_banner($content){
		//executed if only inline banner is wanted
		if(ctype_space(get_option('ca_inline_banner_code')['text']) || get_option('ca_inline_banner_code')['text'] == '' || !isset(get_option('ca_inline_banner_code')['text'])){
			$warn_html = ca_default_inline_banner();
		}else{
			$warn_html = get_option('ca_inline_banner_code')['text'];
		}
		return ca_inline_banner_js($warn_html, $content, admin_url( 'admin-ajax.php' ));
	}

	//retunrn js banner code and jquery modal with prefedined html/css or default
	function ca_modal($content){
		//this is the jquery pop up case(inline banner is always inline with this)
		if(ctype_space(get_option('ca_inline_banner_code')['text']) || get_option('ca_inline_banner_code')['text'] == '' || !isset(get_option('ca_inline_banner_code')['text'])){
			$warn_html = ca_default_inline_banner();
		}else{
			$warn_html = get_option('ca_inline_banner_code')['text'];
		}

		if(ctype_space(get_option('ca_modal_code')['text']) || get_option('ca_modal_code')['text'] == '' || !isset(get_option('ca_modal_code')['text'])){
			$modal_html = ca_default_modal();
		}else{
			$modal_html = get_option('ca_modal_code')['text'];
		}

		return ca_modal_js($warn_html, $content, admin_url( 'admin-ajax.php' ), $modal_html);
	}

	//log adblock user
	function ca_log_user(){
		ca_insert_ip_row(ca_get_ip());
	}

	//get ip of adblock user
	function ca_get_ip(){
		return $_SERVER['REMOTE_ADDR'];
	}

	//deals with incoming ajax request that logs adlocking attempt
	function ca_ajax_adblock_log(){
		ca_insert_ip_row(ca_get_ip());
		//required for ajax response to send immediately
		wp_die(); 
	}

	//deals with incoming ajax request deletes all adblock logs
	function ca_ajax_adblock_clear(){
		ca_clear_ip_rows();
		//required for ajax response to send immediately
		wp_die(); 
	}

	//add submenu for spotify widget options 
	function ca_options_page(){
		add_submenu_page('options-general.php', 'Combat Adblock Options', 'Combat Adblock', 'manage_options', 'ca', 'ca_options_page_html');
	}

	//register the requried ca options 
	function ca_register_options(){
		register_setting('ca_options', 'ca_alert_type', 'ca_options_callback');
		register_setting('ca_options', 'ca_alert_text', 'ca_options_text_callback');
		register_setting('ca_options', 'ca_alert_redirect', 'ca_options_callback');
		register_setting('ca_options', 'ca_inline_banner_code', 'ca_options_callback');
		register_setting('ca_options', 'ca_modal_code', 'ca_options_callback');
		register_setting('ca_options', 'ca_alert_redirect_url', 'ca_options_text_callback');
		register_setting('ca_options', 'ca_global_lock', 'ca_options_callback');
	}

	//callback for ca options page - this needs to be updated at some point to actualy validate input,
	//all input is very rudimentary right now though, so this is unncessary for the time being 
	//^^not entirelt true, probs need to validate html
	function ca_options_callback($input){
		return $input;
	}

	//ca validates text inputted into alert box as it needs to be html clean
	function ca_options_text_callback($input){
		$input['text'] = wp_filter_nohtml_kses($input['text']);
		return $input;
	}


	//html for ca options page
	function ca_options_page_html(){
		echo ca_admin_dependancy(admin_url('admin-ajax.php'));	
		echo ca_load_admin_body();
	}

	//amends short codes around content in post body for global lock function
	function ca_amend_shortcode($content){
		return '[combat-adblock]' . $content . '[/combat-adblock]';
	}

	//load widgets' required assets
	function ca_load_css_js(){
		$plugin_url = plugin_dir_url(__FILE__);

		wp_register_style('ca_style', $plugin_url . 'assets/styles.css');
		wp_enqueue_style('ca_style');
		//appends phantom querystring to script so that browser is forced to not cache it
		wp_enqueue_script('fuckadblock', $plugin_url . 'assets/fuckadblock.js' . '?' . time());
	}

	//holds all tempates
	require_once('ca_html.php');

	//holds db functions 
	require_once('ca_db.php');

	//add admin area hooks
	if(is_admin()){
		add_action('admin_init', 'ca_register_options');
		add_action('admin_menu', 'ca_options_page');
	}

	//global lock filters
	if(isset(get_option('ca_global_lock')['type'])){
		add_filter('the_content', 'ca_amend_shortcode');
	}

	//ajax related hooks
	add_action('wp_ajax_ca_ajax_adblock_log', 'ca_ajax_adblock_log');
	add_action('wp_ajax_ca_ajax_adblock_clear', 'ca_ajax_adblock_clear');
	add_action('wp_ajax_nopriv_ca_ajax_adblock_log', 'ca_ajax_adblock_log');
	add_action('wp_ajax_nopriv_ca_ajax_adblock_clear', 'ca_ajax_adblock_clear');

	//add wordpress action hooks 
	add_action('init', 'ca_load_css_js');
	add_action('init', 'ca_shortcode_init');

	//activation hook for adding db
	register_activation_hook(__FILE__, 'ca_create_ip_table');
?>