<?php

class Img extends Imagick
{
    public static $allowed_extensions = [
        "jpg",
        "jpeg",
        "png",
        "gif",
        "webp"
    ];


    public static $allowed_mime = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/x-webp'
    ];

    
    public static function loadFromHTTP(array $file)
    {
        # check array fields of $_FILES var
        if (!isset($file['name'], $file['size'], $file['type'], $file['tmp_name'])) {
            throw new Exception("file send via HTTP request is not valid");
        }

        # get and check extension
        $extension = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
        if (!in_array($extension, static::$allowed_extensions)) {
            throw new Exception("extension is not an image: ".$extension);
        }

        # check mime type
        if (!in_array($file['type'], static::$allowed_mime)) {
            throw new Exception("mime type is not an image: ".$file['type']);
        }

        # create imagick object
        if (!$image = new static($file['tmp_name'])) {
            throw new Exception("create imagick instance failed");
        }

        return $image;
    }


    public static function loadFromPath(string $path)
    {
        # check path is a file
        if (!is_file($path)) {
            throw new Exception("path is not a file: ".$path);
        }

        # check imagick is ok
        if (!$image = new static(realpath($path))) {
            throw new Exception("invalid imagick path");
        }

        # get and check extension
        if (!in_array($image->getExtension(), static::$allowed_extensions)) {
            throw new Exception("extension is not image: ".$image->getExtension());
        }

        # check mime type
        if (!in_array($image->getImageMimeType(), static::$allowed_mime)) {
            throw new Exception("mime type is not image: ".$imagick->getImageMimeType());
        }

        return $image;
    }


    public static function loadFromURL(string $url)
    {
        # check array fields of $_FILES var
        if (!$content = file_get_contents($url)) {
            throw new Exception("file_get_contents() on URL return empty: ".$url);
        }

        return static::loadFromData($content);
    }


    public static function loadFromData(string $data)
    {
        # create imagick
        if ($image = new static()) {
            throw new Exception("create imagick instance failed");
        }

        if (!$image->readImageBlob($data)) {
            throw new Exception("invalid base64 imagick");
        }

        # get and check extension
        if (!in_array($image->getExtension(), static::$allowed_extensions)) {
            throw new Exception("extension is not image: ".$image->getExtension());
        }

        # check mime type
        if (!in_array($image->getMimeType(), static::$allowed_mime)) {
            throw new Exception("mime type is not image: ".$image->getMimeType());
        }

        return $image;
    }


    public static function loadFromBase64(string $base64)
    {
        $base64 = substr($base64, strpos($base64, 'base64,') + 7);
        return static::loadFromData(base64_decode($base64));
    }


    public function getExtension()
    {
        return strtolower($this->getImageFormat());
    }


    public function getMimeType()
    {
        return $this->getImageMimeType();
    }


    public function getLength()
    {
        return $this->getImageLength();
    }


    public function getName()
    {
        return basename($this->getImageFilename());
    }


    public function getWidth()
    {
        return $this->getImageWidth();
    }


    public function getHeight()
    {
        return $this->getImageHeight();
    }


    public function display()
    {
        header("Content-Type: ".$this->getMimeType());
        echo $this->getImageBlob();
    }


    public function save(string $filepathname)
    {
        if (!$filepath = dirname($filepathname)) {
            throw new Exception('filepath directory is empty: '.$filepath);
        }
        
        if (!is_dir($filepath)) {
            throw new Exception('filepath directory is not a directory: '.$filepath);
        }

        # check filepath exists or try to create it
        if(!mkdir($filepath, 0755, true)) {
            return false;
        }

        return $this->writeImage($filepathname);
    }


    public function resize(int $width, int $height)
    {
        return $this->resizeImage($width, $height, 0, 1, false);
    }


    public function crop(int $width, int $height)
    {
        $offset_x = $this->getWidth() / $width;
        $offset_y = $this->getHeight() / $height;
        $offset_x <= $offset_y ? $this->resizeImage($width, 0, 0, 1) : $this->resizeImage(0, $height, 0, 1);
        return $this->cropImage($width, $height, (int)(abs($this->getWidth() - $width) / 2), (int)(abs($this->getHeight() - $height) / 2));
    }
}
