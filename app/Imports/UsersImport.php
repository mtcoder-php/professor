<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class UsersImport implements ToCollection, WithHeadingRow
{
    protected $results = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public function collection(Collection $rows)
    {
        $this->results['total'] = $rows->count();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because of header row and 0-based index

            try {
                \Log::info("Processing row {$rowNumber}: " . json_encode($row)); // DEBUG

                // Clean keys - remove spaces and convert to lowercase
                $cleanRow = [];
                foreach ($row as $key => $value) {
                    $cleanKey = strtolower(str_replace(' ', '_', trim($key)));
                    $cleanRow[$cleanKey] = $value;
                }

                \Log::info("Cleaned row {$rowNumber}: " . json_encode($cleanRow)); // DEBUG

                // Check required fields exist
                $requiredFields = ['fio', 'kafedra_kodi', 'ilmiy_darajasi', 'passport_seriya', 'tugilgan_kun'];
                foreach ($requiredFields as $field) {
                    if (!isset($cleanRow[$field]) || empty($cleanRow[$field])) {
                        throw new \Exception("Majburiy maydon topilmadi: {$field}");
                    }
                }

                // Validate row
                $validator = Validator::make($cleanRow, [
                    'fio' => 'required|string|max:255',
                    'kafedra_kodi' => 'required|string',
                    'ilmiy_darajasi' => 'required|string',
                    'passport_seriya' => [
                        'required',
                        'string'
                    ],
                    'tugilgan_kun' => 'required',
                    'telefon' => 'nullable',
                    'email' => 'nullable|email'
                ]);

                if ($validator->fails()) {
                    $this->results['failed']++;
                    $this->results['errors'][] = [
                        'row' => $rowNumber,
                        'fio' => $cleanRow['fio'] ?? '',
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                // Clean and validate passport
                $passportSeries = strtoupper(str_replace(' ', '', $cleanRow['passport_seriya']));

                if (!preg_match('/^[A-Z]{2}[0-9]{7}$/', $passportSeries)) {
                    throw new \Exception("Passport format noto'g'ri: {$passportSeries}. Kerakli format: AA1234567");
                }

                // Check if passport already exists
                if (User::where('passport_series', $passportSeries)->exists()) {
                    throw new \Exception("Passport allaqachon mavjud: {$passportSeries}");
                }

                // Find department by code
                $departmentCode = strtoupper(trim($cleanRow['kafedra_kodi']));
                $department = Department::where('code', $departmentCode)->first();

                if (!$department) {
                    throw new \Exception("Kafedra kodi topilmadi: {$departmentCode}");
                }

                // Parse birth date - try multiple formats
                $birthDateStr = trim($cleanRow['tugilgan_kun']);
                $birthDate = null;

                // Try dd.mm.yyyy
                try {
                    $birthDate = Carbon::createFromFormat('d.m.Y', $birthDateStr);
                } catch (\Exception $e) {
                    // Try yyyy-mm-dd
                    try {
                        $birthDate = Carbon::createFromFormat('Y-m-d', $birthDateStr);
                    } catch (\Exception $e) {
                        // Try dd/mm/yyyy
                        try {
                            $birthDate = Carbon::createFromFormat('d/m/Y', $birthDateStr);
                        } catch (\Exception $e) {
                            throw new \Exception("Tug'ilgan kun formati noto'g'ri: {$birthDateStr}. Format: dd.mm.yyyy");
                        }
                    }
                }

                if ($birthDate->isFuture()) {
                    throw new \Exception("Tug'ilgan kun kelajakda bo'lishi mumkin emas");
                }

                // Create password from birth date
                $password = Hash::make($birthDate->format('d.m.Y'));

                // Clean phone if exists
                $phone = null;
                if (!empty($cleanRow['telefon'])) {
                    $phone = preg_replace('/[^0-9]/', '', $cleanRow['telefon']);
                    if (strlen($phone) === 12 && !str_starts_with($phone, '998')) {
                        throw new \Exception("Telefon 998 bilan boshlanishi kerak");
                    }
                }

                // Clean email if exists
                $email = !empty($cleanRow['email']) ? trim($cleanRow['email']) : null;
                if ($email && User::where('email', $email)->exists()) {
                    throw new \Exception("Email allaqachon mavjud: {$email}");
                }

                // Create user
                $user = User::create([
                    'full_name' => trim($cleanRow['fio']),
                    'faculty_id' => $department->faculty_id,
                    'department_id' => $department->id,
                    'passport_series' => $passportSeries,
                    'birth_date' => $birthDate,
                    'scientific_degree' => trim($cleanRow['ilmiy_darajasi']),
                    'phone' => $phone,
                    'email' => $email,
                    'password' => $password
                ]);

                // Assign default role
                $user->assignRole('teacher');

                $this->results['success']++;

                \Log::info("Successfully imported row {$rowNumber}: {$user->full_name}");

            } catch (\Exception $e) {
                $this->results['failed']++;
                $this->results['errors'][] = [
                    'row' => $rowNumber,
                    'fio' => $cleanRow['fio'] ?? $row['fio'] ?? 'Noma\'lum',
                    'errors' => [$e->getMessage()]
                ];

                \Log::error("Failed to import row {$rowNumber}: " . $e->getMessage());
            }
        }
    }

    public function getResults()
    {
        return $this->results;
    }
}
