<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApplicantController extends Controller
{
    public function index()
    {
        return view('admin.applicants.index');
    }
}
