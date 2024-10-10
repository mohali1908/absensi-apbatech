<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\Presence;

class LeaveController extends Controller
{
    public function index()
    {

        return view('leaves.index', [
            "title" => "Cuti Karyawan"
        ]);
    }

    public function create()
    {
        return view('leaves.create', [
            "title" => "Tambah Data Cuti"
        ]);
    }

    public function edit()
    {
        $ids = request('ids');
        if (!$ids)
            return redirect()->back();
        $ids = explode('-', $ids);

        // ambil data user yang hanya memiliki User::USER_ROLE_ID / role untuk karyawaan
        $leaves = Leave::query()
            ->whereIn('id', $ids)
            ->get();


        //dd($leaves);

        return view('leaves.edit', [
            "title" => "Edit Data Izin",
            "leaves" => $leaves
        ]);


    }
}
