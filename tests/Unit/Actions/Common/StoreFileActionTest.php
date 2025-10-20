<?php

declare(strict_types=1);

use App\Actions\Common\StoreFileAction;
use App\Exceptions\FileStorageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores a file and returns its URL', function () {
    Storage::fake('s3');

    $directory = 'test-uploads';
    $filename = 'test-image';
    $extension = 'jpg';

    $file = UploadedFile::fake()->image("photo.$extension");

    $url = (new StoreFileAction())->handle($file, $directory, $filename);

    expect($url)->toBeString();
    expect($url)->toContain($directory, $filename, $extension);
});

it('throws an exception when file storage fails', function () {
    Storage::fake('s3');

    Storage::shouldReceive('disk->putFileAs')
        ->andReturn(false);

    $directory = 'test-uploads';
    $filename = 'test-image';
    $extension = 'jpg';

    $file = UploadedFile::fake()->image("photo.$extension");

    (new StoreFileAction())->handle($file, $directory, $filename);
})->throws(FileStorageException::class);
