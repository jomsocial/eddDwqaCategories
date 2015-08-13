<div id="submit-question" class="dwqa-submit-question">    	

    <?php  	

	

        global $dwqa_options, $dwqa_current_error;	

        

        if( is_wp_error( $dwqa_current_error ) ) {	

            $error_messages = $dwqa_current_error->get_error_messages();	



            if( !empty($error_messages) ) {	

                echo '<div class="alert alert-error">';	

                foreach ($error_messages as $message) {	

                    echo $message;	

                }	

                echo '</div>';	

            }	

        }	

    ?>	

    <form action="" name="dwqa-submit-question-form" id="dwqa-submit-question-form" method="post">	

        <div class="question-advance">	

            <div class="question-meta">	

			<?php          

                //@EDD_DWQA - start

				$user_id = get_current_user_id();

                if(isset( $_REQUEST['question-category'] ) && absint($_REQUEST['question-category']) > 0){

					$product_id = edd_dwqa_categories_get_product_by_question_category(absint($_REQUEST['question-category']));

					if($product_id > 0 && edd_dwqa_categories_has_user_purchased($user_id, $product_id)){

						$can_post = true;

						$disabled = false;

						$disabled_cat = true;

						?>

						<input type="hidden" name="question-category" value="<?php echo absint($_REQUEST['question-category']); ?>" />

						<?php

					} else {

						$can_post = false;

						$disabled = true;

						$disabled_cat = true;

						?>        

						<div class="input-tag"><?php _e('"You don\'t have license to this product, please visit our store to purchase it"','edd_dwqa_categories'); ?></div>

                        <?php

					}	

                } else {

					$disabled_cat = false;

				}

				?>        

                <div class="select-category">        

                    <label for="question-category"><?php _e('Question Category','edd_dwqa_categories') ?></label>                                

                    <?php							        

                        wp_dropdown_categories( array(  
							
							'term_meta'		=> 1,

                            'name'          => 'question-category',        

                            'id'            => 'question-category',        

                            'taxonomy'      => 'dwqa-question_category',        

                            'show_option_none' => __('Select question category','edd_dwqa_categories'),        

                            'hide_empty'    => 0,        

                            'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' ),

							'disabled'		=> ($disabled_cat === true ? "disabled" : "none"),

							'exclude'		=> (!empty($formated_categories) ? implode(',',$formated_categories) : ''),

							'onChange'		=> 'checkUserProduct();',

                            'selected'      => (isset( $_REQUEST['question-category'] ) ? stripslashes(htmlentities($_REQUEST['question-category'])) : false)        

                        ) );        

                    ?>        

                </div>                

				<?php        

                //@EDD_DWQA - end        

                ?>	

                <div class="input-tag" id="checkUserProductMessage"></div>	

            </div>	

        </div>	

        <div class="input-title">	

            <label for="question-title"><?php _e('Your question','edd_dwqa_categories') ?> *</label>	

            <input <?php echo $disabled === true ? "disabled" : ""; ?> type="text" name="question-title" id="question-title" placeholder="<?php _e('How to...','edd_dwqa_categories') ?>" autocomplete="off" data-nonce="<?php echo wp_create_nonce( '_dwqa_filter_nonce' ) ?>" value="<?php echo isset( $_POST['question-title'] ) ? stripslashes(htmlentities($_POST['question-title'])) : ''; ?>" />	

            <span class="dwqa-search-loading dwqa-hide"></span>	

            <span class="dwqa-search-clear fa fa-times dwqa-hide"></span>	

        </div>  	



        <div class="question-advance">	

            <div class="input-content">	

                <label for="question-content"><?php _e('Question details','edd_dwqa_categories') ?></label>	

                <?php 	

                    dwqa_init_tinymce_editor( array( 	

                            'content' => ( isset( $_POST['question-content'] ) ? stripslashes(htmlentities($_POST['question-content'])) : '' ),	

                            'id' => 'dwqa-question-content-editor', 	

                            'textarea_name' => 'question-content',	

                            'media_buttons' => true	

                    ) ); 	

                ?>	

            </div>	



            <?php if( isset($dwqa_options['enable-private-question']) && $dwqa_options['enable-private-question'] ) : ?>	

            <div class="checkbox-private">	

                <label for="private-message"><input type="checkbox" name="private-message" id="private-message" value="true"> <?php _e('Post this Question as Private.','edd_dwqa_categories') ?> <i class="fa fa-question-circle" title="<?php _e('Only you as Author and Admin can see the question', 'edd_dwqa_categories') ?>"></i></label>	

            </div>	

            <?php endif; ?>	

            <div class="question-signin">	

                <?php do_action( 'dwqa_submit_question_ui' ); ?>	

            </div>	

            <script type="text/javascript">	

             var RecaptchaOptions = {	

                theme : 'clean'	

             };	

             </script>	

            <?php  	

                global  $dwqa_general_settings;	

                if( dwqa_is_captcha_enable_in_submit_question() ) {	

                    $public_key = isset($dwqa_general_settings['captcha-google-public-key']) ?  $dwqa_general_settings['captcha-google-public-key'] : '';	

                    echo '<div class="google-recaptcha">';	

                    echo recaptcha_get_html($public_key);	

                    echo '<br></div>';	

                }	

            ?>	

        </div>	

		<div class="form-submit">	

            <input <?php echo $disabled === true ? "disabled" : ""; ?> type="submit" value="<?php _e('Ask Question','edd_dwqa_categories') ?>" class="dwqa-btn dwqa-btn-success btn-submit-question" />	

        </div>  	

    </form>	

</div>