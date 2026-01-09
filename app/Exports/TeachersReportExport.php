<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\PortfolioFile;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TeachersReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::with(['faculty', 'department'])
            ->whereHas('roles', function($q) {
                $q->where('name', 'teacher');
            });

        if (isset($this->filters['faculty_id']) && $this->filters['faculty_id']) {
            $query->where('faculty_id', $this->filters['faculty_id']);
        }

        if (isset($this->filters['department_id']) && $this->filters['department_id']) {
            $query->where('department_id', $this->filters['department_id']);
        }

        $teachers = $query->get();
        $entryTests = Test::where('type', 'entry')->where('is_active', true)->get();
        $exitTests = Test::where('type', 'exit')->where('is_active', true)->get();

        $data = [];

        foreach ($teachers as $index => $teacher) {
            $row = [
                $index + 1,
                $teacher->full_name,
                $teacher->scientific_degree ?? '—',
                $teacher->faculty?->name ?? '—',
                $teacher->department?->name ?? '—',
            ];

            // Entry tests
            foreach ($entryTests as $test) {
                $result = TestResult::where('user_id', $teacher->id)
                    ->where('test_id', $test->id)
                    ->whereNotNull('finished_at')
                    ->orderBy('finished_at', 'desc')
                    ->first();

                $row[] = $result ? $result->score . '/' . $test->total_points : '—';
            }

            // Exit tests
            foreach ($exitTests as $test) {
                $result = TestResult::where('user_id', $teacher->id)
                    ->where('test_id', $test->id)
                    ->whereNotNull('finished_at')
                    ->orderBy('finished_at', 'desc')
                    ->first();

                $row[] = $result ? $result->score . '/' . $test->total_points : '—';
            }

            // Portfolio total
            $portfolioTotal = PortfolioFile::where('user_id', $teacher->id)
                ->where('status', 'evaluated')
                ->with('evaluation')
                ->get()
                ->sum(function($file) {
                    return $file->evaluation ? $file->evaluation->score : 0;
                });

            $row[] = $portfolioTotal ?: '—';

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $entryTests = Test::where('type', 'entry')->where('is_active', true)->get();
        $exitTests = Test::where('type', 'exit')->where('is_active', true)->get();

        $headings = [
            '№',
            'F.I.O',
            'Ilmiy daraja',
            'Fakultet',
            'Kafedra',
        ];

        foreach ($entryTests as $test) {
            $headings[] = $test->title . ' (Kiruvchi)';
        }

        foreach ($exitTests as $test) {
            $headings[] = $test->title . ' (Chiquvchi)';
        }

        $headings[] = 'Portfolio (Umumiy)';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Set row height for header
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Apply borders to all cells with data
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // Center align number column
        $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Alternate row colors
        for ($i = 2; $i <= $highestRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':' . $highestColumn . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F5F5F5']
                    ]
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // №
            'B' => 35,  // F.I.O
            'C' => 20,  // Ilmiy daraja
            'D' => 25,  // Fakultet
            'E' => 25,  // Kafedra
            'F' => 18,  // Entry test 1
            'G' => 18,  // Entry test 2 (if exists)
            'H' => 18,  // Exit test 1
            'I' => 18,  // Exit test 2 (if exists)
            'J' => 18,  // Portfolio
        ];
    }

    public function title(): string
    {
        return 'O\'qituvchilar Hisoboti';
    }
}
