<?php if($this->current_user_can_post ): ?>

        <div class="bp-simple-post-form">
       
        <form class="standard-form bp-simple-post-form"  method="post" action=""  enctype="multipart/form-data">
            
            
            <input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id;?>" />
            <?php wp_nonce_field( 'bp_simple_post_new_post_'.$this->id ); ?>
            <input type="hidden" name="action" value="bp_simple_post_new_post_<?php echo $this->id;?>" />
            <?php if($post_id):?>
                <input type="hidden" name="post_id" value="<?php echo $post_id;?>" />
            <?php endif;?>
                    

             <label for="bp_simple_post_title"><?php _e('Title:','bsfep');?>
                <input type="text" name="bp_simple_post_title"  tabindex="1" value="<?php echo $title;?>"/>
             </label>
            
             <label for="bp_simple_post_text" ><?php _e('Post:','bsfep');?>
                <textarea name="bp_simple_post_text" id="bp_simple_post_text" tabindex="2" ><?php echo $content; ?></textarea>
             </label>
            <?php if($this->upload_count):?>
            
                <label> <?php _e('Uploads','bsfep');?></label>
                
                <div class="bp_simple_post_uploads_input">
                 <?php for($i=0;$i<$this->upload_count;$i++):?>
                    <label><input type="file" name="bp_simple_post_upload_<?php echo $i;?>" /></label>
                <?php endfor;?>
               
                </div>
            <?php endif;?>
             <?php
             //for taxonomy
             
                          
             ?>
            <?php if(!empty($this->tax)&&is_array($this->tax)):?>
               
            <?php foreach((array)$this->tax as $tax=>$tax_options):?>
                    <?php
                    if(!empty($post_id))
                        $tax_options['include']=$this->get_tax_ids($post_id,$tax);
                    
                     $tax_options['taxonomy']=$tax;
                     $tax_options['include']=(array)$tax_options['include'];

                    if($tax_options['view_type']&&$tax_options['view_type']=='dd'):


                                if($post_id){
                                    
                                   
                                    $tax_options['selected']=array_pop($tax_options['include']);
                                }
                                    

                                  if(!empty($tax_options['include'])){

                                    $tax_options['show_all_terms']=0;   
                                  }
                                    echo $this->list_terms_dd($tax_options);
                    else:?>
                         
                   
                    <?php
                        $this->wp_terms_checklist($post_id,$tax_options);



                    endif;
                //$selected=wp_get_object_terms($ticket_id, $taxonomy,array('fields' => 'ids'));
               // $selected=  array_pop($selected);
                ?>
                
                
             <?php endforeach;?>   
                
            
            <?php endif;?>   
           <?php //custom fields ?>
           <?php if(!empty($this->custom_fields)):?>
           <?php echo "<div class='simple-post-custom-fields'>";?>     
                <h3>Custom Fields</h3>
                   <?php foreach($this->custom_fields as $key=>$field):?>
                        <?php
                        $val=false;
                       
                        if($field['default'])
                            $val=$field['default'];
                       
                        if($post_id){
                            $single=true;
                            
                           if($field['type']=='checkbox')
                                $single=false;
                           $val=get_post_meta($post_id,$key,$single);
                            
                        }
                        $field['key']=$key;
                       echo  $this->render_field($field,$val);
                        ?>
                    <?php endforeach;?>
            <?php echo "</div>";?>    
           <?php endif;?>     
                
          
                
            <input checked="checked" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>" name="post_form_url" >

            <input id="submit" name='bp_simple_post_form_subimitted' type="submit" value="<?php _e('Post','bsfep');?>" />

           
        </form>
        </div>
      <?php
        
    endif;
    ?>