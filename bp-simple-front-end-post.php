<?php

/*
  Plugin Name: BP Simple Front End Post
  Plugin URI: http://buddydev.com/plugins/bp-simple-front-end-post/
  Description: Provides the ability to create unlimited post forms and allow users to save the post from front end.It is much powerful than it looks.
  Version: 1.2.3
  Author: Brajesh Singh
  Author URI: http://buddydev.com/members/sbrajesh/
  License: GPL
 */
/**
 * How to Use this plugin
 * 
 * If you want to  create a form and show it on Front end, You will need to create and Register a form as follows
 * 
 * Register a from on/before bp_init action using 
 * $form= bp_new_simple_blog_post_form('form_name',$settings);// please see @ bp_new_simple_blog_post_form for the settings options
 * 
 * now, you can retrieve this form anywhere and render it as below
 * 
 * $form=bp_get_simple_blog_post_form('form_name');
 * if($form)
 *  $form->show();//show this post form
 * 
 */

/**
 * This is a helper class, adds support for localization
 */
class BPSimpleBlogPostComponent {

    private static $instance;

	private $path = '';
    private function __construct() {
		
		$this->path = plugin_dir_path( __FILE__ );
        
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 2 );
		add_action( 'plugins_loaded', array( $this, 'load' ) );
    }

    /**
     * Factory method for singleton object
     * 
     */
    public static function get_instance() {
		
        if ( ! isset( self::$instance ) )
            self::$instance = new self();
		
        return self::$instance;
    }

	public function load() {
		
		$path = $this->path;
		
		$files = array(
			'core/classes/class-terms-checklist-walker.php',
			'core/classes/class-edit-form.php',
			'core/classes/class-editor.php',
			'core/functions.php',
		);
		
		if( is_admin() )
			return ;//we don't need these in admin
		
		foreach( $files as $file ) {
			
			require_once $path . $file ;
		}
		
	}
    //localization
    public function load_textdomain() {


        $locale = apply_filters( 'bsfep_load_textdomain_get_locale', get_locale() );

        // if load .mo file
        if ( ! empty( $locale ) ) {
            $mofile_default = sprintf( '%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale );

            $mofile = apply_filters( 'bsfep_load_mofile', $mofile_default );
            // make sure file exists, and load it
            if ( file_exists( $mofile ) ) {
                load_textdomain( 'bsfep', $mofile );
            }
        }
    }

    public function include_js() {
        
    }

    public function include_css() {
        
    }
	/**
	 * Get file system path of this plugin directory
	 * 
	 * @return type
	 */
	public function get_path() {
		
		return $this->path;
	}

}


/**
 * get singleton instance
 * 
 * @return BPSimpleBlogPostComponent
 */
function bp_simple_blog_post_helper() {
	return BPSimpleBlogPostComponent::get_instance();
}
BPSimpleBlogPostComponent::get_instance();
