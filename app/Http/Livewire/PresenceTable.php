<?php

namespace App\Http\Livewire;

use App\Models\Presence;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class PresenceTable extends PowerGridComponent
{
    use ActionButton;

    protected function getListeners()
    {
        return array_merge(
            parent::getListeners(),
            [
                'bulkCheckedDelete',
                'bulkCheckedEdit'
            ]
        );
    }

    public function header(): array
    {
        return [
           /*  Button::add('bulk-checked')
                ->caption(__('Hapus'))
                ->class('btn btn-danger border-0')
                ->emit('bulkCheckedDelete', []), */
            Button::add('bulk-edit-checked')
                ->caption(__('Edit'))
                ->class('btn btn-success border-0')
                ->emit('bulkCheckedEdit', []),
        ];
    }

    public function bulkCheckedDelete()
    {
        if (auth()->check()) {
            $ids = $this->checkedValues();

            if (!$ids)
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Pilih data yang ingin dihapus terlebih dahulu.']);

            try {
                Presence::whereIn('id', $ids)->delete();
                $this->dispatchBrowserEvent('showToast', ['success' => true, 'message' => 'Data Hadir berhasi dihapus.']);
            } catch (\Illuminate\Database\QueryException $ex) {
                $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Data gagal dihapus, kemungkinan ada data lain yang menggunakan data tersebut.']);
            }
        }
    }

    public function bulkCheckedEdit()
    {
        if (auth()->check()) {
            $ids = $this->checkedValues();  // Mengambil nilai yang di-check
    
            if (!$ids) {
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Pilih data yang ingin diedit terlebih dahulu.']);
            }
    
            // Melakukan pengecekan apakah ada record dengan is_permission = 1 atau is_leave = 1
            $blockedData = \App\Models\Presence::whereIn('id', $ids)
                ->where(function($query) {
                    $query->where('is_permission', 1)
                          ->orWhere('is_leave', 1);
                })->exists();
    
            // Jika ada record yang tidak bisa di-edit, tampilkan pesan
            if ($blockedData) {
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Data tidak bisa diedit karena izin atau cuti.']);
            }
    
            // Jika tidak ada, lakukan redirect untuk proses edit
            $ids = join('-', $ids);
            return $this->dispatchBrowserEvent('redirect', ['url' => route('presences.edit', ['ids' => $ids])]);
        }
    }

    public $attendanceId;
    //Table sort field
    public string $primaryKey = 'presences.id';
    public string $sortField = 'presences.created_at';
    public string $sortDirection = 'desc';


    

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public function setUp(): array
    {

        

        $this->showCheckBox();

        return [
            Exportable::make('export')->stripTags()
                ->striped()
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showSearchInput()->showToggleColumns(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Datasource
    |--------------------------------------------------------------------------
    | Provides data to your Table using a Model or Collection
    |
    */

    /**
     * PowerGrid datasource.
     *
     * @return Builder<\App\Models\Presence>
     */
    public function datasource(): Builder
    {
        return Presence::query()
            ->where('attendance_id', $this->attendanceId)
            ->join('users', 'presences.user_id', '=', 'users.id')
            ->select('presences.*', 'users.name as user_name');
    }

    /*
    |--------------------------------------------------------------------------
    |  Relationship Search
    |--------------------------------------------------------------------------
    | Configure here relationships to be used by the Search and Table Filters.
    |
    */

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    |  Add Column
    |--------------------------------------------------------------------------
    | Make Datasource fields available to be used as columns.
    | You can pass a closure to transform/modify the data.
    |
    */
    public function addColumns(): PowerGridEloquent
    {
        return PowerGrid::eloquent()
            ->addColumn('id')
            ->addColumn('user_name')
            ->addColumn("presence_date")
            ->addColumn("presence_enter_time")
            ->addColumn("Ket",fn (Presence $model) =>  Carbon::parse($model->presence_enter_time)->format('H:i:s') > '09:15:00' ?
                '<span class="badge text-bg-danger">Terlambat</span>' : '<span class="badge text-bg-info"></span>')
            ->addColumn("presence_enter_from",fn (Presence $model) => $model->presence_enter_from ?
                '<span class="badge text-bg-warning">Mesin</span>' : '<span class="badge text-bg-info">Web</span>') 
            ->addColumn("presence_out_from",fn (Presence $model) => $model->presence_out_from ?
                '<span class="badge text-bg-warning">Mesin</span>' : '<span class="badge text-bg-info">Web</span>') 
            ->addColumn("presence_out_time", fn (Presence $model) => $model->presence_out_time ?? '<span class="badge text-bg-danger">Belum Absensi Pulang</span>')
            ->addColumn("is_permission", fn (Presence $model) => 
            $model->is_leave ? 
                ($model->presence_enter_time !== null ? 
                    '<span class="badge text-bg-info">Cuti 1/2 hari</span>' : 
                    '<span class="badge text-bg-warning">Cuti</span>') : 
                ($model->is_permission ? 
                    '<span class="badge text-bg-danger">Izin</span>' : 
                    '<span class="badge text-bg-success">Hadir</span>')
            )
            ->addColumn("notes")
            ->addColumn('created_at')
            ->addColumn('created_at_formatted', fn (Presence $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
    }

    /*
    |--------------------------------------------------------------------------
    |  Include Columns
    |--------------------------------------------------------------------------
    | Include the columns added columns, making them visible on the Table.
    | Each column can be configured with properties, filters, actions...
    |
    */

    /**
     * PowerGrid Columns.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('ID', 'id',)
                ->searchable()
                ->hidden()
                ->sortable(),

            Column::make('Nama', 'user_name')
                ->searchable()
                ->makeInputText('users.name')
                ->sortable(),

            Column::make('Tanggal Hadir', 'presence_date')
                ->makeInputDatePicker()
                ->searchable()
                ->sortable(),

            Column::make('Jam Absen Masuk', 'presence_enter_time')
                ->searchable()
                // ->makeInputRange('presence_enter_time') // terlalu banyak menggunakan bandwidth (ukuran data yang dikirim terlalu besar)
                ->makeInputText('presence_enter_time')
                ->sortable(),

            Column::make('Keterangan', 'Ket'),
            
            Column::make('Absen Masuk Dari', 'presence_enter_from')
                ->searchable()
                ->makeInputText('presence_enter_from')
                ->sortable(),

            Column::make('Jam Absen Pulang', 'presence_out_time')
                ->searchable()
                ->makeInputText('presence_out_time')
                ->sortable(),

            Column::make('Absen Pulang Dari', 'presence_out_from')
                ->searchable()
                ->makeInputText('presence_out_from')
                ->sortable(),

            Column::make('Status', 'is_permission')
                ->sortable(),
            
            Column::make('Notes', 'notes')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted')
                ->makeInputDatePicker()
                ->searchable()
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

    /**
     * PowerGrid Presence Action Buttons.
     *
     * @return array<int, Button>
     */

    /*
    public function actions(): array
    {
       return [
           Button::make('edit', 'Edit')
               ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
               ->route('presence.edit', ['presence' => 'id']),

           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('presence.destroy', ['presence' => 'id'])
               ->method('delete')
        ];
    }
    */

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

    /**
     * PowerGrid Presence Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($presence) => $presence->id === 1)
                ->hide(),
        ];
    }
    */
}
