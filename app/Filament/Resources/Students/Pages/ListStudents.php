<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\Students\StudentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Siswa'),
            ImportAction::make()
                ->importer(StudentImporter::class)
                ->label('Import'),
            Action::make('downloadTemplateCsv')
                ->label('Template CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $headers = ['nis', 'nama', 'kelas'];
                    $rows = [
                        ['1001', 'Budi Santoso', 'X TJKT 1'],
                        ['1002', 'Siti Aminah', 'X TJKT 1'],
                    ];

                    return response()->streamDownload(function () use ($headers, $rows) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF");
                        fputcsv($out, $headers, ';');

                        foreach ($rows as $row) {
                            fputcsv($out, $row, ';');
                        }

                        fclose($out);
                    }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
                }),
        ];
    }
}
