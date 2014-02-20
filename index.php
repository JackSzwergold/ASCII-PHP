<?php

/**
 * ASCII Art Class (ascii_art.class.php)
 *
 * Programming: Jack Szwergold <JackSzwergold@gmail.com>
 *
 * Created: 2014-02-19, js
 * Version: 2014-02-19, js: creation
 *          2014-02-19, js: development & cleanup
 *
 */

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

$file = $image_files[0];

// Set the image source.
$image_source = imagecreatefromjpeg($file);

//**************************************************************************************//
// Character set grayscales.

// Long character grayscale character sets.
$character_sets_long = array();
$character_sets_long[] = str_split("\$@B%8&WM#*oahkbdpqwmZO0QLCJUYXzcvunxrjft/\|()1{}[]?-_+~<>i!lI;:,\"^`'. ");
$character_sets_long[] = str_split("@MBHENR#KWXDFPQASUZbdehx*8Gm&04LOVYkpq5Tagns69owz\$CIu23Jcfry%1v7l+it[] {}?j|()=~!-/<>\"^_';,:`. ");
$character_sets_long[] = str_split("@#\$%&8BMW*mwqpdbkhaoQ0OZXYUJCLtfjzxnuvcr[]{}1()|/?Il!i><+_~-;,. ");

// Short character grayscale character sets.
$character_sets_short = array();
$character_sets_short[] = str_split("#%$*|:.' ");
$character_sets_short[] = str_split("@%#*+=-:. ");
$character_sets_short[] = str_split("@#8&o:*. ");
$character_sets_short[] = str_split("#*+. ");

$character_sets = array_merge($character_sets_short, $character_sets_long);

if (FALSE) {
  shuffle($character_sets);
  $characters = $character_sets[0];
}
else {
  $characters = $character_sets_short[3];
}

$character_count = count($characters);

//**************************************************************************************//
// Here is where the fun begins.

// Set the block size.
$block_size_x_compensation = 2;
$block_size_x = $block_size_y = 4;
$block_size_x = $block_size_x / $block_size_x_compensation;

// Get the image dimensions.
$width_resampled = imagesx($image_source) / $block_size_x;
$height_resampled = imagesy($image_source) / $block_size_y;

// Saturation info.
$saturation_value = 255;
$saturation_multiplier = 3;
$saturation_decimal_places = 4;

// Init the ASCII art array.
$ascii_art_array = array();

// Box X & Y values.
$box_x = $box_y = 0;

for ($position_y = 0; $position_y < $height_resampled; $position_y += 1) {

  // Init the ASCII art row.
  $ascii_art_row = array();

  // Calculate the box Y position.
  $box_y = ($position_y * $block_size_y);

  for ($position_x = 0; $position_x < $width_resampled; $position_x += 1) {
    
    // Calculate the box X position.
    $box_x = ($position_x * $block_size_x);

    // Get the color index.
    $color_index = @imagecolorat($image_source, $box_x, $box_y);
    $rgb_array = imagecolorsforindex($image_source, $color_index);

    // Calculate saturation.
    $rgb_sat = array();
    $rgb_sat['red'] = ($rgb_array['red'] / ($saturation_value * $saturation_multiplier));
    $rgb_sat['green'] = ($rgb_array['green'] / ($saturation_value * $saturation_multiplier));
    $rgb_sat['blue'] = ($rgb_array['blue'] / ($saturation_value * $saturation_multiplier));
    $saturation = round(array_sum($rgb_sat), $saturation_decimal_places) .  PHP_EOL;

    // Get the character key.
    $character_key = intval($saturation * ($character_count - 1));

    // Setting the ASCII art row.
    $ascii_art_row[] = $characters[$character_key];

  }

  $ascii_art_array[] = $ascii_art_row;

}

// Output the final ASCII
header('Content-Type: text/plain; charset=utf-8');
foreach($ascii_art_array as $ascii_art_row) {
  echo implode('', $ascii_art_row) . PHP_EOL;
}
