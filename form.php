<?php if($this->current_user_can_post ): ?>

        <div class="bp-simple-post-form">
       
        <form class="standard-form bp-simple-post-form"  method="post" action=""  enctype="multipart/form-data">
            
            <!-- do not modify/remove the line blow -->
            <input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id;?>" />
            <?php wp_nonce_field( 'bp_simple_post_new_post_'.$this->id ); ?>
            <input type="hidden" name="action" value="bp_simple_post_new_post_<?php echo $this->id;?>" />
            <?php if($post_id):?>
                <input type="hidden" name="post_id" value="<?php echo $post_id;?>" />
            <?php endif;?>
                    
             <!-- you can modify these, just make sure to not change the name of the fields -->
             
             <label for="bp_simple_post_title"><?php _e('Title:','bsfep');?>
                <input type="text" name="bp_simple_post_title"  tabindex="1" value="<?php echo $title;?>"/>
             </label>
            
             <label for="bp_simple_post_text" ><?php _e('Post:','bsfep');?>
                <textarea name="bp_simple_post_text" id="bp_simple_post_text" tabindex="2" ><?php echo $content; ?></textarea>
             </label>
             <!--- generating the file upload box -->
            <?php if($this->upload_count):?>
            
                <label> <?php _e('Uploads','bsfep');?></label>
                
                <div class="bp_simple_post_uploads_input">
                 <?php for($i=0;$i<$this->upload_count;$i++):?>
                    <label><input type="file" name="bp_simple_post_upload_<?php echo $i;?>" /></label>
                <?php endfor;?>
               
                </div>
            <?php endif;?>
                         
            <?php if($this->has_tax()):?>
                <?php $this->render_taxonomies();?>
            <?php endif;?>   
           
            <?php //custom fields ?>
           <?php if($this->has_custom_fields()):?>
           <?php echo "<div class='simple-post-custom-fields'>";?>     
                <h3>Extra Info</h3>
                   <?php $this->render_custom_fields();?>
            <?php echo "</div>";?>    
           <?php endif;?>     
                
          
                
            <input  type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>" name="post_form_url"  />

            <input id="submit" name='bp_simple_post_form_subimitted' type="submit" value="<?php _e('Post','bsfep');?>" />

           
        </form>
        </div>
      <?php
        
    endif;
    ?>