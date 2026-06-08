<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class SchoolController extends Controller
{
    public function index() { return view('admin.schools.index'); }
}
