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

  private $height_resampled = 80;
  private $width_resampled = 80;

} // ImageASCII

?>