=== BP Simple Front-end Post ===
Contributors: Brajesh Singh, CreativeJuiz
Tags: buddypress, fron-end, post, guest, blogging
Requires at least: 3.3.1
Tested up to: 3.9.1
Stable tag: 1.2.0
License: GPLv2 or Later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides the ability to create unlimited post forms and allow users to save the post from front end.It is much powerful than it looks.

== Description ==
Provides the ability to create unlimited post forms and allow users to save the post from front end.It is much powerful than it looks.

Available translation

* English
* French (by [Geoffrey Crofte](http://creativejuiz.fr/))

**How to Use this plugin**

If you want to create a form and show it on Front end, You will need to create and Register a form as follows.

**Register a from on/before bp_init action using**
`$form= bp_new_simple_blog_post_form(\'form_name\',$settings);
// Please see @ bp_new_simple_blog_post_form for the settings options`

Now, you can retrieve this form anywhere and render it as below

`$form=bp_get_simple_blog_post_form(\'form_name\');
if($form)
$form->show(); // shows this post form`

== Installation ==
1. Upload \"bp-simple-front-end-post\" zip content to the \"/wp-content/plugins/\" directory.
1. Activate the plugin through the \"Plugins\" menu in WordPress.
1. Follow the instruction in the readme.txt file

== Frequently Asked Questions ==
= No FAQ for the moment =

== Changelog ==
= 1.2.0 =
* HTML redesign for the front-end. More efficient and usefull classes.
* Some hooks allow you to edit HTML and classes generated
* New Translation files

= 1.1.3 =

== Upgrade Notice ==
*Nothing here*