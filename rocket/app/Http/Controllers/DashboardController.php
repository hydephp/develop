<?php

namespace App\Http\Controllers;

use App\Models\Project;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        return view('dashboard', [
            'project' => Project::get()
        ]);
    }
}
