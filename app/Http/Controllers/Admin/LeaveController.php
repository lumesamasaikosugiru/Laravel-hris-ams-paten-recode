<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LeaveController extends Controller
{
    public function index()   { return view('admin.leaves.index'); }
    public function balance() { return view('admin.leaves.balance'); }
}
