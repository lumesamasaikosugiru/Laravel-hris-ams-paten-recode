<?php
namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class PortalController extends Controller
{
    public function home()       { return view('portal.home'); }
    public function attendance() { return view('portal.attendance'); }
    public function leave()      { return view('portal.leave'); }
    public function leaveCreate(){ return view('portal.leave-create'); }
    public function profile()    { return view('portal.profile'); }
}
