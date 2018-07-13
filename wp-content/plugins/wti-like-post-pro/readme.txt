=== WTI Like Post ===
Contributors: webtechideas
Contributor's website: http://www.webtechideas.in/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_click&business=support@webtechideas.com&item_name=WTI%20Like%20Post%20PRO&return=http://www.webtechideas.com/thanks/
Tags: wp like post,wordpress like post,wp like page,wordpress like page,wplikepost,wplikepage,wti,webtechideas,wp vote page,wp vote post,wordpress vote page,wordpress vote post,thumbs up, thumbs down
Requires at least: 3.7
Tested up to: 4.0.1
Stable tag: 2.2

A smooth ajax-based thumbs up/down functionality for wordpress posts/pages

== Description ==

WTI Like Post is a plugin for adding like (thumbs up) and unlike (thumbs down) functionality for wordpress posts/pages. On admin end alongwith handful of configuration settings, it will show a list of most liked posts/pages. If you have already liked a post/page and now you dislike it, then the old voting will be cancelled and vice-versa. It also has the option to reset the settings to default if needed. You can reset the like counts for all/selected posts/pages. It comes with two widgets, one to display the most liked posts/pages for a given time range and another to show recently liked posts.

= PRO Features =
1. Ability to change your vote when multiple voting is disabled.
2. Action hook available for successful voting so that you can attach any functionality like sending thank you mail to user. There are 2 other hooks available for further customizing the message you see on page load and ajax call.
3. Functionality for viewing like, unlike, total counts for individual post on admin Most Liked Posts screen. You can also view users who voted for each of them. This works only when logged in users are allowed to vote.
4. Buddypress activity integration to show activity updates when a user likes and dislikes any content.
5. Functionality for highlighting the like/unlike icon on successful voting and page load.
6. Compatible with multisite set up.
7. Can be used with custom post types. You can control which post types to use with. You can show most liked posts based on custom post types using shortcode and widget.
8. Functionality for disabling the plugin css file so that you can use custom css from your theme.
9. Functionality for redirecting user to a specific page on successful voting.
10. Functionality for showing the like/unlike buttons for post excerpts.
11. Functionality for allowing/disallowing author to vote against own post with custom message.
12. Option to save plugin settings and table after plugin un-installation in case you need to reuse the data in future.
13. Functionality to disable auto-loading of the like/unlike buttons so that you can use the template tag <?php GetWtiLikePost()?> inside your theme or [wtilp_buttons] inside post/page content using the editor. Please note that [wtilp_buttons] is NOT a shortcode.
14. Functionality to store voting counts (like, unlike, total) as post meta which can be used to show posts sorted by voting counts. 
15. Functionality to have default message to encourage users to like posts. 
16. Functionality to show users who liked a post below the like/unlike buttons. 
17. In total 6 styles of buttons for like/unlike functionality.
18. Functionality to show like count for each post in admin post list/edit section.
19. Has 2 template files which can be used to show most liked posts throughout the site and the most liked posts by an author on author page.
20. Functionality to use texts instead of like/unlike images in case you want to have some encouraging texts which can not be conveyed using images.
21. Functionality for adding default like/unlike entries i.e. 0 for posts and pages when they are created.
22. Functionality to show most liked/unliked posts from selected categories on Most Liked/Unliked Posts widget.
23. Functionality to show posts liked/unliked by all users or the logged in user on the widget and also shortcode for showing the same on a page.
24. Wide range of time including hours to have more control on the posts shown on the widget.
25. Most Liked/Unliked Category Posts widget to show posts liked/unliked on the specific category page.
26. Functionality to show post excerpt, thumbnail on all the available widgets.

**PRO Plugin URL:** http://www.webtechideas.com/product/wti-like-post-pro/ PRO manual is available for downloading on the last section of the page.
**PRO Plugin Demo URL:** http://demowp.webtechideas.com/

= Standard features =
1. AJAX thumbs up/down functionality without refreshing your screen
2. Wide range of voting period to allow users to revote after a specific period of time
3. 3 different voting styles with 3 set of images
4. Show/hide +/- symbols before like/unlike count
5. Reset all/selected like and unlike counts
6. Shortcode for showing most liked posts and recently liked posts on a page
7. Allow or block guest users to vote
8. Custom messages
9. Show thumbs up/down functionality on pages or not
10. Exclude specific posts/pages if you do not want this functionality to be shown there
11. Show excluded posts/pages on widget section or not
12. Show the thumbs up/down functionality on top of the content or at the bottom
13. Show the thumbs up/down functionality on left of the screen or right
14. Exclude specific sections like home, archive page not to show like/dislike functionality
15. Excluded categories not to show like/dislike functionality for posts under those categories
16. Allow specific posts from excluded categories to show like/dislike functionality
17. Excluded like/dislike functionality on post/page add/edit interface
18. Enter custom title text on hovering the like/unlike images
19. English, French, Polish language files
20. Widgets to show most liked posts and recently liked posts
21. Option to save plugin settings and table even after plugin uninstallation which will make upgradation smooth

**Note:**
* In case you are upgrading, please deactivate and then activate the plugin only once after upgradation. There are some settings which come into play this way.

**Language Files:**
* en-US(english- United States)
* pl-PL(polish- Poland)

**Plugin URL:** http://www.webtechideas.com/wti-like-post-plugin/ Plugin manual is available for downloading on the last page of the plugin url.

**Author's Blog:** <a href="http://www.webtechideas.com/" target="_blank">Webtechideas</a>

**Author's Other Plugins:** <a href="http://wordpress.org/extend/plugins/wti-contact-back/" target="_blank">WTI Contact Back</a>

== Installation ==

* Download the plugin and extract it.
* Upload the directory '/wti-like-post/' to the '/wp-content/plugins/' directory.
* Activate the plugin through the 'Plugins' menu in WordPress.
* Click on 'WTI Like Post Pro' link under Settings menu to access the admin section.

== Screenshots ==

* Admin plugin link
* Plugin configuration settings
* Plugin frontend view
* Most Liked Posts admin shortcode functionality
* Showing most liked posts on a page
* Recently Liked Posts admin shortcode functionality
* Showing recently liked posts on a page
* Exlude post/page on post/page add/edit page
* Most Liked Posts Widget admin view
* Most Liked Posts Widget frontend view
* Recently Liked Posts Widget admin view
* Recently Liked Posts Widget frontend view

== Changelog ==

= 1.4 =
* Option to exclude specific sections like home, archive page not to show like/dislike functionality
* Option to excluded categories not to show like/dislike functionality for posts under those categories
* Option to allow specific posts from excluded categories to show like/dislike functionality
* Option to exclude like/dislike functionality on post/page add/edit interface
* Option to enter custom title text on hovering the like/unlike images
* Added polish language file

= 1.3 =
* Option to reset all/selected like and unlike counts
* Option to show/hide +/- symbols before like/unlike count
* Option to show selected title text on hovering the like/unlike images
* Option to show widget on multiple widget positions
* Removed live updation of like count on widget section due to limitation

= 1.2 =
* Added shortcode for showing most liked posts interms of a page. Also fixed issues with css file
* Removed the restriction on showing max 10 posts on the widget. Now you can show more number of posts
* Added fix for default wordpress table prefix

= 1.1 =
* Added 3 different voting styles with 3 sets of images

= 1.0 =
* This is the first version