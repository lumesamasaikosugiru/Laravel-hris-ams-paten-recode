<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;

class CareerController extends Controller
{
    public function index()
    {
        $jobs = JobVacancy::with(['school', 'department', 'position'])
            ->withCount('applicants')
            ->open()
            ->when(request('search'), fn($q) =>
                $q->where('title', 'like', '%' . request('search') . '%'))
            ->when(request('type'), fn($q) =>
                $q->where('employment_type', request('type')))
            ->latest('open_date')
            ->paginate(10);

        return view('public.careers.index', compact('jobs'));
    }

    public function show(JobVacancy $jobVacancy)
    {
        abort_if($jobVacancy->status !== 'open', 404);
        $job = $jobVacancy->load(['school', 'department', 'position']);
        $job->loadCount('applicants');
        return view('public.careers.show', compact('job'));
    }

    public function apply(JobVacancy $jobVacancy)
    {
        abort_if($jobVacancy->status !== 'open', 404, 'Lowongan tidak tersedia.');
        $job = $jobVacancy->load(['school', 'department', 'position']);
        return view('public.careers.apply', compact('job'));
    }
}
