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

Install Laravel:

    $ composer create-project laravel/laravel myproject

Add Composer requirements:

    {
        "require": {
            "laravel/framework": "4.0.*",
            "pda/pheanstalk": "dev-master",
            "aws/aws-sdk-php-laravel": "1.*"
        }
    }

Followed by:

    $ composer update

Setup AWS in Laravel:

    $ php artisan config:publish aws/aws-sdk-php-laravel

    # And then edit app/config/packages/aws/aws-sdk-php-laravel.php
    # And then add `Aws\Laravel\AwsServiceProvider` Service Provider
    # And then (optionally) add Aws Facade

Upload a file to S3:

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

Setup Beastalkd and server requirements. Some [info on installing Beanstalkd](http://fideloper.com/ubuntu-beanstalkd-and-laravel4):

    $ sudo apt-get update
    $ sudo apt-get install beanstalkd supervisor

