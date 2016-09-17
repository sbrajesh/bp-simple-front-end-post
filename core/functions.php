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
    return $editor->get_form_by_id( $form_id );
}


//Helper


/**
 * Default post information to use when populating the "Write Post" form.
 * A clone of get_default_post_to_edit ( wp-admin/includes/post.php 
 *  * 
 * @param string $post_type    Optional. A post type string. Default 'post'.
 * @param bool   $create_in_db Optional. Whether to insert the post into database. Default false.
 * @return WP_Post Post object containing all the default post data as attributes
 */
function bsfep_get_default_post_to_edit( $post_type = 'post', $create_in_db = false ) {
	$post_title = '';
	if ( ! empty( $_REQUEST['post_title'] ) ) {
		$post_title = esc_html( wp_unslash( $_REQUEST['post_title'] ));
	}

	$post_content = '';
	if ( ! empty( $_REQUEST['content'] ) ) {
		$post_content = esc_html( wp_unslash( $_REQUEST['content'] ));
	}

	$post_excerpt = '';
	if ( ! empty( $_REQUEST['excerpt'] ) ) {
		$post_excerpt = esc_html( wp_unslash( $_REQUEST['excerpt'] ));
	}

	if ( $create_in_db ) {
		$post_id = wp_insert_post( array(
			'post_title'    => __( 'Auto Draft', 'bp-simple-front-end-post' ),
			'post_type'     => $post_type,
			'post_status'   => 'auto-draft',
			'post_author'   => get_current_user_id()
		) );

		$post = get_post( $post_id );

		if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) && get_option( 'default_post_format' ) ) {
			set_post_format( $post, get_option( 'default_post_format' ) );
		}

	} else {
		$post = new stdClass;
		$post->ID = 0;
		$post->post_author = '';
		$post->post_date = '';
		$post->post_date_gmt = '';
		$post->post_password = '';
		$post->post_name = '';
		$post->post_type = $post_type;
		$post->post_status = 'auto-draft';
		$post->to_ping = '';
		$post->pinged = '';
		$post->comment_status = get_default_comment_status( $post_type );
		$post->ping_status = get_default_comment_status( $post_type, 'pingback' );
		//$post->post_pingback = get_option( 'default_pingback_flag' );
		//$post->post_category = get_option( 'default_category' );
		$post->page_template = 'default';
		$post->post_parent = 0;
		$post->menu_order = 0;
		$post = new WP_Post( $post );
	}

	/**
	 * Filter the default post content initially used in the "Write Post" form.
	 * @param string  $post_content Default post content.
	 * @param WP_Post $post         Post object.
	 */
	$post->post_content = apply_filters( 'default_content', $post_content, $post );

	/**
	 * Filter the default post title initially used in the "Write Post" form.
	 *
	 * @param string  $post_title Default post title.
	 * @param WP_Post $post       Post object.
	 */
	$post->post_title = apply_filters( 'default_title', $post_title, $post );

	/**
	 * Filter the default post excerpt initially used in the "Write Post" form.
	 *
	 * @param string  $post_excerpt Default post excerpt.
	 * @param WP_Post $post         Post object.
	 */
	$post->post_excerpt = apply_filters( 'default_excerpt', $post_excerpt, $post );

	return $post;
}