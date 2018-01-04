<?php

/**
 * A Form Instance class
 *
 * Do not use it directly, instead call bp_new_simple_blog_post_form to create new instances
 * or you can create your own child class for more felxibility
 */
class BPSimpleBlogPostEditForm {

	/**
	 * A unique md5 hashed id of the post form
	 * Each post form has a unique id
	 * an unique md5ed hash of the human readable name
	 *
	 * @var string form id
	 */
	protected $id;

	protected $post_id = 0;
	/**
	 * It is the responsibility of developer to set it true or false based on whether he wants to allow the current user to post or not
	 * @var boolean
	 */
	protected $current_user_can_post = false;

	/**
	 * Which post type we want to edit/create using the current form
	 *
	 * it can be any valid post type, you can specify it while registering the from
	 *
	 * @var string post_type, defaults to post
	 */
	protected $post_type = 'post';

	/**
	 * post status after the post is submitted via front end, defaults to draft
	 *
	 * You can set it to 'publish' if you want to directly publish it
	 * It can be set via settings while registering the form
	 *
	 * @var string
	 */
	protected $post_status = 'draft';

	/**
	 * Who wrote this post?, the user_id of post autor, default to current logged in user
	 * If it is not set, the logged in user will be attributed as the author
	 * @var int
	 */
	protected $post_author = false;

	/**
	 * Supported taxonomies
	 *
	 * @var array
	 */
	protected $tax = array();

	/**
	 * Custom Fields settings
	 *
	 * @var array Mutidimensional array with custom field settings
	 *
	 */
	protected $custom_fields = array();

	protected $has_visisible_meta = null;
	/**
	 * How many uploads are allowed
	 *
	 * @todo: we need to finetune it for allowed media types?
	 *
	 * @var int
	 * @deprecated 1.2.5
	 */
	protected $upload_count = 0;

	/**
	 * Enable/Disable support for post thumbnail
	 * @var boolean
	 */
	protected $has_post_thumbnail = false;

	/**
	 * Default comment status, is it open or closed?
	 * @var string
	 */
	protected $comment_status = 'open';

	/**
	 * Should show the user option to allow comment
	 *
	 * @var boolean
	 */
	public $show_comment_option = true;

	/**
	 * Used to store error/success message
	 * @var string
	 */
	public $message = '';
	/**
	 * If given, this gets called after updating/saving post
	 *
	 * @var callable
	 */
	private $update_callback = null;
	/**
	 * Create a new instance of the Post Editor Form
	 *
	 * @param string $name name of the form
	 * @param array $settings , a multidimensional array of form settings
	 */

	/**
	 * Whether to allow upload or not?
	 * Uses the WordPress media Uploader for uploads
	 *
	 * @since 1.2.5
	 *
	 * @var boolean
	 */
	protected $allow_upload = false;

	public function __construct( $name, $settings ) {

		$this->id = md5( trim( $name ) );

		$default = array(
			'post_type'             => 'post',
			'post_status'           => 'draft',
			'tax'                   => false,
			'post_author'           => false,
			'can_user_can_post'     => false,
			'custom_fields'         => false,
			'upload_count'          => 0,
			'has_post_thumbnail'    => 1,
			'show_comment_option'   => $this->show_comment_option,
			'comment_status'        => $this->comment_status,
			'current_user_can_post' => is_user_logged_in(),
			//it may be a bad decision on my part, do we really want to allow all logged in users to post?
			'allow_upload'          => false,
		);

		$args = wp_parse_args( $settings, $default );
		extract( $args );

		$this->post_type   = $post_type;
		$this->post_status = $post_status;

		//if the author id is given
		if ( $post_author ) {
			$this->post_author = $post_author;
		} else {
			$this->post_author = get_current_user_id();
		}
		//we will process the taxonomy & meta later
		$this->tax = $tax;

		$this->custom_fields = $custom_fields;

		$this->current_user_can_post = $current_user_can_post; //we will change later for context

		//upload count is deprecated, use allow_upload instead 
		$this->upload_count = $upload_count;

		$this->allow_upload = $allow_upload;

		//back compat
		if ( ! $this->allow_upload && $this->upload_count ) {
			$this->allow_upload = true;
		}

		$this->has_post_thumbnail = $has_post_thumbnail;

		if ( ! $this->allow_upload ) {
			$this->has_post_thumbnail = false;//when upload is disabled, we don't want the featured image thingy
		}

		if ( $comment_status ) {
			$this->comment_status = $comment_status;
		}

		if ( isset( $show_comment_option ) ) {
			$this->show_comment_option = $show_comment_option;
		}
		//is update callback given?
		if ( isset( $update_callback ) ) {
			$this->update_callback = $update_callback;
		}
	}

	/**
	 * Show/Render the Post form
	 */
	public function show() {

		if ( ! $this->current_user_can_post ) {
			return;
		}

		//needed for category/term walker
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/template.php' );
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/post.php' );

		$this->load_post_form();
	}

	/**
	 * Locate and load post from
	 * we need to allow theme authors to modify it
	 * so, we will first look into the template directory and if not found, we will load it from the plugin's included file
	 *
	 */
	protected function load_post_form() {

		$post_id = $this->get_post_id();

		wp_enqueue_script( 'bsfep-js' );

		$default = array(
			'title'   => isset( $_POST['bp_simple_post_title'] ) ? $_POST['bp_simple_post_title'] : '',
			'content' => isset( $_POST['bp_simple_post_text'] ) ? $_POST['bp_simple_post_text'] : ''
		);

		if ( ! empty( $post_id ) ) {
			//should we check if current user can edit this post ?
			$post = get_post( $post_id );

			if ( $post->post_status == 'auto-draft' ) {
				$post->post_title = '';
			}

			$args = array(
				'title'   => $post->post_title,
				'content' => $post->post_content
			);

			$default = wp_parse_args( $args, $default );
		}


		extract( $default );
		//think about more flexibility her
		if ( $located = locate_template( array( 'feposting/form.php' ), false ) ) {
			require_once $located;//we may load it any no. of times
		} else {
			include bp_simple_blog_post_helper()->get_path() . 'form.php';
		}
	}

	/**
	 * Get associated term ids for a post/post type
	 *
	 * @param array $object_ids
	 * @param string $tax
	 *
	 * @return array of term_ids
	 */
	protected function get_term_ids( $object_ids, $tax ) {

		$terms    = wp_get_object_terms( $object_ids, $tax );
		$included = wp_list_pluck( $terms, 'term_id' );

		return $included;
	}

	/**
	 * Get the post id
	 * For editing, filter on the hook to return the post_id
	 * @return int
	 */
	protected function get_post_id() {

		if ( ! $this->post_id ) {
			$post          = bsfep_get_default_post_to_edit( $this->post_type, true );
			$this->post_id = $post->ID;
		}

		return apply_filters( 'bpsp_editable_post_id', $this->post_id );
	}

	/**
	 * Does the saving thing
	 */
	public function save() {

		$post_id = false;

		// Verify nonce.
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bp_simple_post_new_post_' . $this->id ) ) {
			bp_core_add_message( __( 'The Security check failed!', 'bpsfep' ), 'error' );
			return; // Do not proceed.
		}

		$post_type_details = get_post_type_object( $this->post_type );

		$title   = $_POST['bp_simple_post_title'];
		$content = $_POST['bp_simple_post_text'];
		$message = '';
		$error   = '';
		$is_new = true;

		if ( isset( $_POST['post_id'] ) ) {
			$post_id = $_POST['post_id'];
		}

		if ( ! empty( $post_id ) ) {

			$post = get_post( $post_id );
			// In future, we may relax this check.
			if ( ! ( $post->post_author == get_current_user_id() || is_super_admin() ) ) {
				$error   = true;
				$message = __( 'You are not authorized for the action!', 'bp-simple-front-end-post' );
			}

			$is_new = false; //this is an update of existing post
		}

		if ( empty( $title ) || empty( $content ) ) {

			$error   = true;
			$message = __( 'Please make sure to fill the required fields', 'bp-simple-front-end-post' );
		}

		$error = apply_filters( 'bsfep_validate_post', $error, $_POST );

		if ( $error && is_array( $error ) ) {
			$message = $error['message'];
			$error = $error['error'];
		}

		if ( ! $error ) {

			$post_data = array(
				'post_author'  => $this->post_author,
				'post_content' => $content,
				'post_type'    => $this->post_type,
				'post_status'  => $this->post_status,
				'post_title'   => $title,

			);

			if ( ! empty( $_POST['_thumbnail_id'] ) ) {
				$post_data['_thumbnail_id'] = absint( $_REQUEST['_thumbnail_id'] );
			}

			//find comment_status
			$comment_status = isset( $_POST['bp_simple_post_comment_status'] ) ? $_POST['bp_simple_post_comment_status'] : $this->comment_status;

			if ( empty( $comment_status ) && ! $post_id ) {
				$comment_status = 'closed';//user has not checked it
			}

			if ( $comment_status ) {
				$post_data['comment_status'] = $comment_status;
			}

			if ( ! empty( $post_id ) ) {
				$post_data['ID'] = $post_id;
			}

			$post_id = wp_insert_post( $post_data );
			//if everything worked fine, the post was saved
			if ( ! is_wp_error( $post_id ) ) {
				//update the taxonomy
				//currently does not check if post type is associated with the taxonomy or not
				//TODO: Make sure to check for the association of post type and category
				if ( ! empty( $this->tax ) ) {
					//if we have some taxonomy info
					//tax_slug=>tax_options set for that taxonomy while registering the form

					foreach ( $this->tax as $tax => $tax_options ) {

						$selected_terms = array();
						//get all selected terms, may be array, depends on whether a dd or checkklist
						if ( isset( $_POST['tax_input'][ $tax ] ) ) {
							$selected_terms = (array) $_POST['tax_input'][ $tax ];
						}

						//check if include is given when the form was registered and this is a subset of include
						if ( ! empty( $tax_options['include'] ) ) {

							$allowed = $tax_options['include']; //this is an array
							//check a diff of selected vs include
							$is_fake = array_diff( $selected_terms, $allowed );

							if ( ! empty( $is_fake ) ) {
								continue;
							} //we have fake input vales, do not store
						}

						//if we are here, everything is fine

						//it can still be empty, if the user has not selected anything and nothing was given
						//post to all the allowed terms
						if ( empty( $selected_terms ) && isset( $tax_options['include'] ) ) {
							$selected_terms = $tax_options['include'];
						}

						//update the taxonomy/post association

						if ( ! empty( $selected_terms ) ) {
							$selected_terms = array_map( 'intval', $selected_terms );
							wp_set_object_terms( $post_id, $selected_terms, $tax );
						}

					}//end of the loop
				}//end of taxonomy saving block


				//let us process the custom fields

				//same strategy for the custom field as taxonomy

				if ( ! empty( $this->custom_fields ) ) {
					//which fields were updated
					$updated_field = isset( $_POST['custom_fields'] ) ? (array) $_POST['custom_fields'] : array(); //array of key=>value pair

					foreach ( $this->custom_fields as $key => $data ) {
						//shouldn't we validate the data?
						$value = $this->get_validated( $key, $updated_field[ $key ], $data );

						if ( is_array( $value ) ) {
							//there were multiple values
							//delete older one if there is a post id
							//it may not be a very good idea to delete old post meta field, but we don't know the field has multiple entries or single and cann mess arounf
							if ( $post_id ) {
								delete_post_meta( $post_id, $key );
							}

							foreach ( $value as $val ) {
								add_post_meta( $post_id, $key, $val );
							}

						} else {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}//done for custom fields


				//check for upload
				//upload and save


				if ( get_post_status( $post_id ) == 'publish' ) {
					$message = sprintf( __( '%s saved and published successfully.', 'bp-simple-front-end-post' ), $post_type_details->labels->singular_name );
				} else {
					$message = sprintf( __( '%s saved as %s successfully.', 'bp-simple-front-end-post' ), $post_type_details->labels->singular_name, $this->post_status );
				}

				$message = apply_filters( 'bsfep_post_success_message', $message, $post_id, $post_type_details, $this );
				bp_core_add_message( $message, $error );

				do_action( 'bsfep_post_saved', $post_id, $is_new );

				if ( ! empty( $this->update_callback ) && is_callable( $this->update_callback ) ) {
					call_user_func( $this->update_callback, $post_id, $is_new, $this );
				}

				return;

			} else {
				$error   = true;
				$message = sprintf( __( 'There was a problem saving your %s. Please try again later.', 'bp-simple-front-end-post' ), $post_type_details->labels->singular_name );
			}
		}
		//need to refactor the message/error infor data in next release when I will be modularizing the plugin a little bit more
		if ( ! $message ) {
			$message = $this->message;
		}

		if ( $error ) {
			$error = 'error';//buddypress core_add_message does not understand boolean properly
		}

		bp_core_add_message( $message, $error );
	}

	/**
	 * Renders html for individual custom field
	 *
	 * @param type $field_data array of array(type=>checkbox/dd/input/textbox
	 * @param mixed $current_value
	 *
	 * @return string
	 */
	public function render_field( $field_data, $current_value = false ) {

		extract( $field_data );

		$current_value = esc_attr( $current_value );

		$name = "custom_fields[$key]";

		if ( $type == 'checkbox' ) {
			$name = $name . "[]";
		}

		switch ( $type ) {

			case 'textbox':
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
					$input .= "<option  " . selected( $option['value'], $current_value, false ) . "  value='" . $option['value'] . "' >{$option['label']}</option>";
				}
				$input .= "</select></label>";
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

			case 'hidden':
				$input = "<input type='hidden' class='bp-simple-front-end-post-hidden'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' />";
				break;

			default:
				$input = '';
		}

		return $input;//return html
	}

	/**
	 * Get a validated value for the custom field data
	 *
	 * @param type $key
	 * @param type $value
	 * @param type $data
	 *
	 * @return string
	 */
	public function get_validated( $key, $value, $data ) {

		extract( $data, EXTR_SKIP );
		$sanitized = '';

		switch ( $type ) {
			case 'textbox':
			case 'date':
			case 'textarea':
			case 'hidden':
				$sanitized = esc_attr( $value ); //should we escape?
				break;

			case 'radio':
			case 'select':

				foreach ( $options as $option ) {
					if ( $option['value'] == $value ) {
						$sanitized = $value;
					}

				}

				break;
			//for checkbox
			case 'checkbox':
				$vals = array();

				foreach ( $options as $option ) {//how to validate
					$vals[] = $option['value'];
				}
				$sanitized = array_diff( $vals, (array) $vals );
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

	/**
	 * Handles Upload
	 *
	 * @param type $post_id
	 * @param type $input_field_name
	 * @param type $action
	 *
	 * @return type
	 */
	public function handle_upload( $post_id, $input_field_name, $action ) {

		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		$post_data  = array();
		$override   = array( 'test_form' => false, 'action' => $action );
		$attachment = media_handle_upload( $input_field_name, $post_id, $post_data, $override );

		return $attachment;
	}

	/**
	 *
	 * @see wp-admin/includes/template.php:wp_terms_checklist
	 * modified to include categories
	 *
	 * @param int $post_id
	 * @param array $args
	 */
	public function wp_terms_checklist( $post_id = 0, $args = array() ) {

		$defaults = array(
			'descendants_and_self' => 0,
			'selected_cats'        => false,
			'popular_cats'         => false,
			'walker'               => null,
			'include'              => array(),
			'taxonomy'             => 'category',
			'checked_ontop'        => true,
		);

		extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

		if ( empty( $walker ) || ! is_a( $walker, 'Walker' ) ) {
			$walker = new BPSimplePostTermsChecklistWalker;//custom walker
		}

		$descendants_and_self = (int) $descendants_and_self;

		$args = array( 'taxonomy' => $taxonomy );

		$tax = get_taxonomy( $taxonomy );

		$args['disabled'] = false;//allow everyone to assign the tax !current_user_can($tax->cap->assign_terms);

		if ( is_array( $selected_cats ) ) {
			$args['selected_cats'] = $selected_cats;
		} elseif ( $post_id ) {
			$args['selected_cats'] = wp_get_object_terms( $post_id, $taxonomy, array_merge( $args, array( 'fields' => 'ids' ) ) );
		} else {
			$args['selected_cats'] = array();
		}

		if ( is_array( $popular_cats ) ) {
			$args['popular_cats'] = $popular_cats;
		} else {
			$args['popular_cats'] = get_terms( $taxonomy, array(
				'fields'       => 'ids',
				'orderby'      => 'count',
				'order'        => 'DESC',
				'number'       => 10,
				'hierarchical' => false
			) );
		}

		if ( $descendants_and_self ) {
			$categories = (array) get_terms( $taxonomy, array(
				'child_of'     => $descendants_and_self,
				'hierarchical' => 0,
				'hide_empty'   => 0
			) );
			$self       = get_term( $descendants_and_self, $taxonomy );

			array_unshift( $categories, $self );

		} else {
			$categories = (array) get_terms( $taxonomy, array( 'get' => 'all', 'include' => $include ) );
		}

		echo "<div class='simple-post-tax-wrap simple-post-tax-{$taxonomy}-wrap'>";
		echo "<h3>{$tax->labels->singular_name}</h3>";

		echo "<div class='simple-post-tax simple-post-tax-{$taxonomy}'>";

		echo "<ul class='simple-post-tax-check-list'>";

		if ( $checked_ontop ) {
			// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
			$checked_categories = array();
			$keys               = array_keys( $categories );

			foreach ( $keys as $k ) {

				if ( in_array( $categories[ $k ]->term_id, $args['selected_cats'] ) ) {
					$checked_categories[] = $categories[ $k ];
					unset( $categories[ $k ] );
				}
			}

			// Put checked cats on top
			echo call_user_func_array( array( $walker, 'walk' ), array( $checked_categories, 0, $args ) );
		}

		// Then the rest of them
		echo call_user_func_array( array( $walker, 'walk' ), array( $categories, 0, $args ) );

		echo '</ul></div></div>';
	}

	/**
	 * Used to generate terms dropdown
	 *
	 * @param type $args
	 *
	 * @return string
	 */

	public function list_terms_dd( $args ) {

		$defaults = array(
			'show_option_all' => 1,
			'selected'        => 0,
			'hide_empty'      => false,
			'echo'            => false,
			'include'         => false,
			'hierarchical'    => true,
			'select_label'    => false,
			'show_label'      => true,
			'child_of'        => false,
			'orderby'         => 'ID',
			'order'           => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		$excluded = false;

		if ( is_array( $selected ) ) {
			$selected = array_pop( $selected ); //in dd, we don't allow multiple values at the moment
		}

		if ( ! empty( $include ) ) {
			$excluded = array_diff( (array) get_terms( $taxonomy, array(
				'fields' => 'ids',
				'get'    => 'all'
			) ), $include );
		}

		$tax = get_taxonomy( $taxonomy );

		if ( $show_option_all ) {

			if ( ! $select_label ) {
				$show_option_all = sprintf( __( 'Select %s', 'bp-simple-front-end-post' ), $tax->labels->singular_name );
			} else {
				$show_option_all = $select_label;
			}
		}

		$always_echo = false;

		if ( empty( $name ) ) {
			$name = 'tax_input[' . $taxonomy . ']';
		}

		$info = wp_dropdown_categories( array(
			'taxonomy'        => $taxonomy,
			'hide_empty'      => $hide_empty,
			'name'            => $name,
			'id'              => 'bp-simple-post-' . $taxonomy,
			'selected'        => $selected,
			'show_option_all' => $show_option_all,
			'echo'            => false,
			'exclude'         => $excluded,
			'hierarchical'    => $hierarchical,
			'child_of'        => $child_of,
			'orderby'         => $orderby,
			'order'           => $order,
		) );

		$html = "<div class='simple-post-tax-wrap simple-post-tax-{$taxonomy}-wrap'>";

		if ( $show_label ) {
			$info = "<div class='simple-post-tax simple-post-tax-{$taxonomy}'><h3>{$tax->labels->singular_name}</h3>" . $info . "</div>";
		}

		$html = $html . $info . '</div>';

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}
	/***
	 * Some utility functions for template
	 */
	/**
	 * Has taxonomy/terms to process
	 * @return type
	 */
	public function has_tax() {

		if ( ! empty( $this->tax ) && is_array( $this->tax ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Does this form has custom fields
	 *
	 * @return boolean
	 */
	public function has_custom_fields() {

		if ( ! empty( $this->custom_fields ) ) {
			return true;
		}

		return false;
	}

	public function has_visible_meta() {

		if ( ! is_null( $this->has_visisible_meta ) ) {
			return $this->has_visisible_meta;
		}
		//check 
		$fields                 = $this->custom_fields;
		$this->has_visible_meta = false;

		foreach ( $fields as $field ) {

			if ( $field['type'] != 'hidden' ) {
				$this->has_visisible_meta = true;
				break;
			}
		}

		return $this->has_visible_meta;
	}

	/**
	 * Generate the taxonomy dd/checkbox for template
	 */
	public function render_taxonomies() {

		$post_id = $this->get_post_id();

		foreach ( (array) $this->tax as $tax => $tax_options ) {

			//something is wrong here
			/*  if(!empty($post_id))
				  $tax_options['include']=*/

			$tax_options['taxonomy'] = $tax;

			if ( isset( $tax_options['include'] ) ) {
				$tax_options['include'] = (array) $tax_options['include'];
			}

			if ( $tax_options['view_type'] && $tax_options['view_type'] == 'dd' ) {

				if ( $post_id ) {
					$tax_options['selected'] = $this->get_term_ids( $post_id, $tax );//array_pop($tax_options['include']);
				} elseif ( isset( $_POST['tax_input'][ $tax ] ) ) {
					//if this is form submit and some taxonomies were selected
					$tax_options['selected'] = $_POST['tax_input'][ $tax ];
				}

				if ( ! empty( $tax_options['include'] ) ) {
					$tax_options['show_all_terms'] = 0;
				}

				echo $this->list_terms_dd( $tax_options );

			} else {
				//for checklist

				if ( isset( $_POST['tax_input'][ $tax ] ) && ! empty( $_POST['tax_input'][ $tax ] ) ) {
					//if this is form submit and some taxonomies were selected
					$tax_options['selected_cats'] = $_POST['tax_input'][ $tax ];
				}

				if ( isset( $tax_options['child_of'] ) ) {
					$tax_options['descendants_and_self'] = $tax_options['child_of'];
				}

				$this->wp_terms_checklist( $post_id, $tax_options );

			}
			//$selected=wp_get_object_terms($ticket_id, $taxonomy,array('fields' => 'ids'));
			// $selected=  array_pop($selected);
		}

	}

	/**
	 * Support for rendering custom fields
	 *
	 */
	public function render_custom_fields() {

		$post_id = $this->get_post_id();

		foreach ( $this->custom_fields as $key => $field ) {

			$val = false;

			if ( $field['default'] ) {
				$val = $field['default'];
			}

			if ( $post_id && get_post_status( $post_id ) != 'auto-draft' ) {
				$single = true;

				if ( $field['type'] == 'checkbox' ) {
					$single = false;
				}
				$val = get_post_meta( $post_id, $key, $single );
			}

			$field['key'] = $key;

			echo $this->render_field( $field, $val );

		}
	}

	/**
	 * Get the form Id
	 *
	 * @return string md5 hashed unique form id
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Gte teh post type associated with this form
	 *
	 * @return type
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Is the upload enabled?
	 * @return boolean true if enabled else false
	 */
	public function has_upload_enabled() {
		return $this->allow_upload;
	}
}
