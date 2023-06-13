<?php

class Img
{
    public static $allowed_extensions = array("jpg", "jpeg", "png", "gif", "webp");
    public static $allowed_mime = array('image/jpeg', 'image/png', 'image/webp', 'image/x-webp');
    private $_imagick;
    public $path;


    public static function loadFromHTTP(array $file)
    {
        # check array fields of $_FILES var
        if (!isset ($file['name'], $file['size'], $file['type'], $file['tmp_name'])) {
            throw new Exception("Missing field in HTTP _FILES");
        }

        # get and check extension
        $extension = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
        if (!in_array($extension, static::$allowed_extensions)) {
            throw new Exception("extension is not image: ".$extension);
        }

        # check mime type
        if (!in_array($file['type'], static::$allowed_mime)) {
            throw new Exception("mime type is not image: ".$file['type']);
        }

        # create imagick object
        if (!$imagick = new Imagick($file['tmp_name'])) {
            throw new Exception("create imagick instance failed");
        }

        # return img object
        $img = new static();
        $img->_imagick = $imagick;
        return $img;
    }


    public static function loadFromPath(string $path)
    {
        # check array fields of $_FILES var
        if (!is_file($path)) {
            throw new Exception("path is not a file: ".$path);
        }

        # check imagick is ok
        if (!$imagick = new Imagick(realpath($path))) {
            throw new Exception("invalid imagick path");
        }

        # get and check extension
        if (!in_array(strtolower($imagick->getImageFormat()), static::$allowed_extensions)) {
            throw new Exception("extension is not image: ".strtolower($imagick->getImageFormat()));
        }

        # check mime type
        if (!in_array($imagick->getImageMimeType(), static::$allowed_mime)) {
            throw new Exception("mime type is not image: ".$imagick->getImageMimeType());
        }

        # return img object
        $img = new static();
        $img->_imagick = $imagick;
        $img->path = $path;
        return $img;
    }


    public static function loadFromURL(string $url)
    {
        # check array fields of $_FILES var
        if (!$content = file_get_contents($url)) {
            throw new Exception("url content is invalid: ".$url);
        }

        return static::loadFromData($content);
    }


    public static function loadFromData(string $data)
    {
        # create imagick
        $imagick = new Imagick();
        $imagick->readImageBlob($data);
       
        # check imagick is ok
        if (!$imagick) {
            throw new Exception("invalid base64 imagick");
        }

        # get and check extension
        if (!in_array(strtolower($imagick->getImageFormat()), static::$allowed_extensions)) {
            throw new Exception("extension is not image: ".strtolower($imagick->getImageFormat()));
        }

        # check mime type
        if (!in_array($imagick->getImageMimeType(), static::$allowed_mime)) {
            throw new Exception("mime type is not image: ".$imagick->getImageMimeType());
        }

        # return img object
        $img = new static();
        $img->_imagick = $imagick;
        return $img;
    }


    public static function loadFromBase64(string $base64)
    {
        $base64 = substr($base64, strpos($base64, 'base64,') + 7);
        return static::loadFromData(base64_decode($base64));
    }


    public function getExtension()
    {
        return $this->_imagick ? strtolower($this->_imagick->getImageFormat()) : null;
    }


    public function getSize()
    {
        return $this->_imagick ? $this->_imagick->getImageLength() : null;
    }


    public function getName()
    {
        return $this->_imagick ? basename($this->_imagick->getImageFilename()) : null;
    }


    public function getWidth()
    {
        return $this->_imagick ? $this->_imagick->getImageWidth() : null;
    }


    public function getHeight()
    {
        return $this->_imagick ? $this->_imagick->getImageHeight() : null;
    }


    public function display()
    {
        if ($this->_imagick) {
            header("Content-Type: ".$this->_imagick->getImageMimeType());
            echo $this->_imagick->getImageBlob();
        }
    }


    public function getUrl()
    {
        return str_replace(DOC_ROOT.DIR_ROOT, '/', $this->path);
    }


    public function upload(string $filepathname, int $width = null, int $height = null, bool $crop = true)
    {
        # check imagick
        if (!$this->_imagick) {
            return false;
        }

        # check filepath exists or try to create it
        $filepath = dirname($filepathname);
        if(!is_dir($filepath) && !mkdir($filepath, 0755, true)) {
            return false;
        }

        # check width / height
        $width = $width ? $width : $this->getWidth();
        $height = $height ? $height : $this->getHeight();
        if (!$width || !$height) {
            return $this->_imagick->writeImage($filepathname);
        }

        # if crop, try to crop
        if ($crop && !$this->crop($width, $height)) {
            return false;
        }

        # if not crop, try to resize
        if (!$crop && !$this->resize($width, $height)) {
            return false;
        }

        return $this->_imagick->writeImage($filepathname);
    }


    public function resize(int $width, int $height)
    {
        return $this->_imagick->resizeImage($width, $height, 0, 1, false);
    }


    public function crop(int $width, int $height)
    {
        $offset_x = $this->getWidth() / $width;
        $offset_y = $this->getHeight() / $height;
        $offset_x <= $offset_y ? $this->_imagick->resizeImage($width, 0, 0, 1) : $this->_imagick->resizeImage(0, $height, 0, 1);
        return $this->_imagick->cropImage($width, $height, (int)(abs($this->getWidth() - $width) / 2), (int)(abs($this->getHeight() - $height) / 2));
    }
}