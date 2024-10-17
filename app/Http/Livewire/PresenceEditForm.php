<?php

namespace App\Http\Livewire;

use App\Http\Traits\useUniqueValidation;
use App\Models\Presence;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PresenceEditForm extends Component
{
    use useUniqueValidation;

    public $presences;

    public function mount(Collection $presences)
    {
         // Ambil semua data dari model Attendance

        $this->presences = [];
        foreach ($presences as $presence) {
            // $this->presences[] = $presence->toArray(); // jika menggunakan ini akan terjadi bandwith yang cukup besar
            $this->presences[] = [
                'id' => $presence->id,
                'user_id' => $presence->user_id,
                'attendance_id' => $presence->attendance_id,
                'presence_date' => $presence->presence_date,
                'presence_enter_time' => $presence->presence_enter_time,
                'presence_out_time' => $presence->presence_out_time,
                'presence_enter_from' => $presence->presence_enter_from,
                'presence_out_from' => $presence->presence_out_from,
                'notes' => $presence->notes,
            ];
        }
    }

    public function savePresences()
    {

        foreach ($this->presences as $i => $presence) {
            // Jika input waktu tidak memiliki detik, tambahkan ":00"
            if (isset($presence['presence_enter_time']) && strlen($presence['presence_enter_time']) === 5) {
                $this->presences[$i]['presence_enter_time'] .= ':00';
            }
    
            if (isset($presence['presence_out_time']) && strlen($presence['presence_out_time']) === 5) {
                $this->presences[$i]['presence_out_time'] .= ':00';
            }
        }

        
        $this->validate([
            'presences.*.user_id' => 'required|exists:users,id', // Harus ada dan sesuai dengan ID yang ada di tabel users
            'presences.*.attendance_id' => 'required|exists:attendances,id', // Harus ada dan sesuai dengan ID yang ada di tabel attendances
            'presences.*.presence_date' => 'required|date', // Harus ada dan dalam format tanggal yang valid
            'presences.*.presence_enter_time' => 'nullable|date_format:H:i:s', // Waktu masuk kehadiran opsional tapi harus dalam format waktu (jam:menit)
            'presences.*.presence_out_time' => 'nullable|date_format:H:i:s|after:presences.*.presence_enter_time', // Waktu keluar opsional, harus dalam format waktu, dan harus setelah waktu masuk
            'presences.*.presence_enter_from' => 'nullable|string|max:100', // Lokasi masuk opsional, maksimal 100 karakter
            'presences.*.presence_out_from' => 'nullable|string|max:100', // Lokasi keluar opsional, maksimal 100 karakter
            'presences.*.notes' => 'nullable|string|max:255',
        ]);

        

       

        $affected = 0;
        // alasan menggunakan create alih2 mengunakan ::insert adalah karena tidak looping untuk menambahkan created_at dan updated_at
        foreach ($this->presences as $presence) {
            $presenceBeforeUpdated = presence::find($presence['id']);

            // Jika data tidak ditemukan, lewati (ini mencegah error jika ID tidak valid)
            if (!$presenceBeforeUpdated) {
                continue;
            }

            // Lakukan update untuk setiap field yang diperlukan
    $affected += $presenceBeforeUpdated->update([
        'attendance_id' => $presence['attendance_id'],
        'presence_date' => $presence['presence_date'],
        'presence_enter_time' => $presence['presence_enter_time'] ?? null, // Waktu masuk kehadiran, atau null jika tidak ada
        'presence_out_time' => $presence['presence_out_time'] ?? null, // Waktu keluar kehadiran, atau null jika tidak ada
        'presence_enter_from' => $presence['presence_enter_from'] ?? null, // Lokasi masuk kehadiran, atau null
        'presence_out_from' => $presence['presence_out_from'] ?? null, // Lokasi keluar kehadiran, atau null
        'notes' => $presence['notes'] ?? null,
        ]);
    }

    // Memeriksa apakah ada record yang berhasil diubah
    if ($affected > 0) {
        session()->flash('success', "$affected data kehadiran berhasil diperbarui.");
    } else {
        session()->flash('info', "Tidak ada data kehadiran yang diubah.");
    }

    // Redirect ke halaman index atau halaman lain sesuai kebutuhan
    return redirect()->route('presences.index');
    }

    public function render()
    {
        return view('livewire.presence-edit-form');
    }
}
