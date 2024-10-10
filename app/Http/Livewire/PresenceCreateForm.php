<?php

namespace App\Http\Livewire;

use App\Models\Permission;
use App\Models\Accept;
use App\Models\Presence;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PresenceCreateForm extends Component
{
    public $presences;
    public Collection $permissions;
    public Collection $accept;
    public Collection $users;
    public Collection $attendances;

    public function mount()
    {
        $byDate = now()->toDateString();

        $this->attendances= Attendance::all();
        $this->presences= Presence::all();
        $this->accept = Accept::all();
        $this->users = User::query()
            ->distinct()
            ->leftJoin(DB::raw('(SELECT * FROM presences WHERE presence_date = CURDATE()) as p'), 'users.id', '=', 'p.user_id')
            ->where('p.presence_date',NULL )
            ->where('users.id','<>',1 )
            ->select('users.id', 'name')
            ->get();
        $this->presences = [
            ['user_id' => '', 'attendance_id' => '', 'title' => '', 'description' => '','presence_date' => '', 'is_accepted' => $this->accept->first()->id]
        ];
    }

    public function addpresenceInput(): void
    {
        $this->presences[] = ['user_id' => '', 'attendance_id' => '', 'title' => '', 'description' => '', 'presence_date' => '', 'is_accepted' => $this->accept->first()->id];
    }

    public function removepresenceInput(int $index): void
    {
        unset($this->presences[$index]);
        $this->presences = array_values($this->presences);
    }

    public function savePresences()
    {
        


        // Validasi input data presensi
        $this->validate([
            'presences.*.user_id' => 'required',
            'presences.*.attendance_id' => 'required',
            'presences.*.presence_date' => 'required|date',
            'presences.*.presence_enter_time' => 'required',

        ]);

        $affected = 0;
        foreach ($this->presences as $presence) {
            // Jika attendance_id tidak ada, default ke '1'
            if (trim($presence['attendance_id']) === '') {
                $presence['attendance_id'] = '1';
            }

            // Menyimpan data presensi ke dalam tabel presences
            Presence::create([
                'user_id' => $presence['user_id'],
                'attendance_id' => $presence['attendance_id'],
                'presence_date' => $presence['presence_date'],
                'presence_enter_time' => $presence['presence_enter_time'],
                'presence_from' => 0, // Selalu set ke 0
                'create_date' => now(), // Menyimpan waktu saat entri dibuat
                'update_date' => now(), // Menyimpan waktu saat entri diperbarui
            ]);

            $affected++;
        }

        // Redirect ke halaman presences dengan pesan sukses
        session()->flash('success', "Ada ($affected) data karyawan yang berhasil ditambahkan.");
        return redirect()->route('presences.index');
    }

    public function render()
    {
        return view('livewire.presence-create-form');
    }
}
