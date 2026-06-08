<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index() { return view('admin.departments.index'); }
}
