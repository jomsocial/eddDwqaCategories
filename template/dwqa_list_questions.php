<div id="archive-question" class="dw-question">

	<div class="dwqa-list-question">

		<?php dwqa_load_template('search', 'question'); ?>

		<div class="filter-bar">

			<?php wp_nonce_field( '_dwqa_filter_nonce', '_filter_wpnonce', false ); ?>

			<?php 

			//@EDD_DWQA - start

			edd_dwqa_categories_get_ask_question_link(); 

			//@EDD_DWQA - end

			?>

			<div class="filter">

				<li class="status">

					<?php  

						$selected = isset($_GET['status']) ? $_GET['status'] : 'all';

					?>

					<ul>

						<li><?php _e('Status:') ?></li>

						<li class="<?php echo $selected == 'all' ? 'active' : ''; ?> status-all" data-type="all">

							<a href="#"><?php _e( 'All','edd_dwqa_categories' ); ?></a>

						</li>



						<li class="<?php echo $selected == 'open' ? 'active' : ''; ?> status-open" data-type="open">

							<a href="#"><?php echo current_user_can( 'edit_posts' ) ? __( 'Need Answer','edd_dwqa_categories' ) : __( 'Open','edd_dwqa_categories' ); ?></a>

						</li>

						<li class="<?php echo $selected == 'replied' ? 'active' : ''; ?> status-replied" data-type="replied">

							<a href="#"><?php _e( 'Answered','edd_dwqa_categories' ); ?></a>

						</li>

						<li class="<?php echo $selected == 'resolved' ? 'active' : ''; ?> status-resolved" data-type="resolved">

							<a href="#"><?php _e( 'Resolved','edd_dwqa_categories' ); ?></a>

						</li>

						<li class="<?php echo $selected == 'closed' ? 'active' : ''; ?> status-closed" data-type="closed">

							<a href="#"><?php _e( 'Closed','edd_dwqa_categories' ); ?></a>

						</li>

						<?php if( dwqa_current_user_can( 'edit_question' ) ) : ?>

						<li class="<?php echo $selected == 'overdue' ? 'active' : ''; ?> status-overdue" data-type="overdue"><a href="#"><?php _e('Overdue','edd_dwqa_categories') ?></a></li>

						<li class="<?php echo $selected == 'pending-review' ? 'active' : ''; ?> status-pending-review" data-type="pending-review"><a href="#"><?php _e('Queue','edd_dwqa_categories') ?></a></li>



						<?php endif; ?>

					</ul>

				</li>

			</div>

			<div class="filter sort-by">

				<div class="filter-by-category select">

					<?php 

						$selected = false;

						$taxonomy = get_query_var( 'taxonomy' );

						if( $taxonomy && 'dwqa-question_category' == $taxonomy ) {

							$term_name = get_query_var( $taxonomy );

							$term = get_term_by( 'slug', $term_name, $taxonomy );

							if( $term )

								$selected = $term->term_id;

						} else {

						    $question_category_rewrite = get_option( 'dwqa-question-category-rewrite', 'question-category' );

						    $question_category_rewrite = $question_category_rewrite ? $question_category_rewrite : 'question-category';

							$selected =  isset($_GET[$question_category_rewrite]) ? $_GET[$question_category_rewrite] : 'all'; 

						}

						$selected_label = __('Select a category','edd_dwqa_categories');

						if( $selected  && $selected != 'all' ) {

							$selected_term = get_term_by( 'id', $selected, 'dwqa-question_category' );

							$selected_label = $selected_term->name;

						}

					?>

					<?php  

					//@EDD_DWQA - start

					?>

					<form action="<?php echo site_url(); ?>" method="post" name="category_select">

                   <?php

					if(isset($parent_categories) && !empty($parent_categories)){

						?>

                        <select name="dwqa-question_category_parent" id="dwqa-question_category_parent" onChange="document.category_select.submit();">

	                        <option value="" <?php echo $parent_category == "" ? "selected" : ""; ?> ><?php _e('All','edd_dwqa_categories'); ?></option>

							<?php

                            foreach($parent_categories as $category){

                                ?>

                                <option value="<?php echo $category->term_id ?>" <?php echo $parent_category == $category->term_id ? "selected" : ""; ?> ><?php echo $category->name ?></option>

                                <?php

                            }

                            ?>

                        </select>

                        <?php						

					}

					

					if(isset($child_categories) && !empty($child_categories)){

						?>

                        <select name="dwqa-question_category_child" id="dwqa-question_category_child" onChange="document.category_select.submit();">

	                        <option value="" <?php echo $child_category == "" ? "selected" : ""; ?> ><?php _e('All','edd_dwqa_categories'); ?></option>

							<?php

                            foreach($child_categories as $category){

                                ?>

                                <option value="<?php echo $category->term_id ?>" <?php echo $child_category == $category->term_id ? "selected" : ""; ?> ><?php echo $category->name ?></option>

                                <?php

                            }

                            ?>

                        </select>

                       <?php						

					}

					?>

                    <input type="hidden" name="page_id" id="page_id" value="<?php echo absint(get_the_ID()); ?>" />

                    <input type="hidden" name="taxonomy" id="taxonomy" value="dwqa-question_category" />

                    </form>

                    <?php

					//@EDD_DWQA - end

					?>	

				</div>

				<?php 

					$tag_field = '';

					if( $taxonomy == 'dwqa-question_tag' ) {

						$selected = false;

						if( $taxonomy ) {

							$term_name = get_query_var( $taxonomy );

							$term = get_term_by( 'slug', $term_name, $taxonomy );

							$selected = $term->term_id;

						} elseif( 'dwqa-question_category' == $taxonomy ) {

						    $question_tag_rewrite = get_option( 'dwqa-question-tag-rewrite', 'question-tag' );

						    $question_tag_rewrite = $question_tag_rewrite ? $question_tag_rewrite : 'question-tag';

							$selected =  isset($_GET[$question_tag_rewrite]) ? $_GET[$question_tag_rewrite] : 'all'; 

						}

						if( isset( $selected )  &&  $selected != 'all' ) {

							$tag_field = '<input type="hidden" name="dwqa-filter-by-tags" id="dwqa-filter-by-tags" value="'.$selected.'" >';

						}

					} 

					$tag_field = apply_filters( 'dwqa_filter_bar', $tag_field ); 

					echo $tag_field;

				?>

				<ul class="order">

					<li class="most-reads <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'views' ? 'active' : ''; ?>"  data-type="views" >

						<span><?php _e('View', 'edd_dwqa_categories') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'views' ? 'icon-sort-up' : ''; ?>"></i>

					</li>

					<li class="most-answers <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'answers' ? 'active' : ''; ?>" data-type="answers" >

						<span href="#"><?php _e('Answer', 'edd_dwqa_categories') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'answers' ? 'fa-sort-up' : ''; ?>"></i>

					</li>

					<li class="most-votes <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'votes' ? 'active' : ''; ?>" data-type="votes" >

						<span><?php _e('Vote', 'edd_dwqa_categories') ?></span> <i class="fa fa-sort <?php echo isset($_GET['orderby']) && $_GET['orderby'] == 'votes' ? 'fa-sort-up' : ''; ?>"></i>

					</li>

				</ul>

				

				<?php  

					global $dwqa_general_settings;

					$posts_per_page = isset($dwqa_general_settings['posts-per-page']) ?  $dwqa_general_settings['posts-per-page'] : get_query_var( 'posts_per_page' );

				?>

				<input type="hidden" id="dwqa_filter_posts_per_page" name="posts_per_page" value="<?php echo $posts_per_page; ?>">

			</div>

		</div>

		

		<?php do_action( 'dwqa-before-question-list' ); ?>



		<?php do_action('dwqa-prepare-archive-posts'); ?>

		<?php if ( have_posts() ) : ?>

		<div class="loading"></div>

		<div class="questions-list">

			<?php while ( have_posts() ) : the_post(); ?>

               <?php dwqa_load_template( 'content', 'question' ); ?>

            <?php endwhile; ?>

		</div>

		<div class="archive-question-footer">

			<?php dwqa_load_template( 'navigation', 'archive' ); ?>



			<?php 

			//@EDD_DWQA - start

			edd_dwqa_categories_get_ask_question_link(); 

			//@EDD_DWQA - end

			?>



		</div>

		<?php else: ?>

			<?php dwqa_load_template( 'archive', 'question-notfound'); ?>

		<?php endif; ?>



		<?php do_action( 'dwqa-after-archive-posts' ); ?>

	</div>

</div>