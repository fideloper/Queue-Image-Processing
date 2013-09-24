<?php namespace Proc\Worker;

class ImageProcessor {

    protected $width;
    protected $height;
    protected $image;

    public function fire($job, $data)
    {
        $s3 = \Aws::get('s3');

        $response = $s3->getObject(array(
            'Bucket'      => $data['bucket'],
            'Key'         => $data['key'],
        ));

        $this->image = imagecreatefromstring( (string)$response->get('Body') );

        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);

        $resized = $this->resize(10, 10, $data['mimetype']);

        $s3->putObject(array(
            'Bucket'      => 'testprocqueue',
            'Key'         => $data['key'].'10x10.jpg',
            'Body'        => $resized,
            'ContentType' => $data['mimetype'],
        ));

    }

    protected function resize($width, $height, $mime, $forcesize = false)
    {
        /* optional. if file is smaller, do not resize. */
        if ($forcesize === false) {
            if ($width > $this->width && $height > $this->height) {
                $width  = $this->width;
                $height = $this->height;
            }
        }

        $new_image = imagecreatetruecolor($width, $height);

        /* Check if this image is PNG or GIF, then set if Transparent */
        if ( strrpos($mime, 'gif') !== false || strrpos($mime, 'png') !== false ) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->image = $new_image;

        ob_start();
        imagejpeg($this->image);
        $imageData = ob_get_contents();
        ob_end_clean();

        return $imageData;
    }

}