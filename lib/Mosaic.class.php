<?php

/**
 * Mosaic Class (Mosaic.class.php) (c) by Jack Szwergold
 *
 * Mosaic Class is licensed under a
 * Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>.
 *
 * w: http://www.preworn.com
 * e: me@preworn.com
 *
 * Created: 2014-01-11, js
 * Version: 2014-01-11, js: creation
 *          2014-01-11, js: development & cleanup
 *          2014-01-12, js: more development & adding new sample images
 *          2014-01-14, js: moving onto creating actual pixelated images.
 *          2014-01-16, js: More improvements including actual image generation.
 *          2014-01-16, js: getting pure JSON saved instead of plain DIVs.
 *          2014-01-18, js: adjustments to allow for additional image orientations.
 *          2014-02-19, js: version check and setting 'pixelate_image_NO_LONGER_USED'.
 *          2014-02-25, js: reworking the cache manager.
 *          2015-05-09, js: cleanup.
 *          2015-12-03, js: now that this is in git, all changes/notes logged to git commits.
 *
 */

//**************************************************************************************//
// Here is where the magic happens!

class imageMosaic {

  public $DEBUG_MODE = FALSE;

  public $image_file = FALSE;

  public $width_source = NULL;
  public $height_source = NULL;

  public $height_resampled = 46;
  public $width_resampled = 46;

  public $block_size_x = 10;
  public $block_size_y = 10;

  public $generate_images = FALSE;
  public $overlay_image = FALSE;
  public $overlay_tile_file = 'css/brick.png';

  public $row_flip_horizontal = FALSE;
  public $row_delimiter = NULL;
  public $php_version_imageflip = 5.5;
  public $orientation = 'square';

  public $directory_permissions = 0775;
  public $file_permissions = 0664;

  public $cache_path = array('json' => 'cache/data/', 'gif' => 'cache/media/', 'jpeg' => 'cache/media/', 'png' => 'cache/media/');
  public $image_types = array('gif', 'jpeg', 'png');
  public $image_quality = array('gif' => 100, 'jpeg' => 100, 'png' => 9);
  public $cache_expiration_in_minutes = 720;

  public function __construct() {
  } // __construct


  public function debug_mode($DEBUG_MODE) {

    $this->DEBUG_MODE = $DEBUG_MODE;

  } // debug_mode


  public function row_flip_horizontal($row_flip_horizontal) {
    if (version_compare(phpversion(), $this->php_version_imageflip, '>')) {
      $this->row_flip_horizontal = $row_flip_horizontal;
    }
  } // row_flip_horizontal


  public function set_image($image_file, $width_resampled, $height_resampled, $block_size) {

    $this->image_file = $image_file;
    $this->width_resampled = $width_resampled;
    $this->height_resampled = $height_resampled;
    $this->block_size_x = $block_size;
    $this->block_size_y = $block_size;

  } // set_image


  // Set the generate images value.
  function set_generate_images ($generate_images = null) {
    if (!empty($generate_images)) {
      $this->generate_images = $generate_images;
    }
  } // set_generate_images


  // Set the overlay image value.
  function set_overlay_image ($overlay_image = null) {
    if (!empty($overlay_image)) {
      $this->overlay_image = $overlay_image;
    }
  } // set_overlay_image


  // Set the row delimiter.
  function set_row_delimiter ($row_delimiter = null) {
    if (!empty($row_delimiter)) {
      $this->row_delimiter = $row_delimiter;
    }
  } // set_row_delimiter


  // Create the filename.
  function create_filename ($filename = '', $extension = '') {

    // Process the filename.
    $filepath_parts = pathinfo($filename);

    $ret_array = array();
    $ret_array[] = $filepath_parts['filename'];
    $ret_array[] = $this->width_resampled;
    // $ret_array[] = $this->height_resampled; // Hack to debug ratio rendering issues.
    $ret_array[] = $this->block_size_x;
    $ret_array[] = $this->block_size_y;
    $ret_array[] = $this->row_flip_horizontal ? 'h_flip' : '';

    $ret_array = array_filter($ret_array);

    $ret = $this->cache_path[$extension] . implode('-', $ret_array) . '.' . $extension;

    return $ret;

  } // create_filename


  // Get image file basename.
  function get_file_basename ($filename = '') {

    return preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename));

  } // get_file_basename


  // Process the image.
  function process_image () {

    // Check if the image actually exists.
    if (!file_exists($this->image_file)) {
      return;
    }

    // Process the JSON filename.
    $json_filename = $this->create_filename($this->image_file, 'json');

    // Get the raw JSON content.
    $raw_json = $this->cache_manager($json_filename);

    // Check if the image JSON file actually exists.
    $image_array = json_decode($raw_json, TRUE);

    // If the pixel object array is empty, then we need to generate & cache the data.
    if ($this->DEBUG_MODE || empty($image_array)) {

      // Ingest the source image for rendering.
      $image_source = imagecreatefromjpeg($this->image_file);

      // Calculate the image ratio.
      $this->calculate_ratio($image_source);

      // Resample the image.
      $image_processed = $this->resample_image($image_source);

      // Set the image data array for the JSON object.
      $image_object_data = $this->build_image_data_object($json_filename, $image_processed);

      // Build the image object.
      // $image_object = $this->build_image_object($image_object_data);

      // Send the image object to the cache manager.
      $raw_json = $this->cache_manager($json_filename, $image_object_data);

      // Pixelate the image via the JSON data.
      $this->generate_image_from_json($json_filename);

      // Cast the image object as an array.
      $image_array = (array) $image_object_data;

    }

    // Get the actual pixel array from the image object.
    $pixel_array_final = $image_array['pixels'];

    // Process the pixel_array
    $blocks = array();
    foreach ($pixel_array_final as $pixel_row) {
      if ($this->row_flip_horizontal) {
        $pixel_row = array_reverse($pixel_row);
      }
      foreach ($pixel_row as $pixel) {
        $blocks[] = $this->generate_pixel_boxes($pixel);
      }
      if (!empty($this->row_delimiter)) {
        $blocks[] = $this->row_delimiter;
      }
    }

    // Return the data.
    $ret = array();

    // If the blocks value isn’t empty, set that value in the output.
    if (!empty($blocks)) {
      $ret['blocks'] = $this->render_pixel_box_container($blocks);
    }

    // If the JSON value isn’t empty, set that value in the output.
    if (!empty($raw_json)) {
      $ret['json'] = $raw_json;
    }

    return $ret;

  } // process_image


  // Build the image data object.
  function build_image_data_object ($json_filename, $image_processed) {

    // Build the object.
    $ret = array();
    $ret['name'] = $this->get_file_basename($json_filename);
    $ret['pixel_size'] = array('width' => $this->block_size_x, 'height' => $this->block_size_y);
    $ret['resampled_size'] = array('width' => $this->width_resampled, 'height' => $this->height_resampled);
    $ret['pixels'] = $this->generate_pixels($image_processed);

    return $ret;

  } // build_image_data_object


  // Build the content object.
  function build_content_object ($content_object_array, $page_base, $page_base_suffix, $extra_endpoints, $type = 'undefined') {

    // Create the data JSON object.
    $parent_obj = new stdClass();

    // Set the endpoints.
    $endpoints = array('self' => $page_base . $page_base_suffix);
    foreach ($extra_endpoints as $endpoint_key => $endpoint_value) {
      $endpoints[$endpoint_value] = BASE_URL . $endpoint_value . '/' . $page_base_suffix;
    }

    // Add the endpoints to the object.
    $parent_obj->links = $endpoints;

    // Set the image data array to the image object.
    $child_obj = new stdClass();
    $child_obj->type = $type;
    $child_obj->attributes = $content_object_array;
    $parent_obj->data = $child_obj;

    return $parent_obj;

  } // build_content_object


  // JSON encoding helper.
  function json_encode_helper ($data, $pretty_print = FALSE) {

    $ret = json_encode((object) $data);
    $ret = str_replace('\/','/', $ret);
    if ($pretty_print) {
      $ret = prettyPrint($ret);
    }

    return $ret;

  } // json_encode_helper


  // Manage caching.
  function cache_manager ($json_filename, $pixel_array = null) {

    $json_content = '';

    // If the '$json_filename' value is empty.
    if (empty($json_filename)) {
      return $json_content;
    }

    // Set the basic time values.
    $modified_time = file_exists($json_filename) ? filemtime($json_filename) : 0;
    $current_time = time();

    // Calculate the time difference in minutes.
    $diff_time_minutes = (($current_time - $modified_time) / 60);

    // Set the boolean for file expired.
    $file_expired = ($diff_time_minutes > $this->cache_expiration_in_minutes);

    if ($file_expired || !empty($pixel_array)) {

      // If the cache directory doesn’t exist, create it.
      if (!is_dir($this->cache_path['json'])) {
        mkdir($this->cache_path['json'], $this->directory_permissions, true);
      }

      // Process the JSON content.
      $json_content = $this->json_encode_helper($pixel_array, FALSE);

      // Cache the pixel blocks to a JSON file.
      $file_handle = fopen($json_filename, 'w');
      fwrite($file_handle, $json_content);
      fclose($file_handle);

    }
    else if (file_exists($json_filename)) {

      // Return the JSON from the file.
      $json_content = file_get_contents($json_filename);

    }

    return $json_content;

  } // cache_manager


  // Calculate the image ratio.
  function calculate_ratio ($image_source) {

    // Get the image dimensions.
    $this->width_source = imagesx($image_source);
    $this->height_source = imagesy($image_source);

    // Determine the orientation.
    $ratio = 1;
    if ($this->width_source > $this->height_source) {
      $this->orientation = 'landscape';
    }
    else if ($this->width_source < $this->height_source) {
      $this->orientation = 'portrait';
    }
    else {
      $this->orientation = 'square';
    }

    if ($this->orientation == 'landscape') {
      $ratio = $this->height_source / $this->width_source;
      $ratio_grow = $this->width_source / $this->height_source;
    }
    else if ($this->orientation == 'portrait') {
      $ratio = $this->width_source / $this->height_source;
      $ratio_grow = $this->height_source / $this->width_source;
    }

    if ($this->orientation == 'landscape') {
      $this->width_resampled = floor($this->width_resampled * 1);
      $this->height_resampled = floor($this->height_resampled * $ratio);
    }
    else if ($this->orientation == 'portrait') {
      $this->width_resampled = floor($this->width_resampled * 1);
      $this->height_resampled = floor($this->height_resampled * $ratio_grow);
    }
    else {
      $this->width_resampled = floor($this->width_resampled * $ratio);
      $this->height_resampled = floor($this->height_resampled * $ratio);
    }

  } // calculate_ratio


  // Resample the image.
  function resample_image ($image_source = null) {

    // Check if the image resource actually exists.
    if (empty($image_source)) {
      return;
    }

    // Set the canvas for the processed image.
    $image_processed = imagecreatetruecolor($this->width_resampled, $this->height_resampled);

    // Process the image via 'imagecopyresampled'
    imagecopyresampled($image_processed, $image_source, 0, 0, 0, 0, $this->width_resampled, $this->height_resampled, $this->width_source, $this->height_source);

    // Get rid of the image to free up memory.
    imagedestroy($image_source);

    return $image_processed;

  } // resample_image


  // Pixelate the image via JSON data.
  function generate_image_from_json ($json_filename) {

    // Load the JSON into an array.
    $pixel_array = json_decode($this->cache_manager($json_filename), TRUE);

    // If the pixel array is empty, bail out of this function.
    if (empty($pixel_array)) {
      return;
    }

    // Calculate the final width & final height
    $width_pixelate = $this->width_resampled * $this->block_size_x;
    $height_pixelate = $this->height_resampled * $this->block_size_y;

    // Set the canvas for the processed image & resample the source image.
    $image_processed = imagecreatetruecolor($width_pixelate, $height_pixelate);
    $background_color = imagecolorallocate($image_processed, 20, 20, 20);
    imagefill($image_processed, 0, 0, IMG_COLOR_TRANSPARENT);

    // Process the pixel_array
    $blocks = array();
	foreach ($pixel_array['pixels'] as $position_y => $pixel_row) {
	  $box_y = ($position_y * $this->block_size_y);
	  foreach ($pixel_row as  $position_x => $pixel) {
	    $box_x = ($position_x * $this->block_size_x);
	    $color = imagecolorclosest($image_processed, $pixel['rgba']['red'], $pixel['rgba']['green'], $pixel['rgba']['blue']);
	    imagefilledrectangle($image_processed, $box_x, $box_y, ($box_x + $this->block_size_x), ($box_y + $this->block_size_y), $color);
	  }
	}


    // Place a tiled overlay on the image.
    if ($this->row_flip_horizontal) {
      imageflip($image_processed, IMG_FLIP_HORIZONTAL);
    }

    // Apply a gaussian blur.
    if (FALSE) {
      $blur_matrix = array(array(1.0, 2.0, 1.0), array(2.0, 4.0, 2.0), array(1.0, 2.0, 1.0));
      imageconvolution($image_processed, $blur_matrix, 16, 0);
    }

    // Process the filename & save the image files.
    if ($this->generate_images) {

      if ($this->overlay_image) {
        // Place a tiled overlay on the image.
        $tiled_overlay = imagecreatefrompng($this->overlay_tile_file);
        imagealphablending($image_processed, true);
        imagesettile($image_processed, $tiled_overlay);
        imagefilledrectangle($image_processed, 0, 0, $width_pixelate, $height_pixelate, IMG_COLOR_TILED);
        imagedestroy($tiled_overlay);
      }

      $image_filenames = array();
      foreach ($this->image_types as $image_type) {

        // If the cache directory doesn’t exist, create it.
        if (!is_dir($this->cache_path[$image_type])) {
          mkdir($this->cache_path[$image_type], $this->directory_permissions, true);
        }

        // Process the filename & generate the image files.
        $filename = $this->create_filename($this->image_file, $image_type);
        if ($image_type == 'gif' && !file_exists($filename)) {
          // imagegif($image_processed, $filename, $this->image_quality['gif']);
          imagegif($image_processed, $filename);
        }
        else if ($image_type == 'jpeg' && !file_exists($filename)) {
          imagejpeg($image_processed, $filename, $this->image_quality['jpeg']);
        }
        else if ($image_type == 'png' && !file_exists($filename)) {
          imagepng($image_processed, $filename, $this->image_quality['png']);
        }
      }
    }

    imagedestroy($image_processed);

  } // generate_image_from_json


  // Generate the pixel boxes.
  function generate_pixel_boxes ($rgb_array) {

    $block_dimensions = sprintf('height: %spx; width: %spx;', $this->block_size_x, $this->block_size_y);

    if (FALSE) {
      $rgb_final = sprintf('rgb(%s)', implode(',', $rgb_array['rgba']));
      $block_rgb = sprintf('background-color: %s;', $rgb_final);
      $block_style = $block_dimensions . ' ' . $block_rgb;
    }
    else {
      $hex_final = $this->rgb_to_hex($rgb_array['rgba']);
      $block_hex = sprintf('background-color: #%s;',  $hex_final);
      $block_style = $block_dimensions . ' ' . $block_hex;
    }

    $ret = sprintf('<div class="PixelBox" style="%s">', $block_style)
         . '</div><!-- .PixelBox -->' . "\r\n"
         ;

    return $ret;

  } // generate_pixel_boxes


  // Convert RGB values to .
  function rgb_to_hex ($rgb_array) {
    return sprintf("%02X%02X%02X", $rgb_array['red'], $rgb_array['green'], $rgb_array['blue']);
  } // rgb_to_hex


  // Generate the pixels.
  function generate_pixels ($image_processed) {

    $order = 0;
    $ret = array();
    for ($height = 0; $height < $this->height_resampled; $height++) {

     $rows = array();
     for ($width = 0; $width <= $this->width_resampled; $width++) {

        $color_index = @imagecolorat($image_processed, $width, $height);

        if (FALSE) {
          $rgb_array = array();

          $red = ($color_index >> 16) & 0xFF;
          $green = ($color_index >> 8) & 0xFF;
          $blue = $color_index & 0xFF;

          $rgb_array['red'] = intval($red);
          $rgb_array['green'] = intval($green);
          $rgb_array['blue'] = intval($blue);
        }
        else {
          $rgb_array = imagecolorsforindex($image_processed, $color_index);
        }

        if ($width != $this->width_resampled) {
          // $rows[] = array('x' => $width, 'y' => $height, 'width' => $this->block_size_x, 'height' => $this->block_size_y, 'order' => $order, 'hex' => $this->rgb_to_hex($rgb_array), 'rgba' => $rgb_array);
          $rows[] = array('x' => $width, 'y' => $height, 'order' => $order, 'hex' => $this->rgb_to_hex($rgb_array), 'rgba' => $rgb_array);
          $order++;
        }
        else {
          // $rows[] = $rgb_array;
        }

        if ($width == $this->width_resampled) {
          $ret[] = $rows;
        }


      } // $width loop.

    } // $height loop.

    // Get rid of the image to free up memory.
    imagedestroy($image_processed);

    return $ret;

  } // generate_pixels


  // Render the pixel boxes into a container.
  function render_pixel_box_container ($blocks) {

    $css_width = $this->width_resampled * $this->block_size_x;
    // $css_height = $this->height_resampled * $this->block_size_y;

    // $block_container_dimensions = sprintf('width: %spx; height: %spx;', $css_width, $css_height);
    $block_container_dimensions = sprintf('width: %spx;', $css_width);

    $ret = sprintf('<div class="PixelBoxContainer" style="%s">' . "\r\n", $block_container_dimensions)
         . implode('', $blocks)
         . '</div><!-- .PixelBoxContainer -->' . "\r\n"
         ;

    return $ret;

  } // render_pixel_box_container


  // Render the image straight to the browser.
  function render_image ($image_processed) {

    // Set  the output header; in this case making it a JPEG.
    header('Content-Type: image/jpeg');

    // Output the image; note that 'null' is set in the second option to prevent a file from being saved.
    imagejpeg($image_processed, null, 60);

  } // renderImage


} // imageMosaic

?>