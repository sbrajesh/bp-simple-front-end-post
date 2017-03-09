<?php if ( $this->current_user_can_post ): ?>

    <div class="bp-simple-post-form">

        <form class="standard-form bp-simple-post-form" method="post" action="" enctype="multipart/form-data" id="<?php echo $this->get_id(); ?>">

            <!-- do not modify/remove the line blow -->
            <input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id; ?>"/>
            <input type="hidden" name="action" value="bp_simple_post_new_post_<?php echo $this->id; ?>"/>

			<?php wp_nonce_field( 'bp_simple_post_new_post_' . $this->id ); ?>
			<?php wp_nonce_field( 'update-post_' . $post_id, '_bsfep_media_uploader_nonce' ); ?>

			<?php if ( $post_id ): ?>
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" id="post_ID"/>
			<?php endif; ?>

			<?php do_action( 'bsfep_before_title', $this->id, $post_id ); ?>
            <!-- you can modify these, just make sure to not change the name of the fields -->

            <label for="bp_simple_post_title"><?php _e( 'Title:', 'bp-simple-front-end-post' ); ?>
                <input type="text" name="bp_simple_post_title" value="<?php echo $title; ?>"/>
            </label>

			<?php do_action( 'bsfep_before_content', $this->id, $post_id ); ?>

            <label for="bp_simple_post_text"><?php _e( 'Post:', 'bp-simple-front-end-post' ); ?>

				<?php wp_editor( $content, 'bp_simple_post_text', array(
					'media_buttons' => $this->allow_upload,
					'quicktags'     => false
				) ); ?>

            </label>

            <?php do_action( 'bsfep_before_thumbnail', $this->id, $post_id ); ?>

            <?php if ( $this->has_post_thumbnail ): ?>
                <div id="postimagediv">
                    <div class="inside">
						<?php $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
						echo _wp_post_thumbnail_html( $thumbnail_id, $post->ID );
						?>
                    </div>
                </div>
			<?php endif; ?>

			<?php do_action( 'bsfep_before_taxonomy_terms', $this->id, $post_id ); ?>

            <!-- taxonomy terms box -->
			<?php if ( $this->has_tax() ): ?>
                <div class='simple-post-taxonomies-box clearfix'>
					<?php $this->render_taxonomies(); ?>
                    <div class="clear"></div>
                </div>
			<?php endif; ?>

			<?php do_action( 'bsfep_before_custom_fields', $this->id, $post_id ); ?>

            <!-- custom fields -->
			<?php if ( $this->has_custom_fields() ): ?>

                <div class='simple-post-custom-fields'>

					<?php if ( $this->has_visible_meta() && $this->custom_field_title ): ?>
                        <h3> <?php echo $this->custom_field_title; ?> </h3>
					<?php endif; ?>

					<?php $this->render_custom_fields(); ?>
                </div>

			<?php endif; ?>

			<?php do_action( 'bsfep_before_comment_options', $this->id, $post_id ); ?>

			<?php if ( $this->show_comment_option ): ?>

                <div class="simple-post-comment-option">

                    <h4><?php _e( 'Allow Comments', 'bp-simple-front-end-post' ); ?></h4>

					<?php $current_status = $this->comment_status;

					if ( $post_id ) {
						$post = get_post( $post_id );
						$current_status = $post->comment_status;
					}
					?>

                    <label for="bp-simple-post-comment-status">
                        <input id="bp-simple-post-comment-status" name="bp_simple_post_comment_status" type="checkbox"
                               value="open" <?php echo checked( 'open', $current_status ); ?> /> <?php _e( 'Yes', 'bp-simple-front-end-post' ); ?>
                    </label>

                </div>

			<?php endif; ?>

            <?php do_action( 'bsfep_before_submit', $this->id, $post_id ); ?>

            <input type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>" name="post_form_url"/>
            <input id="submit" name='bp_simple_post_form_subimitted' type="submit" value="<?php _e( 'Post', 'bp-simple-front-end-post' ); ?>"/>
        </form>
    </div>

<?php endif; ?>

