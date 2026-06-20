<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * READ-ONLY sejak 20 Juni 2026. Sebelumnya komponen ini punya workflow
 * approve/reject kegiatan luar lokasi (offsite) -- diputuskan untuk
 * dihapus: absensi offsite sekarang otomatis sah saat dicatat lewat
 * Portal (lihat PortalAttendance::doCheckIn/doCheckOut, offsite_status
 * langsung 'approved'), HR cukup butuh VISIBILITAS, bukan keputusan.
 *
 * Kolom offsite_status/offsite_approved_by/dst TETAP disimpan di
 * database sebagai LOG INFORMASI (siapa yang sedang/sudah kegiatan
 * luar, alasan, kapan) -- bukan lagi gerbang keputusan apa pun.
 */
class OffsiteApproval extends Component
{
    use WithPagination;

    public string $filterDate = '';
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Attendance::query()
            ->where('is_offsite', true)
            ->with(['employee.school'])
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->when($this->search, fn($q) => $q->whereHas(
                'employee',
                fn($eq) => $eq->where('name', 'like', "%{$this->search}%")
            ))
            ->orderByDesc('date');

        return view('livewire.admin.offsite-approval', [
            'records' => $query->paginate(15),
        ])->title('Kegiatan Luar Lokasi');
    }
}