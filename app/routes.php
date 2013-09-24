<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::get('/complete', function()
{
    return View::make('complete');
});

// Upload an image to S3 and
// create a job to process it
Route::post('/', function()
{
    $validator = Validator::make(Input::all(), array(
        'title' => 'required',
        'file'  => 'required|mimes:jpeg,jpg,png',
    ));

    if( $validator->fails() )
    {
        return Redirect::to('/');
    }

    // Upload File
    $file = Input::file('file');

    $s3 = AWS::get('s3');
    $s3->putObject(array(
        'Bucket'      => 'testprocqueue',
        'Key'         => $file->getClientOriginalName(),
        'SourceFile'  => $file->getRealPath(),
        'ContentType' => $file->getClientMimeType(),
    ));

    // Create job
    Queue::push('\Proc\Worker\ImageProcessor', array(
        'bucket'   => 'testprocqueue',
        'key' => $file->getClientOriginalName(),
        'mimetype' => $file->getClientMimeType(),
    ));

    return Redirect::to('/complete');
});