=== Advanced Comments Widget ===
Contributors: dbmartin
Tags: comments, recent-comments, widget, comment-widget
Requires at least: 4.4
Tested up to: 4.5
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A highly customizable recent comments widget with avatars and excerpts.

== Description ==

This recent comments widget provides advanced widget features for displaying comment author avatars _and_ an excerpt of their comment.

__Features__

* Select which post types to show comments for.  (You can choose either all types, or a specific type.)
* Exclude pingbacks and trackbacks.
* Set the number of comments to show.
* Set the order for displaying the comments.
* Show comment author avatar.
* Set _and preview_ avatar size.
* Show an excerpt of the comment.
* Determine the length of the excerpt.
* Select the comment list format to match your site's markup.  Choose from: `ol`, `ul`, or `div`.
* Select the comment format. Choose between `html5` or `xhtml`.

__Developer Features__

This widget was built not only with end-users in mind, but also plugin developers.  Almost every aspect of this widget is extensible through filters and action hooks.  You can even add your own form fields to the widget form!

* Need to remove a field from the widget form?  Not a problem!  Every field is passed through its own filter for easy customization.
* Need to modify the output of the widget?  Easy!  The output is passed through numerous filters, allowing you to customize the comment list to meet your project's requirements.
* For a full list of action hooks and filters, please see the plugin documentation: http://darrinb.com/plugins/advanced-comments-widget


== Installation ==

= From the WordPress.org plugin repository: =

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

= From GitHub: =

* Download the [latest stable version](https://github.com/dboutote/Advanced-Comments-Widget/archive/master.zip).
* Extract the zip folder to your plugins directory.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.


== Frequently Asked Questions ==

= Where can I find documentation? =

The plugin's official page: http://darrinb.com/plugins/advanced-comments-widget

= Can I contribute? =
Of course! Have an idea for a feature?  Reach out on the plugin's official page.  Want to dive into  the code?  Jump right in!  Feel free to submit pull requests for new ideas enhancements!


== Screenshots ==

1. Multiple widget options.
2. Preview your avatar size selection.
3. Easily create a stylish comment list with avatars and excerpts.
3. Example comment list in sidebar.


== Changelog ==

= 1.1.1 =
* added field reference to `"acw_form_field_{$name}"` filter
* added instance reference to `"acw_form_field_{$name}"` filter
* added widget reference to `"acw_form_field_{$name}"` filter

= 1.1 =
* Added support for Selective Refresh for the Customizer for WP 4.5

= 1.0 =
* Initial release
