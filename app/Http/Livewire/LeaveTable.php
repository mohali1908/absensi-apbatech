<?php

namespace App\Http\Livewire;

use App\Models\Leave;
use App\Models\Presence;
use App\Models\Accept;
use App\Models\Leavetype;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class LeaveTable extends PowerGridComponent
{
    use ActionButton;

    //Table sort field
    public string $sortField = 'leaves.created_at';
    public string $sortDirection = 'desc';

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

    /* public function header(): array
    {
        return [
            Button::add('bulk-checked')
                ->caption(__('Hapus'))
                ->class('btn btn-danger border-0')
                ->emit('bulkCheckedDelete', []),
            Button::add('bulk-edit-checked')
                ->caption(__('Edit'))
                ->class('btn btn-success border-0')
                ->emit('bulkCheckedEdit', []),
        ];
    } */

    public function bulkCheckedDelete()
    {
        if (auth()->check()) {
            $ids = $this->checkedValues();

           
            
            

            if (!$ids)
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Pilih data yang ingin dihapus terlebih dahulu.']);

            if (in_array(auth()->user()->id, $ids))
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Anda tidak diizinkan untuk menghapus data yang sedang anda gunakan untuk login.']);


            try {
                $permissondels = Leave::whereIn('id',$ids )->get();
                foreach ($permissondels as $permissondel) {
                    Presence::where('user_id', $permissondel->user_id)
                            ->where('presence_date', $permissondel->permission_date)
                            ->where('is_permission', $permissondel->is_accepted)
                            ->delete();
                }
                Leave::whereIn('id', $ids)->delete();
                $this->dispatchBrowserEvent('showToast', ['success' => true, 'message' => 'Data karyawaan berhasil dihapus.']);
            } catch (\Illuminate\Database\QueryException $ex) {
                $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Data gagal dihapus, kemungkinan ada data lain yang menggunakan data tersebut.']);
            }
        }
    }

    public function bulkCheckedEdit()
    {
        if (auth()->check()) {
            $ids = $this->checkedValues();
            

            if (!$ids)
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Pilih data yang ingin diedit terlebih dahulu.']);

            $ids = join('-', $ids);
            // return redirect(route('employees.edit', ['ids' => $ids])); // tidak berfungsi/menredirect
            return $this->dispatchBrowserEvent('redirect', ['url' => route('leaves.edit', ['ids' => $ids])]);
        }
    }

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
            Exportable::make('export')
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
     * @return Builder<\App\Models\User>
     */
    public function datasource(): Builder
    {
        return Leave::query()
            ->join('users', 'leaves.user_id', '=', 'users.id')
            ->join('leaves_type', 'leaves.leave_type', '=', 'leaves_type.id')
            ->join('accept', 'leaves.is_leave', '=', 'accept.id')
            ->select('leaves.*', 'users.name as name','leaves_type.title as leaves_name','accept.name as accept');
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
            ->addColumn('name')
            ->addColumn('start_date')
            ->addColumn('end_date')
            ->addColumn('leaves_name', function (Leave $model) {
                return ucfirst($model->leaves_name);
            })
            ->addColumn('accept', function (Leave $model) {
                return ucfirst($model->accept);
            })
            ->addColumn('number_of_leaves')
            ->addColumn('remaining_days_off') 
            ->addColumn('reason')              
            ->addColumn('created_at')
            ->addColumn('created_at_formatted', fn (Leave $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
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
            Column::make('ID', 'id', 'leaves.id')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name', 'users.name')
                ->makeInputText()
                ->searchable()
                ->sortable(),

            Column::make('Start Date', 'start_date', 'leaves.start_date'),

            Column::make('End Date', 'end_date', 'leaves.end_date'),
              

            Column::make('Is Leaved', 'accept', 'accept.name')
                ->searchable()
                ->makeInputMultiSelect(Accept::all(), 'name', 'is_leave')
                ->sortable(),

            Column::make('Leave Name', 'leaves_name', 'leaves_type.title')
                ->searchable()
                ->makeInputMultiSelect(Leavetype::all(), 'title', 'leave_type')
                ->sortable(),

            Column::make('Number of leaves', 'number_of_leaves', 'leaves.number_of_leaves'),
              

            Column::make('Remaining Days Off', 'remaining_days_off', 'leaves.remaining_days_off'),

            Column::make('Reason', 'reason', 'leaves.reason')
                ->searchable()
                ->makeInputText()
                ->sortable(),


            Column::make('Created at', 'created_at', 'leaves.created_at')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted', 'leaves.created_at')
                ->makeInputDatePicker()
                ->searchable()
        ];
    }
}
