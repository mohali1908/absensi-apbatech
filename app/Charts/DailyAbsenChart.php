<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;
use App\Models\Presence;
use Carbon\Carbon;

class dailyAbsenChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
       // Tanggal mulai
       $startDate = Carbon::now();

       // Array kosong untuk menampung data
    
       // Interval dalam hari (misalnya 7 hari)
       
       $interval = 7;
       
        for ($i = 0; $i <= 7 ; $i++)  {

           
           $currentDate = $startDate->copy()->addDays($i - $interval)->toDateString();
       
           $absenharian = Presence::select(DB::raw('DATE_FORMAT(presence_date, "%Y-%m-%d") as tanggal'), DB::raw('COUNT(*) as datacount'))
           ->where(DB::raw('DATE_FORMAT(presence_date, "%Y-%m-%d")'), $currentDate)
           ->groupby('user_id',DB::raw('DATE_FORMAT(presence_date, "%Y-%m-%d")'))
           ->pluck('datacount');        
           
           $countharian = $absenharian->count();
           $dates[] = $currentDate;
           $datacount[] = $countharian;

       } 

       //dd($datacount);

       //dd($dates);
       return $this->chart->barChart()
           ->setTitle('Data absen perhari')
           ->setSubtitle('Jumlah absen')
           ->addData( 'Jumlah absen', $datacount)
           ->setXAxis($dates );
           
    }
}

