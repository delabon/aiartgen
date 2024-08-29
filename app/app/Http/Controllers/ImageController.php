<?php

namespace App\Http\Controllers;

use App\Models\Art;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller
{
    public function show(Art $art): BinaryFileResponse|Response
    {
        $path = storage_path(Config::get('services.dirs.arts')) . '/' . $art->filename;

        if (!file_exists($path) || !is_readable($path)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->file($path);
    }
}
