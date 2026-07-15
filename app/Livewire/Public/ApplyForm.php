<?php
namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\JobVacancy;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\ApplicantExperience;
use Illuminate\Support\Facades\DB;

class ApplyForm extends Component
{
    use WithFileUploads;

    public JobVacancy $job;
    public string $activeTab = 'biodata';

    // Tab yang sudah lolos validasi -- user hanya bisa klik tab header
    // kalau tab sebelumnya sudah selesai. Mencegah skip validasi dengan
    // klik langsung ke tab header tanpa lewat tombol "Selanjutnya".
    public array $completedTabs = [];
    public bool $submitted = false;

    // ── Biodata ──────────────────────────────────────────────
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $gender = 'male';
    public string $place_of_birth = '';
    public string $date_of_birth = '';
    public string $address = '';

    // ── Pendidikan terakhir ───────────────────────────────────
    public string $last_education = 's1';
    public string $last_education_major = '';
    public string $last_education_institution = '';

    // ── Riwayat pendidikan (multiple) ────────────────────────
    public array $educations = [
        [
            'level' => 's1',
            'institution' => '',
            'major' => '',
            'start_year' => '',
            'end_year' => '',
            'gpa' => '',
        ]
    ];

    // ── Pengalaman kerja (multiple) ──────────────────────────
    public array $experiences = [
        [
            'company_name' => '',
            'position' => '',
            'start_date' => '',
            'end_date' => '',
            'is_current' => false,
            'description' => '',
        ]
    ];

    // ── CV ───────────────────────────────────────────────────
    public $cv_file = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'last_education' => 'required',
            'last_education_major' => 'nullable|string|max:255',
            'last_education_institution' => 'nullable|string|max:255',
            // nullable — hanya divalidasi kalau institution diisi
            'educations.*.institution' => 'nullable|string|max:255',
            'educations.*.level' => 'nullable',
            'educations.*.start_year' => 'nullable|integer|min:1970|max:' . now()->year,
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'last_education.required' => 'Pendidikan terakhir wajib dipilih.',
        'educations.*.institution.required' => 'Nama institusi wajib diisi.',
        'educations.*.start_year.required' => 'Tahun masuk wajib diisi.',
        'cv_file.mimes' => 'File CV harus berformat PDF, DOC, atau DOCX.',
        'cv_file.max' => 'Ukuran file CV maksimal 5MB.',
        'cv_file.required' => 'CV / Resume wajib diupload.',
    ];

    public function mount(JobVacancy $job): void
    {
        $this->job = $job;
    }

    // ── Tab navigation ────────────────────────────────────────

    /** Aturan validasi per tab -- dipanggil saat klik Selanjutnya */
    private function rulesForTab(string $tab): array
    {
        return match ($tab) {
            'biodata' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'gender' => 'required|in:male,female',
                'last_education' => 'required',
            ],
            'education' => [
                'educations.*.institution' => 'required|string|max:255',
                'educations.*.start_year' => 'required|digits:4|integer',
            ],
            // Tab experience & document tidak ada validasi saat nextTab
            // (experience boleh kosong, cv_file divalidasi di submit())
            default => [],
        };
    }

    public function nextTab(): void
    {
        $tabs = ['biodata', 'education', 'experience', 'document'];
        $current = array_search($this->activeTab, $tabs);

        // Validasi field tab saat ini dulu sebelum pindah
        $rules = $this->rulesForTab($this->activeTab);
        if (!empty($rules)) {
            $this->validate($rules, $this->messages);
        }

        // Tandai tab ini sudah selesai
        if (!in_array($this->activeTab, $this->completedTabs)) {
            $this->completedTabs[] = $this->activeTab;
        }

        if ($current !== false && $current < count($tabs) - 1) {
            $this->activeTab = $tabs[$current + 1];
            $this->dispatch('scroll-to-top');
        }
    }

    public function prevTab(): void
    {
        $tabs = ['biodata', 'education', 'experience', 'document'];
        $current = array_search($this->activeTab, $tabs);
        if ($current > 0) {
            $this->activeTab = $tabs[$current - 1];
            $this->dispatch('scroll-to-top');
        }
    }

    /**
     * Pindah ke tab tertentu via klik header -- hanya diizinkan kalau
     * tab tujuan sudah pernah dikunjungi/divalidasi (ada di completedTabs)
     * ATAU tab tujuan = tab saat ini atau sebelumnya. Mencegah user
     * melompat maju dan skip validasi.
     */
    public function goToTab(string $tab): void
    {
        $tabs = ['biodata', 'education', 'experience', 'document'];
        $currentIndex = array_search($this->activeTab, $tabs);
        $targetIndex = array_search($tab, $tabs);

        if ($targetIndex === false)
            return;

        // Boleh mundur ke tab sebelumnya tanpa validasi
        if ($targetIndex <= $currentIndex) {
            $this->activeTab = $tab;
            $this->dispatch('scroll-to-top');
            return;
        }

        // Maju ke tab berikutnya: hanya kalau sudah completed
        if (in_array($tab, $this->completedTabs)) {
            $this->activeTab = $tab;
            $this->dispatch('scroll-to-top');
            return;
        }

        // Blokir -- user harus lewat tombol Selanjutnya
        // (tidak ada aksi, tab header tidak bisa diklik)
    }

    // ── Education rows ────────────────────────────────────────
    public function addEducation(): void
    {
        $this->educations[] = [
            'level' => 's1',
            'institution' => '',
            'major' => '',
            'start_year' => '',
            'end_year' => '',
            'gpa' => '',
        ];
    }

    public function removeEducation(int $i): void
    {
        if (count($this->educations) > 1) {
            array_splice($this->educations, $i, 1);
        }
    }

    // ── Experience rows ───────────────────────────────────────
    public function addExperience(): void
    {
        $this->experiences[] = [
            'company_name' => '',
            'position' => '',
            'start_date' => '',
            'end_date' => '',
            'is_current' => false,
            'description' => '',
        ];
    }

    public function removeExperience(int $i): void
    {
        if (count($this->experiences) > 1) {
            array_splice($this->experiences, $i, 1);
        }
    }

    // ── Submit ────────────────────────────────────────────────
    public function submit(): void
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Arahkan ke tab yang punya error
            $errors = $e->errors();

            $tabFieldMap = [
                'biodata' => [
                    'name',
                    'email',
                    'phone',
                    'gender',
                    'place_of_birth',
                    'date_of_birth',
                    'address',
                    'last_education',
                    'last_education_major',
                    'last_education_institution'
                ],
                'education' => ['educations'],
                'experience' => ['experiences'],
                'document' => ['cv_file'],
            ];

            foreach ($tabFieldMap as $tab => $fields) {
                foreach ($fields as $field) {
                    foreach ($errors as $key => $msg) {
                        if (str_starts_with($key, $field)) {
                            $this->activeTab = $tab;
                            // Scroll ke atas supaya user langsung lihat error
                            $this->dispatch('scroll-to-top');
                            throw $e;
                        }
                    }
                }
            }

            throw $e;
        }

        DB::transaction(function () {
            $cvPath = null;
            if ($this->cv_file) {
                $cvPath = $this->cv_file->store('applicants/cv', 'public');
            }

            $applicant = Applicant::create([
                'job_vacancy_id' => $this->job->id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone ?: null,
                'gender' => $this->gender,
                'place_of_birth' => $this->place_of_birth ?: null,
                'date_of_birth' => $this->date_of_birth ?: null,
                'address' => $this->address ?: null,
                'last_education' => $this->last_education,
                'last_education_major' => $this->last_education_major ?: null,
                'last_education_institution' => $this->last_education_institution ?: null,
                'cv_file' => $cvPath,
                'status' => 'submitted',
                'source' => 'public_form',
            ]);

            foreach ($this->educations as $edu) {
                if (!empty($edu['institution'])) {
                    ApplicantEducation::create([
                        'applicant_id' => $applicant->id,
                        'level' => $edu['level'],
                        'institution' => $edu['institution'],
                        'major' => $edu['major'] ?: null,
                        'start_year' => $edu['start_year'] ?: null,
                        'end_year' => $edu['end_year'] ?: null,
                        'gpa' => $edu['gpa'] ?: null,
                    ]);
                }
            }

            foreach ($this->experiences as $exp) {
                if (!empty($exp['company_name'])) {
                    ApplicantExperience::create([
                        'applicant_id' => $applicant->id,
                        'company_name' => $exp['company_name'],
                        'position' => $exp['position'] ?: null,
                        'start_date' => $exp['start_date'] ?: null,
                        'end_date' => $exp['is_current'] ? null : ($exp['end_date'] ?: null),
                        'is_current' => $exp['is_current'],
                        'description' => $exp['description'] ?: null,
                    ]);
                }
            }
        });

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.apply-form');
    }
}