<?php namespace Proc\Worker;

class ImageProcessor {

    public function fire($job, $data)
    {
        $s3 = Aws::get('s3');
        $resizer = new \Eventviva\ImageResize('picture.jpg');

        $image = $s3->getObject(array(
            'Bucket'      => $data['bucket'],
            'Key'         => $data['key'],
        ));



    }

}