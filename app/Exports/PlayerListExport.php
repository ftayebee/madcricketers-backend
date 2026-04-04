<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PlayerListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $type;

    public function __construct($type = 'full') // 'full' or 'jersey'
    {
        $this->type = $type;
    }

    public function collection()
    {
        return User::with('player')->whereHas('player')->get();
    }

    public function headings(): array
    {
        if ($this->type == 'jersey') {
            return [
                'ID',
                'Name',
                'Phone',
                'Jersey Name',
                'Jersey Number',
                'Jersey Size',
                'Chest Measurement',
            ];
        }

        return [
            'ID',
            'Full Name',
            'Nickname',
            'Email',
            'Phone',
            'Type',
            'Role',
            'Blood Group',
            'Gender',
            'Married Status',
            'Religion',
            'Date of Birth',
            'National ID',
            'Address',
        ];
    }

    public function map($user): array
    {
        $player = $user->player;

        if ($this->type == 'jersey') {
            return [
                $user->id,
                $user->full_name,
                $user->phone,
                $player ? $player->jursey_name : '',
                $player ? $player->jursey_number : '',
                $player ? $player->jursey_size : '',
                $player ? $player->chest_measurement : '',
            ];
        }

        return [
            $user->id,
            $user->full_name,
            $user->nickname,
            $user->email,
            $user->phone,
            $player ? ucfirst($player->player_type) : '',
            $player ? ucfirst($player->player_role) : '',
            $user->blood_group,
            ucfirst($user->gender ?? ''),
            $player ? $player->married_status : '',
            $user->religion,
            $user->date_of_birth,
            $user->national_id,
            $user->address,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $cellRange = 'A1:' . $highestColumn . $highestRow;
                
                // 1. Give some margin/padding by setting row height slightly larger and vertical center
                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }
                
                // Style all cells: Add light border and vertical centering
                $sheet->getStyle($cellRange)->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCCCCCC'],
                        ],
                    ],
                ]);

                // 2. Make the header row clear and understandable
                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => Color::COLOR_WHITE],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4F81BD'], // Nice blue
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // 3. Align Jersey details to center
                if ($this->type == 'jersey') {
                    // Jersey columns: D(Name), E(Number), F(Size), G(Measurement)
                    $sheet->getStyle('D2:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Set page orientation to Landscape to fit more columns in PDF
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                if ($this->type == 'full') {
                    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
                }

                // Force it to fit to 1 page wide
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
