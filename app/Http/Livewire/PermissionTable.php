<?php

namespace App\Http\Livewire;

use App\Models\Accept;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Presence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class PermissionTable extends PowerGridComponent
{
    use ActionButton;

    //Table sort field
    public string $sortField = 'permissions.created_at';
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

    public function header(): array
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
    }

    public function bulkCheckedDelete()
    {
        if (auth()->check()) {
            $ids = $this->checkedValues();

           
            
            

            if (!$ids)
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Pilih data yang ingin dihapus terlebih dahulu.']);

            if (in_array(auth()->user()->id, $ids))
                return $this->dispatchBrowserEvent('showToast', ['success' => false, 'message' => 'Anda tidak diizinkan untuk menghapus data yang sedang anda gunakan untuk login.']);


            try {
                $permissondels = Permission::whereIn('id',$ids )->get();
                foreach ($permissondels as $permissondel) {
                    Presence::where('user_id', $permissondel->user_id)
                            ->where('presence_date', $permissondel->permission_date)
                            ->where('is_permission', $permissondel->is_accepted)
                            ->delete();
                }
                Permission::whereIn('id', $ids)->delete();
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
            return $this->dispatchBrowserEvent('redirect', ['url' => route('permissions.edit', ['ids' => $ids])]);
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
        return Permission::query()
            ->join('users', 'permissions.user_id', '=', 'users.id')
            ->join('accept', 'permissions.is_accepted', '=', 'accept.id')
            ->select('permissions.*', 'users.name as name','accept.name as accept');
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
            ->addColumn('title')
            ->addColumn('description')
            ->addColumn('permission_date')
            ->addColumn('accept', function (Permission $model) {
                return ucfirst($model->accept);
            })
            ->addColumn('created_at')
            ->addColumn('created_at_formatted', fn (Permission $model) => Carbon::parse($model->created_at)->format('d/m/Y H:i:s'));
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
            Column::make('ID', 'id', 'permissions.id')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'name', 'users.name')
                ->searchable()
                ->makeInputText()
                ->editOnClick()
                ->sortable(),

            Column::make('Title', 'title', 'permissions.title')
                ->searchable()
                ->makeInputText()
                ->sortable(),

            Column::make('Description', 'description', 'permissions.description')
                ->searchable()
                ->makeInputText()
                ->sortable(),

            Column::make('Tanggal', 'permission_date', 'permissions.permission_date')
                ->makeInputDatePicker()
                ->searchable()
                ->sortable(),

            Column::make('Status', 'accept', 'accept.name')
                ->searchable()
                ->makeInputMultiSelect(Accept::all(), 'name', 'is_accepted')
                ->sortable(),

            Column::make('Created at', 'created_at', 'users.created_at')
                ->hidden(),

            Column::make('Created at', 'created_at_formatted', 'users.created_at')
                ->makeInputDatePicker()
                ->searchable()
        ];
    }
}
