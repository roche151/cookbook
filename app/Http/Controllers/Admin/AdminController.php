<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecipeRevision;

class AdminController extends Controller
{
    public function index()
    {
        $pendingCount = RecipeRevision::where('status', 'pending')->count();
        
        return view('admin.index', compact('pendingCount'));
    }
}
