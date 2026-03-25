<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redirect to templates explore page
     */
    public function index()
    {
        return redirect()->route('design.templates.explore');
    }
}











