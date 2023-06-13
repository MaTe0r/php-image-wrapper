# php-image-wrapper
A very simple PHP Image Wrapper that use the Imagick library

# How to use
Just include the PHP script and work with images

```php
<?php

// include the script
require ('Img.php');


try {
  
  // 1st example : load image from path on server, crop it and save it
  $image = Img::loadFromPath("/path/to/image");
  $image->crop(800, 600);
  $image->save("/path/to/save/image");
  
  // you can chain the method directly
  Img::loadFromPath("/path/to/image")->crop(800, 600)->save("/path/to/save/image");
  
  //
  
catch (Exception $exception) {
  echo $exception->getMessage(); 
}
