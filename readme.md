# Laravel Upload, Queue, Process

The process followed in this code will:

1. Upload image via form, Send to S3
2. Finish processing form - Image to DB
3. Create queue job
4. Process queue
    1. Download image from S3
    2. Re-size (a few sizes?)
    3. Re-upload each to S3
    4. Save results to DB (mark as processed)

## Get Started

**Install Laravel:**

```shell
$ composer create-project laravel/laravel myproject
```

Add Composer requirements:

```json
{
    "require": {
        "laravel/framework": "4.0.*",
        "pda/pheanstalk": "dev-master",
        "aws/aws-sdk-php-laravel": "1.*",
        "imagine/imagine": "0.6.*@dev"
    }
}
```

Followed by:

```shell
$ composer update
```

**Setup Autoloading:**

Create directory `app/Proc` and then autoload it:

```json
{
    "autoload": {
        "classmap": [
            /* ... */
        ],
        "psr-0": {
            "Proc": "app"
        }
    },
}
```

Then run a `$ composer dump-autoload`.

**Setup AWS in Laravel:**

```shell
$ php artisan config:publish aws/aws-sdk-php-laravel

# And then edit app/config/packages/aws/aws-sdk-php-laravel.php
# And then add `Aws\Laravel\AwsServiceProvider` Service Provider
# And then (optionally) add Aws Facade
```

## Queues

**Setup Beastalkd and server requirements.**

Note: some more [info on installing Beanstalkd](http://fideloper.com/ubuntu-beanstalkd-and-laravel4).

```shell
$ sudo apt-get update
$ sudo apt-get install beanstalkd supervisor
```

**Create a job to resize the images**

```php
<?php namespace Proc\Workers;

class ImageProcessor {

    public function fire($job, $data)
    {

    }

}
```

**Upload a file to S3 and create Queue to process it:**

```php
$file = Input::file('file');

$now = new DateTime;
$hash = md5( $file->getClientOriginalName().$now->format('Y-m-d H:i:s') );
$key = $hash.'.'.$file->getClientOriginalExtension();

$s3 = AWS::get('s3');
$s3->putObject(array(
    'Bucket'      => 'testprocqueue',
    'Key'         => $key,
    'SourceFile'  => $file->getRealPath(),
    'ContentType' => $file->getClientMimeType(),
    // ACL to be public? (Not yet)
));

Queue::push('ImageProcessor', array(
    'bucket'   => 'testprocqueue',
    'hash'     => $hash,
    'key'      => $key,
    'ext'      => $file->getClientOriginalExtension(),
    'mimetype' => $file->getClientMimeType(),
));
```

**Process the Queued item!**

```php
<?php namespace Proc\Worker;

use Aws;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageProcessor {

    protected $width;
    protected $height;
    protected $image;

    public function fire($job, $data)
    {
        $s3 = Aws::get('s3');

        $response = $s3->getObject(array(
            'Bucket'      => $data['bucket'],
            'Key'         => $data['key'],
        ));

        $imagine = new Imagine();
        $image = $imagine->load( (string)$response->get('Body') );

        $size = new Box(100, 100);
        $thumb = $image->thumbnail($size);

        $s3->putObject(array(
            'Bucket'      => 'testprocqueue',
            'Key'         => $data['hash'].'_100x100.'.$data['ext'],
            'Body'        => $thumb->get($data['ext']),
            'ContentType' => $data['mimetype'],
        ));

        // Probaby save file info to a media database here
        // If a user-generated or profile image

    }

}
```