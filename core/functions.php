<?php


//API for general use
/**
 * Create and Register a New Form Instance, Please make sure to call it before bp_init action to make the form available to the controller logic
 * 
 * @param type $form_name:string, a unique name, It can contain letters or what ever eg. my_form or my form or My Form 123
 * 
 * @param type $settings:array,It governs what is shown in the form and how the form will be handled, possible values are
 *  array('post_type'=>'post'|'page'|valid_post_type,'post_status'=>'draft'|'publish'|'valid_post_status','show_categories'=>true|false,'current_user_can_post'=>true|false
 * 
 * @return BPSimpleBlogPostEditForm 
 */
function bp_new_simple_blog_post_form( $form_name, $settings ) {

    $form = new BPSimpleBlogPostEditForm( $form_name, $settings );
    
	$editor = BPSimpleBlogPostEditor::get_instance();
    $editor->register_form( $form );

    return $form;
}
/**
 * get a referenace to a particular form instance
 * 
 * @param string $name
 * @return BPSimpleBlogPostEditForm
 */
function bp_get_simple_blog_post_form( $name ) {
    
	$editor = BPSimpleBlogPostEditor::get_instance();
	
    return $editor->get_form_by_name($name);
}

/**
 *  get a referenace to a particular form instance by hashed id
 * 
 * @param string $form_id
 * @return BPSimpleBlogPostEditForm
 */
function bp_get_simple_blog_post_form_by_id( $form_id ) {
    $editor = BPSimpleBlogPostEditor::get_instance();
    return $editor->get_form_by_id($form_id);
}