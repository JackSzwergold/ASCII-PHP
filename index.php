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
 *          2014-02-21, js: adding more options for flexibilty.
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
// Set an array of mode options.

$mode_options = array();

$mode_options['micro']['width'] = 6;
$mode_options['micro']['height'] = 6;
$mode_options['micro']['block_size_x'] = 10;
$mode_options['micro']['block_size_y'] = 10;
$mode_options['micro']['how_many'] = 25;

$mode_options['tiny']['width'] = 12;
$mode_options['tiny']['height'] = 12;
$mode_options['tiny']['block_size_x'] = 10;
$mode_options['tiny']['block_size_y'] = 10;
$mode_options['tiny']['how_many'] = 16;

$mode_options['small']['width'] = 23;
$mode_options['small']['height'] = 23;
$mode_options['small']['block_size_x'] = 10;
$mode_options['small']['block_size_y'] = 10;
$mode_options['small']['how_many'] = 9;

$mode_options['large']['width'] = 46;
$mode_options['large']['height'] = 46;
$mode_options['large']['block_size_x'] = 10;
$mode_options['large']['block_size_y'] = 10;
$mode_options['large']['how_many'] = 1;

$mode_options['mega']['width'] = 72;
$mode_options['mega']['height'] = 72;
$mode_options['mega']['block_size_x'] = 6;
$mode_options['mega']['block_size_y'] = 6;
$mode_options['mega']['how_many'] = 1;

//**************************************************************************************//
// Set the mode.

if (FALSE) {
  $mode_keys = array_keys($mode_options);
  shuffle($mode_keys);
  $mode = $mode_keys[0];
}
else {
  $mode = 'mega';
}

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
$block_size = 10;

$asciiArtClass = new asciiArtClass();
$asciiArtClass->set_image($image_file);
$asciiArtClass->set_character_sets(TRUE, TRUE);
$asciiArtClass->set_block_size_x($mode_options[$mode]['block_size_x']);
$asciiArtClass->set_block_size_y($mode_options[$mode]['block_size_y']);
$asciiArtClass->set_block_size_x_compensation(2);
$ascii_art_array = $asciiArtClass->generate_ascii_art();

//**************************************************************************************//
// Process the ASCII art array.

$final_ascii_art_array = array();
$raw_row = '';
foreach($ascii_art_array as $ascii_art_row) {
  $raw_row = htmlentities(implode('', $ascii_art_row));
  // $final_ascii_art_array[] = sprintf('<nowrap>%s</nowrap>', $raw_row);
  $final_ascii_art_array[] = $raw_row;
}
$final_ascii = implode('<br />', $final_ascii_art_array);

//**************************************************************************************//
// Init the "frontendDisplay()" class.

$frontendDisplayClass = new frontendDisplay('text/html', 'utf-8', FALSE, FALSE);
$frontendDisplayClass->setViewMode($mode);
$frontendDisplayClass->setPageTitle('ascii art');
$frontendDisplayClass->setPageDescription('a dynamically generated ascii art image using php, the gd graphics libarary, html &amp; css.');
// $frontendDisplayClass->setPageContentMarkdown('index.md');
$frontendDisplayClass->setPageContent('<pre>' . $final_ascii . '</pre>');
// $frontendDisplayClass->setPageViewport('width=device-width, initial-scale=0.65, maximum-scale=2, minimum-scale=0.65, user-scalable=yes');
$frontendDisplayClass->setPageRobots('noindex, nofollow');
$frontendDisplayClass->setJavascripts(array('script/common.js'));
$frontendDisplayClass->initContent();
