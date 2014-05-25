bp-simple-front-end-post
========================

A simple front end posting plugin for BuddyPress/WordPress

**How to Use this plugin**

If you want to create a form and show it on Front end, You will need to create and Register a form as follows.

**Register a from on/before bp_init action using**
`$form= bp_new_simple_blog_post_form(\'form_name\',$settings);
// Please see @ bp_new_simple_blog_post_form for the settings options`

Now, you can retrieve this form anywhere and render it as below

`$form=bp_get_simple_blog_post_form(\'form_name\');
if($form)
$form->show(); // shows this post form`
