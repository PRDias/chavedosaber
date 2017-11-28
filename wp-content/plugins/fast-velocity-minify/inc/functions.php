<?php

# Consider fallback to PHP Minify [2016.08.01] from https://github.com/matthiasmullie/minify (must be defined on the outer scope)
require_once ($plugindir . 'libs/matthiasmullie/minify/src/Minify.php');
require_once ($plugindir . 'libs/matthiasmullie/minify/src/CSS.php');
require_once ($plugindir . 'libs/matthiasmullie/minify/src/JS.php');
require_once ($plugindir . 'libs/matthiasmullie/minify/src/Exception.php');
require_once ($plugindir . 'libs/matthiasmullie/path-converter/src/Converter.php');
use MatthiasMullie\Minify;

# use HTML minification
require_once ($plugindir . 'libs/mrclay/HTML.php');



# functions, get hurl info
function fastvelocity_min_get_hurl($src, $wp_domain, $protocol, $wp_home) {
$hurl = trim($src); if(empty($hurl)) { return $hurl; }      # preserve empty source handles

#make sure wp_home doesn't have a forward slash
if(substr($wp_home, -1) == '/') { $wp_home = trim($wp_home, '/'); }

# apply some filters
if (substr($hurl, 0, 2) === "//") { $hurl = $protocol.ltrim($hurl, "/"); }  # protocol only
if (substr($hurl, 0, 4) === "http" && stripos($hurl, $wp_domain) === false) { return $hurl; } # return if external domain
if (substr($hurl, 0, 4) !== "http" && stripos($hurl, $wp_domain) !== false) { $hurl = $wp_home.'/'.ltrim($hurl, "/"); } # protocol + home


# consider different wp-content directory
$proceed = 0; if(!empty($wp_home)) { 
	$alt_wp_content = basename($wp_home); 
	if(substr($hurl, 0, strlen($alt_wp_content)) === $alt_wp_content) { $proceed = 1; } 
}

# protocol + home for relative paths
if (substr($hurl, 0, 12) === "/wp-includes" || substr($hurl, 0, 9) === "/wp-admin" || substr($hurl, 0, 11) === "/wp-content" || $proceed == 1) { 
$hurl = $wp_home.'/'.ltrim($hurl, "/"); }
return $hurl;	
}


# functions, minify html
function fastvelocity_min_minify_html($html) {
$html = fastvelocity_min_Minify_HTML::minify($html);
return $html;
}


# case-insensitive in_array() wrapper
function fastvelocity_min_in_arrayi($needle, $haystack){
	return in_array(strtolower($needle), array_map('strtolower', $haystack));
}



# process minification of javascript files
function fastvelocity_min_minify_js_process($jsfile) {
	
# default settings
global $use_google_closure, $use_php_minify, $tmpdir, $plugindir;

# default cache path location
$jstmp = $tmpdir.'/'.hash('adler32', $jsfile).'-'.basename($jsfile);
$jstmplog = $jstmp.'.log';

# return from cache if it's still valid
if(file_exists($jstmp) && file_exists($jstmplog) && filesize($jstmp) > 0 && filemtime($jstmp) >= filemtime($jsfile)) {
	$output = file_get_contents($jstmp); # use cache
	return array('js'=>$output, 'log'=>file_get_contents($jstmplog).' [cache]');
	exit();
}

# skip if PHP Minify is selected
if(!$use_php_minify) {

# check for exec + a supported java version
if(function_exists('exec') && exec('command -v java >/dev/null && echo "yes" || echo "no"') == 'yes' && exec('java -version 2>&1',$jav_version) && preg_match("/java\ version\ \"[0-9]+\.([7-9]{1}+|[0-9]{2,}+)\..*/", $jav_version[0])) {

	# define jar paths
	$yui = $plugindir.'libs/jar/yuicompressor-2.4.8.jar';
	$gog = $plugindir.'libs/jar/google-closure.jar';
	
	# choose between YUI or Google Closure, YUI by default (faster)
	if($use_google_closure) {
		$cmd = 'java -jar '.$gog.' --warning_level QUIET --js '.$jsfile.' --js_output_file '.$jstmp;
		$savelog = 'GOOGLE CLOSURE';
	} else {
		$cmd = 'java -jar '.$yui.' --preserve-semi '.$jsfile.' -o '.$jstmp;
		$savelog = 'YUI COMPRESSOR';
	}
	
	# save log
	file_put_contents($jstmplog, $savelog, LOCK_EX);
	
	# clear utf8 bom
	if(is_file($jstmp)) { file_put_contents($jstmp, fastvelocity_min_remove_utf8_bom(file_get_contents($jstmp)), LOCK_EX); }
	
	# run local compiler
	exec($cmd . ' 2>&1', $output);
	if(count($output) == 0 && file_exists($jstmp)) {
		$output = file_get_contents($jstmp);
		return array('js'=>$output, 'log'=>$savelog);
		exit();
	}		
}
}


# Fallback to PHP Minify [2016.08.01] from https://github.com/matthiasmullie/minify
$fixbom = fastvelocity_min_remove_utf8_bom(file_get_contents($jsfile)); # clear utf8 bom
$minifier = new Minify\JS($fixbom);
$jsmin = $minifier->minify();
if(!empty($jsmin)) {
$savelog = 'PHP MINIFY';
return array('js'=>$jsmin, 'log'=>$savelog);
exit();
}
	

# this is our last resort fallback
$savelog = 'MERGED ONLY';
file_put_contents($jstmplog, $savelog, LOCK_EX);
return array('js'=>file_get_contents($jsfile, LOCK_EX), 'log'=>$savelog);
exit();

}
	

# minify js on demand (one file at one time, for compatibility)
function fastvelocity_min_minify_js($handle, $url, $path, $nocompress) {
global $plugindir;
	
# try to get the unminified version if available, for better compatibility	
$xtralog = '';
$ignorehandles = array('jquery-core', 'jquery', 'jquery-migrate');
if (!fastvelocity_min_in_arrayi($handle, $ignorehandles)) {
$use = str_ireplace(array('.min.js', '-min.js'), '.js', $path);
if($use != $path && file_exists($use)) { $path = $use; $xtralog = ' - [minified '.basename($use).' for compatibility]'; }
if (stripos(basename($url), 'min.js') && empty($xtralog)) { $xtralog = ' [already minified]'; }
} else { $xtralog = ' - [already minified]'; }

# basic cleaning and minification
$js = preg_replace("/^\xEF\xBB\xBF/", '', file_get_contents($path)); # remove BOM
$js = trim(join("\n", array_map("trim", explode("\n", preg_replace('/\v+/', "\n", preg_replace('/\h+/', " ", $js)))))); # BASIC MINIFICATION

# jQuery no conflict mode
if (stripos(basename($path), 'jquery.js') !== false) { $js = $js."jQuery.noConflict();"; }

# exclude minification on already minified files + jquery (because minification might break those)
$excl = array('jquery.js', '.min.js', '-min.js'); 
foreach($excl as $e) { if (stripos(basename($path), $e) !== false) { $nocompress = true; break; } }	

# default log
$loginfo = 'MERGED';

# minification (if allowed)
if (!$nocompress) { 
	$newjsarr = fastvelocity_min_minify_js_process($path);
	if (empty($newjsarr['js'])) { 
		$xtralog = ' [empty file]';
	} else {
		$js = $newjsarr['js'];
		$loginfo = $newjsarr['log'];
	}
}

# define log
$log = " - $loginfo".$xtralog." - $handle - $url \n"; 


# fix compatibility when mergin some scripts
if(substr($js, -1) != ';') { $js = $js.";\n"; } $js = $js."\n";

# return html
return array('js'=> $js, 'log' => $log);
}



# minify css string with PHP Minify
function fastvelocity_min_minify_css_string($css) {
$minifier = new Minify\CSS($css);
$cssmin = $minifier->minify();
if(!empty($cssmin)) { return $cssmin; }
return $css;
}




# minify css on demand (one file at one time, for compatibility)
function fastvelocity_min_minify_css($handle, $url, $path, $skip_clean_fonts, $disable_css_minification) {

# default settings
global $tmpdir;
$savelog = 'MERGED';
$xtralog = '';

# must have, or log error
if(!is_file($path)) { return array('css'=> '', 'log' => "NOT FOUND [$path] - Permission denied or the file does not exist"); }

# default cache path location
$csstmp = $tmpdir.'/'.hash('adler32', $path).'-'.basename($path);
$csstmplog = $csstmp.'.log';

# return from cache if it's still valid
if(file_exists($csstmp) && file_exists($csstmplog) && filesize($csstmp) > 0 && filemtime($csstmp) >= filemtime($path)) {
	$css = file_get_contents($csstmp, LOCK_EX);
	$log = ' - '.file_get_contents($csstmplog)." [cache] - $handle - $url \n";
	return array('css'=>$css, 'log'=>$log);
	exit();
}



# basic processing with PHP
$css = preg_replace("/^\xEF\xBB\xBF/", '', file_get_contents($path)); # remove BOM
$css = preg_replace("/url\(\s*['\"]?(?!data:)(?!http)(?![\/'\"])(.+?)['\"]?\s*\)/i", "url(".dirname($url)."/$1)", $css); # fix paths

# remove query strings from fonts (for better seo, but add a small cache buster based on most recent updates)
if(!$skip_clean_fonts) { $css = preg_replace('/(.eot|.svg|.woff2|.woff|.ttf)+[?+](.+?)(\)|\'|\")/', "$1"."#".filemtime($path)."$3", $css); }

# minify CSS
if(!$disable_css_minification) {
	$mincss = fastvelocity_min_minify_css_string($css);
	$savelog = 'MINIFIED';
	$css = $mincss;
	if (empty($mincss)) { $xtralog = ' [empty file]'; }
}

# save css +  logs
file_put_contents($csstmp, $css, LOCK_EX);
file_put_contents($csstmplog, $savelog, LOCK_EX);

# generate log
$log = " - $savelog".$xtralog." - $handle - $url \n";

# return html
return array('css'=> $css, 'log' => $log);
}



# functions to minify HTML
function fastvelocity_min_html_compression_finish($html) { return fastvelocity_min_minify_html($html); }
function fastvelocity_min_html_compression_start() { ob_start('fastvelocity_min_html_compression_finish'); }


# remove all cache files
function rrmdir($dir) { 
	if(is_dir(rtrim($dir, '/'))) { 
		if ($handle = opendir($dir.'/')) { 
			while (false !== ($file = readdir($handle))) { 
			@unlink($dir.'/'.$file); 
			} 
		closedir($handle); } 
	} 
}


# Concatenate Google Fonts tags (http://fonts.googleapis.com/css?...)
function fastvelocity_min_concatenate_google_fonts($array) {
global $protocol;

# extract unique font families
$families = array(); foreach ($array as $font) {

# get fonts name, type and subset, remove wp query strings
$font = explode('family=', htmlspecialchars_decode(rawurldecode(urldecode($font))));
$a = explode('&v', end($font)); $font = trim(trim(trim(current($a)), ','));

# reprocess if fonts are already concatenated in this url
if(stristr($font, '|') !== FALSE) { 
	$multiple = explode('|', $font); if (count($multiple) > 0) { foreach ($multiple as $f) { $families[] = trim($f); } }
} else { $families[] = $font; }
}

# process types, subsets, merge, etc
$fonts = array(); 
foreach ($families as $font) {
		
# if no type or subset
if(stristr($font, ':') === FALSE) { 
	$fonts[] = array('name'=>$font, 'type'=>'', 'sub'=>''); 
} else {

	# get type and subset
	$name = stristr($font, ':', true);       # font name, before :
	$ftype = trim(stristr($font, ':'), ':'); # second part of the string, after :

	# get font types and subset
	if(stristr($ftype, '&subset=') === FALSE) { 
		$fonts[] = array('name'=>$name, 'type'=>$ftype, 'sub'=>''); 
	} else { 
		$newftype = stristr($ftype, '&', true);        # font type, before &
		$subset = trim(str_ireplace('&subset=', '', stristr($ftype, '&')));     # second part of the string, after &
		$fonts[] = array('name'=>$name, 'type'=>$newftype, 'sub'=>$subset); 
	}

}
}

# make sure we have unique font names, types and subsets
$ufonts = array(); foreach ($fonts as $f) { $ufonts[$f['name']] = $f['name']; }                              # unique font names
$usubsets = array(); foreach ($fonts as $f) { if(!empty($f['sub'])) { $usubsets[$f['sub']] = $f['sub']; } }  # unique subsets

# prepare
$fonts_and_types = $ufonts;

# get unique types and subsets for each unique font name
foreach ($ufonts as $uf) {
	
	# types
	$utypes = array(); 
	foreach ($fonts as $f) {
		if($f['name'] == $uf && !empty($f['type'])) { $utypes = array_merge($utypes, explode(',', $f['type'])); }
	}
	
	# filter types
	$utypes = array_unique($utypes);
    sort($utypes);
	$ntype = ''; if(count($utypes) > 0) { $ntype = ':'.implode(',', $utypes); } # types to append to the font name
	
	# generate font url queries
	$fonts_and_types[$uf] = str_ireplace(' ', '+', $uf).$ntype;
}

# concat fonts, generate unique google fonts url
if(count($fonts_and_types) > 0) {
	$msubsets = ''; if(count($usubsets) > 0) { $msubsets = '&subset='.implode(',', $usubsets); } # merge subsets
	return $protocol.'fonts.googleapis.com/css?family='.implode('|', $fonts_and_types).$msubsets;
}

return false;
}


# readme parser
function fastvelocity_min_readme($url) {

	# read file
	$file = @file_get_contents( $url );
	if (empty($file)) { return '<strong>Readme Parser: readme.txt not found!</strong>'; }
	
	// line end to \n
	$file = preg_replace("/(\n\r|\r\n|\r|\n)/", "\n", $file);

	// headlines
	$s = array('===','==','=' ); 
	$r = array('h2' ,'h3','h4');
	for ( $x = 0; $x < sizeof($s); $x++ ) { 
		$file = preg_replace('/(.*?)'.$s[$x].'(?!\")(.*?)'.$s[$x].'(.*?)/', '$1<'.$r[$x].'>$2</'.$r[$x].'>$3', $file); 
	}

	// inline
	$s = array('\*\*','\`'); 
	$r = array('b'   ,'code');
	for ( $x = 0; $x < sizeof($s); $x++ ) { 
		$file = preg_replace('/(.*?)'.$s[$x].'(?!\s)(.*?)(?!\s)'.$s[$x].'(.*?)/', '$1<'.$r[$x].'>$2</'.$r[$x].'>$3', $file); 
	}
	
	// ' _italic_ '
	$file = preg_replace('/(\s)_(\S.*?\S)_(\s|$)/', ' <em>$2</em> ', $file);
	
	// ul lists	
	$s = array('\*','\+','\-');
	for ( $x = 0; $x < sizeof($s); $x++ )
	$file = preg_replace('/^['.$s[$x].'](\s)(.*?)(\n|$)/m', '<li>$2</li>', $file);
	$file = preg_replace('/\n<li>(.*?)/', '<ul><li>$1', $file);
	$file = preg_replace('/(<\/li>)(?!<li>)/', '$1</ul>', $file);
	
	// ol lists
	$file = preg_replace('/(\d{1,2}\.)\s(.*?)(\n|$)/', '<li>$2</li>', $file);
	$file = preg_replace('/\n<li>(.*?)/', '<ol><li>$1', $file);
	$file = preg_replace('/(<\/li>)(?!(\<li\>|\<\/ul\>))/', '$1</ol>', $file);
	
	// ol screenshots style
	$file = preg_replace('/(?=Screenshots)(.*?)<ol>/', '$1<ol class="readme-parser-screenshots">', $file);
	
	// line breaks
	$file = preg_replace('/(.*?)(\n)/', "<p>$1</p>", $file);
	$file = preg_replace('/(1|2|3|4)(><br\/>)/', '$1>', $file);
	$file = str_ireplace('</ul><br/>', '</ul>', $file);
	
	# cleanup
	$file = str_ireplace('<p></p>', '', $file);
	$file = str_ireplace('<p><h4>', '<h4>', $file);
	$file = str_ireplace('</h4></p>', '</h4>', $file);
	
	// urls
	$file = str_replace('http://www.', 'www.', $file);
	$file = str_replace('www.', 'http://www.', $file);
	$file = preg_replace('#(^|[^\"=]{1})(http://|ftp://|mailto:|https://)([^\s<>]+)([\s\n<>]|$)#', '$1<a target="_blank" href="$2$3">$2$3</a>$4', $file);
	
	# extract faqs
	$prefix = "Frequently Asked Questions";
	$faq = substr($file, strpos($file, $prefix) + strlen($prefix));
	$faq = substr($faq, 0, strpos($faq, '<p><h3>'));
	
	
	return trim($faq);
}


# remove emoji support
function fastvelocity_min_disable_wp_emojicons() {
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
}


# remove UTF8 BOM
function fastvelocity_min_remove_utf8_bom($text) {
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}