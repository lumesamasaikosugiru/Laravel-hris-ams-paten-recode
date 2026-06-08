<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;

class CheckProbationEndDate extends Command
{
    protected $signature = 'hris:check-probation';
    protected $description = 'Cek pegawai yang masa percobaannya selesai atau hampir selesai';

    public function handle(): int
    {
        $overdue = Employee::probationEnding()->get();
        if ($overdue->isNotEmpty()) {
            $this->warn("⏰ {$overdue->count()} pegawai masa percobaannya sudah selesai:");
            foreach ($overdue as $e) {
                $this->line("  - {$e->name} ({$e->nik}) — {$e->role_label} — berakhir {$e->probation_end_date->format('d M Y')}");
            }
        }

        $soon = Employee::probationEndingSoon(7)->get();
        if ($soon->isNotEmpty()) {
            $this->info("⚠ {$soon->count()} pegawai masa percobaannya berakhir dalam 7 hari:");
            foreach ($soon as $e) {
                $this->line("  - {$e->name} ({$e->nik}) — {$e->probation_days_left} hari lagi");
            }
        }

        if ($overdue->isEmpty() && $soon->isEmpty()) {
            $this->info('✅ Tidak ada pegawai yang perlu dievaluasi.');
        }
        return Command::SUCCESS;
    }
}
