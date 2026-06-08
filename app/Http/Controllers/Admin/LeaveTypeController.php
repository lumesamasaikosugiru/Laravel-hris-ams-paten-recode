<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class LeaveTypeController extends Controller
{
    public function index() { return view('admin.leave-types.index'); }
}
