<?php

namespace App\Http\Controllers;

use App\Models\Art;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $arts = Art::with('user')->orderBy('created_at', 'desc')->limit(12)->get();

        return view('home', [
            'arts' => $arts
        ]);
    }
}
