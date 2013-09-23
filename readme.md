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
        "eventviva/php-image-resize": "dev-master"
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

// Consider file naming:
// md5($file->getClientOriginalName() . new DateTime->format('Y-m-d H:i:s'));

$s3 = AWS::get('s3');
$s3->putObject(array(
    'Bucket'      => 'testprocqueue',
    'Key'         => $file->getClientOriginalName(),
    'SoureFile'   => $file->getRealPath(),
    'ContentType' => $file->getClientMimeType(),
    // ACL to be public? (Not yet)
));

Queue::push('ImageProcessor', array(
    'filename'    => $file->getClientOriginalName(),
    'mimetype' => $file->getClientMimeType(),
));
```