<?php

namespace App\Exports;

use App\Models\Presence;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PresenceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Presence::all();
    }

    public function map($presence): array
    {
        return [
            $presence->id,
            strip_tags($presence->user_name),
            strip_tags($presence->presence_date),
            strip_tags($presence->presence_enter_time),
            strip_tags($presence->Ket),
            strip_tags($presence->presence_enter_from),
            strip_tags($presence->presence_out_time),
            strip_tags($presence->presence_out_from),
            strip_tags($presence->is_permission),
            strip_tags($presence->created_at->format('d/m/Y H:i:s')),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Tanggal Hadir',
            'Jam Absen Masuk',
            'Keterangan',
            'Absen Masuk Dari',
            'Jam Absen Pulang',
            'Absen Pulang Dari',
            'Status',
            'Created at',
        ];
    }
}
