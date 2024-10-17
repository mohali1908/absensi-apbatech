<?php

namespace App\Http\Livewire;

use App\Models\Leave;
use App\Models\Leavetype;
use App\Models\Accept;
use App\Models\Presence;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LeaveCreateForm extends Component
{
    public $leaves = [];
    public Collection $attendances;
    public Collection $leavetypes;
    public Collection $accept;
    public Collection $users;

    public function mount()
    {
        $this->attendances = Attendance::all();
        $this->leavetypes = Leavetype::all();
        $this->accept = Accept::all();
        
        // Ambil user yang belum absen hari ini
        $this->users = User::query()
            ->distinct()
            ->leftJoin(DB::raw('(SELECT * FROM presences WHERE presence_date = CURDATE()) as p'), 'users.id', '=', 'p.user_id')
            ->where('users.id', '<>', 1) // Exclude admin (user ID = 1)
            ->select('users.id', 'name')
            ->get();
        
        // Inisialisasi form awal
        $this->leaves[] = [
            'user_id' => '', 
            'start_date' => '', 
            'end_date' => '', 
            'leave_type' => '', 
            'number_of_leaves' => 1,  
            'remaining_days_off' => Carbon::today()->toDateString(), 
            'reason' => '', 
            'is_accepted' => '',
            'attendance_id' => '',
            'half_day' => '' // Tambahkan untuk radio button siang/pagi
        ];
    }

    // Menambah input dinamis
    public function addLeaveInput(): void
    {
        $this->leaves[] = [
            'user_id' => '', 
            'start_date' => '', 
            'end_date' => '', 
            'leave_type' => '', 
            'number_of_leaves' => '', 
            'remaining_days_off' => '', 
            'reason' => '', 
            'is_accepted' => '',
            'attendance_id' => '',
            'half_day' => '' // Tambahkan untuk radio button siang/pagi
        ];
    }

    // Menghapus input berdasarkan index
    public function removeLeaveInput(int $index): void
    {
        unset($this->leaves[$index]);
        $this->leaves = array_values($this->leaves); // Reset array index
    }

    // Ketika `user_id` diubah, ambil sisa cuti dari user terkait
    public function updated($propertyName)
    {
        if (str_contains($propertyName, 'user_id')) {
            $index = explode('.', $propertyName)[1]; // Ambil index dari leaves
            $userId = $this->leaves[$index]['user_id'];

            $lastLeave = Leave::where('user_id', $userId)
                ->orderBy('end_date', 'desc') // Urutkan berdasarkan end_date terbaru
                ->first();

            if ($lastLeave) {
                // Jika ada data cuti sebelumnya, ambil sisa cuti dari record tersebut
                $this->leaves[$index]['remaining_days_off'] = $lastLeave->remaining_days_off;
            } else {
                // Jika tidak ada data cuti sebelumnya, set default sisa cuti (misal: 0)
                $this->leaves[$index]['remaining_days_off'] = 0;
            }
        }
    }

    public function calculateEndDate($startDate, $numberOfLeaves)
    {
        $startDate = \Carbon\Carbon::parse($startDate);
        $endDate = $startDate->copy();
        while ($numberOfLeaves > 0) {
            if ($endDate->isWeekday()) {
                $numberOfLeaves--;
            }
            if ($numberOfLeaves > 0) {
                $endDate->addDay();
            }
        }
        return $endDate->toDateString();
    }

    public function saveLeaves()
    {
        $acceptIdRuleIn = join(',', $this->accept->pluck('id')->toArray());
        $leavetypeIdRuleIn = join(',', $this->leavetypes->pluck('id')->toArray());

        $this->validate([
            'leaves.*.user_id' => 'required',
            'leaves.*.leave_type' => 'required|in:' . $leavetypeIdRuleIn,
            'leaves.*.number_of_leaves' => 'required|numeric|min:1',
            'leaves.*.remaining_days_off' => 'required|numeric',
            'leaves.*.reason' => 'required',
            'leaves.*.is_accepted' => 'required|in:' . $acceptIdRuleIn,
        ]);

        $affected = 0;
        foreach ($this->leaves as $leave) {
            $userId = $leave['user_id'];
            $startDate = $leave['start_date'];
            $numberOfLeaves = (int)$leave['number_of_leaves'];
            $leaveType = (int)$leave['leave_type'];

            if ($leaveType == 2 && $numberOfLeaves != 1) {
                $this->addError('Untuk jenis cuti 2, jumlah cuti harus 1 hari.');
                continue;
            }

            $endDate = $this->calculateEndDate($startDate, $numberOfLeaves);

            $lastLeave = Leave::where('user_id', $userId)
                ->orderBy('end_date', 'desc')
                ->first();

            $lastRemainingDaysOff = $lastLeave ? $lastLeave->remaining_days_off : 0;

            // Sesuaikan pengurangan sisa cuti berdasarkan leave_type
            $leaveDaysToDeduct = $leaveType == 2 ? $numberOfLeaves * 0.5 : $numberOfLeaves;

            $calculatedRemainingDaysOff = $lastRemainingDaysOff - $leaveDaysToDeduct;

            if ($calculatedRemainingDaysOff < 0) {
                $calculatedRemainingDaysOff = $leave['remaining_days_off'] - $leaveDaysToDeduct;
            }

            Leave::create([
                'user_id' => $leave['user_id'],
                'leave_type' => $leave['leave_type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'number_of_leaves' => $numberOfLeaves,
                'remaining_days_off' => $calculatedRemainingDaysOff,
                'reason' => $leave['reason'],
                'is_leave' => $leave['is_accepted'],
            ]);

            $currentDate = \Carbon\Carbon::parse($startDate);
            $leaveDates = [];
            while ($numberOfLeaves > 0) {
                if ($currentDate->isWeekday()) {
                    $leaveDates[] = $currentDate->toDateString();
                    $numberOfLeaves--;
                }
                $currentDate->addDay();
            }

            foreach ($leaveDates as $date) {
                $presence = Presence::where('user_id', $leave['user_id'])
                    ->where('presence_date', $date)
                    ->first();

                if ($leaveType == 2) {
                    // Update presence untuk cuti setengah hari
                    if ($presence) {
                        $presence->update([
                            'is_leave' => 2,
                            'presence_enter_time' => now()->toTimeString(),
                        ]);
                    }
                } else {
                    // Simpan data presences untuk cuti penuh
                    Presence::create([
                        'user_id' => $leave['user_id'],
                        'attendance_id' => $leave['attendance_id'] ?? 1,
                        'presence_date' => $date,
                        'is_leave' => 1,
                        'presence_from' => 0,
                        'presence_enter_time' => null,
                    ]);
                }
            }

            $affected++;
        }

        redirect()->route('leaves.index')->with('success', "Ada ($affected) data cuti karyawan yang berhasil ditambahkan.");
    }

    public function render()
    {
        return view('livewire.leave-create-form');
    }
}
