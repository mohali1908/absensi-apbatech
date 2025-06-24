<?php

namespace App\Http\Controllers;

use App\Http\Livewire\AttendanceAbstract;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Permission;
use App\Models\Presence;
use App\Models\Leave;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Charts\DailyAbsenChart;
use App\Charts\DailyPieChart;
use App\Charts\MonthlyAbsenUserChart;
use App\Charts\MultiValueLineChart;

use App\Http\Requests\LoginRequests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index(DailyAbsenChart $dailyAbsenChart, DailyPieChart $dailyPieChart,MultiValueLineChart $multiValueLineChart )
    {
        $absenall = $absenall = DB::table('users')
        ->select('users.id','user_id','users.name' ,'attendance_id', 'b.id as presence_id','presence_date','presence_enter_time','presence_out_time','presence_from','presence_enter_from','presence_out_from','is_permission','is_leave')
        ->leftjoin(DB::raw('(SELECT * from presences WHERE presence_date = CURDATE()) b'),function($join){ 
            $join->on ('b.user_id','=', 'users.id');
        })
        ->whereNotIn('users.id', [1,2]) // Mengabaikan user dengan id 1 dan 2
        ->where('users.status', 1)
        ->groupBy('users.id')
        ->orderBy('presence_enter_time', 'desc')
        ->get();

        // cek absen nasuk dari
        foreach ($absenall as $absen) {
            if ($absen->presence_enter_time !== null) {
                if ($absen->presence_enter_from == 1) {
                    $absen->presence_enter_from = 'Mesin';
                } elseif ($absen->presence_enter_from == 0) {
                    $absen->presence_enter_from = 'Web';
                } 
            } else {
                $absen->presence_enter_from = ''; // Nilai jika 'presence' adalah null
            }
        }

        // cek absen pulang  dari
        foreach ($absenall as $absen) {
            if ($absen->presence_out_time !== null) {
                if ($absen->presence_out_from == 1) {
                    $absen->presence_out_from = 'Mesin';
                } elseif ($absen->presence_out_from == 0) {
                    $absen->presence_out_from = 'Web';
                } 
            } else {
                $absen->presence_out_from = ''; // Nilai jika 'presence' adalah null
            }
        }
        

        $byDate = now()->toDateString();
        if (request('display-by-date'))
            $byDate = request('display-by-date');

        $permissions = Permission::query()
            ->get();

        $leaves = Leave::query()
            ->get();

        

        return view('home.front', [
            "title" => "Informasi Absensi Kehadiran",
            'absenall' => $absenall,
            "permissions" => $permissions,
            "leaves" => $leaves,
            "date" => $byDate,
            'dailyAbsenChart' => $dailyAbsenChart->build(),
            'dailyPieChart' => $dailyPieChart->build(),
            'multiValueLineChart' => $multiValueLineChart->build(),

            
          
        ]);
    }

    
    public function login()
    {
        return view('auth.login', [
            "title" => "Masuk"
        ]);
    }

    public function authenticate(LoginRequest $request)
    {
        $remember = $request->boolean('remember');
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials, $remember)) { // login gagal
            request()->session()->regenerate();
            $data = [
                "success" => true,
                "redirect_to" => auth()->user()->isUser() ? route('home.index') : route('dashboard.index'),
                "message" => "Login berhasil, silahkan tunggu!"
            ];
            return response()->json($data);
        }

        $data = [
            "success" => false,
            "message" => "Login gagal, silahkan coba lagi!"
        ];
        return response()->json($data)->setStatusCode(400);
    }

    public function frontdetail($id, $attendanceId, monthlyAbsenUserChart $monthlyAbsenUserChart)
    {

        $presences = Presence::query()
        ->where('attendance_id', $attendanceId)
        ->where('user_id', $id)
        ->get();

        $isHasEnterToday = $presences
            ->where('presence_date', now()->toDateString())
            ->isNotEmpty();


        $isTherePermission = Permission::query()
            ->where('permission_date', now()->toDateString())
            ->where('attendance_id',  $id)
            ->first();

        $data = [
            'is_has_enter_today' => $isHasEnterToday, // sudah absen masuk
            'is_not_out_yet' => $presences->where('presence_out_time', null)->isNotEmpty(), // belum absen pulang
            'is_there_permission' => (bool) $isTherePermission,
            'is_permission_accepted' => $isTherePermission?->is_accepted ?? false
        ];

        $user_name = User::query()
            ->where('id',$id)
            ->first();
    

        
        $history = Presence::query()
            ->where('user_id', $id)
            //->where('attendance_id', $absen->attendance_id)
            ->get();

        $byDate = now()->toDateString();
            if (request('display-by-date'))
                $byDate = request('display-by-date');
    
        $permissions = Permission::query()
            //->where('user_id', $id)
            //->where('permission_date', $byDate)
            ->get();
        
        $leaves = Leave::query()
            ->get();
        
            //dd($permissions );

        $holidays = Holiday::pluck('holiday_date')->toArray();


        // untuku melihat karyawan yang tidak hadir
        $priodDate = CarbonPeriod::create('2024-01-01', now()->toDateString());

        $priodDate = $priodDate->filter(function (Carbon $date) {
                return $date;
            })->toArray();

        foreach ($priodDate as $i => $date) { // get only stringdate
            $priodDate[$i] = $date->toDateString();
        }
        
        $priodDate = array_slice(array_reverse($priodDate), 0, 30);

         // Tambahkan logika untuk menentukan apakah hari ini adalah hari libur
        $isWeekend = Carbon::now()->isWeekend();
        $isHoliday = in_array(Carbon::now()->toDateString(), $holidays);
        $holidayDescription = '';
        if ($isHoliday) $holidayDescription = Holiday::where('holiday_date', Carbon::now()->toDateString())->first()->description;

        // Generate the chart data for the specific user
        $monthlyAbsenUserChart = $monthlyAbsenUserChart->build($id); // Pass the user ID

        return view('home.frontdetail', [
            "title" => "Informasi Absensi Kehadiran",
            "data" => $data,
            'history' => $history,
            'priodDate' => $priodDate,
            "leaves" => $leaves,
            "permissions" => $permissions,
            'holidays' => $holidays,
            'user_name' => $user_name,
            'monthlyAbsenUserChart' => $monthlyAbsenUserChart, // Pass the chart instance,
            'isWeekend' => $isWeekend,
            'isHoliday' => $isHoliday,
            'holidayDescription' => $holidayDescription,
            'date' => $byDate,
        ]);
    }

    public function logout()
    {
        auth()->logout();

        request()->session()->regenerate();
        request()->session()->regenerateToken();

        return redirect()->route('home.front')->with('success', 'Anda berhasil keluar.');
    }
}
