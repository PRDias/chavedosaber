<?php
/*
Plugin Name: Fast Velocity Minify
Plugin URI: http://fastvelocity.com
Description: Improve your speed score on GTmetrix, Pingdom Tools and Google PageSpeed Insights by merging and minifying CSS and JavaScript files into groups, compressing HTML and other speed optimizations. 
Author: Raul Peixoto
Author URI: http://fastvelocity.com
Version: 1.3.1
License: GPL2

------------------------------------------------------------------------
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


# get current protocol scheme
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') { $protocol = 'https://'; } else { $protocol = 'http://'; }

# get the plugin directory and include functions
$plugindir = plugin_dir_path( __FILE__ ); # with trailing slash
include($plugindir.'inc/functions.php');

# generate cache directory
$tmpdir = $plugindir.'temp'; 
$cachedir = $plugindir.'cache';
$cachedirurl = plugins_url('cache', __FILE__ );
if(!is_dir($cachedir)) { mkdir($cachedir); }
if(!is_dir($tmpdir)) { mkdir($tmpdir); }

# get the current wordpress installation url and path
$wp_home = site_url();   # get the current wordpress installation url
$wp_domain = trim(str_ireplace($protocol, '', trim($wp_home, '/')));
$wp_home_path = ABSPATH;

# cleanup, delete any minification files older than 45 days (most probably unused files)
if ($handle = opendir($cachedir.'/')) {
while (false !== ($file = readdir($handle))) { $file = $cachedir.'/'.$file; if (is_file($file) && time() - filemtime($file) >= 86400 * 45) { unlink($file); } }
closedir($handle);
}

# default globals
$fastvelocity_min_global_js_done = array();


###########################################
# build control panel pages ###############
###########################################

# default options
$ignore = array();                   # urls to exclude for merging and minification
$disable_js_merge = false;           # disable JS merging? Default: false (if true, minification is also disabled)
$disable_css_merge = false;          # disable CSS merging? Default: false (if true, minification is also disabled)
$disable_js_minification = false;    # disable JS minification? Default: false
$disable_css_minification = false;   # disable CSS minification? Default: false
$remove_print_mediatypes = false;    # remove CSS files of "print" mediatype
$skip_html_minification = false;     # skip HTML minification? Default: false
$skip_clean_fonts = false;           # skip removing query strings from fonts? Default: false (there's a cache buster with latest plugin/theme update time)
$skip_cssorder = false;              # skip reordering CSS files by mediatype
$skip_google_fonts = false;          # skip google fonts optimization? Default: false
$skip_fontawesome_fonts = false;     # skip font awesome optimization? Default: false
$skip_emoji_removal = false;         # skip removing emoji support? Default: false
$enable_defer_js = false;            # Defer parsing of JavaScript? Default false
$enable_defer_js_ignore = false;     # Defer parsing of JavaScript on the ignore list? Default false
$use_google_closure = false;         # Use Google closure (slower) instead of YUI processor? Default false
$use_php_minify = false;             # Use PHP Minify instead of YUI or Closure? Default false

# add admin page and rewrite defaults
if(is_admin()) {
    add_action('admin_menu', 'fastvelocity_min_admin_menu');
    add_action('admin_enqueue_scripts', 'fastvelocity_min_load_admin_jscss');
    add_action('wp_ajax_fastvelocity_min_files', 'fastvelocity_min_files_callback');
    add_action('admin_init', 'fastvelocity_min_register_settings');
    register_deactivation_hook( __FILE__, 'fastvelocity_min_plugin_deactivate');
} else {
    # overwrite options from the database, false if not set
	$ignore = array_map('trim', explode(PHP_EOL, get_option('fastvelocity_min_ignore')));
	$disable_js_merge = get_option('fastvelocity_min_disable_js_merge');
	$disable_css_merge = get_option('fastvelocity_min_disable_css_merge');
	$disable_js_minification = get_option('fastvelocity_min_disable_js_minification');
	$disable_css_minification = get_option('fastvelocity_min_disable_css_minification');
	$remove_print_mediatypes = get_option('fastvelocity_min_remove_print_mediatypes'); 
	$skip_html_minification = get_option('fastvelocity_min_skip_html_minification');
	$skip_cssorder = get_option('fastvelocity_min_skip_cssorder');
	$skip_clean_fonts = get_option('fastvelocity_min_skip_clean_fonts');
	$skip_google_fonts = get_option('fastvelocity_min_skip_google_fonts');
	$skip_fontawesome_fonts = get_option('fastvelocity_min_skip_fontawesome_fonts');
	$skip_emoji_removal = get_option('fastvelocity_min_skip_emoji_removal');
	$enable_defer_js = get_option('fastvelocity_min_enable_defer_js');
	$enable_defer_js_ignore = get_option('fastvelocity_min_enable_defer_js_ignore');	
	$use_google_closure = get_option('fastvelocity_min_use_google_closure');
	$use_php_minify = get_option('fastvelocity_min_use_php_minify');
	
	# actions for frontend only
	if(!$disable_js_merge) { 
		add_action( 'wp_print_scripts', 'fastvelocity_min_merge_header_scripts', PHP_INT_MAX );
		add_action( 'wp_print_footer_scripts', 'fastvelocity_min_merge_footer_scripts', 9.999999 ); 
	}
	if(!$disable_css_merge) { 
		add_action( 'wp_print_styles', 'fastvelocity_min_merge_header_css', PHP_INT_MAX ); 
		add_action( 'wp_print_footer_scripts', 'fastvelocity_min_merge_footer_css', 9.999999 ); 
	}
	if(!$skip_emoji_removal) { 
		add_action( 'init', 'fastvelocity_min_disable_wp_emojicons' );
	}

}



# delete the cache when we deactivate the plugin
function fastvelocity_min_plugin_deactivate() { global $cachedir; if(is_dir($cachedir)) { rrmdir($cachedir); } }


# function to list all cache files
function fastvelocity_min_files_callback() {
	global $cachedir;
    if (isset($_POST['purge']) && $_POST['purge'] == 'all') { rrmdir($cachedir); } 
	else if (isset($_POST['purge'])) { 
		if ($handle = opendir($cachedir.'/')) {
		while (false !== ($file = readdir($handle))) { if (stripos($file, $_POST['purge']) !== false) { @unlink($cachedir.'/'.$file); } }
		closedir($handle);
		}
	}

	# default
    $return = array('js' => array(), 'css' => array(), 'stamp' => $_POST['stamp']);
	
	# inspect directory with opendir, since glob might not be available in some systems
	if ($handle = opendir($cachedir.'/')) {
		while (false !== ($file = readdir($handle))) {
			$file = $cachedir.'/'.$file;
			$ext = pathinfo($file, PATHINFO_EXTENSION);
            if (in_array($ext, array('js', 'css'))) {
                $log = file_get_contents($file.'.txt');
                $mincss = substr($file, 0, -4).'.min.css';
                $minjs = substr($file, 0, -3).'.min.js';
                $filename = basename($file);
                if ($ext == 'css' && file_exists($mincss)) { $filename = basename($mincss); }
                if ($ext == 'js' && file_exists($minjs)) { $filename = basename($minjs); }
				
				# get location, hash, modified date
				$info = explode('-', $filename);
				$hash = $info['1'];
                array_push($return[$ext], array('hash' => $hash, 'filename' => $filename, 'log' => $log));
            }
		}
	closedir($handle);
	}

    header('Content-Type: application/json');
    echo json_encode($return);
    wp_die();
}


# load wp-admin css and js files
function fastvelocity_min_load_admin_jscss($hook) {
	if ('settings_page_fastvelocity-min' != $hook) { return; }
	wp_enqueue_script('postbox');
    wp_enqueue_style('fastvelocity-min', plugins_url('admin.css', __FILE__));
    wp_enqueue_script('fastvelocity-min', plugins_url('admin.js', __FILE__), array(), false, true);
}


# create admin menu
function fastvelocity_min_admin_menu() {
add_options_page('Fast Velocity Minify Settings', 'Fast Velocity Minify', 'manage_options', 'fastvelocity-min', 'fastvelocity_min_settings');
}


# register plugin settings
function fastvelocity_min_register_settings() {
    register_setting('fvm-group', 'fastvelocity_min_ignore');
    register_setting('fvm-group', 'fastvelocity_min_disable_js_merge');
    register_setting('fvm-group', 'fastvelocity_min_disable_css_merge');
    register_setting('fvm-group', 'fastvelocity_min_disable_js_minification');
    register_setting('fvm-group', 'fastvelocity_min_disable_css_minification');
    register_setting('fvm-group', 'fastvelocity_min_remove_print_mediatypes');
    register_setting('fvm-group', 'fastvelocity_min_skip_html_minification');
    register_setting('fvm-group', 'fastvelocity_min_skip_cssorder');
    register_setting('fvm-group', 'fastvelocity_min_skip_clean_fonts');
	register_setting('fvm-group', 'fastvelocity_min_skip_google_fonts');
	register_setting('fvm-group', 'fastvelocity_min_skip_fontawesome_fonts');
	register_setting('fvm-group', 'fastvelocity_min_skip_emoji_removal');
	register_setting('fvm-group', 'fastvelocity_min_enable_defer_js');
	register_setting('fvm-group', 'fastvelocity_min_enable_defer_js_ignore');
	register_setting('fvm-group', 'fastvelocity_min_use_google_closure');
	register_setting('fvm-group', 'fastvelocity_min_use_php_minify');
}



# add settings link on plugin page
function fastvelocity_min_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=fastvelocity-min">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'fastvelocity_min_settings_link' );



# manage settings page
function fastvelocity_min_settings() {
if (!current_user_can('manage_options')) { wp_die( __('You do not have sufficient permissions to access this page.')); }

# tmp folder
global $tmpdir, $cachedir, $plugindir;

# get active tab, set default
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'status';

?>
<div class="wrap">
<h1>Fast Velocity Minify</h1>

<?php
if(isset($_POST['purgealt']) && $_POST['purgealt'] == 1) { rrmdir($tmpdir);
echo '<div class="notice notice-success is-dismissible"><p>The intermediate minification cache has been purged!</p></div>';
}
?>

<?php
if(isset($_POST['purgeall']) && $_POST['purgeall'] == 1) { rrmdir($cachedir);
echo '<div class="notice notice-success is-dismissible"><p>The CSS and JS files have been purged!</p></div>';
}
?>

<h2 class="nav-tab-wrapper wp-clearfix">
    <a href="?page=fastvelocity-min&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>">Status</a> 
    <a href="?page=fastvelocity-min&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a> 
	<a href="?page=fastvelocity-min&tab=help" class="nav-tab <?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>">Help</a>
</h2>


<?php if( $active_tab == 'status' ) { ?>

<div id="fastvelocity-min">
    <div id="poststuff">
        <div id="fastvelocity_min_processed" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">
			
				<div class="postbox" id="tab-purge">
                    <h3 class="hndle"><span>Purge processed files</span></h3>
                    <div class="inside" id="fastvelocity_min_topbtns">
                        <ul class="processed">
						<li id="purgeall-row">
							<span class="filename">Purge processed CSS and JS files</span> 
							<span class="actions">
							<form method="post" id="fastvelocity_min_clearall" action="<?php echo admin_url('options-general.php?page=fastvelocity-min&tab=status'); ?>">
							<input type="hidden" name="purgeall" value="1" />
							<?php submit_button('Delete', 'button-secondary', 'submit', false); ?>
							</form>
						</li>
						<li id="purgealt-row">
							<span class="filename">Purge intermediate minification cache</span> 
							<span class="actions">
							<form method="post" id="fastvelocity_min_clearalt" action="<?php echo admin_url('options-general.php?page=fastvelocity-min&tab=status'); ?>">
							<input type="hidden" name="purgealt" value="1" />
							<?php submit_button('Delete', 'button-secondary', 'submit', false); ?>
							</form>
							</span>
						</li>
						<div class="clear"></div>
						</ul>
                    </div>
                </div>
			
                <div class="postbox" id="tab-js">
                    <h3 class="hndle"><span>List of processed JS files</span></h3>
                    <div class="inside" id="fastvelocity_min_jsprocessed">
					<ul class="processed"></ul>
                    </div>
                </div>

                <div class="postbox" id="tab-css">
                    <h3 class="hndle"><span>List of processed CSS files</span></h3>
                    <div class="inside" id="fastvelocity_min_cssprocessed">
                        <ul class="processed"></ul>
                    </div>
                </div>
					
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php if( $active_tab == 'settings' ) { ?>
<form method="post" action="options.php">
<?php settings_fields('fvm-group'); do_settings_sections('fvm-group'); ?>

<table class="form-table" id="fvm-settings">
<tbody>

<tr>
<th scope="row">CSS Options</th>
<td><fieldset><legend class="screen-reader-text"><span>CSS Options</span></legend>

<label for="fastvelocity_min_disable_css_merge">
<input name="fastvelocity_min_disable_css_merge" type="checkbox" id="fastvelocity_min_disable_css_merge" value="1" <?php echo checked(1 == get_option('fastvelocity_min_disable_css_merge'), true, false); ?>>
Disable CSS processing<span class="note-info">[ If selected, this plugin will ignore CSS files completely ]</span></label>
<br />
<label for="fastvelocity_min_disable_css_minification">
<input name="fastvelocity_min_disable_css_minification" type="checkbox" id="fastvelocity_min_disable_css_minification" value="1" <?php echo checked(1 == get_option('fastvelocity_min_disable_css_minification'), true, false); ?>>
Disable minification on CSS files <span class="note-info">[ If selected, CSS files will be merged but not minified ]</span></label>
<br />
<label for="fastvelocity_min_skip_cssorder">
<input name="fastvelocity_min_skip_cssorder" type="checkbox" id="fastvelocity_min_skip_cssorder" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_cssorder'), true, false); ?> >
Disable reordering of CSS files <span class="note-info">[ If selected, you will have better CSS compatibility but possibly more CSS files]</span></label>
<br />
<label for="fastvelocity_min_remove_print_mediatypes">
<input name="fastvelocity_min_remove_print_mediatypes" type="checkbox" id="fastvelocity_min_remove_print_mediatypes" value="1" <?php echo checked(1 == get_option('fastvelocity_min_remove_print_mediatypes'), true, false); ?> >
Remove Print Style Sheets <span class="note-info">[ If selected, CSS files of mediatype "print" will be removed from the site]</span></label>
<br />
<label for="fastvelocity_min_skip_clean_fonts">
<input name="fastvelocity_min_skip_clean_fonts" type="checkbox" id="fastvelocity_min_skip_clean_fonts" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_clean_fonts'), true, false); ?> >
Disable removal of query strings on local Web Fonts <span class="note-info">[ If selected, query strings will be preserved on local web fonts referred by your CSS files ]</span></label>
<br />
<label for="fastvelocity_min_skip_google_fonts">
<input name="fastvelocity_min_skip_google_fonts" type="checkbox" id="fastvelocity_min_skip_google_fonts" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_google_fonts'), true, false); ?> >
Disable Google Fonts optimization <span class="note-info">[ If selected, Google Fonts will no longer be merged into one request ]</span></label>
<br />
<label for="fastvelocity_min_skip_fontawesome_fonts">
<input name="fastvelocity_min_skip_fontawesome_fonts" type="checkbox" id="fastvelocity_min_skip_fontawesome_fonts" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_fontawesome_fonts'), true, false); ?> >
Disable Font Awesome optimization <span class="note-info">[ If selected, Font Awesome loading will be left alone and no longer be optimized ]</span></label>
<br />
</fieldset></td>
</tr>

<tr>
<th scope="row">JavaScript Options</th>
<td><fieldset><legend class="screen-reader-text"><span>JavaScript Options</span></legend>
<label for="fastvelocity_min_disable_js_merge">
<input name="fastvelocity_min_disable_js_merge" type="checkbox" id="fastvelocity_min_disable_js_merge" value="1" <?php echo checked(1 == get_option('fastvelocity_min_disable_js_merge'), true, false); ?> >
Disable JavaScript processing <span class="note-info">[ If selected, this plugin will ignore JS files completely ]</span></label>
<br />
<label for="fastvelocity_min_skip_emoji_removal">
<input name="fastvelocity_min_skip_emoji_removal" type="checkbox" id="fastvelocity_min_skip_emoji_removal" class="jsprocessor" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_emoji_removal'), true, false); ?> >
Stop removing Emojis and smileys <span class="note-info">[ If selected, Emojis will be left alone and won't be removed from wordpress ]</span></label>
<br />
<label for="fastvelocity_min_use_google_closure">
<input name="fastvelocity_min_use_google_closure" type="checkbox" id="fastvelocity_min_use_google_closure" class="jsprocessor" value="1" <?php echo checked(1 == get_option('fastvelocity_min_use_google_closure'), true, false); ?> >
Use Google Closure instead of the YUI Processor <span class="note-info">[ Google Closure is more recent but YUI works by default and it's faster ]</span></label>
<br />
<label for="fastvelocity_min_use_php_minify">
<input name="fastvelocity_min_use_php_minify" type="checkbox" id="fastvelocity_min_use_php_minify" class="jsprocessor" value="1" <?php echo checked(1 == get_option('fastvelocity_min_use_php_minify'), true, false); ?> >
Use PHP Minify instead of YUI or Google Closure <span class="note-info">[ If selected, JS minification will be done by PHP Minify only ]</span></label>
<br />
<label for="fastvelocity_min_disable_js_minification">
<input name="fastvelocity_min_disable_js_minification" type="checkbox" id="fastvelocity_min_disable_js_minification" value="1" <?php echo checked(1 == get_option('fastvelocity_min_disable_js_minification'), true, false); ?> >
Disable JS files minification <span class="note-info">[ If selected, JS files will be merged but not minified ]</span></label>
<br />
<label for="fastvelocity_min_enable_defer_js">
<input name="fastvelocity_min_enable_defer_js" type="checkbox" id="fastvelocity_min_enable_defer_js" value="1" <?php echo checked(1 == get_option('fastvelocity_min_enable_defer_js'), true, false); ?> >
Defer parsing of JS files <span class="note-info">[ Not all browsers, themes and plugins support this, so watch out for problems ]</span></label>
<br />
<label for="fastvelocity_min_enable_defer_js_ignore">
<input name="fastvelocity_min_enable_defer_js_ignore" type="checkbox" id="fastvelocity_min_enable_defer_js_ignore" value="1" <?php echo checked(1 == get_option('fastvelocity_min_enable_defer_js_ignore'), true, false); ?> >
Force parsing of JS files for the "Ignore List" <span class="note-info">[ When "Defer parsing of JS files" is selected, this option will also defer JS files on the Ignore List ]</span></label>
<br />
</fieldset></td>
</tr>

<tr>
<th scope="row">HTML Options</th>
<td><fieldset><legend class="screen-reader-text"><span>HTML Options</span></legend>
<label for="fastvelocity_min_skip_html_minification">
<input name="fastvelocity_min_skip_html_minification" type="checkbox" id="fastvelocity_min_skip_html_minification" value="1" <?php echo checked(1 == get_option('fastvelocity_min_skip_html_minification'), true, false); ?>>
Disable minification on HTML <span class="note-info">[ Normally, it's safe to leave HTML minification enabled ]</span></label>
<br />
</fieldset></td>
</tr>

<tr>
<th scope="row">Ignore List</th>
<td><fieldset><legend class="screen-reader-text"><span>Ignore List</span></legend>
<p><label for="blacklist_keys">Ignore the following CSS and JS full urls below:</label></p>
<p>
<textarea name="fastvelocity_min_ignore" rows="10" cols="50" id="fastvelocity_min_ignore" class="large-text code" placeholder="Example: http://yourdomain.com/wp-includes/js/jquery/jquery.js"><?php echo get_option('fastvelocity_min_ignore'); ?></textarea>
</p>
<p class="description">[ View the logs for a list of urls and add one url per line here to be ignored. No wildcards support yet. ]</p>
</fieldset></td>
</tr>
</tbody></table>

<p class="submit"><input type="submit" name="fastvelocity_min_save_options" id="fastvelocity_min_save_options" class="button button-primary" value="Save Changes"></p>
</form>
<?php } ?>

<?php if( $active_tab == 'help' ) { ?>

<div class="wrap" id="fastvelocity-min">
    <div id="poststuff">
        <div id="fastvelocity_min_processed" class="postbox-container">
			<div class="meta-box-sortables ui-sortable">

			
			
			
				<div class="postbox" id="tab-info">
                    <h3 class="hndle"><span>Frequently Asked Questions</span></h3>
                    <div class="inside"><? echo fastvelocity_min_readme($plugindir.'readme.txt'); ?></div>
                </div>
			
            </div>
        </div>
    </div>
</div>
<?php } ?>



</div>

<div class="clear"></div>

<?php
}


###########################################
# process header javascript ###############
###########################################
function fastvelocity_min_merge_header_scripts() {
global $wp_scripts, $wp_domain, $protocol, $wp_home, $wp_home_path, $cachedir, $cachedirurl, $ignore, $disable_js_merge, $disable_js_minification;
if(!is_object($wp_scripts)) { return false; }
$scripts = wp_clone($wp_scripts);
$scripts->all_deps($scripts->queue);
$header = array();

# mark as done (as we go)
$done = $scripts->done;

# get groups of handles & latest modified date
foreach( $scripts->to_do as $handle ) :

# is it a footer script?
$is_footer = 0; if (isset($wp_scripts->registered[$handle]->extra["group"]) || isset($wp_scripts->registered[$handle]->args)) { $is_footer = 1; }

	# skip footer scripts for now
	if($is_footer != 1) {
		
		# get full url
		$hurl = fastvelocity_min_get_hurl($wp_scripts->registered[$handle]->src, $wp_domain, $protocol, $wp_home);

		# skip ignore list, scripts with conditionals, external scripts
		if ((!fastvelocity_min_in_arrayi($hurl, $ignore) && !isset($wp_scripts->registered[$handle]->extra["conditional"]) && substr($hurl, 0, strlen($wp_home)) === $wp_home) || empty($hurl)) {
			
			# process
			if(isset($header[count($header)-1]['handle']) || count($header) == 0) {
				array_push($header, array('modified'=>0,'handles'=>array()));
			}
			
			# get path and last modified date
			$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
			$modified = 0; if (is_file($handlepath)) { $modified = filemtime($handlepath); }
			
			# push it to the array get latest modified time
			array_push($header[count($header)-1]['handles'], $handle);
			if($modified > $header[count($header)-1]['modified']) { $header[count($header)-1]['modified'] = $modified; }
				
		# external and ignored scripts
		} else { 
			array_push($header, array('handle'=>$handle));
		}
	
	# make sure that the scripts skipped here, show up in the footer
	} else {
		$hurl = fastvelocity_min_get_hurl($wp_scripts->registered[$handle]->src, $wp_domain, $protocol, $wp_home);
		wp_enqueue_script($handle, $hurl, array(), null, true);
	}
endforeach;

# loop through header scripts and merge
for($i=0,$l=count($header);$i<$l;$i++) {
	if(!isset($header[$i]['handle'])) {
		
		# static cache file info + done
		$done = array_merge($done, $header[$i]['handles']);		
		$hash = 'header-'.hash('adler32',implode('',$header[$i]['handles']));
		$modified = $header[$i]['modified'];
		$cache_path = $cachedir.'/'.$hash.'-'.$modified.'.min.js';
		$cache_lock = $cache_path.'.lock';
		$cache_url = $cachedirurl.'/'.$hash.'-'.$modified.'.min.js';
		
		# generate a new cache file
		if (!file_exists($cache_path)) {

		# create a lock file to prevent incomplete views, log start
		file_put_contents($cache_lock, '', LOCK_EX);
        file_put_contents($cache_path.'.txt', date('r')." - PROCESSED:\n");
		
		# minify and write to file
		$js = ''; $log = '';
			foreach( $header[$i]['handles'] as $handle ) :
			if(!empty($wp_scripts->registered[$handle]->src)) {
				
				# get full url and path
				$hurl = fastvelocity_min_get_hurl($wp_scripts->registered[$handle]->src, $wp_domain, $protocol, $wp_home);
				$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
			
				# process
				$jsarr = array(); $jsarr = fastvelocity_min_minify_js($handle, $hurl, $handlepath, $disable_js_minification);
				$js.= $jsarr['js']; $log.= $jsarr['log'];
			
			# consider dependencies on handles with an empty src
			} else {
				wp_dequeue_script($handle); wp_enqueue_script($handle);
			}
			endforeach;	
		
		# generate cache, write log
        file_put_contents($cache_path, $js, LOCK_EX);
		file_put_contents($cache_path.'.txt', $log.date('r')." - ALL DONE\n", FILE_APPEND);
		file_put_contents($cache_path.'.gz', gzencode(file_get_contents($cache_path), 9)); # useful for gzip_static on nginx
		@unlink($cache_lock); clearstatcache();
		}
		
		# register minified file
		wp_register_script("fvm-header-$i", $cache_url, array(), null, false); 
		
		# add all extra data from wp_localize_script
		$data = array();
		foreach($header[$i]['handles'] as $handle) { 					
			if(isset($wp_scripts->registered[$handle]->extra['data'])) { $data[] = $wp_scripts->registered[$handle]->extra['data']; }
		}
		if(count($data) > 0) { $wp_scripts->registered["fvm-header-$i"]->extra['data'] = implode("\n", $data); }
		
		# enqueue file
		wp_enqueue_script("fvm-header-$i");
	
	# other scripts need to be requeued for the order of files to be kept
	} else {
		wp_dequeue_script($header[$i]['handle']); wp_enqueue_script($header[$i]['handle']);
	}
}

# remove from queue
$wp_scripts->done = $done;
}




###########################################
# process header css ######################
###########################################
function fastvelocity_min_merge_header_css() {
global $wp_styles, $wp_domain, $protocol, $wp_home, $wp_home_path, $cachedir, $cachedirurl, $ignore, $disable_css_merge, $disable_css_minification, $skip_clean_fonts, $skip_google_fonts, $skip_fontawesome_fonts, $skip_cssorder, $remove_print_mediatypes;
if(!is_object($wp_styles)) { return false; }
$styles = wp_clone($wp_styles);
$styles->all_deps($styles->queue);
$done = $styles->done;

$header = array();
$google_fonts = array();
$colect_css_after = array();
$process = array();


# get list of handles to process, dequeue duplicate css urls and keep empty source handles (for dependencies)
$uniq = array(); $gfonts = array();
foreach( $styles->to_do as $handle):
	$conditional = NULL; if(isset($wp_styles->registered[$handle]->extra["conditional"])) { 
	$conditional = $wp_styles->registered[$handle]->extra["conditional"]; # such as ie7, ie8, ie9, etc
	}
	$mediatype = isset($wp_styles->registered[$handle]->args) ? $wp_styles->registered[$handle]->args : 'all'; # such as all, print, mobile, etc
	if ($mediatype == 'screen') { $mediatype = 'all'; } # mediatype screen and all are the same, standardize
	$hurl = fastvelocity_min_get_hurl($wp_styles->registered[$handle]->src, $wp_domain, $protocol, $wp_home);  # full url or empty
	
	# colect inline css for this handle
	if(isset($wp_styles->registered[$handle]->extra['after']) && is_array($wp_styles->registered[$handle]->extra['after'])) { 
		$colect_css_after[$handle] = fastvelocity_min_minify_css_string(implode('', $wp_styles->registered[$handle]->extra['after'])); # save
		$wp_styles->registered[$handle]->extra['after'] = null; # dequeue
	}	
	
	# get path and last modified date
	$handlepath = ''; if(!empty($hurl)) { $handlepath = str_ireplace($wp_home, $wp_home_path, $hurl); }
	$modified = 0; if (!empty($handlepath) && is_file($handlepath)) { $modified = filemtime($handlepath); }
	
	# mark duplicates as done and remove from the queue
	if(!empty($hurl)) {
		$key = hash('adler32', $hurl); 
		if (isset($uniq[$key])) { $done = array_merge($done, array($handle)); continue; } else { $uniq[$key] = $handle; }
	}
	
	# array of info to save
	$arr = array('handle'=>$handle, 'url'=>$hurl, 'path'=>$handlepath, 'modified'=>$modified, 'conditional'=>$conditional, 'mediatype'=>$mediatype);
	
	# google fonts to the top
	if (stripos($hurl, 'fonts.googleapis.com') !== false) { 
	if(!$skip_google_fonts) { $google_fonts[$handle] = $hurl; } else { wp_enqueue_style($handle); }
	continue; 
	} 
	
	# add font awesome to the top, if found anywhere
	if (stripos($hurl, 'font-awesome.min.css') !== false || stripos($hurl, 'font-awesome.css') !== false) { 
	if(!$skip_fontawesome_fonts) {
	$done = array_merge($done, array($handle)); # mark as done
	wp_enqueue_style('header-fvm-fontawesome', $protocol.'maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css', array(), null, 'all'); 
	} else { wp_enqueue_style($handle); }
	} 
	
	# all else
	$process[$handle] = $arr;

endforeach;


# concat google fonts, if enabled
if(!$skip_google_fonts && count($google_fonts) > 0) {
	$concat_google_fonts = fastvelocity_min_concatenate_google_fonts($google_fonts);
	foreach ($google_fonts as $h=>$a) { $done = array_merge($done, array($h)); } # mark as done
	wp_enqueue_style('header-fvm-googlefonts', $concat_google_fonts, array(), null, 'all');
}


# get groups of handles & latest modified date
foreach( $styles->to_do as $handle ) :

# skip already processed google fonts and empty dependencies
if(isset($google_fonts[$handle])) { continue; }                     # skip google fonts
if(empty($wp_styles->registered[$handle]->src)) { continue; } 		# skip empty src
if (fastvelocity_min_in_arrayi($handle, $done)) { continue; }       # skip if marked as done before
if (!isset($process[$handle])) { continue; } 						# skip if not on our unique process list

# get full url
$hurl = $process[$handle]['url'];
$conditional = $process[$handle]['conditional'];
$mediatype = $process[$handle]['mediatype'];
$handlepath = $process[$handle]['path'];
$modified = $process[$handle]['modified'];

# skip ignore list, conditional css, external css
if ((!fastvelocity_min_in_arrayi($hurl, $ignore) && !isset($conditional) && substr($hurl, 0, strlen($wp_home)) === $wp_home) || empty($hurl)) {
	
	# process
	if(isset($header[count($header)-1]['handle']) || count($header) == 0 || $header[count($header)-1]['media'] != $mediatype) {
		array_push($header, array('modified'=>0, 'handles'=>array(), 'media'=>$mediatype )); 
	}
	
	# push it to the array get latest modified time
	array_push($header[count($header)-1]['handles'], $handle);
	if($modified > $header[count($header)-1]['modified']) { $header[count($header)-1]['modified'] = $modified; }

	# external and ignored css
	} else {
		array_push($header, array('handle'=>$handle));
	}
endforeach;


# reorder CSS by mediatypes
if(!$skip_cssorder) {
if(count($header) > 0) {

# get unique mediatypes and most recent modified time
$allmedia = array(); foreach($header as $key=>$array) {
	if(isset($array['media']) && isset($allmedia[$array['media']]) && isset($array['modified'])) {
		if($allmedia[$array['media']] <= $array['modified']) { 
			$allmedia[$array['media']] = $array['modified']; 
			unset($header[$key]);
		} else { 
			$allmedia[$array['media']] = $array['modified'];
			unset($header[$key]);
		}
	}
}

# extract handles by mediatype
$grouphandles = array(); foreach ($allmedia as $md=>$dt) { foreach($header as $array) { 
if (isset($array['media']) && $array['media'] == $md) { foreach($array['handles'] as $h) {$grouphandles[$md][] = $h; } } } }

# reset and reorder header by mediatypes
foreach ($allmedia as $md=>$dt) { $header[] = array('modified'=>$dt, 'handles' => $grouphandles[$md], 'media'=>$md); }
}
}


# loop through header css and merge
for($i=0,$l=count($header);$i<$l;$i++) {
	if(!isset($header[$i]['handle'])) {
		
		# static cache file info + done
		$done = array_merge($done, $header[$i]['handles']);		
		$hash = 'header-'.hash('adler32',implode('',$header[$i]['handles']));
		$modified = $header[$i]['modified'];
		$cache_path = $cachedir.'/'.$hash.'-'.$modified.'.min.css';
		$cache_lock = $cache_path.'.lock';
		$cache_url = $cachedirurl.'/'.$hash.'-'.$modified.'.min.css';

		# generate a new cache file
		if (!file_exists($cache_path)) {

		# create a lock file to prevent incomplete views, log start
		file_put_contents($cache_lock, '', LOCK_EX);
        file_put_contents($cache_path.'.txt', date('r')." - PROCESSED:\n");
		
		# minify and write to file
		$css = ''; $log = '';
			foreach( $header[$i]['handles'] as $handle ) :
			if(!empty($wp_styles->registered[$handle]->src)) {
				
				# get full url and path
				$hurl = $process[$handle]['url'];
				$handlepath = $process[$handle]['path'];
			
				# process
				$cssarr = array(); $cssarr = fastvelocity_min_minify_css($handle, $hurl, $handlepath, $skip_clean_fonts, $disable_css_minification);
				$css.= $cssarr['css']; $log.= $cssarr['log'];
			
			# consider dependencies on handles with an empty src
			} else {
				wp_dequeue_style($handle); wp_enqueue_style($handle);
			}
			endforeach;
		
		# generate cache, write log
        file_put_contents($cache_path, $css, LOCK_EX);
		file_put_contents($cache_path.'.txt', $log.date('r')." - ALL DONE\n", FILE_APPEND);
		file_put_contents($cache_path.'.gz', gzencode(file_get_contents($cache_path), 9)); # useful for gzip_static on nginx
		@unlink($cache_lock); clearstatcache();
		}
		
		# register and enqueue minified file, consider excluding of mediatype "print"
		if ($remove_print_mediatypes != 1 || ($remove_print_mediatypes == 1 && $header[$i]['media'] != 'print')) {
		wp_register_style("fvm-header-$i", $cache_url, array(), null, $header[$i]['media']); 
		wp_enqueue_style("fvm-header-$i");
		if(count($colect_css_after) > 0) { wp_add_inline_style("fvm-header-$i", implode('', $colect_css_after)); } # add extra, inline css
		}
		
	# other css need to be requeued for the order of files to be kept
	} else {
		wp_dequeue_style($header[$i]['handle']); wp_enqueue_style($header[$i]['handle']);
	}
}

# remove from queue
$wp_styles->done = $done;

}







###########################################
# process js in the footer ################
###########################################
function fastvelocity_min_merge_footer_scripts() {
global $wp_scripts, $protocol, $wp_domain, $wp_home, $wp_home_path, $cachedir, $cachedirurl, $ignore, $disable_js_merge, $disable_js_minification;
if(!is_object($wp_scripts)) { return false; }

# process JS in the footer
$scripts = wp_clone($wp_scripts);
$scripts->all_deps($scripts->queue);
$footer = array();

# mark as done (as we go)
$done = $scripts->done;

# get groups of handles & latest modified date
foreach( $scripts->to_do as $handle ) :

	# get full url
	$hurl = fastvelocity_min_get_hurl($wp_scripts->registered[$handle]->src, $wp_domain, $protocol, $wp_home);

	# skip ignore list, scripts with conditionals, external scripts
	if ((!fastvelocity_min_in_arrayi($hurl, $ignore) && !isset($wp_scripts->registered[$handle]->extra["conditional"]) && substr($hurl, 0, strlen($wp_home)) === $wp_home) || empty($hurl)) {
			
		# process
		if(isset($footer[count($footer)-1]['handle']) || count($footer) == 0) {
			array_push($footer, array('modified'=>0,'handles'=>array()));
		}
			
		# get path and last modified date
		$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
		$modified = 0; if (is_file($handlepath)) { $modified = filemtime($handlepath); }
		
		# push it to the array get latest modified time
		array_push($footer[count($footer)-1]['handles'], $handle);
		if($modified > $footer[count($footer)-1]['modified']) { $footer[count($footer)-1]['modified'] = $modified; }
				
	# external and ignored scripts
	} else { 
		array_push($footer, array('handle'=>$handle));
	}
endforeach;


# loop through footer scripts and merge
for($i=0,$l=count($footer);$i<$l;$i++) {
	if(!isset($footer[$i]['handle'])) {
		
		# static cache file info + done
		$done = array_merge($done, $footer[$i]['handles']);		
		$hash = 'footer-'.hash('adler32',implode('',$footer[$i]['handles']));
		$modified = $footer[$i]['modified'];
		$cache_path = $cachedir.'/'.$hash.'-'.$modified.'.min.js';
		$cache_lock = $cache_path.'.lock';
		$cache_url = $cachedirurl.'/'.$hash.'-'.$modified.'.min.js';
		
		# generate a new cache file
		if (!file_exists($cache_path)) {

		# create a lock file to prevent incomplete views, log start
		file_put_contents($cache_lock, '', LOCK_EX);
        file_put_contents($cache_path.'.txt', date('r')." - PROCESSED:\n");
		
		# minify and write to file
		$js = ''; $log = '';
			foreach( $footer[$i]['handles'] as $handle ) :
			if(!empty($wp_scripts->registered[$handle]->src)) {
				
				# get full url and path
				$hurl = fastvelocity_min_get_hurl($wp_scripts->registered[$handle]->src, $wp_domain, $protocol, $wp_home);
				$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
			
				# process
				$jsarr = array(); $jsarr = fastvelocity_min_minify_js($handle, $hurl, $handlepath, $disable_js_minification);
				$js.= $jsarr['js']; $log.= $jsarr['log'];
			
			# consider dependencies on handles with an empty src
			} else {
				wp_dequeue_script($handle); wp_enqueue_script($handle);
			}
			endforeach;
		
		# generate cache, write log
        file_put_contents($cache_path, $js, LOCK_EX);
		file_put_contents($cache_path.'.txt', $log.date('r')." - ALL DONE\n", FILE_APPEND);
		file_put_contents($cache_path.'.gz', gzencode(file_get_contents($cache_path), 9)); # useful for gzip_static on nginx
		@unlink($cache_lock); clearstatcache();
		}
		
		# register minified file
		wp_register_script("fvm-footer-$i", $cache_url, array(), null, true); 
		
		# add all extra data from wp_localize_script
		$data = array();
		foreach($footer[$i]['handles'] as $handle) { 					
			if(isset($wp_scripts->registered[$handle]->extra['data'])) { $data[] = $wp_scripts->registered[$handle]->extra['data']; }
		}
		if(count($data) > 0) { $wp_scripts->registered["fvm-footer-$i"]->extra['data'] = implode("\n", $data); }
		
		# enqueue file
		wp_enqueue_script("fvm-footer-$i");
	
	# other scripts need to be requeued for the order of files to be kept
	} else {
		wp_dequeue_script($footer[$i]['handle']); wp_enqueue_script($footer[$i]['handle']);
	}
}

# remove from queue
$wp_scripts->done = $done;
}


###########################################
# process css in the footer ###############
###########################################
function fastvelocity_min_merge_footer_css() {
global $wp_styles, $protocol, $wp_domain, $wp_home, $wp_home_path, $cachedir, $cachedirurl, $ignore, $disable_css_merge, $disable_css_minification, $skip_clean_fonts, $skip_google_fonts, $skip_fontawesome_fonts, $skip_cssorder, $remove_print_mediatypes;
if(!is_object($wp_styles)) { return false; }

# process CSS in the footer
$styles = wp_clone($wp_styles);
$styles->all_deps($styles->queue);
$footer = array();
$google_fonts = array();
$colect_css_after = array();

# mark as done (as we go)
$done = $styles->done;

# google fonts to the top
foreach( $styles->to_do as $handle ) :

	# dequeue and get a list of google fonts, or requeue external
	$hurl = fastvelocity_min_get_hurl($wp_styles->registered[$handle]->src, $wp_domain, $protocol, $wp_home);
	if (stripos($hurl, 'fonts.googleapis.com') !== false) { 
		wp_dequeue_style($handle); 
		if(!$skip_google_fonts) { $google_fonts[$handle] = $hurl; } else { wp_enqueue_style($handle); } # skip google fonts optimization?
	}

	# add font awesome, if found in the footer with the same handle to prevent duplicates
	else if (stripos($hurl, 'font-awesome.min.css') !== false || stripos($hurl, 'font-awesome.css') !== false) { 
	if(!$skip_fontawesome_fonts) {
		$done = array_merge($done, array($handle)); # mark as done
		wp_enqueue_style('header-fvm-fontawesome', $protocol.'maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css', array(), null, 'all');
	}
	} 
	
	# failsafe
	else { wp_dequeue_style($handle); wp_enqueue_style($handle); }
endforeach;


# concat google fonts, if enabled
if(!$skip_google_fonts && count($google_fonts) > 0) {
	$concat_google_fonts = fastvelocity_min_concatenate_google_fonts($google_fonts);
	foreach ($google_fonts as $h=>$a) { $done = array_merge($done, array($h)); } # mark as done
	wp_enqueue_style('footer-fvm-fonts', $concat_google_fonts, array(), null, 'all');
}


# get groups of handles & latest modified date
foreach( $styles->to_do as $handle ) :

# skip already processed google fonts
if(isset($google_fonts[$handle])) { continue; }

# get full url
$hurl = fastvelocity_min_get_hurl($wp_styles->registered[$handle]->src, $wp_domain, $protocol, $wp_home);

# skip ignore list, conditional css, external css
if ((!fastvelocity_min_in_arrayi($hurl, $ignore) && !isset($wp_scripts->registered[$handle]->extra["conditional"]) && substr($hurl, 0, strlen($wp_home)) === $wp_home) || empty($hurl)) {
	
	# colect inline css for this handle
	if(isset($wp_styles->registered[$handle]->extra['after']) && is_array($wp_styles->registered[$handle]->extra['after'])) { 
		$colect_css_after[$handle] = fastvelocity_min_minify_css_string(implode('', $wp_styles->registered[$handle]->extra['after'])); # save
		$wp_styles->registered[$handle]->extra['after'] = null; # dequeue
	}
	
	# process
	if(isset($footer[count($footer)-1]['handle']) || count($footer) == 0 || $footer[count($footer)-1]['media'] != $wp_styles->registered[$handle]->args) {
		$media = isset($wp_styles->registered[$handle]->args) ? $wp_styles->registered[$handle]->args : 'all';
		array_push($footer, array('modified'=>0,'handles'=>array(),'media'=>$media ));
	}
	
	# get path and last modified date
	$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
	$modified = 0; if (is_file($handlepath)) { $modified = filemtime($handlepath); }
	
	# push it to the array get latest modified time
	array_push($footer[count($footer)-1]['handles'], $handle);
	if($modified > $footer[count($footer)-1]['modified']) { $footer[count($footer)-1]['modified'] = $modified; }

	# external and ignored css
	} else {
		array_push($footer, array('handle'=>$handle));
	}
endforeach;


# reorder CSS by mediatypes
if(!$skip_cssorder) {
if(count($footer) > 0) {
	
# get unique mediatypes and most recent modified time
$allmedia = array(); foreach($footer as $key=>$array) {
	if(isset($array['media']) && isset($allmedia[$array['media']]) && isset($array['modified'])) {
		if($allmedia[$array['media']] <= $array['modified']) { 
			$allmedia[$array['media']] = $array['modified']; 
			unset($footer[$key]);
		} else { 
			$allmedia[$array['media']] = $array['modified'];
			unset($footer[$key]);
		}
	}
}

# extract handles by mediatype
$grouphandles = array(); foreach ($allmedia as $md=>$dt) { foreach($footer as $array) { 
if (isset($array['media']) && $array['media'] == $md) { foreach($array['handles'] as $h) {$grouphandles[$md][] = $h; } } } }

# reset and reorder footer by mediatypes
foreach ($allmedia as $md=>$dt) { $footer[] = array('modified'=>$dt, 'handles' => $grouphandles[$md], 'media'=>$md); }
}
}



# loop through footer css and merge
for($i=0,$l=count($footer);$i<$l;$i++) {
	if(!isset($footer[$i]['handle'])) {
		
		# static cache file info + done
		$done = array_merge($done, $footer[$i]['handles']);		
		$hash = 'footer-'.hash('adler32',implode('',$footer[$i]['handles']));
		$modified = $footer[$i]['modified'];
		$cache_path = $cachedir.'/'.$hash.'-'.$modified.'.min.css';
		$cache_lock = $cache_path.'.lock';
		$cache_url = $cachedirurl.'/'.$hash.'-'.$modified.'.min.css';

		# generate a new cache file
		if (!file_exists($cache_path)) {

		# create a lock file to prevent incomplete views, log start
		file_put_contents($cache_lock, '', LOCK_EX);
        file_put_contents($cache_path.'.txt', date('r')." - PROCESSED:\n");
		
		# minify and write to file
		$css = ''; $log = '';
			foreach( $footer[$i]['handles'] as $handle ) :
			if(!empty($wp_styles->registered[$handle]->src)) {
				
				# get full url and path
				$hurl = fastvelocity_min_get_hurl($wp_styles->registered[$handle]->src, $wp_domain, $protocol, $wp_home);
				$handlepath = str_ireplace($wp_home, $wp_home_path, $hurl);
			
				# process
				$cssarr = array(); $cssarr = fastvelocity_min_minify_css($handle, $hurl, $handlepath, $skip_clean_fonts, $disable_css_minification);
				$css.= $cssarr['css']; $log.= $cssarr['log'];
			
			# consider dependencies on handles with an empty src
			} else {
				wp_dequeue_style($handle); wp_enqueue_style($handle);
			}
			endforeach;

		# generate cache, write log
        file_put_contents($cache_path, $css, LOCK_EX);
		file_put_contents($cache_path.'.txt', $log.date('r')." - ALL DONE\n", FILE_APPEND);
		file_put_contents($cache_path.'.gz', gzencode(file_get_contents($cache_path), 9)); # useful for gzip_static on nginx
		
		@unlink($cache_lock); clearstatcache();
		}
		
		# register and enqueue minified file, consider excluding of mediatype "print"
		if ($remove_print_mediatypes != 1 || ($remove_print_mediatypes == 1 && $header[$i]['media'] != 'print')) {
		wp_register_style("fvm-footer-$i", $cache_url, array(), null, $footer[$i]['media']); 
		wp_enqueue_style("fvm-footer-$i");
		}
		
		# add extra, inline css
		if(count($colect_css_after) > 0) { wp_add_inline_style("fvm-footer-$i", implode('', $colect_css_after)); }

	# other css need to be requeued for the order of files to be kept
	} else {
		wp_dequeue_style($footer[$i]['handle']); wp_enqueue_style($footer[$i]['handle']);
	}
}

# remove from queue
$wp_styles->done = $done;
}


# enable defer for JavaScript (WP 4.1 and above)
function fastvelocity_min_defer_js($tag, $handle, $src) {
global $ignore, $enable_defer_js_ignore;
#$ignore = array_map('trim', explode(PHP_EOL, get_option('fastvelocity_min_ignore')));
#$enable_defer_js_ignore = get_option('fastvelocity_min_enable_defer_js_ignore');

# skip the ignore list by default, defer the rest
if (fastvelocity_min_in_arrayi($src, $ignore) && $enable_defer_js_ignore != 1) { return $tag; } 
else { return '<script src="'.$src.'" defer="defer" type="text/javascript"></script>'; }

}

# process defering
if ($enable_defer_js == 1 && !is_admin()) { 
add_filter('script_loader_tag', 'fastvelocity_min_defer_js', 10, 3); 
}

# enable html minification
if(!$skip_html_minification) {
add_action('get_header', 'fastvelocity_min_html_compression_start'); 
} 
