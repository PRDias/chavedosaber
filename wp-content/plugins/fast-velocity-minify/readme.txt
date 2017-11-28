=== Fast Velocity Minify ===
Contributors: Alignak
Tags: merge, combine, concatenate, YUI Compressor, Google Closure, PHP Minify, CSS, javascript, JS, minification, minify, optimization, optimize, stylesheet, aggregate, cache, CSS, html, minimize, pagespeed, performance, speed, GTmetrix, pingdom
Requires at least: 4.3
Stable tag: 1.3.1
Tested up to: 4.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Improve your speed score on GTmetrix, Pingdom Tools and Google PageSpeed Insights by merging and minifying CSS, JavaScript and HTML. 


== Description ==

This plugin reduces HTTP requests by merging CSS & Javascript files into groups of files, while attempting to use the least amount of files as possible. It minifies CSS using PHP only and gives you the option to minify JS files with YUI Compressor or Google Closure (if you have java available) with fallback to PHP Minify (no extra requirements).

Minification is done in real time and done on the frontend only leading to a slower first request. Once the first request is done the other pages that require the same set of minified CSS and JavaScript will be served with a static cache file. We have a "preload cache" option planned for the future to avoid the first request being slow. 

Fast Velocity Minify will check if any of your theme and plugins CSS or JS files are newer than the cached merged files and reprocess the minification. Example: If you update a plugin or theme that overwrites a CSS or JavaScript that is being merged by this plugin, it will automatically generate a new minified file for you in real time. There's no need to manually purge the cache when you update your plugins or themes!


= Aditional Optimization =

I can offer you aditional `custom made` optimization on top of this plugin. If you would like to hire me, please visit my profile for further information.


= Features =

*	Merge JS and CSS files into groups to reduce the number of HTTP requests
*	Google Fonts concatenation and optimization
*	Handles scripts loaded both in the header & footer separately
*	Keeps the order of the scripts even if you exclude some files from minification
*	Supports localized scripts (https://codex.wordpress.org/Function_Reference/wp_localize_script)
*	Minifies JS with YUI Compressor or Google Closure API with fallback to PHP Minify.
*	Minifies CSS with PHP only, no third party software or libraries needed.
*	Option to defer JavaScript loading of the minified JS files.
*	Stores cache and minified files in the plugin directory.
*	Checks the last modified date on merged CSS and JS files so that the minified files are always up to date.
*	View the status and logs on the WordPress admin page.
*	Option to Minify HTML for further improvements.
*	Ability to turn off minification
*	Ability to turn off merging
*	Ability to manually ignore scripts or css
*	Support for conditional scripts and styles
*	Support for multisite installations
*	Support for gzip_static on Nginx


= Notes =
*	The JavaScript minification is by [YUI Compressor](https://github.com/yui/yuicompressor)
*	The alternative JavaScript minification is by [Google Closure API](https://developers.google.com/closure/)
*	The fallback JavaScript minification is by [PHP Minify](https://github.com/matthiasmullie/minify)
*	Compatible with Nginx, HHVM and PHP 7. 
*	PHP 5.6 or above, with Zend Opcache is recommended.


== Installation ==

1. Upload the `fastvelocity-min` folder to the `/wp-content/plugins/` directory or upload the zip within WordPress
2. Activate the plugin through the `Plugins` menu in WordPress
3. Configure the options under: `Settings > Fast Velocity Minify` and that's it.
4. Check that you have permissions for PHP to call the jar files if the JS minification fails and you have PHP exec and Java available.


== Screenshots ==

1. You can view the logs and purge individual merged files.
2. The settings page. By default, all settings are already optimized.


== Frequently Asked Questions ==

= Why are there several js and css files listed on the logs page? =

Those files are created whenever a new set of javascript or css files are found on your front end. This is because your plugins and themes might load different scripts and css per page, post, category, tag, homepage or even custom post types. If you always load the exact same css and javascript in every page on your site, you won't see as many files.

= What about logged in users? =

When you are logged in as admin for example, you might have the admin bar on top or other custom css and javascript that can be added by WordPress to the frontend. In that case, a new set of minified files would be created for logged in users only. This is needed because the plugin tries to have as less files as possible on each page in the frontend.

= Can I update other plugins and themes? =

On every page load we compare the `last modified date` of all the original needed JavaScript and CSS with the generated cache. This is a fast and normal operation in PHP without any performance impact on the loading time. When you upgrade something on your WordPress site that overwrites any CSS or JavaScript file needed for the frontend, our plugin will automatically invalidate the cache for that specific page and regenerate a new one for you.

= Do I need to purge the cache directory? =

If you are changing to other plugins and themes, then you should purge the files once in a while. All else being equal, when the site invalidates a cache file it deletes the minified files automatically. When you add or remove plugins that add /remove CSS and JavaScript to the frontend, the `needed files signature for that pageview` will change, thus avoiding our cache invalidation checks. This happens, because it's considered a new set of files that the plugin needs to merge and minify, rather than an update to the same set of files.

= Is it compatible with other caching plugins? =

Yes. The only time it would stop working is if you `manually purge` the minified files on our plugin. When you use a third party caching plugin, your HTML is cached and therefore will keep pointing to the old minified files. If you ever encounter such situation, simply delete the cache on your caching plugin.

= Is it resource intensive, or will it use too much CPU on my shared hosting plan? =

No it's not. The generation of the minified files takes only a couple of seconds and it's done only once per page (and only if needed). After that all requests will be served a static file from the cache directory. There is no PHP involved on serving the minified files after the first request.

= Is it compatible with multisites? =

It should be compatible as it generates a new cache file for every different requirements it finds. 

= Is it compatible with Adsense and other ad networks? =

This plugin should be compatible with any add network, depending on how you're loading the ads into the site. We only merge and minify css and javascript files enqueued in the header and footer, which would exclude any ads, as they are usually inserted (not enqueued) directly on the template. Therefore, you should have no problems if you are following the normal adsense implementation of copy pasting your code into the theme.  

= After installing, why did my site became slow? =

Please note that the cache regeration happen's once per page and only if the required CSS + JS files change. If you need the same set of CSS and JS files in every page, then the cache will only be generated once and reused for all other pages. If you have different CSS + JS files being loaded in every page, then the first view for those pages will be slower... however, the second and further requests will be much faster.

= Whats the intermediate minification cache for? =

Each page on your site requires a possibly different set of JS and CSS files and while merging is fast, minification is slow. To speed up things, whenever we find a JS or CSS file that needs minification, we minify it and keep it on the intermediate cache. The next time a page requires any of those files, it doesn't need to minify those again thus speeding things up by minifying only the new required files.

= How do I use the precompressed files with gzip_static on Nginx? =

When we merge and minify the css and js files, we also create a `.gz` file to be used with `gzip_static` on Nginx. You need to enable this feature on your Nginx configuration file if you want to make use of it. If you're upgrading from 1.2.3 or earlier, you may need to clear the plugin cache.

= Where can I get support? =

You can get support on the official wordpress plugin page at: https://wordpress.org/support/plugin/fast-velocity-minify

= How can I donate to the plugin author? =

If you would like to donate any amount to the plugin author (thank you in advance), you can do it via Paypal at https://goo.gl/vpLrSV



== Changelog ==

= 1.3.1 [2016.10.31] =
* fixed some other reported notices that are visible when debug mode is enabled

= 1.3.0 [2016.10.23] =
* fixed a few notices that are visible when debug mode is enabled
* fixed keeping of CSS handles with empty src for better dependency management (JS processing already does this)

= 1.2.9 [2016.10.22] =
* added merging of "screen" and "all" CSS mediatypes
* added auto reordering of CSS files by mediatype
* added support to keep order of CSS for better compatibility
* fixed a bug with CSS where "print" mediatypes were being merged together with "all", breaking some designs
* added an option to remove Print Style Sheets (CSS files of mediatype "print" for printers)
* changed the defer JS files logic in order to skip files that are on the ignore list
* added option to force JS files defer even if they are on the ignore list
* improved some descriptions for some options in the settings page
* added option to remove emojis support

= 1.2.8 [2016.10.21] =
* added font awesome optimization and cdn delivery (if used by your theme)
* load only one font awesome css file, even when your theme or plugins enqueue multiple files
* replaced the HTML minification engine with mrclay minify (same as the autoptimize plugin, w3 total cache and a few other popular plugins)

= 1.2.7 [2016.10.18] =
* fixed CSS minification not working on some cases after the latest update

= 1.2.6 [2016.10.16] =
* improved html minification speed and compatibility
* fixed a PHP 7 compatibility issue on PHP Minify JS minify script
* fixed the JS defer option for scripts that already have defer or async, such as the AMP plugin

= 1.2.5 [2016.10.03] =
* reverted back the PHP Minify library due to a bug

= 1.2.4 [2016.10.03] =
* added support for `gzip_static` on nginx for cached files
* updated PHP Minify with today's release date
* added donation link to the FAQ's section

= 1.2.3 [2016.10.02] =
* google fonts related bugfixes
* improved help section

= 1.2.2 [2016.09.22] =
* bugfixes

= 1.2.1 [2016.09.22] =
* more improvements on multisite installations

= 1.2.0 [2016.09.22] =
* improved compatibility with multisite instalations

= 1.1.9 [2016.09.21] =
* fixed a fatal error on versions older than PHP 5.5 (note that our recommended PHP version is still PHP 5.6+)

= 1.1.8 [2016.09.20] =
* fixed support for custom directory names on wordpress (wp-content, plugins, etc)
* bug fixes

= 1.1.7 [2016.08.26] =
* fixed a compatibility issue with SunOS and Solaris systems

= 1.1.6 [2016.08.24] =
* changed the CSS minification to PHP Minify for better compatibility with the calc() expression and others.
* fixed a bug for when wp-content has been renamed to something else

= 1.1.5 [2016.08.21] =
* better support for third party cache plugins
* bug fixes

= 1.1.4 [2016.08.20] =
* added logic for when wp-content has been renamed to something else
* small improvements

= 1.1.3 [2016.08.05] =
* minor bug fix on the defer javascript option

= 1.1.2 [2016.08.02] =
* added option to force the use of PHP Minify for JS minification instead of YUI or Google Closure

= 1.1.1 [2016.08.01] =
* added PHP Minify [2016.08.01] as fallback for JS files again
* PHP Minify issues (white screen on PHP 7) might have been fixed
* other small bug fixes

= 1.1.0 [2016.07.11] =
* improved compatibility for PHP 7
* new location for cache and temporary files
* removed PHP Minify as fallback due to incompatibility (white screen) with some PHP 7 configurations
* Fallback method for JS files updated to merge only

= 1.0.9 [2016.07.10] =
* added new logic to group handling for better compatibility
* added an intermediate minification cache for faster performance
* added PHP Minify as fallback option for JS files
* added a local Google Closure alternative to YUI Compressor
* added help page to the plugin
* removed the Google Closure API because their rate limit can lead to incomplete minified files

= 1.0.8 [2016.07.02] =
* disabled error reporting messages
* added some extra code checks

= 1.0.7 [2016.07.01] =
* bug fixes related to warnings being displayed at the admin area

= 1.0.6 [2016.06.30] =
* fixed some header and footer scripts not being enqueued on the right place
* added a better dependency check before merging and minifying of JS files
* added more logic to keep the order of js files, when one or several of them are excluded from minification
* performance improvements and some code simplification

= 1.0.5 [2016.06.24] =
* bug fixes on the admin page log viewer

= 1.0.4 [2016.06.23] =
* added YUI Compressor for local JS minification with java (if available)
* JS minification fallback to the Google Closure API if java not available or YUI Compressor fails
* added individual cache for already minified files, so they are minified again only if that cache is older than the original files to minify

= 1.0.3 [2016.06.23] =
* removed JSrink and added back the Google Closure API for compatibility with PHP 7

= 1.0.2 [2016.06.23] =
* Fixed Google Fonts optimization
* Replaced Google Closure API with JSrink PHP library
* Reorganized inline CSS code dependencies
* New (safer) HTML minification

= 1.0.1 [2016.06.22] =
* Javascript minification fixes

= 1.0 [2016.06.19] =
* Initial Release
