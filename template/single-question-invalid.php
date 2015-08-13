<?php 

/**

 *  Template use for display a single question

 *

 *  @since  DW Question Answer 1.0

 */

    global $current_user, $post;

	$post_id 		= get_the_ID();

	$question 		= get_post( $post_id );

	$best_answer_id = dwqa_get_the_best_answer( $post_id );



	$status = array( 'publish', 'private' );

	$args = array(

	   'post_type' => 'dwqa-answer',

	   'posts_per_page' =>  -1,

	   'order'      => 'ASC',

	   'meta_query' => array(

		   array(

			   'key' => '_question',

			   'value' => array( $post_id ),

			   'compare' => 'IN',

		   )

	   ),

	   'post_status' => $status

	 );

	$answers = new WP_Query($args);	

?>



    <?php if( have_posts() ) : ?>

        <?php while ( have_posts() ) : the_post(); ?>

            <?php $post_id = get_the_ID(); $post_status = get_post_status();  ?>

            <div class="dwqa-single-question">

                <!-- dwqa-status-private -->

                <div class="dwqa-question">

                    <header class="dwqa-header">

                        <?php if( $post_status == 'draft' || $post_status == 'pending' ) : ?>

                        <div class="dwqa-alert alert"><?php echo $current_user->ID == $post->post_author ? __('Your question has been submitted and is currently awaiting approval','edd_dwqa_categories') : __('This question is currently awaiting approval','edd_dwqa_categories'); ?></div>

                        <?php endif; ?>

                        <?php dwqa_question_meta_button( $post_id ); ?>

                        <div class="dwqa-author">

                            <?php echo get_avatar( $post->post_author, 64, false ); ?>

                            <span class="author">

                                <?php  

                                    if( dwqa_is_anonymous( $post->ID ) ) {

										$anonymous_name = get_post_meta( $post->ID, '_dwqa_anonymous_name', true );
	
										if ( $anonymous_name ) {
	
											echo $anonymous_name;
	
										} else {
	
											_e( 'Anonymous', 'dwqa' );
	
										}									

                                    } else {

                                        printf('<a href="%1$s" title="%2$s %3$s">%3$s</a>',

                                            get_author_posts_url( get_the_author_meta( 'ID' ) ),

                                            __('Posts by','edd_dwqa_categories'),

                                            get_the_author_meta(  'display_name')

                                        );

                                    }

                                ?>

                            </span><!-- Author Info -->

                            <span class="dwqa-date">

                                <?php 

                                    printf('<a href="%s" title="%s #%d">%s %s</a>',

                                        get_permalink(),

                                        __('Link to','edd_dwqa_categories'),

                                        $post_id,

                                        __('asked','edd_dwqa_categories'),

                                        get_the_date()

                                    ); 

                                ?>

                            </span> <!-- Question Date -->

                        </div>

                    </header>



                    <div class="dwqa-content">

                        <?php the_content(); ?>

                    </div>

                </div><!-- end question -->

                <div class="js_layout_no_replies"><?php _e('There are '.$answers->post_count.' replies in this thread. Replies are visible to customers only.','edd_dwqa_categories');  ?></div>

            </div><!-- end dwqa-single-question -->

        <?php endwhile; // end of the loop. ?>  

    <?php endif; ?>