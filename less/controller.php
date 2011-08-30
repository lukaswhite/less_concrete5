<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * Support for the dynamic stylesheet language, Less
 * 
 * @author Lukas White <hello@lukaswhite.com>
 * @copyright (c) 2011 Lukas White
 * 
 */
class LessPackage extends Package {

     protected $pkgHandle = 'less';
     protected $appVersionRequired = '5.3.0';
     protected $pkgVersion = '1.0';

     public function getPackageDescription() {
        return t("Allows support for compiling Less files into CSS.");
     }

     public function getPackageName() {
        return t("Less");
     }
		 
		 public function install() {
				$pkg = parent::install();
		 }
    
}