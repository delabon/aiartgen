<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller
{
    public function show(string $name): BinaryFileResponse|Response
    {
        $path = Config::get('services.dirs.arts') . '/' . $name;

        if (!file_exists($path) || !is_readable($path)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->file($path);
    }
}
