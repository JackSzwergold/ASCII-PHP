<?php

/**
 * Frontend Display Helpers (frontendDisplayHelpers.php) (c) by Jack Szwergold
 *
 * Frontend Display Helpers is licensed under a
 * Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>.
 *
 * w: http://www.preworn.com
 * e: me@preworn.com
 *
 * Created: 2015-11-10, js
 * Version: 2015-11-10, js: creation
 *          2015-11-10, js: development
 *
 */

//**************************************************************************************//
// Require the basic configuration settings & functions.

require_once BASE_FILEPATH . '/lib/ASCII.class.php';

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
  $VIEW_MODE = $mode_keys[0];
}
else {
  $VIEW_MODE = 'large';
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

$skip_files = array('..', '.', '.DS_Store', 'ignore');
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

$image_files = array_slice($raw_image_files, 0, $mode_options[$VIEW_MODE]['how_many']);

//**************************************************************************************//
// Init the class and roll through the images.

$ASCIIClass = new imageASCII();

// Init the items array.
$items = array();

// Loop through the image files array.
foreach ($image_files as $image_file) {

  // Set the options for the image processing.
  $ASCIIClass->set_image($image_file, $mode_options[$VIEW_MODE]['width'], $mode_options[$VIEW_MODE]['height'], $mode_options[$VIEW_MODE]['block_size']);
  $ASCIIClass->debug_mode(FALSE);
  $ASCIIClass->row_flip_horizontal(FALSE);
  $ASCIIClass->set_row_delimiter('<br />');
  $ASCIIClass->set_generate_images(TRUE);
  $ASCIIClass->set_overlay_image(FALSE);
  $ASCIIClass->flip_character_set(TRUE);
  $ASCIIClass->set_character_sets(TRUE);
  $ASCIIClass->set_ascii_vertical_compensation(2);
  $ASCIIClass->process_ascii(TRUE);

  // Process the image and add it to the items array.
  $processed_image = $ASCIIClass->process_image();
  $items[$image_file]['blocks'] = $processed_image['blocks'];
  $items[$image_file]['json'] = $processed_image['json'];

} // foreach

//**************************************************************************************//
// Use 'array_filter' to filter out the empty images.

$items = array_filter($items);

//**************************************************************************************//
// Place the images in <li> tags.

// Init the image item and related json array.
$image_item_array = $image_json_array = array();

// Loop through the artworks array.
foreach ($items as $file => $image) {

  // Set the image item array value.
  $image_item_array[$file] = sprintf('%s', $image['blocks']);

  // Set the image json array value.
  $image_json_array[$file] = $image['json'];

} // foreach

// Set the body content.
$body_content = sprintf('%s', implode('', $image_item_array));

// Convert the JSON back to an object.
$json_data_array = array();
foreach ($image_json_array as  $image_json_value) {
  $json_data_array[] = json_decode($image_json_value);
}

// Now merge the JSON data object back into the parent image object.
$image_object = $ASCIIClass->build_image_object($json_data_array);

// Process the JSON content.
$json_content = $ASCIIClass->json_encode_helper($image_object);

?>