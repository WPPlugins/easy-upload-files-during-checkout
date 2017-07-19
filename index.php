<?php 
/*
Plugin Name: Easy Upload Files During Checkout
Plugin URI: http://www.websitedesignwebsitedevelopment.com/wufdc
Description: Attach files during checkout process on cart page with ease.
Version: 1.1.8
Author: Fahad Mahmood 
Author URI: http://www.androidbubbles.com
License: GPL2
Text Domain: easy-ufdc
Domain Path: /languages

This WordPress Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
This free software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/ 

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $easy_ufdc_page, $easy_ufdc_req, $easy_ufdc_error, $wufdc_dir, $ufdc_premium_link, $ufdc_custom, $wufdc_pro_file, $eufdc_data, $easy_ufdc_error_default;
	$easy_ufdc_error_default = __('Please choose a file to upload!','easy-ufdc');
	$ufdc_premium_link = 'http://shop.androidbubbles.com/product/woocommerce-upload-files-checkout';
	$easy_ufdc_page = get_option( 'easy_ufdc_page' );
	$easy_ufdc_req = (get_option( 'easy_ufdc_req' )*1);
	$wufdc_dir = plugin_dir_path( __FILE__ );
	$eufdc_data = get_plugin_data(__FILE__);
	$wufdc_pro_file = $wufdc_dir.'/pro/eufdc_advanced.php';
	$ufdc_custom = file_exists($wufdc_pro_file);
	$easy_ufdc_error = trim(get_option('easy_ufdc_error'));
	$easy_ufdc_error = ($easy_ufdc_error!=''?$easy_ufdc_error:$easy_ufdc_error_default);
    include('inc/functions.php');

	function eufdc_backup_pro($src='pro', $dst='') { 
		$plugin_dir = plugin_dir_path( __FILE__ );
		$uploads = wp_upload_dir();
		$dst = ($dst!=''?$dst:$uploads['basedir']);
		$src = ($src=='pro'?$plugin_dir.$src:$src);
		$pro_check = basename($plugin_dir);
		$pro_check = $dst.'/'.$pro_check.'.dat';
		if(file_exists($pro_check)){
			if(!is_dir($plugin_dir.'pro')){
				mkdir($plugin_dir.'pro');
			}
			$files = file_get_contents($pro_check);
			$files = explode('\n', $files);
			if(!empty($files)){
				foreach($files as $file){
					if($file!=''){
						$file_src = $uploads['basedir'].'/'.$file;
						//echo $file_src.' > '.$plugin_dir.'pro/'.$file.'<br />';
						$file_trg = $plugin_dir.'pro/'.$file;
						if(!file_exists($file_trg))
						copy($file_src, $file_trg);
					}
				}//exit;
			}
		}

		if(is_dir($src)){
			if(!file_exists($pro_check)){
				$f = fopen($pro_check, 'w');
				fwrite($f, '');
				fclose($f);
			}	
			$dir = opendir($src); 
			@mkdir($dst); 
			while(false !== ( $file = readdir($dir)) ) { 
				if (( $file != '.' ) && ( $file != '..' )) { 
					if ( is_dir($src . '/' . $file) ) { 
						eufdc_backup_pro($src . '/' . $file, $dst . '/' . $file); 
					} 
					else { 
						$dst_file = $dst . '/' . $file;
						if(!file_exists($dst_file)){
							copy($src . '/' . $file,$dst_file); 
							$f = fopen($pro_check, 'a+');
							fwrite($f, $file.'\n');
							fclose($f);
						}
					} 
				} 
			} 
			closedir($dir); 
		}	
	}

	function eufdc_activate() {	
		eufdc_backup_pro();
	}
	register_activation_hook( __FILE__, 'eufdc_activate' );

	require_once('admin/ufdc-settings.php');
	add_action('admin_menu', 'easy_ufdc_admin_menu');
	add_action('init', 'ufdc_custom_file_upload');						
	//add_action('woocommerce_checkout_process', 'ufdc_custom_file_upload'); 

	switch($easy_ufdc_page){
		case 'checkout':
			add_action('woocommerce_checkout_after_customer_details', 'add_file_to_upcoming_order');
		break;
		case 'cart':
		case '':
			add_action('woocommerce_after_cart_table', 'add_file_to_upcoming_order');
			add_action('wp_footer', 'ufdc_easy_ufdc_req');
		break;
	}
	add_action('woocommerce_init', 'file_during_checkout');
	add_action('woocommerce_order_status_pending', 'wc_checkout_order_processed');
	add_action('woocommerce_order_status_failed', 'wc_checkout_order_processed');
	add_action('woocommerce_order_status_on-hold', 'wc_checkout_order_processed');
	add_action('woocommerce_order_status_processing', 'wc_checkout_order_processed');
	add_action('woocommerce_order_status_completed', 'wc_checkout_order_processed');
	add_action('woocommerce_order_status_cancelled', 'wc_checkout_order_processed');
	add_action( 'save_post', 'pre_wc_checkout_order_processed' );

	/**************************/
	//add_action('woocommerce_order_status_pending', 'mysite_pending');
	//add_action('woocommerce_order_status_failed', 'mysite_failed');
	//add_action('woocommerce_order_status_on-hold', 'mysite_hold');
	// Note that it's woocommerce_order_status_on-hold, not on_hold.
	//add_action('woocommerce_order_status_processing', 'mysite_processing');
	//add_action('woocommerce_order_status_completed', 'mysite_completed');
	//add_action('woocommerce_order_status_refunded', 'mysite_refunded');
	//add_action('woocommerce_order_status_cancelled', 'mysite_cancelled');	
	/**************************/

	//add_action('init', 'init_sessions');	
	add_action('add_meta_boxes', 'easy_ufdc_add_box');

	if(!is_admin()){
		add_action( 'wp_enqueue_scripts', 'wufdc_enqueue_style' );
		add_action( 'wp_enqueue_scripts', 'wufdc_enqueue_script' );
	}else{
		$plugin = plugin_basename(__FILE__);
		add_action( 'admin_enqueue_scripts', 'wufdc_admin_enqueue_script' );
		add_filter("plugin_action_links_$plugin", 'ufdc_plugin_links' );	
	}



	