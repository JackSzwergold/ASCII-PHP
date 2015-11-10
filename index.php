<?php

/**
 * Index Controller (index.php) (c) by Jack Szwergold
 *
 * Index Controller is licensed under a
 * Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>. 
 *
 * w: http://www.preworn.com
 * e: me@preworn.com
 *
 * Created: 2014-02-19, js
 * Version: 2014-02-19, js: creation
 *          2014-02-19, js: development & cleanup
 *          2014-02-20, js: refactoring into a real class.
 *          2014-02-21, js: adding more options for flexibilty.
 *          2014-02-22, js: fixing the way character set flipping works.
 *          2014-02-23, js: renaming the class to Image ASCII.
 *          2014-02-23, js: setting ImageASCII as an extension of ImageMosaic.
 *
 */

//**************************************************************************************//
// Require the basic configuration settings & functions.

require_once 'conf/conf.inc.php';
require_once BASE_FILEPATH . '/common/functions.inc.php';
require_once BASE_FILEPATH . '/lib/frontendDisplay.class.php';
// require_once BASE_FILEPATH . '/lib/Parsedown.php';
// require_once BASE_FILEPATH . '/lib/processimage.class.php';
require_once BASE_FILEPATH . 'lib/imageascii.class.php';

//**************************************************************************************//
// Define the valid arrays.

$VALID_CONTROLLERS = array();
$DISPLAY_CONTROLLERS = array();
$VALID_GET_PARAMETERS = array('_debug');
$VALID_CONTENT_TYPES = array('application/json','text/plain','text/html');
$VALID_CHARSETS = array('utf-8','iso-8859-1','cp-1252');

//**************************************************************************************//
// Set config options.

$DEBUG_OUTPUT_JSON = false;

//**************************************************************************************//
// Set an array of mode options.

$mode_options = array();

$mode_options['micro']['width'] = 20;
$mode_options['micro']['height'] = 20;
$mode_options['micro']['block_size'] = 10;
$mode_options['micro']['how_many'] = 1;

$mode_options['tiny']['width'] = 40;
$mode_options['tiny']['height'] = 40;
$mode_options['tiny']['block_size'] = 10;
$mode_options['tiny']['how_many'] = 1;

$mode_options['small']['width'] = 60;
$mode_options['small']['height'] = 60;
$mode_options['small']['block_size'] = 10;
$mode_options['small']['how_many'] = 1;

$mode_options['large']['width'] = 80;
$mode_options['large']['height'] = 80;
$mode_options['large']['block_size'] = 10;
$mode_options['large']['how_many'] = 1;

$mode_options['mega']['width'] = 132;
$mode_options['mega']['height'] = 132;
$mode_options['mega']['block_size'] = 10;
$mode_options['mega']['how_many'] = 1;

//**************************************************************************************//
// Set the mode.

if (FALSE) {
  $mode_keys = array_keys($mode_options);
  shuffle($mode_keys);
  $mode = $mode_keys[0];
}
else {
  $mode = 'large';
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
// Set the page DIVs array.

$page_divs_array = array();
$page_divs_array[] = 'Wrapper';
$page_divs_array[] = 'Padding';
$page_divs_array[] = 'Content';
$page_divs_array[] = 'Padding';
$page_divs_array[] = 'Section';
$page_divs_array[] = 'Padding';
$page_divs_array[] = 'Middle';
$page_divs_array[] = 'Core';
$page_divs_array[] = 'Padding';
$page_divs_array[] = 'Grid';
$page_divs_array[] = 'Padding';

//**************************************************************************************//
// Instantialize the 'ImageASCIIClass()'.

$ImageASCIIClass = new ImageASCII();
$ImageASCIIClass->set_image($image_file, $mode_options[$mode]['width'], $mode_options[$mode]['height'], $mode_options[$mode]['block_size']);
$ImageASCIIClass->debug_mode(FALSE);
$ImageASCIIClass->row_flip_horizontal(FALSE);
$ImageASCIIClass->set_row_delimiter('<br />');
$ImageASCIIClass->set_generate_images(FALSE);
$ImageASCIIClass->set_overlay_image(FALSE);
$ImageASCIIClass->flip_character_set(TRUE);
$ImageASCIIClass->set_character_sets(TRUE);
$ImageASCIIClass->set_ascii_vertical_compensation(2);
$ImageASCIIClass->process_ascii(TRUE);
$body = $ImageASCIIClass->process_image();

//**************************************************************************************//
// Init the "frontendDisplay()" class.

$frontendDisplayClass = new frontendDisplay('text/html', 'utf-8', FALSE, FALSE);
$frontendDisplayClass->setViewMode($mode);
$frontendDisplayClass->setPageTitle($SITE_TITLE);
$frontendDisplayClass->setPageURL($SITE_URL);
$frontendDisplayClass->setPageCopyright($SITE_COPYRIGHT);
$frontendDisplayClass->setPageDescription($SITE_DESCRIPTION);
// $frontendDisplayClass->setPageContentMarkdown('index.md');
$frontendDisplayClass->setPageContent($body);
$frontendDisplayClass->setPageDivs($page_divs_array);
$frontendDisplayClass->setPageDivWrapper('PixelBoxWrapper');
// $frontendDisplayClass->setPageViewport($SITE_VIEWPORT);
$frontendDisplayClass->setPageRobots($SITE_ROBOTS);
$frontendDisplayClass->setJavascripts(array('script/common.js'));
$frontendDisplayClass->initContent();

?>
