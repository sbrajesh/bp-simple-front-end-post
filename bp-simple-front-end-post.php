<?php

/*
  Plugin Name: BP Simple Front End Post
  Plugin URI: http://buddydev.com/plugins/bp-simple-front-end-post/
  Description: provides the ability to create unlimited post forms and allow users to save the post from front end.It is much powerful than it looks.
  Version: 1.0.1
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

    private function __construct() {

        add_action('bp_init', array($this, 'load_textdomain'), 2);
    }

    /**
     * Factory method for singleton object
     * 
     */
    function get_instance() {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    //localization
    function load_textdomain() {


        $locale = apply_filters('bsfep_load_textdomain_get_locale', get_locale());

        // if load .mo file
        if (!empty($locale)) {
            $mofile_default = sprintf('%slanguages/%s.mo', plugin_dir_path(__FILE__), $locale);

            $mofile = apply_filters('bsfep_load_mofile', $mofile_default);
            // make sure file exists, and load it
            if (file_exists($mofile)) {
                load_textdomain('bsfep', $mofile);
            }
        }
    }

    function include_js() {
        
    }

    function include_css() {
        
    }

}

/*
 * Main controller class
 * stores various forms and delegates the post saving to appropriate form
 * 
 */

class BPSimpleBlogPostEditor {

    private static $instance;
    var $forms = array(); // array of Post Forms(multiple post forms)
    private $self_url;

    private function __construct() {
        $this->self_url = plugin_dir_url(__FILE__);

        //hook save action to init
        add_action('bp_ready', array($this, 'save'));
    }

    /**
     * Factory method for singleton object
     * 
     */
    function get_instance() {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Register a form
     * 
     * @param BPSimpleBlogPostEditForm $form 
     */
    public function register_form($form) {
        $this->forms[$form->id] = $form; //save/overwrite
    }

    /**
     *
     * @param string $form_name
     * @return BPSimpleBlogPostEditForm|boolean 
     */
    public function get_form_by_name($form_name) {
        $id = md5(trim($form_name));
        return $this->get_form_by_id($id);
    }

    /**
     * Returns the Form Object
     * 
     * @param string $form_id
     * @return BPSimpleBlogPostEditForm|boolean 
     */
    public function get_form_by_id($form_id) {

        if (isset($this->forms[$form_id]))
            return $this->forms[$form_id];
        return false;
    }

    /**
     * Save a post
     * 
     * Delegates the task to  BPSimpleBlogPostEditForm::save() of appropriate form(which was submitted)
     * 
     * @return type 
     */
    function save() {
        if (!empty($_POST['bp_simple_post_form_subimitted'])) {
            //yeh form was submitted
            //get form id
            $form_id = $_POST['bp_simple_post_form_id'];
            $form = $this->get_form_by_id($form_id);

            if (!$form)
                return; //we don't need to do anything
                
//so if it is a registerd form, let the form handle it

            $form->save(); //save the post and redirect properly
        }
    }

}

/**
 * A Form Instance class
 * 
 * Do not use it directly, instead call bp_new_simple_blog_post_form to create new instances
 * 
 * or you can create your own child class for more felxibility
 */
class BPSimpleBlogPostEditForm {

    // the post form
    var $id; //an unique md5ed hash of the human readable name
    var $current_user_can_post = false; // It is trhe responsibility of developer to set it true or false based on whether he wants to allow the current user to post or not
    var $post_type = 'post'; //it can be any valid post type, you can specify it while registering the from
    var $post_status = 'draft'; //what is the default post status, can be set via settings
    var $post_author = false; //If it is not set, the logged in user will be attributed as the author

    /**
     * Which categories are allowed for this form
     * just for backward compatibilit
     * we will rather use taxonomy
     * @var type 
     */
    var $allowed_categories = array(); //if there are any
    /**
     * @todo: remove in next release
     * 
     * @var type 
     */
    var $allowed_tags = array(); //not implemented, 
    /**
     * Taxonomy settings
     * 
     * @var array  Multidimensional array with details of allowed taxonomy 
     */
    var $tax = array(); //multidimensional array
    /**
     * Custom Fields settings
     * 
     * @var array Mutidimensional array with custom field settings
     *  
     */
    var $custom_fields = array(); //multidimensional array
    /**
     * How many uploads are allowed
     * 
     * @todo: we need to finetune it for allowed media types?
     *
     *  @var type 
     */
    var $upload_count = 0;

    public function __construct($name, $settings) {
        $this->id = md5(trim($name));

        $default = array('post_type' => 'post',
            'post_status' => 'draft',
            'tax' => false,
            'post_author' => false,
            'can_user_can_post' => false,
            'upload_count' => 0,
            'current_user_can_post' => is_user_logged_in() //it may be a bad decision on my part, do we really want to allow all logged in users to post?
        );

        $args = wp_parse_args($settings, $default);
        extract($args);

        $this->post_type = $post_type;
        $this->post_status = $post_status;



        if ($post_author)
            $this->post_author = $post_author;
        else
            $this->post_author = get_current_user_id();

        $this->tax = $tax;

        $this->custom_fields = $custom_fields;

        $this->current_user_can_post = $current_user_can_post; //we will change later for context

        $this->upload_count = $upload_count;
    }

    /**
     * Show Post form
     */
    function show() {
        //needed for category/term walker
        require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/template.php');
        //will be exiting post for editing or 0 for new post
        $post_id = $this->get_post_id();


        $default = array(
            'title' => '',
            'content' => ''
        );

        if (!empty($post_id)) {
            //should we check if current user can edit this post ?
            $post = get_post($post_id);
            $args = array('title' => $post->post_title,
                'content' => $post->post_content);
            $default = wp_parse_args($args, $default);
        }
        extract($default);
        //load the post form
        $this->load_post_form();
    }

    function load_post_form() {
        include plugin_dir_path(__FILE__) . 'form.php';
    }

    /**
     *
     * @param type $object_ids
     * @param type $tax
     * @return type 
     */
    function get_tax_ids($object_ids, $tax) {
        $terms = wp_get_object_terms($object_ids, $tax);
        $included = array();
        foreach ((array) $terms as $term)
            $included[] = $term->term_id;
        return $included;
    }

    /**
     * Get the post id
     * For editing, filter on the hook to return the post_id
     * @return type 
     */
    function get_post_id() {
        return apply_filters('bpsp_editable_post_id', 0);
    }

    /**
     * Does the saving thing
     */
    function save() {



        if (!wp_verify_nonce($_POST['_wpnonce'], 'bp_simple_post_new_post_' . $this->id))
            wp_die("The Security check failed!");


        $post_type_details = get_post_type_object($this->post_type);

        $title = $_POST['bp_simple_post_title'];
        $content = $_POST['bp_simple_post_text'];
        $message = '';
        $error = '';
        $post_id = $_POST['post_id'];
        if (!empty($post_id)) {
            $post = get_post($post_id);
            if (!($post->post_author == bp_loggedin_user_id() || is_super_admin())) {
                $error = true;
                $message = __('Please make sure to fill the required fields', 'bsfep');
            }
        }
        if (empty($title) || empty($content)) {
            $error = true;
            $message = __('Please make sure to fill the required fields', 'bsfep');
        }

        if (!$error) {
            $post_data = array(
                'post_author' => $this->post_author,
                'post_content' => $content,
                'post_type' => $this->post_type,
                'post_status' => $this->post_status,
                'post_title' => $title
            );
            if (!empty($post_id))
                $post_data['ID'] = $post_id;
            //EDIT

            $post_id = wp_insert_post($post_data);
            if (!is_wp_error($post_id)) {

                //update the taxonomy
                //currently does not check if post type is associated with the taxonomy or not
                //TODO: Make sure to check for the association of post type and category
                if (!empty($this->tax) && !$error) {
                    //if we have some taxonomy info
                    foreach ($this->tax as $tax => $tax_options) {
                        $selected_terms = (array) $_POST['tax_input'][$tax]; //get all selected terms, may be array, depends on whether a dd or checkklist
                        //check if include is given and this is a subset of include
                        if (!empty($tax_options['include'])) {

                            $allowed = $tax_options['include']; //this is an array
                            //check a diff of selected vs include
                            $is_fake = array_diff($selected_terms, $allowed);
                            if (!empty($is_fake))
                                continue; //we have fake input vales, do not store
                        }
                        //if we are here, everything is fine


                        if (empty($selected_terms))
                            $selected_terms = $tax_options['include']; //post to all the allowed categories

                            
//it can still be empty, if the user has not selected anything and nothing was given
                        if (!empty($selected_terms)) {
                            $selected_terms = array_map('intval', $selected_terms);
                            // print_r($selected_terms);
                            //foreach($selected_terms as $term_id){
                            wp_set_object_terms($post_id, $selected_terms, $tax);
                            //}
                        }
                        // $post_data['post_category']=$selected_cats;
                        //in future, do test for the category permitted, for now, I am leaving it
                        //foreach($selected_cats as $cat)
                        //wp_set_object_terms($post_id , (int)$cat, 'category' );
                    }
                }//end of taxonomy saving block
                //same strategy for the custom field

                if (!empty($this->custom_fields)) {
                    $updated_field = (array) $_POST['custom_fields']; //array of key=>value pair
                    // print_r($updated_field);
                    foreach ($this->custom_fields as $key => $data) {

                        //shouldn't we validate the data?
                        $value = $this->get_validated($key, $updated_field[$key], $data);

                        if (is_array($value)) {
                            //there were multiple values
                            //delete older one if there is a post id
                            if ($post_id)
                                delete_post_meta($post_id, $key);

                            foreach ($value as $val)
                                add_post_meta($post_id, $key, $val);
                        }
                        else
                            update_post_meta($post_id, $key, $value);
                    }
                }//done for custom fields
                //check for upload 
                //upload and save

                $action = 'bp_simple_post_new_post_' . $this->id;
                for ($i = 0; $i < $this->upload_count; $i++) {
                    $input_field_name = 'bp_simple_post_upload_' . $i;
                    $attachment = $this->handle_upload($post_id, $input_field_name, 'bpsfep_new_post');
                }
                do_action('bsfep_post_saved', $post_id);
                $message = sprintf(__('%s Saved as %s successfully.', 'bsfep'), $post_type_details->labels->singular_name, $this->post_status);
                $message = apply_filters('bsfep_post_success_message', $message, $post_id, $post_type_details, $this);
            } else {
                $error = true;
                $message = sprintf(__('There was a problem saving your %s. Please try again later.', 'bsfep'), $post_type_details->labels->singular_name);
            }
        }

        bp_core_add_message($message, $error);
    }

    function render_field($field_data, $current_value=false) {
        extract($field_data);
        $current_value = esc_attr($current_value);

        $name = "custom_fields[$key]";
        if ($type == 'checkbox')
            $name = $name . "[]";

        switch ($type) {
            case 'textbox':
                $input = "<label>{$label}<input type='text' name='{$name}' id='custom-field-{$key}' value='{$current_value}' /></label>";
                break;

            case 'textarea':
                $input = "<label>{$label}</label><textarea  name='{$name}' id='custom-field-{$key}' >{$current_value}</textarea>";
                break;


            case 'radio':
                $input = "<label>{$label}</label>";
                foreach ($options as $option)
                    $input.="<label>{$option['label']}<input type='radio' name='{$name}' " . checked($option['value'], $current_value, false) . "  value='" . $option['value'] . "' /></label>";

                break;

            case 'select':
                $input = "<label>{$label}<select name='{$name}' id='custom-field-{$key}'>";
                foreach ($options as $option)
                    $input.="<option  " . selected($option['value'], $current_value, false) . "  value='" . $option['value'] . "' >{$option['label']}</option>";

                $input.="</select></label>";
                break;

            case 'checkbox':
                $input = "<label>{$label}</label>";
                foreach ($options as $option)
                    $input.="<label>{$option['label']}<input type='checkbox' name='{$name}' " . checked($option['value'], $current_value, false) . "  value='" . $option['value'] . "' /></label>";

                break;

            case 'date':
                $input = "<label>{$label}<input type='text' class='bp-simple-front-end-post-date'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' /></label>";
                break;
            case 'hidden':
                $input = "<input type='hidden' class='bp-simple-front-end-post-hidden'  id='custom-field-{$key}' name='{$name}' value='{$current_value}' />";
                break;

            default:
                $input = '';
        }
        return $input;
    }

    function get_validated($key, $value, $data) {

        extract($data, EXTR_SKIP);
        $sanitized = '';

        switch ($type) {
            case 'textbox':
            case 'date':
            case 'textarea':
            case 'hidden':
                $sanitized = $value; //should we escape?   
                break;


            case 'radio':
            case 'select':

                foreach ($options as $option)
                    if ($option['value'] == $value)
                        $sanitized = $value;

                break;




            case 'checkbox':
                $vals = array();
                foreach ($options as $option)//how to validate
                    $vals[] = $option['value'];

                $sanitized = array_diff($vals, (array) $vals);

                break;


            default:
                $sanitized = '';
                break;
        }
        return $sanitized;
    }

    function handle_upload($post_id, $input_field_name, $action) {
        require_once( ABSPATH . 'wp-admin/includes/admin.php' );
        $post_data = array();
        $override = array('test_form' => false, 'action' => $action);
        $attachment = media_handle_upload($input_field_name, $post_id, $post_data, $override);

        return $attachment;
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
            'include' => array(),
            'taxonomy' => 'category',
            'checked_ontop' => true
        );
        extract(wp_parse_args($args, $defaults), EXTR_SKIP);

        if (empty($walker) || !is_a($walker, 'Walker'))
            $walker = new SimplePostTermsChecklistWalker;

        $descendants_and_self = (int) $descendants_and_self;

        $args = array('taxonomy' => $taxonomy);

        $tax = get_taxonomy($taxonomy);
        $args['disabled'] = !current_user_can($tax->cap->assign_terms);

        if (is_array($selected_cats))
            $args['selected_cats'] = $selected_cats;
        elseif ($post_id)
            $args['selected_cats'] = wp_get_object_terms($post_id, $taxonomy, array_merge($args, array('fields' => 'ids')));
        else
            $args['selected_cats'] = array();

        if (is_array($popular_cats))
            $args['popular_cats'] = $popular_cats;
        else
            $args['popular_cats'] = get_terms($taxonomy, array('fields' => 'ids', 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false));

        if ($descendants_and_self) {
            $categories = (array) get_terms($taxonomy, array('child_of' => $descendants_and_self, 'hierarchical' => 0, 'hide_empty' => 0));
            $self = get_term($descendants_and_self, $taxonomy);
            array_unshift($categories, $self);
        } else {

            $categories = (array) get_terms($taxonomy, array('get' => 'all', 'include' => $include));
        }

        echo "<div class='simple-post-tax'>
        <h3>{$tax->labels->singular_name}</h3>";
        echo "<ul class='simple-post-tax-check-list'>";

        if ($checked_ontop) {
            // Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
            $checked_categories = array();
            $keys = array_keys($categories);

            foreach ($keys as $k) {
                if (in_array($categories[$k]->term_id, $args['selected_cats'])) {
                    $checked_categories[] = $categories[$k];
                    unset($categories[$k]);
                }
            }

            // Put checked cats on top
            echo call_user_func_array(array(&$walker, 'walk'), array($checked_categories, 0, $args));
        }
        // Then the rest of them
        echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
        echo "</ul></div>";
    }

    function list_terms_dd($args) {
        $defaults = array(
            'show_option_all' => 1,
            'selected' => 0,
            'hide_empty' => false,
            'echo' => false,
            'include' => false,
            'hierarchical' => true,
            'select_label' => false,
            'show_label' => true
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);
        $excluded = false;
        if (is_array($selected))
            $selected = array_pop($selected); //in dd, we don't allow multipl evaues at themoment

        if (!empty($include))
            $excluded = array_diff((array) get_terms($taxonomy, array('fields' => 'ids', 'get' => 'all')), $include);
        $tax = get_taxonomy($taxonomy);
        if ($show_option_all) {

            if (!$select_label)
                $show_option_all = sprintf(__('Select %s', 'buggm'), $tax->labels->singular_name);
            else
                $show_option_all = $select_label;
        }
        $always_echo = false;
        if (empty($name))
            $name = 'tax_input[' . $taxonomy . ']';


        $info = wp_dropdown_categories(array('taxonomy' => $taxonomy, 'hide_empty' => $hide_empty, 'name' => $name, 'id' => 'bp-simple-post-' . $taxonomy, 'selected' => $selected, 'show_option_all' => $show_option_all, 'echo' => false, 'excluded' => $excluded, 'hierarchical' => $hierarchical));

        if ($show_label)
            $info = "<div class='simple-post-tax'><h3>{$tax->labels->singular_name}</h3>" . $info . "</div>";

        if ($echo)
            echo $info;
        else
            return $info;
    }

}

//end of class

/**
 * A Taxonomy Walker class to fix the input name of the taxonomy terms
 * 
 */
class BPSimplePostTermsChecklistWalker extends Walker {

    var $tree_type = 'category';
    var $db_fields = array('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

    function start_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el(&$output, $category, $depth, $args, $id = 0) {
        extract($args);
        if (empty($taxonomy))
            $taxonomy = 'category';


        $name = 'tax_input[' . $taxonomy . ']';

        $class = in_array($category->term_id, $popular_cats) ? ' class="popular-category"' : '';
        $output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked(in_array($category->term_id, $selected_cats, false), true, false) . disabled(empty($args['disabled']), false, false) . ' /> ' . esc_html(apply_filters('the_category', $category->name)) . '</label>';
    }

    function end_el(&$output, $category, $depth = 0, $args = array()) {
        $output .= "</li>\n";
    }

}

//API for general use
/**
 * Create and Register a New Form Instance, Please make sure to call it before bp_init action to make the form available to the controller logic
 * @param type $form_name:string, a unique name, It can contain letters or what ever eg. my_form or my form or My Form 123
 * @param type $settings:array,It governs what is shown in the form and how the form will be handled, possible values are
 *  array('post_type'=>'post'|'page'|valid_post_type,'post_status'=>'draft'|'publish'|'valid_post_status','show_categories'=>true|false,'current_user_can_post'=>true|false
 * @return BPSimpleBlogPostEditForm 
 */
function bp_new_simple_blog_post_form($form_name, $settings) {

    $form = new BPSimpleBlogPostEditForm($form_name, $settings);
    $editor = BPSimpleBlogPostEditor::get_instance();
    $editor->register_form($form);

    return $form;
}

//get a referenace to a particulare form instance
function bp_get_simple_blog_post_form($name) {
    $editor = BPSimpleBlogPostEditor::get_instance();
    return $editor->get_form_by_name($name);
}

BPSimpleBlogPostComponent::get_instance();
?>