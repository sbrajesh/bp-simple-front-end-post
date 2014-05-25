<?php 
    if ( $this->current_user_can_post ) { 
?>
	<div class="bp-simple-post-form">
		<form class="standard-form bp-simple-post-form"  method="post" action=""  enctype="multipart/form-data">
			<!-- do not modify/remove the line blow -->
			<input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id;?>" />
			<?php wp_nonce_field( 'bp_simple_post_new_post_'.$this->id ); ?>
			<input type="hidden" name="action" value="bp_simple_post_new_post_<?php echo $this->id;?>" />
			<?php if($post_id) {?>
			<input type="hidden" name="post_id" value="<?php echo $post_id;?>" />
			<?php } ?>
	
			<?php 
			/*-----------------------------------------------------------------------------
				you can modify these, just make sure to not change the name of the fields 
			-------------------------------------------------------------------------------*/

				$title_name 	= 'bp_simple_post_title';
				$posttext_name 	= 'bp_simple_post_text';
				$featured_name 	= 'bp_simple_post_upload_thumbnail';
				$comment_name	= 'bp_simple_post_comment_status';

				$h3 				= apply_filters('bp_simple_post_title_tag', 'h3');
				$h3_class			= apply_filters('bp_simple_post_title_classes', '');
				$h3_class			= $h3_class != '' ? ' '.$h3_class : '';
				$div 				= apply_filters('bp_simple_post_line_tag', 'div');
				$bpsp_block_class 	= apply_filters('bp_simple_post_line_classes', '');
				$bpsp_block_class 	= $bpsp_block_class != '' ? ' '.$bpsp_block_class : '';
			?>
			<<?php echo $div; ?> class="bp_simple_post_block<?php echo $bpsp_block_class; ?>">
				<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><label for="<?php echo $title_name; ?>"><?php _e('Title','bsfep');?></label></<?php echo $h3; ?>>
				<input type="text" id="<?php echo $title_name; ?>" name="<?php echo $title_name; ?>"  value="<?php echo $title;?>"/>
			</<?php echo $div; ?>>

			<<?php echo $div; ?> class="bp_simple_post_block<?php echo $bpsp_block_class; ?>">
				<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><label for="<?php echo $posttext_name; ?>" ><?php _e('Post','bsfep');?></label></<?php echo $h3; ?>>
				<textarea name="<?php echo $posttext_name; ?>" id="<?php echo $posttext_name; ?>"><?php echo $content; ?></textarea>
			</<?php echo $div; ?>>

			<?php 
				// Generating the file upload box
				if ( $this->upload_count ) {
			?>
				<div class="bp_simple_post_block bp_simple_post_uploads_input<?php echo $bpsp_block_class; ?>">
					<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><?php _e('Uploads','bsfep');?></<?php echo $h3; ?>>
				<?php 
					for( $i = 0; $i < $this->upload_count; $i++ ) {
				?>
					<input type="file" name="bp_simple_post_upload_<?php echo $i;?>" />
				<?php
					}
				?>
				</div>
			<?php 
				} // end upload box

				// Generating the file upload box
				if( $this->has_post_thumbnail ) {
			?>
				<div class="bp_simple_post_block bp_simple_post_featured_image_input<?php echo $bpsp_block_class; ?>">
					<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><label for="<?php echo $featured_name; ?>"><?php _e('Featured Image','bsfep'); ?></label></<?php echo $h3; ?>>
					<input type="file" name="<?php echo $featured_name; ?>" id="<?php echo $featured_name; ?>" />
				</div>
			<?php 
				} // end post thumbnail


				// taxonomies
				if($this->has_tax()) {
			?>
				<div class="bp_simple_post_block simple-post-taxonomies-box <?php echo $bpsp_block_class; ?>">
					<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><?php echo __('Categories','bsfep') ?></<?php echo $h3; ?>>
					<?php $this->render_taxonomies();?>
				</div>   
			<?php 
				} // end taxonomies

				// custom fields
				if($this->has_custom_fields()) {
			?>
				<div class="bp_simple_post_block simple-post-custom-fields<?php echo $bpsp_block_class; ?>">
					<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><?php echo __('Extra info','bsfep') ?></<?php echo $h3; ?>>
					<?php $this->render_custom_fields(); ?>
				</div>
			<?php
				} // end custom fields

				// comments
				if ( $this->show_comment_option ) {
			?>
			<div class="bp_simple_post_block simple-post-comment-option<?php echo $bpsp_block_class; ?>">
				<<?php echo $h3; ?> class="bp_simpe_post_title<?php echo $h3_class; ?>"><?php echo __('Allow Comments','bsfep') ?></<?php echo $h3; ?>>
			<?php
				$current_status = $this->comment_status;

				if ( $post_id ) {
					$post=  get_post($post_id);
					$current_status=$post->comment_status;
				}
			?>
				<label for="<?php echo $comment_name; ?>"><input id="<?php echo $comment_name; ?>" name="<?php echo $comment_name; ?>" type="checkbox" value="open" <?php echo checked('open',$current_status);?> /> <?php echo __('Yes') ?></label>
			</div><!-- .simple-post-comment-option --> 

			<?php
				} // end if $this->show_comment_option
			?>
			<input type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>" name="post_form_url" />
			<input type="submit" id="submit" name='bp_simple_post_form_subimitted'  value="<?php _e('Send post','bsfep');?>" />   
		</form>
	</div>
<?php
	}