<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Aturan bisnis yang terpusat.
     * Semua kebijakan cuti Yayasan Fatahillah ada di sini.
     */

    // ── Kebijakan ─────────────────────────────────────────────

    /** Minimal hari pengajuan sebelum tanggal mulai cuti */
    const MIN_DAYS_BEFORE = 5;

    /** Jenis cuti yang tidak boleh diambil guru (by nama) */
    const EXCLUDED_FOR_GURU = ['cuti tahunan'];

    // ── Validasi ──────────────────────────────────────────────

    /**
     * Validasi semua aturan bisnis pengajuan cuti.
     * Return array of errors, kosong = valid.
     */
    public static function validate(
        Employee $employee,
        LeaveType $leaveType,
        string $startDate,
        string $endDate,
        ?array $balance
    ): array {
        $errors = [];

        // 1. Guru tidak boleh ambil jenis cuti tertentu
        if (
            $employee->is_guru &&
            in_array(strtolower($leaveType->name), self::EXCLUDED_FOR_GURU)
        ) {
            $errors['leave_type_id'] =
                "Guru tidak diperkenankan mengambil {$leaveType->name}.";
        }

        // 2. Gender tidak sesuai
        if (
            $leaveType->gender !== 'all' &&
            $leaveType->gender !== $employee->gender
        ) {
            $errors['leave_type_id'] =
                "Jenis cuti ini hanya untuk " .
                ($leaveType->gender === 'female' ? 'perempuan' : 'laki-laki') . ".";
        }

        // 3. Minimal H-5 sebelum tanggal mulai
        $minStart = now()->addDays(self::MIN_DAYS_BEFORE)->startOfDay();
        if (Carbon::parse($startDate)->lt($minStart)) {
            $errors['start_date'] =
                "Pengajuan harus minimal H-" . self::MIN_DAYS_BEFORE . " sebelum tanggal mulai. " .
                "Paling cepat: " . $minStart->translatedFormat('d M Y') . ".";
        }

        // 4. Tanggal selesai tidak boleh sebelum tanggal mulai
        if ($endDate < $startDate) {
            $errors['end_date'] = "Tanggal selesai harus sama atau setelah tanggal mulai.";
        }

        // 5. Hari kerja tidak boleh melebihi saldo
        $days = LeaveRequest::countWorkDays($startDate, $endDate);
        $maxDays = $balance ? $balance['remaining'] : $leaveType->quota;

        if ($days > $maxDays) {
            $errors['end_date'] =
                "Melebihi saldo. Sisa: {$maxDays} hari, diajukan: {$days} hari.";
        }

        // 6. Tidak boleh ada pengajuan pending aktif
        $hasPending = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->exists();
        if ($hasPending) {
            $errors['selectedEmployeeId'] =
                "Pegawai ini masih memiliki pengajuan cuti yang belum diproses.";
        }

        return $errors;
    }

    /**
     * Cek apakah jenis cuti tersedia untuk pegawai ini.
     */
    public static function isLeaveTypeAllowed(LeaveType $lt, Employee $employee): bool
    {
        // Gender tidak sesuai
        if ($lt->gender !== 'all' && $lt->gender !== $employee->gender)
            return false;
        // Guru tidak boleh ambil jenis cuti tertentu
        if ($employee->is_guru && in_array(strtolower($lt->name), self::EXCLUDED_FOR_GURU))
            return false;
        return true;
    }

    /**
     * Hitung tanggal selesai maksimal berdasarkan kuota dan tanggal mulai.
     */
    public static function calcMaxEndDate(string $startDate, int $quota): string
    {
        $current = Carbon::parse($startDate);
        $days = 0;
        while ($days < $quota) {
            if ($current->isWeekday())
                $days++;
            if ($days < $quota)
                $current->addDay();
        }
        return $current->format('Y-m-d');
    }

    /**
     * Hitung minimal tanggal mulai yang diperbolehkan.
     */
    public static function minStartDate(): string
    {
        return now()->addDays(self::MIN_DAYS_BEFORE)->format('Y-m-d');
    }

    /**
     * Ambil label aturan untuk ditampilkan di UI.
     */
    public static function rules(): array
    {
        return [
            'min_days_before' => self::MIN_DAYS_BEFORE,
            'min_start_date' => self::minStartDate(),
            'excluded_guru' => self::EXCLUDED_FOR_GURU,
        ];
    }
}