<?php

/**
 * ASCII Art Class (ascii_art.class.php)
 *
 * Programming: Jack Szwergold <JackSzwergold@gmail.com>
 *
 * Created: 2014-02-19, js
 * Version: 2014-02-19, js: creation
 *          2014-02-19, js: development & cleanup
 *          2014-02-20, js: refactoring into a real class.
 *
 */

//**************************************************************************************//
// Require the basic configuration settings & functions.

require_once('common/functions.inc.php');
require_once 'lib/frontendDisplay.class.php';
require_once('lib/asciiart.class.php');

//**************************************************************************************//
// Define the valid arrays.

$VALID_CONTROLLERS = array('portfolio');
$DISPLAY_CONTROLLERS = array('portfolio');
$VALID_GET_PARAMETERS = array('_debug', 'portfolio');
$VALID_CONTENT_TYPES = array('application/json','text/plain','text/html');
$VALID_CHARSETS = array('utf-8','iso-8859-1','cp-1252');

//**************************************************************************************//
// Set config options.

$DEBUG_OUTPUT_JSON = false;

//**************************************************************************************//
// Set the image directory.

$image_dir = 'images/';

//**************************************************************************************//
// Check if there is an image directory. If not? Exit.

if (!is_dir($image_dir)) {
  die();
}

//**************************************************************************************//
// Process the images in the directory.

$skip_files = array('..', '.', '.DS_Store','ignore');
$image_files = scandir($image_dir);
$image_files = array_diff($image_files, $skip_files);

if (empty($image_files)) {
  die('Sorry. No images found.');
}

$raw_image_files = array();
foreach ($image_files as $image_file_key => $image_file_value) {
  $raw_image_files[$image_file_key] = $image_dir . $image_file_value;
}

//**************************************************************************************//
// Shuffle the image files.

shuffle($raw_image_files);

//**************************************************************************************//
// Slice off a subset of the image files.

$image_files = array_slice($raw_image_files, 0, 1);

$image_file = $image_files[0];

//**************************************************************************************//
// Instantialize the 'asciiArtClass()'.

// $block_size = intval(rand(10, 20));
$block_size = 6;

$asciiArtClass = new asciiArtClass();
$asciiArtClass->set_image($image_file);
$asciiArtClass->set_character_sets(TRUE);
$asciiArtClass->set_block_size_x($block_size);
$asciiArtClass->set_block_size_y($block_size);
$asciiArtClass->set_block_size_x_compensation(2);
$ascii_art_array = $asciiArtClass->generate_ascii_art();

//**************************************************************************************//
// Process the ASCII art array.

$final_ascii_art_array = array();
foreach($ascii_art_array as $ascii_art_row) {
  $final_ascii_art_array[] = htmlentities(implode('', $ascii_art_row));
}
$final_ascii = implode('<br />', $final_ascii_art_array);

//**************************************************************************************//
// Init the "frontendDisplay()" class.

$frontendDisplayClass = new frontendDisplay('text/html', 'utf-8', FALSE, FALSE);
$frontendDisplayClass->setViewMode('mega');
$frontendDisplayClass->setPageTitle('ascii art');
$frontendDisplayClass->setPageDescription('a dynamically generated ascii art image using php, the gd graphics libarary, html &amp; css.');
// $frontendDisplayClass->setPageContentMarkdown('index.md');
$frontendDisplayClass->setPageContent('<pre>' . $final_ascii . '</pre>');
$frontendDisplayClass->setPageViewport('width=device-width, initial-scale=0.65, maximum-scale=2, minimum-scale=0.65, user-scalable=yes');
$frontendDisplayClass->setPageRobots('noindex, nofollow');
$frontendDisplayClass->setJavascripts(array('script/common.js'));
$frontendDisplayClass->initContent();
