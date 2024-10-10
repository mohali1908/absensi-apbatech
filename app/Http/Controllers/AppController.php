<?php

namespace App\Http\Controllers;
use App\Charts\DailyAbsenChart;
use App\Models\KehadiranModel;
use App\Models\KaryawanModel;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;


use Illuminate\Http\Request;

class AppController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */


     public function index(DailyAbsenChart $dailyAbsenChart){

        $absenall = DB::table('tbl_karyawan')
        ->select('b.id','tbl_karyawan.uid','b.keterangan','tbl_karyawan.nama',DB::raw('min(b.tanggal) as masuk'))
        ->leftjoin(DB::raw('(SELECT id,uid,keterangan,tanggal from tbl_kehadiran WHERE DATE_FORMAT(tanggal, "%Y-%m-%d") = CURDATE()) b'),function($join){ 
            $join->on ('b.uid','=', 'tbl_karyawan.uid'); 
        })
        ->groupBy('tbl_karyawan.uid')
        ->orderBy('tbl_karyawan.nama')
        ->get();
        

        $absenmasuk = DB::table('tbl_karyawan')
        ->select(DB::raw('count(*) as masuk'))
        ->leftjoin(DB::raw('(SELECT uid,  tanggal from tbl_kehadiran WHERE DATE_FORMAT(tanggal, "%Y-%m-%d") = CURDATE()  GROUP BY uid) b '),function($join){ 
            $join->on ('b.uid','=', 'tbl_karyawan.uid'); 
        })
        ->whereNotNull('tanggal')
        ->get();

        

        $absenkosong = DB::table('tbl_karyawan')
        ->select(DB::raw('count(*) as kosong'))
        ->leftjoin(DB::raw('(SELECT uid,  tanggal from tbl_kehadiran WHERE DATE_FORMAT(tanggal, "%Y-%m-%d") = CURDATE()  GROUP BY uid) b'),function($join){ 
            $join->on ('b.uid','=', 'tbl_karyawan.uid'); 
        })
        ->whereNull('tanggal')
        ->get();

        
        return view('admin.dashboard',[
            'absenmasuk' => $absenmasuk, 
            'absenall' => $absenall, 
            'absenkosong' => $absenkosong,
            'dailyAbsenChart' => $dailyAbsenChart->build()
        ]);

        
    }


   

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        //
    }

    public function tampilketerangan(string $uid)
    {

        $data =  KaryawanModel::where('uid', $uid)
                ->first();

        //dd($data);
        
        return view('admin.insertketerangan', compact('data'));
        
    }
    
    public function insertketerangan(Request $request){
       // dd($request->all());
        KehadiranModel::create($request->all());
        Alert::success('Hore!', 'Update Keterangan Successfully');
        return redirect()->route('admin.dashboard');

    }


    /**
     * Show the form for editing the specified resource.
     */

    public function edit(string $id)
    {
        //
    }

    public function updateketerangan(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
