<?php

namespace App\Http\Livewire;

use App\Models\Leave;
use App\Models\Leavetype;
use App\Models\Accept;
use App\Models\Presence;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class LeaveCreateForm extends Component
{
    public $leaves;
    public $selectedOption;
    public $selectedhalfOption;
    public $additionalFields = [];
    public $addhalfLeaveInput = [];
    public Collection $presences;
    public Collection $leavetypes;
    public Collection $accept;
    public Collection $users;
    public Collection $attendances;

    public function mount()
    {
        $byDate = now()->toDateString();


        $this->attendances= Attendance::all();
        $this->leavetypes= Leavetype::all();
        $this->presences= Presence::all();
        $this->accept = Accept::all();
        $this->users = User::query()
            ->distinct()
            ->leftJoin(DB::raw('(SELECT * FROM presences WHERE presence_date = CURDATE()) as p'), 'users.id', '=', 'p.user_id')
            ->where('p.presence_date',NULL )
            ->where('users.id','<>',1 )
            ->select('users.id', 'name')
            ->get();
       
        $this->leaves = [
            ['user_id' => '', 'start_date' => '', 'end_date' => '', 'leave_type' => $this->leavetypes->first()->id,'number_of_leaves' => '', 'remaining_days_off' => '', 'reason' => '',  'is_leave' => $this->accept->first()->id]
        ];
    }

    public function addhalfLeaveInput(): void
    {
        $this->leaves[] = ['user_id' => '', 'start_date' => '', 'end_date' => '', 'leave_type' => $this->leavetypes->first()->id,'number_of_leaves' => '', 'remaining_days_off' => '', 'reason' => '',  'is_leave' => $this->accept->first()->id];
    }

    public function addLeaveInput(): void
    {
        $this->leaves[] = ['user_id' => '', 'start_date' => '', 'end_date' => '', 'leave_type' => $this->leavetypes->first()->id,'number_of_leaves' => '', 'remaining_days_off' => '', 'reason' => '',  'is_leave' => $this->accept->first()->id];
    }

    public function removeLeaveInput(int $index): void
    {
        unset($this->leaves[$index]);
        $this->leaves = array_values($this->leaves);
    }

    public function updatedSelectedOption($value)
    {
        // Jika opsi tertentu dipilih, tambahkan field baru
        if ($value === 'specific_option') { // ganti 'specific_option' dengan nilai yang sesuai
            $this->additionalFields[] = ['name' => '', 'description' => ''];
        }
    }

    public function updated($propertyName)
    {
        if (str_contains($propertyName, 'user_id')) {
            $index = explode('.', $propertyName)[1]; // Mengambil index dari array leaves
            $userId = $this->leaves[$index]['user_id'];

            $user = User::find($userId); // Ambil data user
            if ($user && $user->leaveBalance) {
                // Update sisa cuti dari user
                $this->leaves[$index]['remaining_days_off'] = $user->leaveBalance->remaining_leaves;
            } else {
                $this->leaves[$index]['remaining_days_off'] = 0; // Jika tidak ada data sisa cuti
            }
        }
    }

    public function saveLeaves()
    {
        // cara lebih cepat, dan kemungkinan data role tidak akan diubah/ditambah
        $acceptIdRuleIn = join(',', $this->accept->pluck('id')->toArray());
        $leavetypeIdRuleIn = join(',', $this->leavetypes->pluck('id')->toArray());
        // $roleIdRuleIn = join(',', Role::all()->pluck('id')->toArray());

        // setidaknya input pertama yang hanya required,
        // karena nanti akan difilter apakah input kedua dan input selanjutnya apakah berisi
        $this->validate([
            'leaves.*.user_id' => 'required',
            'leaves.*.leave_type' => 'required|in:' . $leavetypeIdRuleIn,
            'leaves.*.number_of_leaves' => 'required',
            'leaves.*.remaining_days_off' => 'required',
            'leaves.*.reason' => 'required',
            'leaves.*.is_accepted' => 'required|in:' . $acceptIdRuleIn,
        ]);
        // cek apakah no. telp yang diinput unique
       
        // alasan menggunakan create alih2 mengunakan ::insert adalah karena tidak looping untuk menambahkan created_at dan updated_at
        $affected = 0;
        foreach ($this->leaves as $leave) {

            if (trim($leave['attendance_id']) === '') $leave['attendance_id'] = '1';
            
            // insert table leaves
            Leave::create($leave);
            //Presence::create($leave);


            if (trim($leave['leave_type']) == 2 ) {
            // insert table presences
            Presence::create([
                'user_id'     => $leave['user_id'],
                'attendance_id'   => $leave['attendance_id'],
                'presence_date'   => $leave['start_date'],
                'is_leave'   => 1,
                'presence_from'   => 0,
                'presence_enter_time' =>now()->toTimeString(),
            ]);

            } else {
                Presence::create([
                    'user_id'     => $leave['user_id'],
                    'attendance_id'   => $leave['attendance_id'],
                    'presence_date'   => $leave['start_date'],
                    'is_leave'   => 1,
                    'presence_from'   => 0,
                    'presence_enter_time' =>'',
                ]);

            } 
            $affected++;
        }

        redirect()->route('leaves.index')->with('success', "Ada ($affected) data Cuti karyawaan yang berhasil ditambahkan.");
    }

    public function render()
    {
        return view('livewire.leave-create-form');
    }
}
