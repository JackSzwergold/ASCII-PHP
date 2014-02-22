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
// Here is where the magic happens!

class asciiArtClass {

  private $DEBUG_MODE = FALSE;

  private $image_file = FALSE;

  private $height_resampled = 46;
  private $width_resampled = 46;

  private $block_size_x = 10;
  private $block_size_y = 10;
  private $block_size_x_compensation = 2;

  private $saturation_value = 255;
  private $saturation_multiplier = 3;
  private $saturation_decimal_places = 4;

  private $overlay_tile = 'css/brick.png';

  private $flip_horizontal = FALSE;
  private $php_version_imageflip = 5.5;
  private $orientation = 'square';

  private $directory_permissions = 0775;
  private $file_permissions = 0664;

  private $cache_path = array('json' => 'cache/data/', 'gif' => 'cache/media/', 'jpeg' => 'cache/media/', 'png' => 'cache/media/');
  private $image_types = array('gif', 'jpeg', 'png');
  private $image_quality = array('gif' => 100, 'jpeg' => 100, 'png' => 9);

  private $character_set = NULL;
  private $character_set_count = 0;
  private $character_set_shuffle = FALSE;
  private $character_set_reverse = FALSE;

  public function __construct() {
  } // __construct


  public function debug_mode($DEBUG_MODE) {

    $this->DEBUG_MODE = $DEBUG_MODE;

  } // debug_mode


  public function set_image($image_file) {
    if (!empty($image_file)) {
      $this->image_file = $image_file;
    }
  } // set_image


  // Generate the pixel boxes.
  function set_block_size_x ($block_size_x = null) {
    if (!empty($block_size_x)) {
      $this->block_size_x = $block_size_x;
    }
  } // set_block_size_x


  // Set the block y size.
  function set_block_size_y ($block_size_y = null) {
    if (!empty($block_size_y)) {
      $this->block_size_y = $block_size_y;
    }
  } // set_block_size_y


  // Set the block x size compensation.
  function set_block_size_x_compensation ($block_size_x_compensation = null) {
    if (!empty($block_size_x_compensation)) {
      $this->block_size_x_compensation = $block_size_x_compensation;
    }
  } // set_block_size_x_compensation


  // Generate the pixel boxes.
  function set_character_sets ($character_set_shuffle = FALSE, $character_set_reverse = FALSE) {

    // Set the character set shuffle value.
    if (!empty($character_set_shuffle)) {
      $this->character_set_shuffle = $character_set_shuffle;
    }

    // Set the character set reverse value.
    if (!empty($character_set_reverse)) {
      $this->character_set_reverse = $character_set_reverse;
    }

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

    if ($this->character_set_shuffle) {
      shuffle($character_sets);
      $this->character_set = $character_sets[0];
    }
    else {
      $this->character_set = $character_sets_short[3];
    }

    if ($this->character_set_reverse) {
      $this->character_set = array_reverse($this->character_set);
    }

    $this->character_set_count = count($this->character_set);

  } // set_character_sets


  // Generate the ascii art.
  function generate_ascii_art () {

    // Set the image source.
    $image_source = imagecreatefromjpeg($this->image_file);

    // Set the block size.
    $block_size_x = $this->block_size_x;
    $block_size_y = $this->block_size_y;
    $block_size_x = $block_size_x / $this->block_size_x_compensation;

    // Get the image dimensions.
    $width_resampled = imagesx($image_source) / $block_size_x;
    $height_resampled = imagesy($image_source) / $block_size_y;

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
        $rgb_sat['red'] = ($rgb_array['red'] / ($this->saturation_value * $this->saturation_multiplier));
        $rgb_sat['green'] = ($rgb_array['green'] / ($this->saturation_value * $this->saturation_multiplier));
        $rgb_sat['blue'] = ($rgb_array['blue'] / ($this->saturation_value * $this->saturation_multiplier));
        $saturation = round(array_sum($rgb_sat), $this->saturation_decimal_places) . PHP_EOL;

        // Get the character key.
        $character_key = intval($saturation * ($this->character_set_count - 1));

        // Setting the ASCII art row.
        $ascii_art_row[] = $this->character_set[$character_key];

      }

      $ascii_art_array[] = $ascii_art_row;

    }

    return $ascii_art_array;

  } // generate_ascii_art


} // asciiArtClass

?>