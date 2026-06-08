<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class PositionController extends Controller
{
    public function index() { return view('admin.positions.index'); }
}
