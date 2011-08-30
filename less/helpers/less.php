<?php 
/**
 * @package Helpers
 * @category Less
 * @author Lukas White <hello@lukaswhite.com>
 * @copyright (c) 2011 Lukas White
 */

 /**
  * Helper for the dynamic stylesheet language, Less.
  * 
  * Allows you to create a link to a less file (e.g. from a theme), and it will compile it to CSS and add
  * a link to that.
  * 
  * If the less file has changed more recently than the generated CSS file, it re-generates it.
  * 
  * Uses the Less PHP Compiler by Leaf Corcoran <leafot@gmail.com>
  */
defined('C5_EXECUTE') or die("Access Denied.");
class LessHelper {

	/** 
	 * Takes a Less file, compiles it into CSS, and returns a link to that CSS
	 * 
	 * If the Less file hasn't changed since it last compiled it, it uses the same CSS but once the 
	 * file changes it will compile it again. 
	 *
	 * This function looks for the Less file in several places, including the theme directory and optionally,
	 * a supplied package.
	 * 
	 * @param $file
	 * @param $pkgHandle
	 * @return $str
	 */
	public function link($file, $pkgHandle = null) {

		$fh=loader::helper('file');

		Loader::library('3rdparty/lessc.inc', 'less');
		
		$less = new LessOutputObject();

		// if the first character is a / then that means we just go right through, it's a direct path
		if (substr($file, 0, 1) == '/' || substr($file, 0, 4) == 'http' || strpos($file, DISPATCHER_FILENAME) > -1) {
			$less->compress = false;
			$filename = $file;
		}
		
		$v = View::getInstance();
		// checking the theme directory for it. It's just in the root.
		if ($v->getThemeDirectory() != '' && file_exists($v->getThemeDirectory() . '/' . $file)) {
			$filename = $v->getThemePath() . '/' . $file;
		} else if ($pkgHandle != null) {
			if (file_exists(DIR_BASE . '/' . DIRNAME_PACKAGES . '/' . $pkgHandle . '/' . DIRNAME_CSS . '/' . $file)) {
				$filename = DIR_REL . '/' . DIRNAME_PACKAGES . '/' . $pkgHandle . '/' . DIRNAME_CSS . '/' . $file;
			} else if (file_exists(DIR_BASE_CORE . '/' . DIRNAME_PACKAGES . '/' . $pkgHandle . '/' . DIRNAME_CSS . '/' . $file)) {
				$filename = ASSETS_URL . '/' . DIRNAME_PACKAGES . '/' . $pkgHandle . '/' . DIRNAME_CSS . '/' . $file;
			}
		}
			
		if ($filename == '') {
			if (file_exists(DIR_BASE . '/' . DIRNAME_CSS . '/' . $file)) {
				$filename = DIR_REL . '/' . DIRNAME_CSS . '/' . $file;
			} else {
				$filename = ASSETS_URL_CSS . '/' . $file;
			}
		}
		
		$dest_filename = md5($filename).".css";
		
		
		// Now we have the file to play with
		$less->file = $v->getThemeDirectory() . '/' . $file;

		// if there is no CSS, or the Less has changed more recently than the corresponding
		// CSS, we need to parse it. 		
		if ( ( !file_exists(DIR_BASE . '/' . DIRNAME_CSS . '/' .$dest_filename) ) 
					|| ( filemtime(DIR_BASE . '/' . $filename) > filemtime(DIR_BASE . '/' . DIRNAME_CSS . '/' .$dest_filename) ) ) {
		
			$lessc = new lessc();
			$data = file_get_contents(DIR_BASE . '/' . $filename);
			$output_file = DIRNAME_CSS."/".$dest_filename;
			
			try {
      	$output_data = $lessc->parse($data);              
			  file_put_contents($output_file, $output_data);
			}
			catch (Exception $e) {
				$message = t('LESS ERROR: '). $e->getMessage() .', '. $less->file;
			}
		}

		$less->file = ASSETS_URL_WEB.'/'.DIRNAME_CSS.'/'.$dest_filename;

		$less->file .= (strpos($less->file, '?') > -1) ? '&' : '?';
		$less->file .= 'v=' . md5(APP_VERSION . PASSWORD_SALT);		
		// for the javascript addHeaderItem we need to have a full href available
		$less->href = $less->file;
		if (substr($less->file, 0, 4) != 'http') {
			$less->href = ASSETS_URL_WEB_FULL . $less->file;
		}
		return $less;
	}

}

/** 
 * @access private
 */
class LessOutputObject extends HeaderOutputObject {

	public function __toString() {
		return '<link rel="stylesheet" type="text/css" href="' . $this->file . '" />';
	}
	
}