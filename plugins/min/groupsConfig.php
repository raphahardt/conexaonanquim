<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/

return array(
    // 'js' => array('//js/file1.js', '//js/file2.js'),
    // 'css' => array('//css/file1.css', '//css/file2.css'),
    'essentials' => array('//js/core/modernizr.js'),
    'styles' => array('//css/base.css'),
    'core' => array('//js/core/jquery.js', '//js/core/bootstrap.js'),
    'reader_dependencies' => array('//js/plugins/jquery.cookie.js', '//js/plugins/jquery.transit.js', '//js/plugins/jquery.event.hash.js', '//js/plugins/jquery.event.scroll.js'),
    'reader' => array('//js/helpers/cnreader.js')
);