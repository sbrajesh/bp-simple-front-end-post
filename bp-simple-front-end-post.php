<?php 
/*
Plugin Name: BP Simple Front End Post
Plugin URI: http://buddydev.com/plugins/bp-simple-front-end-post/
Description: provides the ability to create unlimited post forms and allow users to save the post from front end.It is much powerful than it looks.
Version: 1.0
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
class BPSimpleBlogPostComponent{
    private static $instance;
    private function __construct(){
       
        add_action('bp_init', array($this,'load_textdomain'), 2 );
    }
    
    /**
     * Factory method for singleton object
     * 
     */
    function get_instance(){
        if(!isset(self::$instance))
                self::$instance=new self();
        return self::$instance;
    }
    
   //localization
  function load_textdomain() {
      
        
        $locale = apply_filters( 'bsfep_load_textdomain_get_locale', get_locale() );
        
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slanguages/%s.mo',  plugin_dir_path(__FILE__),  $locale );
               
		$mofile = apply_filters( 'bsfep_load_mofile', $mofile_default );
		// make sure file exists, and load it
		if ( file_exists( $mofile ) ) {
			load_textdomain( 'bsfep', $mofile );
		}
	}
}

  function include_js(){
        
    }
    
   function include_css(){
        
    }  
    
}

/*
 * Main controller class
 * stores various forms and delegates the post saving to appropriate form
 */
class BPSimpleBlogPostEditor{
    
    private static $instance;
    var $forms=array();// array of Post Forms(multiple post forms)
    private $self_url;
   
    private function __construct(){
        $this->self_url=plugin_dir_url(__FILE__);
        
        //hook save action to init
        add_action('bp_init',array($this,'save'));
       

    }
    /**
     * Factory method for singleton object
     * 
     */
    function get_instance(){
        if(!isset(self::$instance))
                self::$instance=new self();
        return self::$instance;
    }
   /**
    * Register a form
    * @param type $form 
    */
   public function register_form($form){
       $this->forms[$form->id]=$form;//save/overwrite
   }
   
    /**
     * @return Form Object or false if the form was not registered 
     */
   public function get_form_by_name($form_name){
       $id=md5(trim($form_name));
      return $this->get_form_by_id($id);
  }
  
  public function get_form_by_id($form_id){
       
       if(isset ($this->forms[$form_id]))
               return $this->forms[$form_id];
       return false;
  }
  /**
   * Save a post
   * Calls the form->save() of appropriate form(which was submitted)
   * @return type 
   */ 
  function save(){
       if(!empty($_POST['bp_simple_post_form_subimitted'])){
           //yeh form was submitted
          //get form id
          $form_id=$_POST['bp_simple_post_form_id'];
          $form=$this->get_form_by_id($form_id);
         
          if(!$form)
              return;//we don't need to do anything
          $form->save();//save the post and redirect properly
          //lets get an instance of the form
          
          
       }
   }
  
    
}
/**
 * A Form Instance class
 * Do not use it directly, instead call bp_new_simple_blog_post_form to create new instances
 */
class BPSimpleBlogPostEditForm{
    // the post form
    var $id;//an unique md5ed hash of the human readable name
    var $current_user_can_post=false;// It is trhe responsibility of developer to set it true or false based on whether he wants to allow the current user to post or not
    var $post_type='post';
    var $post_status='draft';
    var $post_author=false;//If it is not set, the logged in user will be attributed as the author
    var $show_categories=true;//whether to show category or not
    var $show_tags=false;//not implemented
    var $allowed_categories=array();//if there are any
    var $allowed_tags=array();//not implemented, 
    //will include support for custom taxonomies soooooon

function __construct($name,$settings){
    $this->id=md5(trim($name));
    $default=array( 'post_type'=>'post',
                    'post_status'=>'draft',
                    'show_categories'=>true,
                    'show_tags'=>false,
                    'allowed_categories'=>array(),
                    'allowed_tags'=>array(),
                    'post_author'=>false,
                    'can_user_can_post'=>false
        );
    
    $args=wp_parse_args($settings, $default);
    extract($args);
    
    $this->post_type=$post_type;
    $this->post_status=$post_status;
    $this->show_categories=$show_categories;
    $this->show_tags=$show_tags;
    $this->allowed_categories=$allowed_categories;
    $this->allowed_tags=$allowed_tags;
    if($post_author)
        $this->post_author=$post_author;
    else
        $this->post_author=bp_loggedin_user_id ();
    
   $this->current_user_can_post=$current_user_can_post;//we will change later for context
    
}

//render form
function show() {
   //needed for category/term walker
    require_once(trailingslashit(ABSPATH).'wp-admin/includes/template.php');   
 
    if($this->current_user_can_post ): ?>

        <div class="bp-simple-post-form">
       
        <form class="standard-form bp-simple-post-form"  method="post" action="">
            
            
            <input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id;?>" />
            <?php wp_nonce_field( 'bp_simple_post_new_post_'.$this->id ); ?>
           
                    

             <label for="bp_simple_post_title"><?php _e('Title:','bsfep');?>
                <input type="text" name="bp_simple_post_title"  tabindex="1" />
             </label>
            
             <label for="bp_simple_post_text" ><?php _e('Post:','bsfep');?>
                <textarea name="bp_simple_post_text" id="bp_simple_post_text" tabindex="2" ></textarea>
             </label>   
             
            
            <?php if ($this->show_categories):?> 
                <label for="cats" id="bp_simple_post_cats"><?php _e('Category:','bsfep');?></label>
                    <ul>
                <?php
                    $this->wp_terms_checklist(false,array('include'=>$this->allowed_categories));

                ?>
              </ul>          
          <?PHP endif;?>  
                
            <input checked="checked" type="hidden" value="<?php echo $_SERVER['REQUEST_URI']; ?>" name="post_form_url" >

            <input id="submit" name='bp_simple_post_form_subimitted' type="submit" value="<?php _e('Post','bsfep');?>" />

           
        </form>
        </div>
      <?php
        
    endif;
}


function save(){
   
    if(!wp_verify_nonce($_POST['_wpnonce'],'bp_simple_post_new_post_'.$this->id))
            die("The Security check failed");
    
   $title=$_POST['bp_simple_post_title'];
   $content=$_POST['bp_simple_post_text'];
   $message='';
   $error='';
  
   if(empty($title)||empty($content)){
       $error=true;
       $message=__('Please make sure to fill the required fields','bsfep');
   }
    
   if(!$error){
        $post_data=array(
            'post_author'=> $this->post_author,
            'post_content'=>$content,
            'post_type'=>$this->post_type,
            'post_status'=>$this->post_status,
            'post_title'=> $title
            );
     if($this->show_categories&&!$error){
       $selected_cats=$_POST['post_category'];
       if(empty($selected_cats))
           $selected_cats=$this->allowed_categories;//post to all the allowed categories
         $post_data['post_category']=$selected_cats;
       //in future, do test for the category permitted, for now, I am leaving it
      //foreach($selected_cats as $cat)
       //wp_set_object_terms($post_id , (int)$cat, 'category' );

  }
    $post_id=wp_insert_post($post_data);  
    if(!is_wp_error($post_id))
        $message=sprintf(__('Post Saved as %s successfully.','bsfep'),$this->post_status);
    else{
        $error=true;
        $message=__('There was a problem saving your post. Please try again later.','bsfep');
        
    }
}
    
bp_core_add_message($message,$error);
}
//copy of 
//@see wp-admin/includes/template.php:wp_terms_checklist
//modified to include categories
function wp_terms_checklist($post_id = 0, $args = array()) {
 	$defaults = array(
		'descendants_and_self' => 0,
		'selected_cats' => false,
		'popular_cats' => false,
		'walker' => null,
                'include'=>array(),
               	'taxonomy' => 'category',
		'checked_ontop' => true
	);
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );

	if ( empty($walker) || !is_a($walker, 'Walker') )
		$walker = new Walker_Category_Checklist;

	$descendants_and_self = (int) $descendants_and_self;

	$args = array('taxonomy' => $taxonomy);

	$tax = get_taxonomy($taxonomy);
	$args['disabled'] = !current_user_can($tax->cap->assign_terms);

	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;
	elseif ( $post_id )
		$args['selected_cats'] = wp_get_object_terms($post_id, $taxonomy, array_merge($args, array('fields' => 'ids')));
	else
		$args['selected_cats'] = array();

	if ( is_array( $popular_cats ) )
		$args['popular_cats'] = $popular_cats;
	else
		$args['popular_cats'] = get_terms( $taxonomy, array( 'fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

	if ( $descendants_and_self ) {
		$categories = (array) get_terms($taxonomy, array( 'child_of' => $descendants_and_self, 'hierarchical' => 0, 'hide_empty' => 0 ) );
		$self = get_term( $descendants_and_self, $taxonomy );
		array_unshift( $categories, $self );
	} else {
           
		$categories = (array) get_terms($taxonomy, array('get' => 'all','include'=>$include));
	}

	if ( $checked_ontop ) {
		// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
		$checked_categories = array();
		$keys = array_keys( $categories );

		foreach( $keys as $k ) {
			if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
				$checked_categories[] = $categories[$k];
				unset( $categories[$k] );
			}
		}

		// Put checked cats on top
		echo call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
	}
	// Then the rest of them
	echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
}
}//end of class
/**
 * Create and Register a New Form Instance, Please make sure to call it before bp_init action to make the form available to the controller logic
 * @param type $form_name:string, a unique name, It can contain letters or what ever eg. my_form or my form or My Form 123
 * @param type $settings:array,It governs what is shown in the form and how the form will be handled, possible values are
 *  array('post_type'=>'post'|'page'|valid_post_type,'post_status'=>'draft'|'publish'|'valid_post_status','show_categories'=>true|false,'current_user_can_post'=>true|false
 * @return BPSimpleBlogPostEditForm 
 */

function bp_new_simple_blog_post_form($form_name,$settings){
    $form=new BPSimpleBlogPostEditForm($form_name,$settings);
    $editor= BPSimpleBlogPostEditor::get_instance();
    $editor->register_form($form);
    
    return $form;
    
            
}


//get a referenace to a particulare form instance
function bp_get_simple_blog_post_form($name){
     $editor=BPSimpleBlogPostEditor::get_instance();
     return $editor->get_form_by_name($name);
        
}
BPSimpleBlogPostComponent::get_instance();
?>