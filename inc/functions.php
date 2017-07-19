<?php
	global $easy_ufdc_page, $wufdc_dir, $wufdc_pro_file, $ufdc_custom;        

	if($ufdc_custom)

	include($wufdc_pro_file);
	function wufdc_admin_enqueue_script(){
	    wp_enqueue_script( 'wufdc_scripts', plugin_dir_url( dirname(__FILE__) ) . 'js/admin_scripts.js' );
		wp_enqueue_style( 'wufdc-style', plugins_url('css/admin.css', dirname(__FILE__)), array(), date('Yhmi'));
	}
	
	
	/*if(!function_exists('wc_add_notice')){
		function wc_add_notice($error, $domain){
			
			return $error;
			
		}	 
	} 	*/
	if(!function_exists('pre')){
	function pre($data){
			if(isset($_GET['debug'])){
				pree($data);
			}
		}	 
	} 	

	if(!function_exists('pree')){
	function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		}	 
	} 

	if(!function_exists('add_file_to_upcoming_order')){
		function add_file_to_upcoming_order(){
		?>
		<div id="wufdc_div">
			<?php echo get_option( 'easy_ufdc_caption' ); ?><br />
			<input type="file" name="file_during_checkout" />
			<small><?php _e("Allowed filesize:","easy-ufdc"); ?> <?php echo get_option( 'easy_ufdc_max_uploadsize' ); ?></small>        
	  		<script type="text/javascript">jQuery(document).ready(function($) {jQuery(document).ready(function(){ layered_js(); });});</script>  
		</div>
		<?php	
		}
	}

	if(!function_exists('easy_ufdc_admin_menu')){
		function easy_ufdc_admin_menu() {
			$page = add_submenu_page('woocommerce', __( 'Upload Files During Checkout', 'easy-ufdc' ), __( 'Easy Upload Files', 'easy-ufdc' ), 'manage_woocommerce', 'easy_ufdc', 'easy_ufdc_page' );
		}	
	}

	function wufdc_enqueue_style() {
		wp_enqueue_style(
			'wufdc-style',
			plugins_url('css/style.css', dirname(__FILE__))
		);
	}

	function wufdc_enqueue_script() {
		global $woocommerce;
		$cart_url = $woocommerce->cart->get_cart_url();
		$checkout_url = $woocommerce->cart->get_checkout_url();
		// Register the script
		wp_register_script( 'eufdc', plugins_url('js/scripts.js', dirname(__FILE__)) );

		// Localize the script with new data
		$translation_array = array(
			'cart_url' => $cart_url,
			'checkout_url' => $checkout_url,
		);

		wp_localize_script( 'eufdc', 'eufdc_obj', $translation_array );
		// Enqueued script with localized data.
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'eufdc' );

		/*wp_enqueue_script(
			'eufdc',
			plugins_url('js/scripts.js', __FILE__),
			array('jquery')
		);*/	

	}

	if(!function_exists('ufdc_file_during_checkout')){
		function ufdc_file_during_checkout(){
			//pree($_FILES);
			$ret = (isset($_FILES['file_during_checkout']['name']) && $_FILES['file_during_checkout']['error']==0)?true:false;
			//pree($ret);//exit;
			return $ret;
		}
	}

	if(!function_exists('ufdc_custom_file_upload')){
	 	function ufdc_custom_file_upload() {
			if(is_admin())
			return;
			global $easy_ufdc_page, $easy_ufdc_req, $easy_ufdc_error;
			/*pree(!empty($_POST));
			pree($easy_ufdc_req);
			pree(!ufdc_file_during_checkout());
			exit;*/

			if(!empty($_POST) && $easy_ufdc_req && !ufdc_file_during_checkout()){	
				//pree($easy_ufdc_page);exit;
				switch($easy_ufdc_page){			
					case 'checkout':
						if(strpos($_SERVER['REQUEST_URI'], '/checkout')>0){
							wc_add_notice( __( $easy_ufdc_error ), 'error' );				
						}
					break;

					case 'cart':

					case '':
						//pree(!is_cart());
						if(strpos($_SERVER['REQUEST_URI'], '/cart')>0){
							wc_add_notice( __( $easy_ufdc_error ), 'error' );					
						}
					break;
				}
			}
		}
	}


	if(!function_exists('ufdc_easy_ufdc_req')){	
		function ufdc_easy_ufdc_req(){
			global $woocommerce;
?>
		<script type="text/javascript" language="javascript">
			jQuery(document).ready(function($){
				//console.log($('form[method="post"]'));
				var act = setInterval(function(){
					$.each($('form[method="post"]'), function(){
						var attr = $(this).attr('action');
						if (typeof attr !== typeof undefined && attr !== false) {
							$(this).removeAttr('action');
							clearInterval(act);
						}
					});
				}, 1000);

				if($('input[name="file_during_checkout"]').length>0){
					$('input[name="file_during_checkout"]').on('change', function(){
						$('form[method="post"]').attr('action', '<?php echo $woocommerce->cart->get_checkout_url(); ?>');
					});
				}
			});
		</script>
<?php			
		}
	}	

	if(!function_exists('file_during_checkout')){
		function file_during_checkout(){			
			global $easy_ufdc_page, $woocommerce, $easy_ufdc_req, $easy_ufdc_error;
			$REQUEST_URI = $_SERVER['REQUEST_URI'];
			if(
				!empty($_FILES) 
			&& 
				!is_admin() 
			&&
				(
					(
						$easy_ufdc_req 
						&& 
						array_key_exists('file_during_checkout', $_FILES)
						)
						||
						array_key_exists('file_during_checkout', $_FILES)
					)
			){	
			
				$checkout_url = $woocommerce->cart->get_checkout_url();
				$cart_url = $woocommerce->cart->get_cart_url();
				$is_checkout = strpos($checkout_url, $REQUEST_URI);
				$is_cart = strpos($cart_url, $REQUEST_URI);
			
				if($is_checkout || $is_cart){
					//pree($checkout_url);
					//pree($cart_url);
		
	
					//exit;
					switch($easy_ufdc_page){
						case 'checkout':					
							$redir = $checkout_url;
						break;
						case 'cart':
						default:					
							$redir = $cart_url;
						break;
					}		
	
					//pree(ufdc_file_during_checkout());exit;
	
					if($easy_ufdc_req && !ufdc_file_during_checkout()){
						wc_add_notice(sprintf( __( $easy_ufdc_error, 'easy-ufdc'), $ext ), 'error');
						wp_redirect($redir);
						exit;
					}elseif(ufdc_file_during_checkout()){
						$file_during_checkout = $_FILES['file_during_checkout']['name'];
						$file_during_checkout = explode('.', $file_during_checkout);
						$ext = end($file_during_checkout);
						$doctypes = explode( ',', get_option( 'easy_ufdc_allowed_file_types' ) );
						$doctypes=array_map('trim',$doctypes);
						if(!empty($ext) && !in_array($ext, $doctypes)){
							wc_add_notice(sprintf( __( 'The "%s" file type is not allowed.', 'easy-ufdc'), $ext ), 'error');
							wp_redirect($redir);
							exit;
						}
	
						if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
						$uploadedfile = $_FILES['file_during_checkout'];
						$upload_overrides = array( 'test_form' => false );
						$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
						$wc = (array)$woocommerce->session;
						$wc = array_values($wc);
	
						if(!empty($movefile)){					
							global $wpdb;
							$myposts = $wpdb->get_results("
								SELECT 
									$wpdb->posts.* 
								FROM 
									$wpdb->posts                                     				
								WHERE 
									$wpdb->posts.post_parent = '".$wc[1]."'  
								AND 
									$wpdb->posts.post_type = 'attachment' 
								AND 
									(($wpdb->posts.post_status = 'inherit')) 
								ORDER BY 
									$wpdb->posts.post_date 
								DESC 
									LIMIT 0, 1"
								);
					
							//$args = array( 'posts_per_page' => 1, 'offset'=>0, 'post_type'=>'attachment', 'post_status'=>'inherit', 'post_parent'=>$wc[1] );
							//$myposts = get_posts( $args );
							//pre($args);pre($wc);pre($myposts);exit;
	
							if(!empty($myposts)){
								foreach($myposts as $post){
									wp_delete_post( $post->ID, true );
								}
							}
	
							// $filename should be the path to a file in the upload directory.
							$filename = $movefile['file'];
	
							// The ID of the post this attachment is for.
							$parent_post_id = $wc[1];
	
							// Check the type of tile. We'll use this as the 'post_mime_type'.
							$filetype = wp_check_filetype( basename( $filename ), null );
	
							// Get the path to the upload directory.
							$wp_upload_dir = wp_upload_dir();
	
							// Prepare an array of post data for the attachment.
							$attachment = array(
								'guid'           => $movefile['url'], 
								'post_mime_type' => $filetype['type'],
								'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
								'post_content'   => '',
								'post_status'    => 'inherit'
							);
					
							// Insert the attachment.
							$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
		
							// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
							require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
							// Generate the metadata for the attachment, and update the database record.
							$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
							wp_update_attachment_metadata( $attach_id, $attach_data );
						}
					}elseif($is_cart){
							wp_redirect($checkout_url);
							exit;
					}
				}
			}
		}
	}

	function pre_wc_checkout_order_processed($post_id){
		$post = get_post($post_id); 
		$post_type = $post->post_type;
		if($post_type=='shop_order'){		
			//pre($post);exit;
			wc_checkout_order_processed($post_id);
		}
	}

	if(!function_exists('wc_checkout_order_processed')){
		function wc_checkout_order_processed($order_id){
			global $woocommerce;
			$wc = (array)$woocommerce->session;
			$wc = array_values($wc);

			if(isset($wc[1])){
				$args = array( 'posts_per_page' => 1, 'offset'=>0, 'post_type'=>'attachment', 'post_status'=>'inherit', 'post_parent'=>$wc[1] );
				$myposts = get_posts( $args );	

				if(!empty($myposts)){
					$my_post = (array)current($myposts);
					$my_post['post_type'] = 'attachment_order';
					$my_post['post_parent'] = $order_id;
					//pree($my_post);exit;
					wp_update_post( $my_post );	
					$key = 1;
					update_post_meta( $order_id, '_woo_ufdc_uploaded_file_name_' . $key, $my_post['post_title'] );
					update_post_meta( $order_id, '_woo_ufdc_uploaded_file_path_' . $key, str_replace(get_bloginfo('siteurl').'/', '', $my_post['guid']) );
					update_post_meta( $order_id, '_woo_ufdc_uploaded_product_name_' . $key, 'Order ID: '.$order_id );
				}
			}		
		}
	}

	if(!function_exists('init_sessions')){
		function init_sessions(){
			if (!session_id()){
				ob_start();
				@session_start();
			}
		}
	}

	if(!function_exists('easy_ufdc_add_box')){
		function easy_ufdc_add_box() {
			add_meta_box( 'easy-ufdc-box-order-detail', __( 'Attached Files', 'easy-ufdc' ), 'easy_ufdc_box_order_detail', 'shop_order', 'side', 'default' );
		}
	}

	function add_order_attachments(  $order, $sent_to_admin, $plain_text=false, $email='' ) {

		if (get_option('eufdc_email', 0) && in_array($order->post_status, array('wc-on-hold', 'wc-processing'))) {
			global $woocommerce;
			$post = $order->post;
			$ret = '<h3>'. __("Attachments","easy-ufdc") . '</h3>';
			$ret .= '<ul style="padding:0; margin:0; list-style:decimal outside;">';
			$i=1;
			$j=1;
			$upload_count=0;		
			$max_upload_count=1;
			while ($i <= $max_upload_count) {
				$name = get_post_meta( $post->ID, '_woo_ufdc_uploaded_file_name_' . $j, true );	
				$uploaded_file_path = get_post_meta( $post->ID, '_woo_ufdc_uploaded_file_path_' . $j, true );					
				$url = home_url('/').( str_replace( ABSPATH, '',  $uploaded_file_path) );
				$forproduct = get_post_meta( $post->ID, '_woo_ufdc_uploaded_product_name_' . $j, true );

				if( !empty( $url ) && !empty( $name ) ) {
					$ret .= '<li>';
					$ret .= str_replace(array('_URL', '_NAME'), array($url, $name), ($sent_to_admin?'<a href="_URL" target="_blank">':'').'_NAME'.($sent_to_admin?'</a>':'') );
					$ret .= '</li>';
					$upload_count++;
				} else {
					//silence is golden
				}
				$i++;
				$j++;
			}
			$ret .= '</ul>';
			echo $ret;
			//exit;
		}
	}

	//add_filter('woocommerce_email_recipient_customer_processing_order', 'wc_cc_store_email', 1, 2);

	add_action( 'woocommerce_email_before_order_table', 'add_order_attachments', 10, 2 );

	if(!function_exists('easy_ufdc_box_order_detail')){
		function easy_ufdc_box_order_detail($post) {
			
			if(is_numeric($post))
			$order = new WC_Order($post);			
			else
			$order = new WC_Order($post->ID);			
			//pre(get_post_meta( $post->ID));
			$j=1;
			/* per product een formulier met gegevens */
			foreach ( $order->get_items() as $order_item ) {				
				$max_upload_count=1;
				//$max_upload_count=get_max_upload_count_plus($order,$order_item['product_id']);

				//if($max_upload_count!=0){
				$item_meta = new WC_Order_Item_Meta( $order_item['item_meta'] );
				//pree($item_meta);
				//pree($item_meta->display(true,true));
				//$item_meta->display(true,true)

				$forproduct = $order_item['name'];
				echo '<strong>';
				printf( __('File for product: "%s"', 'easy-ufdc'), $forproduct);
				echo '</strong><br>';

				/* Controle of er al een bestand is geupload */

				$i=1;
				$upload_count=0;
				echo '<ul>';
				while ($i <= $max_upload_count) {
					echo '<li>';
					$name = get_post_meta( $post->ID, '_woo_ufdc_uploaded_file_name_' . $j, true );					
					$uploaded_file_path = get_post_meta( $post->ID, '_woo_ufdc_uploaded_file_path_' . $j, true );					
					$url = home_url('/').( str_replace( ABSPATH, '',  $uploaded_file_path) );
					$forproduct = get_post_meta( $post->ID, '_woo_ufdc_uploaded_product_name_' . $j, true );
					if( !empty( $url ) && !empty( $name ) ) {
						printf( '<a href="%s" target="_blank">%s</a>', $url, $name );
						$upload_count++;
					} else {
						//silence is golden
					}
				$i++;
				$j++;
					echo '</li>';
				}
				echo '</ul>';
			//}
			}
		}
	}

	function get_max_upload_count_plus($order,$order_item=0) {
		$max_upload_count=0;
		//product specifiek
		if( 
			(
				(
					is_array( get_option( 'easy_umf_status' )
				) 
				&& 
				in_array( $order->status, get_option( 'easy_umf_status' ) ) ) 
			) 
				|| 
				$order->status == get_option( 'easy_umf_status' ) 
		){
			if($order_item!=0) {
				$product = easy_umf_get_product($order_item);
				if( easy_umf_get_product_meta($product,'woo_umf_enable') == 1) {
					$max_upload_count=1;
				}
			} else {
			// order totaal
			foreach ( $order->get_items() as $order_item ) {
				$product = easy_umf_get_product($order_item['product_id']);
				$limit=1;
				if( easy_umf_get_product_meta($product,'woo_umf_enable') == 1 && $limit > 0 ) {
					$max_upload_count+=$limit;
				}
			}
			}
		}
		return $max_upload_count;
	}

	function ufdc_plugin_links($links) { 
		global $ufdc_premium_link, $ufdc_custom;
		$settings_link = '<a href="admin.php?page=easy_ufdc">' . __("Settings","easy-ufdc") . '</a>';
		if($ufdc_custom){
			array_unshift($links, $settings_link); 
		}else{
			$ufdc_premium_link = '<a href="'.$ufdc_premium_link.'" title="' . __('Go Premium','easy-ufdc') . '" target=_blank>' . __("Go Premium","easy-ufdc") . '</a>'; 
			array_unshift($links, $settings_link, $ufdc_premium_link); 
		}
		return $links; 
	}
	
	function eufdc_header_scripts(){
?>
	<style type="text/css">
	<?php
		if(get_option('eufdc_shipping_off', 0)){
?>
			.woocommerce-shipping-fields{
				display:none;	
			}
<?php			
		}
		if(get_option('eufdc_billing_off', 0)){
?>
			.woocommerce-billing-fields{
				display:none;	
			}
<?php			
		}
		if(get_option('eufdc_order_comments_off', 0)){
?>

<?php			
		}				
	?>
	</style>
<?php		
	}
	
	add_action('wp_head', 'eufdc_header_scripts');
	
	add_filter( 'woocommerce_checkout_fields' , 'eufdc_override_checkout_fields' );
	
	function eufdc_override_checkout_fields( $fields ) {
	
		if(get_option('eufdc_shipping_off', 0)){
			unset($fields['shipping']['shipping_first_name']);
			unset($fields['shipping']['shipping_last_name']);
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_address_1']);
			unset($fields['shipping']['shipping_address_2']);
			unset($fields['shipping']['shipping_city']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_country']);
			unset($fields['shipping']['shipping_state']);
			unset($fields['shipping']['shipping_phone']);	
			unset($fields['shipping']['shipping_address_2']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_last_name']);
			unset($fields['shipping']['shipping_email']);
			unset($fields['shipping']['shipping_city']);	
		}
		
		if(get_option('eufdc_billing_off', 0)){
			unset($fields['billing']['billing_first_name']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_address_1']);
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_city']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_country']);
			unset($fields['billing']['billing_state']);
			unset($fields['billing']['billing_phone']);	
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_email']);
			unset($fields['billing']['billing_city']);
		}
		
		if(get_option('eufdc_order_comments_off', 0))
		unset($fields['order']['order_comments']);
		
		return $fields;
	}
	
	function wdm_my_custom_notes_on_single_order_page($order){

       	$note = easy_ufdc_box_order_detail($order);
		$order->add_order_note( $note );

    }

    add_action( 'woocommerce_order_details_after_order_table', 'wdm_my_custom_notes_on_single_order_page',10,1 );	
		