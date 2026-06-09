<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class JobController extends Controller
{
    public function index()
    {
        return view('admin.jobs.index');
    }
}
