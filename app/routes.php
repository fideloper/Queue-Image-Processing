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

    $file = Input::file('file');

    $s3 = AWS::get('s3');
    $s3->putObject(array(
        'Bucket'      => 'testprocqueue',
        'Key'         => $file->getClientOriginalName(),
        'SourceFile'  => $file->getRealPath(),
        'ContentType' => $file->getClientMimeType(),
    ));

    return 'success';
});