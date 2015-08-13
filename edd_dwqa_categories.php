<?php	

/**	

 * Plugin Name: EDD and DWQA Categories	

 * Plugin URI: http://peepso.com	

 * Description: This plugin creates an integration between EDD (Easy Digital Downloads) and the DWQA forum. 

 				It automatically generates DWQA forum categories for every EDD product and verifies the user’s license before posting a question.	

 * Version: 1.0	

 * Author: peepso.com	

 * Author URI: peepso.com

 * Text Domain: edd_dwqa_categories

 * License: 	

 */	

 	

defined('ABSPATH') or die("No script kiddies please!");	

	

/* Helpers functions */	

function edd_dwqa_css(){	

	wp_register_style( 'edd_dwqa_css', plugin_dir_url(__FILE__)."helpers/edd_dwqa_categories.css" );	

	wp_enqueue_style( 'edd_dwqa_css' );		

}	

add_action('wp_enqueue_scripts', 'edd_dwqa_css');	

	

	

function edd_dwqa_categories_created_edd_term($term_id, $tt_id, $taxonomy){	

		

	$term = get_term_by( 'id', $term_id, $taxonomy );	

	

	if(!empty($term) && $term->parent == 0 && $taxonomy == 'download_category'){	

	

		$tag = wp_insert_term($term->name, 'dwqa-question_category', $_POST );	

	

		if ( !$tag || is_wp_error($tag) ) {// || (!$tag = get_term( $tag['term_id'], $taxonomy ))	

			$message = __('An error has occurred. DW Q&A category could not be added!', 'edd_dwqa_categories');	

			if ( is_wp_error($tag) && $tag->get_error_message() )	

				$message = $tag->get_error_message();	

					

			$x = new WP_Ajax_Response();	

			$x->add( array(	

				'what' => 'taxonomy',	

				'data' => new WP_Error('error', $message )	

			) );	

			$x->send();	

		} else{		

			//global $wpdb;	

			//$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix."js_dwqa_categories (id, dwqa_category_id, edd_product_id, edd_category_id) VALUES ('', %d, '', %d)", $tag->term_id, $term_id) );	

		}	

			

	}	

}	

add_action( "created_term", "edd_dwqa_categories_created_edd_term", 100, 3 );	



	

function edd_dwqa_categories_save_post_download($post_ID, $post, $update){	

	global $wpdb;	

		

	$bundle = get_post_meta( $post_ID, '_edd_product_type', true );	

	

	if($bundle == 'bundle'){	

		return true;	

	}	

	

	$checked_terms = wp_get_object_terms($post_ID, 'download_category', array('fields'=>'ids'));	

	

		

	if(is_array($checked_terms) && !empty($checked_terms)){	

		foreach($checked_terms as $category){	

			//check if product category is dwqa category also	

			$term_taxonomy_id  = $wpdb->get_results( $wpdb->prepare( "SELECT term_taxonomy_id FROM " . $wpdb->prefix . "term_taxonomy WHERE term_id = %d AND taxonomy = 'dwqa-question_category' ", $category ) );	

				

			if($term_taxonomy_id > 0){	

				$post_object['parent'] = $category;	

			} else {	

				$post_object['parent'] = 0;	

			}	

			$post_object = array();	

			$post_object['action'] = 'add-tag';	

				

	

			$tag = wp_insert_term($post->post_title, 'dwqa-question_category', $post_object );	

			if(!is_wp_error($tag)){	

				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix."edd_dwqa_categories (id, dwqa_category_id, edd_product_id, edd_product_category_id) VALUES ('', %d, %d, '')", $tag['term_id'], $post_ID) );					

			}	

		}	

	} else {	

		$post_object = array();	

		$post_object['action'] = 'add-tag';	

		$post_object['parent'] = 0;					

		

		$tag = wp_insert_term($post->post_title, 'dwqa-question_category', $post_object );	

		if(!is_wp_error($tag)){	

			$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix."edd_dwqa_categories (id, dwqa_category_id, edd_product_id, edd_product_category_id) VALUES ('', %d, %d, '')", $tag['term_id'], $post_ID) );					

		}			

	}	

	return true;	

	

}	

add_action( "save_post_download", "edd_dwqa_categories_save_post_download", 100, 3 );	

	

	

function edd_dwqa_categories_list_questions($atts, $content = null){	



	global $script_version, $dwqa_sript_vars, $dwqa_template_compat, $wpdb, $dwqa_options, $dwqa_filter;	

	$category 			= isset($_REQUEST['dwqa-question_category']) ? get_term_by( 'slug', $_REQUEST['dwqa-question_category'], 'dwqa-question_category' ) : "";

	$parent_category	= isset($_REQUEST['dwqa-question_category_parent']) ? absint($_REQUEST['dwqa-question_category_parent']) : $category->term_id;	

	$child_category 	= isset($_REQUEST['dwqa-question_category_child']) ? absint($_REQUEST['dwqa-question_category_child']) : $category->term_id;

/*	if ($child_category > 0) {
		
		$_POST['category'] = $child_category;
		
	} elseif ($parent_category > 0) {
		
		$_POST['category'] = $parent_category;		
		
	}*/

	if($parent_category != ''){	

		$child_categories 			= get_terms( 'dwqa-question_category', array( 'parent' => $parent_category, 'hide_empty' => false, 'term_meta' => 1 ) );		

	}	

	$parent_categories = get_terms( 'dwqa-question_category', array( 'parent' => 0, 'hide_empty' => false, 'term_meta' => 1) );	


	echo '<div class="dwqa-container" >';	

		

	require plugin_dir_path(__FILE__)."template/dwqa_list_questions.php";	

		

	echo '</div>';	

	

	wp_dequeue_script( 'dwqa-questions-list' );	

		

	wp_enqueue_script( 'edd-dwqa-questions-list', plugin_dir_url(__FILE__) . 'helpers/edd_dwqa_categories.js', array( 'jquery' ), $script_version, true );	

	

	wp_localize_script( 'edd-dwqa-questions-list', 'dwqa', $dwqa_sript_vars );	

	

}	

add_shortcode('dwqa-list-questions', 'edd_dwqa_categories_list_questions', 100);

function edd_dwqa_categories_enque_js($atts, $content = null){

	

	global $script_version, $dwqa_sript_vars, $dwqa_template_compat, $wpdb, $dwqa_options, $dwqa_filter;

	

	wp_enqueue_script( 'edd-dwqa-questions-list', plugin_dir_url(__FILE__) . 'helpers/edd_dwqa_categories.js', array( 'jquery' ), $script_version, true );	

	wp_localize_script( 'edd-dwqa-questions-list', 'dwqa', $dwqa_sript_vars );	

	

}

add_action( "dwqa_submit_question_ui", "edd_dwqa_categories_enque_js" );

	

	

function edd_dwqa_categories_get_ask_question_link(){	

	global $wpdb;	

	edd_dwqa_categories_get_ask_question_link_code();		

/*	$user 		= wp_get_current_user();	

	if(isset($user->caps['administrator']) && $user->caps['administrator'] == 1){	

		edd_dwqa_categories_get_ask_question_link_code();	

	}		

		

	//see in which product page we are	

	$product_page = isset($_REQUEST['dwqa-question_category_child']) ? absint($_REQUEST['dwqa-question_category_child']) : (isset($_REQUEST['dwqa-question_category_parent']) ? absint($_REQUEST['dwqa-question_category_parent']) : "");	

	if($product_page != ""){	

		//$product_id = $product_page;	

		$product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT edd_product_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE dwqa_category_id = %d ", $product_page ) );	

		if($product_id > 0){	

			if(edd_dwqa_categories_has_user_purchased(get_current_user_id(), $product_id)){	

				edd_dwqa_categories_get_ask_question_link_code();	

			} else {	

				return false;	

			}	

	

		}	

	

	}	*/

}	



function checkUserProduct(){

	global $wpdb;



	$response 	= new stdClass();



    if( !is_user_logged_in() ) {

        $response->error = "Error";

		echo json_encode($response);

		exit;		

    }



	$user 		= wp_get_current_user();

	if($user->caps['administrator'] == 1){

		$response->message = "Ok";

		echo json_encode($response);

		exit;

	}



	$cat_id 	= absint( $_REQUEST['cat_id'] );



	$product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT edd_product_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE dwqa_category_id = %d ", $cat_id ) );

	if($product_id > 0){

		$checkUserProduct = edd_dwqa_categories_has_user_purchased(get_current_user_id(), $product_id, NULL, true);

		if($checkUserProduct === true){

			$response->message = "Ok";			

		}elseif($checkUserProduct == 'purchased_expired'){

			$response->error = "purchased_expired";

		} else{

			$response->error = "Error";

		}

	} else{

		$response->error = "Error";

	}

	

	echo json_encode($response);



	exit;

}

add_action( 'wp_ajax_checkUserProduct', "checkUserProduct");

	

function edd_dwqa_categories_get_product_by_question_category($cat_id){	

	global $wpdb;	

	$product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT edd_product_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE dwqa_category_id = %d ", $cat_id ) );	

	if($product_id > 0){	

		return $product_id;	

	} else {	

		return false;	

	}	

}	

	

	

function edd_dwqa_categories_get_question_category_by_product_id($product_id){	

	global $wpdb;	

	$cat_id  = $wpdb->get_var( $wpdb->prepare( "SELECT dwqa_category_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE edd_product_id = %d ", $product_id ) );	

	if($cat_id > 0){	

		return $cat_id;	

	} else {	

		return false;	

	}	

}	

	

/**	

 * Has User Purchased	

 *	

 * Checks to see if a user has purchased a download.	

 *	

 * @access      public	

 * @since       1.0	

 * @param       int $user_id - the ID of the user to check	

 * @param       array $downloads - Array of IDs to check if purchased. If an int is passed, it will be converted to an array	

 * @param       int $variable_price_id - the variable price ID to check for	

 * @return      boolean - true if has purchased and license is active, false otherwise	

 */	

function edd_dwqa_categories_has_user_purchased( $user_id, $downloads, $variable_price_id = null, $verify_purchase = false ) {	

	

	$users_purchases = edd_get_users_purchases( $user_id );	
	
	

	$return = false;	

	

	if ( ! is_array( $downloads ) ) {	

		$downloads = array( $downloads );	

	}	


	$now	 		= strtotime(date('Y-m-d H:i:s'));	

	

	if ( $users_purchases ) {	

		foreach ( $users_purchases as $purchase ) {	

	

			$purchased_files = edd_get_payment_meta_downloads( $purchase->ID );	

			if (empty($purchased_files)) {
				return false;
			}

			$licenses = edd_software_licensing()->get_licenses_of_purchase( $purchase->ID );	

			$licenses_products = array();	



			if( is_array( $licenses ) ){	

				foreach($licenses as $license){	

					$download_id 	= get_post_meta($license->ID, '_edd_sl_download_id', true);	

					$status 		= get_post_meta($license->ID, '_edd_sl_status', true);	

					$expire 		= get_post_meta($license->ID, '_edd_sl_expiration', true);	

					$licenses_products[$download_id] 			= array();	

					$licenses_products[$download_id]['status'] 	= $status;	

					$licenses_products[$download_id]['expire'] 	= $expire;					

				}	

			}else{	

				return false;	

			}	
			

			if ( is_array( $purchased_files ) ) {	

				foreach ( $purchased_files as $download ) {	

					if (edd_is_bundled_product( $download['id'] ) ) {
					
						$bundled_products = edd_get_bundled_products( $download['id'] );
						
						if (empty($bundled_products)) {
							return false;
						}
						
						
						foreach ($bundled_products as $bundle_product) {
							
							if ( in_array( $bundle_product, $downloads ) ) {
								
								//check to see if the license is active	
		
								//echo $licenses_products[$download['id']]['expire'] . ">" . $now . "==========";	
		
								if(isset($licenses_products[$bundle_product]['expire']) && $now > $licenses_products[$bundle_product]['expire']){// || $licenses_products[$download['id']]['status'] == 'inactive'	
		
									if($verify_purchase){
		
										return "purchased_expired";
		
									}else{
		
										return false;
		
									}
		
								}	
		
			
		
								$variable_prices = edd_has_variable_prices( $bundle_product );	
		
								if ( $variable_prices && ! is_null( $variable_price_id ) && $variable_price_id !== false ) {	
		
									if ( isset( $download['options']['price_id'] ) && $variable_price_id == $download['options']['price_id'] ) {	
		
										return true;	
		
									} else {	
		
										return false;	
		
									}	
		
								} else {	
		
									return true;	
		
								}									
								
							}
							
						}
						
						return false;
					
					}


					if ( in_array( $download['id'], $downloads ) ) {	

	

						//check to see if the license is active	

						//echo $licenses_products[$download['id']]['expire'] . ">" . $now . "==========";	

						if(isset($licenses_products[$download['id']]['expire']) && $now > $licenses_products[$download['id']]['expire']){// || $licenses_products[$download['id']]['status'] == 'inactive'	

							if($verify_purchase){

								return "purchased_expired";

							}else{

								return false;

							}

						}	

	

						$variable_prices = edd_has_variable_prices( $download['id'] );	

						if ( $variable_prices && ! is_null( $variable_price_id ) && $variable_price_id !== false ) {	

							if ( isset( $download['options']['price_id'] ) && $variable_price_id == $download['options']['price_id'] ) {	

								return true;	

							} else {	

								return false;	

							}	

						} else {	

							return true;	

						}	

					}	

				}	

			}	

		}	

	}	

	

	return false;	

}	

	

	

function edd_dwqa_categories_get_ask_question_link_code( $echo = true, $label = false, $class = false ){	



    global $dwqa_options;	

	

    $submit_question_link = get_permalink( $dwqa_options['pages']['submit-question'] );	
	
	//echo $dwqa_options['pages']['submit-question'];
	//echo $submit_question_link;

    if( $dwqa_options['pages']['submit-question'] && $submit_question_link ) {	

	

	

        if( dwqa_current_user_can('post_question') ) {	

	

            $label = $label ? $label : __('Ask a question','edd_dwqa_categories');	

	

        } elseif( ! is_user_logged_in() ) {	

	

            $label = $label ? $label : __('Login to ask a question','edd_dwqa_categories');	

	

            $submit_question_link = wp_login_url( $submit_question_link );	

	

        } else {	

	

            return false;	

	

        }	

	

        //Add filter to change ask question link text	

	

        $label = apply_filters( 'dwqa_ask_question_link_label', $label );	

	

	

	

        $class = $class ? $class  : 'dwqa-btn-success';	

	

        //$button = '<a href="'.$submit_question_link.'" class="dwqa-btn '.$class.'">'.$label.'</a>';	

		$button = '	<form action="'.$submit_question_link.'" method="post" onSubmit="return edd_dwqa_populate_question_category();">	

						<input type="submit" class="dwqa-btn '.$class.'" value="'.$label.'"/>	

						<input type="hidden" name="question-category" value="" id="question-category" />	

					</form>';	

	

        $button = apply_filters( 'dwqa_ask_question_link', $button, $submit_question_link );	

	

        if( ! $echo ) {	

	

            return $button;	

	

        }	

	

        echo $button;	

	

    }	

	

}	

	

function edd_dwqa_categories_wp_dropdown_cats($output, $r){	

		

	if(isset($r['disabled']) && $r['disabled'] == 'disabled'){	

		$output = str_replace("<select","<select disabled", $output);	

	}

	

	if(isset($r['onChange']) && $r['onChange'] != ''){	

		$output = str_replace("<select","<select onChange='javascript:".$r['onChange']."'", $output);	

	}		

		

	return $output;	

}	

add_filter( 'wp_dropdown_cats', 'edd_dwqa_categories_wp_dropdown_cats', 10000, 2 );	



function edd_dwqa_categories_override_question_content($template, $name){



	if($name != 'single-question'){

		return $template;

	}



	$user 		= wp_get_current_user();

	if(isset($user->caps['administrator']) && $user->caps['administrator'] == 1){

		return plugin_dir_path(__FILE__)."template/single-question.php";//$template;

	}

	

	if($name == 'single-question'){

		global $wpdb;

		$post_id 	= get_the_ID();

		$categories = wp_get_post_terms( $post_id, 'dwqa-question_category' );



		if(!empty($categories)){

			$product_id  = $wpdb->get_var( $wpdb->prepare( "SELECT edd_product_id FROM " . $wpdb->prefix . "edd_dwqa_categories WHERE dwqa_category_id = %d ", $categories[0]->term_id ) );

			if($product_id > 0){

				if(!edd_dwqa_categories_has_user_purchased(get_current_user_id(), $product_id)){

					return plugin_dir_path(__FILE__)."template/single-question-invalid.php";

				}

			}

		}

	}

	return plugin_dir_path(__FILE__)."template/single-question.php";//$template;

}

add_filter( 'dwqa-load-template', 'edd_dwqa_categories_override_question_content', 10000, 2  );



/*DWQA Submit question override*/

function edd_dwqa_categories_submit_question_form_shortcode(){

	global $dwqa_sript_vars, $script_version, $dwqa_template_compat;



	ob_start();



	$dwqa_template_compat->remove_all_filters( 'the_content' );



	echo '<div class="dwqa-container" >';

		//dwqa_load_template( 'question', 'submit-form' );

		require plugin_dir_path(__FILE__)."template/question-submit-form.php";

	echo '</div>';



	$html = ob_get_contents();



	$dwqa_template_compat->restore_all_filters( 'the_content' );



	ob_end_clean();



	wp_enqueue_script( 'dwqa-submit-question', DWQA_URI . 'inc/templates/default/assets/js/dwqa-submit-question.js', array( 'jquery' ), $script_version, true );

	wp_localize_script( 'dwqa-submit-question', 'dwqa', $dwqa_sript_vars );

	//return $this->sanitize_output( $html );	

	return $html;	

}

//add_shortcode( 'dwqa-submit-question-form', 'edd_dwqa_categories_submit_question_form_shortcode' );



function edd_dwqa_categories_activate() {

	global $wpdb;

	

	$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "edd_dwqa_categories` (

			`id` bigint(20) NOT NULL AUTO_INCREMENT,

			`dwqa_category_id` int(255) NOT NULL,

			`edd_product_id` int(255) NOT NULL,

			`edd_product_category_id` int(255) NOT NULL,

			PRIMARY KEY (`id`)

			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta($sql);			

}

register_activation_hook( __FILE__, 'edd_dwqa_categories_activate' );

