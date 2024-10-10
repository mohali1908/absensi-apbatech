<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Presence;

class PermissionController extends Controller
{
    public function index()
    {

        return view('permissions.index', [
            "title" => "Izin Karyawaan"
        ]);
    }

    public function create()
    {
        return view('permissions.create', [
            "title" => "Tambah Data Izin"
        ]);
    }

    public function edit()
    {
        $ids = request('ids');
        if (!$ids)
            return redirect()->back();
        $ids = explode('-', $ids);

        // ambil data user yang hanya memiliki User::USER_ROLE_ID / role untuk karyawaan
        $permissions = Permission::query()
            ->whereIn('id', $ids)
            ->get();


        //dd($permissions);

        return view('permissions.edit', [
            "title" => "Edit Data Izin",
            "permissions" => $permissions
        ]);


    }
}
