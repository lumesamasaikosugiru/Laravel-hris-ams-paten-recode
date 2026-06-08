<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

class SkillController extends Controller
{
    public function index() { return view('admin.skills.index'); }
}
