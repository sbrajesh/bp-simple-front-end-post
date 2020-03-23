<?php
/**
 * Function.
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;
/**
 * Create and Register a New Form Instance, Please make sure to call it before bp_init action to make the form available to the controller logic
 *
 * @param string $form_name A unique name, It can contain letters or what ever eg. my_form or my form or My Form 123.
 * @param array  $settings It governs what is shown in the form and how the form will be handled, possible values are
 *      array('post_type'=>'post'|'page'|valid_post_type,'post_status'=>'draft'|'publish'|'valid_post_status','show_categories'=>true|false,'current_user_can_post'=>true|false).
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
 * Get a reference to a particular form instance
 *
 * @param string $name form name.
 *
 * @return BPSimpleBlogPostEditForm
 */
function bp_get_simple_blog_post_form( $name ) {

	$editor = BPSimpleBlogPostEditor::get_instance();

	return $editor->get_form_by_name( $name );
}

/**
 *  Get a reference to a particular form instance by hashed id
 *
 * @param string $form_id form id.
 *
 * @return BPSimpleBlogPostEditForm
 */
function bp_get_simple_blog_post_form_by_id( $form_id ) {

	$editor = BPSimpleBlogPostEditor::get_instance();

	return $editor->get_form_by_id( $form_id );
}

/**
 * Default post information to use when populating the "Write Post" form.
 * A clone of get_default_post_to_edit ( wp-admin/includes/post.php
 *  *
 *
 * @param string $post_type Optional. A post type string. Default 'post'.
 * @param bool   $create_in_db Optional. Whether to insert the post into database. Default false.
 *
 * @return WP_Post Post object containing all the default post data as attributes
 */
function bsfep_get_default_post_to_edit( $post_type = 'post', $create_in_db = false ) {
	$post_title = '';
	if ( ! empty( $_REQUEST['post_title'] ) ) {
		$post_title = esc_html( wp_unslash( $_REQUEST['post_title'] ) );
	}

	$post_content = '';
	if ( ! empty( $_REQUEST['content'] ) ) {
		$post_content = esc_html( wp_unslash( $_REQUEST['content'] ) );
	}

	$post_excerpt = '';
	if ( ! empty( $_REQUEST['excerpt'] ) ) {
		$post_excerpt = esc_html( wp_unslash( $_REQUEST['excerpt'] ) );
	}

	if ( $create_in_db ) {
		$post_id = wp_insert_post( array(
			'post_title'  => __( 'Auto Draft', 'bp-simple-front-end-post' ),
			'post_type'   => $post_type,
			'post_status' => 'auto-draft',
			'post_author' => get_current_user_id(),
		) );

		$post = get_post( $post_id );

		if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) && get_option( 'default_post_format' ) ) {
			set_post_format( $post, get_option( 'default_post_format' ) );
		}
	} else {
		$post                 = new stdClass;
		$post->ID             = 0;
		$post->post_author    = '';
		$post->post_date      = '';
		$post->post_date_gmt  = '';
		$post->post_password  = '';
		$post->post_name      = '';
		$post->post_type      = $post_type;
		$post->post_status    = 'auto-draft';
		$post->to_ping        = '';
		$post->pinged         = '';
		$post->comment_status = get_default_comment_status( $post_type );
		$post->ping_status    = get_default_comment_status( $post_type, 'pingback' );
		//$post->post_pingback = get_option( 'default_pingback_flag' );
		//$post->post_category = get_option( 'default_category' );
		$post->page_template = 'default';
		$post->post_parent   = 0;
		$post->menu_order    = 0;
		$post                = new WP_Post( $post );
	}

	/**
	 * Filter the default post content initially used in the "Write Post" form.
	 *
	 * @param string $post_content Default post content.
	 * @param WP_Post $post Post object.
	 */
	$post->post_content = apply_filters( 'default_content', $post_content, $post );

	/**
	 * Filter the default post title initially used in the "Write Post" form.
	 *
	 * @param string $post_title Default post title.
	 * @param WP_Post $post Post object.
	 */
	$post->post_title = apply_filters( 'default_title', $post_title, $post );

	/**
	 * Filter the default post excerpt initially used in the "Write Post" form.
	 *
	 * @param string $post_excerpt Default post excerpt.
	 * @param WP_Post $post Post object.
	 */
	$post->post_excerpt = apply_filters( 'default_excerpt', $post_excerpt, $post );

	return $post;
}

/**
 * Get a validated value for the custom field data
 *
 * @param string $key name.
 * @param mixed  $value value to validate.
 * @param array  $data other details.
 *
 * @return string
 */
function bpsfep_get_validate_value( $key, $value, $data ) {

	$type    = isset( $data['type'] ) ? $data['type'] : '';
	$options = isset( $data['options'] ) ? $data['options'] : '';

	$sanitized = '';

	switch ( $type ) {
		case 'textbox':
		case 'text':
		case 'date':
		case 'textarea':
		case 'hidden':
			$sanitized = esc_attr( $value ); // should we escape?
			break;

		case 'radio':
		case 'select':
			foreach ( $options as $option ) {
				if ( $option['value'] == $value ) {
					$sanitized = $value;
				}
			}

			break;
		// for checkbox.
		case 'checkbox':
			$vals = array();

			foreach ( $options as $option ) {// how to validate?
				$vals[] = $option['value'];
			}
			$sanitized = array_intersect( $vals, (array) $value );
			break;

		case 'url':
			$sanitized = esc_url( $value );
			break;

		case 'number':
			$sanitized = intval( $value );
			break;
		default:
			$sanitized = '';
			break;
	}

	return $sanitized;
}

function bsfep_get_field_render_html( $field_data, $current_value = false, $parent_name = 'custom_fields' ) {
	$key   = isset( $field_data['key'] ) ? $field_data['key'] : '';
	$label = isset( $field_data['label'] ) ? $field_data['label'] : '';

	$type    = isset( $field_data['type'] ) ? $field_data['type'] : '';
	$options = isset( $field_data['options'] ) ? $field_data['options'] : '';

	$current_value = esc_attr( $current_value );

	$name = "{$parent_name}[{$key}]";

	if ( 'checkbox' === $type ) {
		$name = $name . '[]';
	}

	switch ( $type ) {

		case 'textbox':
		case 'text':
			$input = "<label>{$label}<input type='text' name='{$name}' id='custom-field-{$key}' value='{$current_value}' /></label>";
			break;

		case 'textarea':
			$input = "<label>{$label}</label><textarea  name='{$name}' id='custom-field-{$key}' >{$current_value}</textarea>";
			break;

		case 'radio':
			$input = "<label>{$label}</label>";

			foreach ( $options as $option ) {
				$input .= "<label>{$option['label']}<input type='radio' name='{$name}' " . checked( $option['value'], $current_value, false ) . "  value='" . $option['value'] . "' /></label>";
			}
			break;

		case 'select':
			$input = "<label>{$label}<select name='{$name}' id='custom-field-{$key}'>";

			foreach ( $options as $option ) {
				$input .= '<option  ' . selected( $option['value'], $current_value, false ) . "  value='" . $option['value'] . "' >{$option['label']}</option>";
			}
			$input .= '</select></label>';
			break;

		case 'checkbox':
			$input = "<label>{$label}</label>";

			foreach ( $options as $option ) {
				$input .= "<label>{$option['label']}<input type='checkbox' name='{$name}' " . checked( $option['value'], $current_value, false ) . "  value='" . $option['value'] . "' /></label>";
			}

			break;

		case 'date':
			$input = "<label>{$label}<input type='text' class='bp-simple-front-end-post-date'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' /></label>";
			break;

		case 'url':
			$input = "<label>{$label}<input type='text' class='bp-simple-front-end-post-url'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' /></label>";
			break;

		case 'image':
		case 'file':
			$input = "<label>{$label}<input type='file' class='bp-simple-front-end-post-file'  id='custom-field-{$key}' name='custom-fields_{$key}'/></label>";
			if ( $current_value ) {
				$input .= "<div class='bp-simple-front-end-post-file-attachments'>";
				$input .= "<a href='" . esc_url( $current_value ) . "'>" . wp_basename( $current_value ) . '</a>';
				$input .= "<label><input type='checkbox' value='1' name='{$key}_delete' >" . __( 'Delete', 'bp-simple-front-end-post' ) . '</label>';
				$input .= '</div>';
			}

			break;

		case 'hidden':
			$input = "<input type='hidden' class='bp-simple-front-end-post-hidden'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' />";
			break;

		default:
			$input = '';
	}

	return $input; // return html.
}