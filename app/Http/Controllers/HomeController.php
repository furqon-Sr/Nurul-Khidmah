<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $news = News::latest()->take(3)->get();
        $heroes = \App\Models\Hero::orderBy('position')->take(3)->get();
        $contents = \App\Models\PageContent::all()->keyBy('section_key');
        return view('home', compact('news', 'heroes', 'contents'));
    }
}
