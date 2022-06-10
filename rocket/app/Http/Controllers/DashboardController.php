<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Hyde\Framework\Services\CollectionService;

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
            'project' => Project::get(),
            'pages' => $this->getContentList(),
        ]);
    }

    protected function getContentList(): array
    {
        return array_merge([
           'Blade Pages' => CollectionService::getBladePageList(),
           'Markdown Pages' => CollectionService::getMarkdownPageList(),
           'Markdown Posts' => CollectionService::getMarkdownPostList(),
           'Documentation Pages' => CollectionService::getDocumentationPageList(),
        ]);
    }
}
