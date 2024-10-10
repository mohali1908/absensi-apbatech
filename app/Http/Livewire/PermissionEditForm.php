<?php

namespace App\Http\Livewire;

use App\Http\Traits\useUniqueValidation;
use App\Models\Accept;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class permissionEditForm extends Component
{
    use useUniqueValidation;

    public $permissions;
    public Collection $presences;
    public Collection $accept;
    public Collection $users;
    public Collection $attendances;
   

    public function mount(Collection $permissions)
    {
        $this->permissions = []; // reset, karena ada data permissions sebelumnya

        foreach ($permissions as $permission) {
            $this->permissions[] = [
                'id' => $permission->id,
                'user_id' => $permission->user_id,
                'title' => $permission->title,
                'description' => $permission->description,
                'permission_date' => $permission->permission_date,
                'is_accepted' => $permission->is_accepted,
            ];
        }
        $this->accept = Accept::all();
    }
    public function savepermissions()
    {
        $acceptIdRuleIn = join(',', $this->accept->pluck('id')->toArray());

        $this->validate([
            'permissions.*.user_id' => 'required',
            'permissions.*.title' => 'required',
            'permissions.*.description' => 'required',
            'permissions.*.permission_date' => 'required',
            'permissions.*.is_accepted' => 'required|in:' . $acceptIdRuleIn,
        ]);

    


        // alasan menggunakan create alih2 mengunakan ::insert adalah karena tidak looping untuk menambahkan created_at dan updated_at
        $affected = 0;
        foreach ($this->permissions as $permission) {
            // cek unique validasi
            $permissionBeforeUpdated = Permission::find($permission['id']);
          
            $affected += $permissionBeforeUpdated->update([
                'user_id' => $permission['user_id'],
                'title' => $permission['title'],
                'description' => $permission['description'],
                'permission_date' => $permission['permission_date'],
                'is_accepted' => $permission['is_accepted'],
            ]);
        }

        $message = $affected === 0 ?
            "Tidak ada data karyawaan yang diubah." :
            "Ada $affected data karyawaan yang berhasil diedit.";

        return redirect()->route('permissions.index')->with('success', $message);
    }

    public function render()
    {
        return view('livewire.permission-edit-form');
    }
}
