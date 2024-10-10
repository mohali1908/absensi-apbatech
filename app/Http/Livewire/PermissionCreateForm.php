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

class PermissionCreateForm extends Component
{
    public $permissions;
    public Collection $presences;
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
        $this->permissions = [
            ['user_id' => '', 'attendance_id' => '', 'title' => '', 'description' => '','permission_date' => '', 'is_accepted' => $this->accept->first()->id]
        ];
    }

    public function addPermissionInput(): void
    {
        $this->permissions[] = ['user_id' => '', 'attendance_id' => '', 'title' => '', 'description' => '', 'permission_date' => '', 'is_accepted' => $this->accept->first()->id];
    }

    public function removePermissionInput(int $index): void
    {
        unset($this->permissions[$index]);
        $this->permissions = array_values($this->permissions);
    }

    public function savePermissions()
    {
        // cara lebih cepat, dan kemungkinan data role tidak akan diubah/ditambah
        $acceptIdRuleIn = join(',', $this->accept->pluck('id')->toArray());
        //$positionIdRuleIn = join(',', $this->positions->pluck('id')->toArray());
        // $roleIdRuleIn = join(',', Role::all()->pluck('id')->toArray());

        // setidaknya input pertama yang hanya required,
        // karena nanti akan difilter apakah input kedua dan input selanjutnya apakah berisi
        $this->validate([
            'permissions.*.user_id' => 'required',
            'permissions.*.attendance_id' => 'required',
            'permissions.*.title' => 'required',
            'permissions.*.description' => 'required',
            'permissions.*.permission_date' => 'required',
            'permissions.*.is_accepted' => 'required|in:' . $acceptIdRuleIn,
        ]);
        // cek apakah no. telp yang diinput unique
       
        // alasan menggunakan create alih2 mengunakan ::insert adalah karena tidak looping untuk menambahkan created_at dan updated_at
        $affected = 0;
        foreach ($this->permissions as $permission) {
            if (trim($permission['attendance_id']) === '') $permission['attendance_id'] = '1';
            
            // insert table permissions
            Permission::create($permission);
            //Presence::create($permission);

            // insert table presences
            Presence::create([
                'user_id'     => $permission['user_id'],
                'attendance_id'   => $permission['attendance_id'],
                'presence_date'   => $permission['permission_date'],
                'is_permission'   => 1,
                'presence_from'   => 0,
                'presence_enter_time' =>  now()->toTimeString(),
            ]);
            $affected++;
        }

        redirect()->route('permissions.index')->with('success', "Ada ($affected) data karyawaan yang berhasil ditambahkan.");
    }

    public function render()
    {
        return view('livewire.permission-create-form');
    }
}
