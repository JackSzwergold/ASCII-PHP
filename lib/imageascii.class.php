<?php

/**
 * Image ASCII Class (imageascii.class.php)
 *
 * Programming: Jack Szwergold <JackSzwergold@gmail.com>
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
// The parent image mosaic class.

require_once('imagemosaic.class.php');

//**************************************************************************************//
// Here is where the magic happens!

class ImageASCII extends ImageMosaic {

  public $height_resampled = 80;
  public $width_resampled = 80;

  public $character_set = NULL;
  public $character_set_count = 0;
  public $character_set_shuffle = FALSE;
  public $character_set_flip = FALSE;

  public $ascii_vertical_compensation = 2;
  public $process_ascii = FALSE;

  public $saturation_value = 255;
  public $saturation_multiplier = 3;
  public $saturation_decimal_places = 4;

  // Set to process ascii.
  function process_ascii ($process_ascii = null) {
    if (!empty($process_ascii)) {
      $this->process_ascii = $process_ascii;
    }
  } // process_ascii


  // Set the ascii vertical compensation.
  function set_ascii_vertical_compensation ($ascii_vertical_compensation = null) {
    if (!empty($ascii_vertical_compensation)) {
      $this->ascii_vertical_compensation = $ascii_vertical_compensation;
      $this->height_resampled = $this->height_resampled / $this->ascii_vertical_compensation;
    }
  } // set_ascii_vertical_compensation


  // Set the character sets.
  function flip_character_set ($character_set_flip = FALSE) {
    if ($character_set_flip) {
      $this->character_set_flip = $character_set_flip;
    }
  } // flip_character_set


  // Set the character sets.
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
    $character_sets_short[] = str_split("#&+. ");
    
    // $character_sets = array_merge($character_sets_short, $character_sets_long);
    $character_sets = $character_sets_short;

    if ($this->character_set_shuffle) {
      shuffle($character_sets);
      $this->character_set = $character_sets[0];
    }
    else {
      $this->character_set = $character_sets_short[3];
    }

    if ($this->character_set_flip) {
      $this->character_set = array_reverse($this->character_set);
    }
    $this->character_set_count = count($this->character_set);

  } // set_character_sets


  // Generate the ascii art boxes.
  function generate_pixel_boxes ($rgb_array) {

    // Check if the image actually exists.
    if (empty($rgb_array)) {
      return;
    }

    // Calculate saturation.
    $rgb_sat = array();
    $rgb_sat['red'] = ($rgb_array['red'] / ($this->saturation_value * $this->saturation_multiplier));
    $rgb_sat['green'] = ($rgb_array['green'] / ($this->saturation_value * $this->saturation_multiplier));
    $rgb_sat['blue'] = ($rgb_array['blue'] / ($this->saturation_value * $this->saturation_multiplier));
    $saturation = round(array_sum($rgb_sat), $this->saturation_decimal_places);

    // Get the character key.
    $character_key = intval($saturation * ($this->character_set_count - 1));

    // Setting the ASCII art character.
    // $ret = sprintf('<div>%s</div>', htmlentities($this->character_set[$character_key]));
    $ret = sprintf('<span>%s</span>', htmlentities($this->character_set[$character_key]));

    return $ret;

  } // generate_pixel_boxes

  // Render the pixel boxes into a container.
  function render_pixel_box_container ($blocks) {

    $ret = sprintf('<pre>%s</pre>', implode('', $blocks));

    return $ret;

  } // render_pixel_box_container


} // ImageASCII

?>