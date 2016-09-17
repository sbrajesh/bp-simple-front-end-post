<?php

/*
 * Main controller class
 * stores various forms and delegates the post saving to appropriate form
 * 
 */

class BPSimpleBlogPostEditor {

	private static $instance;
	private $forms = array(); // array of Post Forms(multiple post forms)

	private function __construct() {

		//hook save action to init
		add_action( 'bp_ready', array( $this, 'save' ) );
	}

	/**
	 * Factory method for singleton object
	 *
	 * @return BPSimpleBlogPostEditor
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register a form
	 *
	 * @param BPSimpleBlogPostEditForm $form
	 */
	public function register_form( $form ) {
		$this->forms[ $form->get_id() ] = $form; //save/overwrite
	}

	/**
	 *
	 * @param string $form_name
	 *
	 * @return BPSimpleBlogPostEditForm|boolean
	 */
	public function get_form_by_name( $form_name ) {

		$id = md5( trim( $form_name ) );

		return $this->get_form_by_id( $id );
	}

	/**
	 * Returns the Form Object
	 *
	 * @param string $form_id
	 *
	 * @return BPSimpleBlogPostEditForm|boolean
	 */
	public function get_form_by_id( $form_id ) {

		if ( isset( $this->forms[ $form_id ] ) ) {
			return $this->forms[ $form_id ];
		}

		return false;
	}

	/**
	 * Save a post
	 *
	 * Delegates the task to  BPSimpleBlogPostEditForm::save() of appropriate form(which was submitted)
	 *
	 */
	public function save() {

		if ( ! empty( $_POST['bp_simple_post_form_subimitted'] ) ) {
			//yeh form was submitted
			//get form id
			$form_id = $_POST['bp_simple_post_form_id'];
			$form    = $this->get_form_by_id( $form_id );

			if ( ! $form ) {
				return; //we don't need to do anything
			}
			//so if it is a registerd form, let the form handle it

			$form->save(); //save the post and redirect properly
		}
	}

}
