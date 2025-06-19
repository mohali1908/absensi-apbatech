<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;
use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;


class dailyPieChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\PieChart
    {

        $tglskr = Carbon::now()->toDateString();

        $users = User::select(DB::raw('count(*) as jml' ))
                    ->where('id', '<>', 1)
                    ->get();

        $users = $users[0]['jml'];

        $absenpies = Presence::select(DB::raw('(count(presence_date)-sum(is_permission)-sum(CASE WHEN is_leave = 2 THEN 1 ELSE is_leave END)-SUM(CASE WHEN TIME(presence_enter_time) >= "09:16:00"  AND is_permission = 0 THEN 1 ELSE 0 END))  AS hadir'),
                     DB::raw('('.$users.'-count(presence_date)) AS belumhadir'),
                     DB::raw('sum(is_permission) as izin'),
                     DB::raw('sum(is_leave) as cuti'),
                     DB::raw('SUM(CASE WHEN TIME(presence_enter_time) >= "09:16:00" AND is_permission = 0 THEN 1 ELSE 0 END) AS terlambat') // Hitung yang terlambat
                    )
                    ->where(DB::raw('DATE_FORMAT(presence_date, "%Y-%m-%d")'), $tglskr)
                    ->get();

        
          
        $hadir = ceil($absenpies[0]['hadir']);
        $belumhadir = ceil($absenpies[0]['belumhadir']);
        $izin = ceil($absenpies[0]['izin']);
        $cuti = ceil($absenpies[0]['cuti']);
        $terlambat = ceil($absenpies[0]['terlambat']);

         // Data untuk chart
         $data = [
            'Belum Hadir' => $belumhadir,
            'Hadir' => $hadir,
            'Cuti' => $cuti,
            'Izin' => $izin,
            '(Hadir) keterangan' => $terlambat,
        ];
        
        return $this->chart->pieChart()
            ->setTitle('Absen Hari Ini')
            ->setSubtitle($tglskr)
            ->addData(array_values($data))
            ->setLabels(array_keys($data))
            ->setColors(['#808080', '#11c147','#9900ff','#1172c1', '#ffbd33'  ]) // Tentukan warna secara manual
            ->setOptions([
                'dataLabels' => [
                    'enabled' => true,
                    'style' => [
                        'colors' => ['black'], // Set data labels text color to black inside pie slices
                        'fontSize' => '14px',
                        'fontWeight' => 'bold',
                    ]
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function(value) { return value; }', // Display actual value
                        'style' => [
                            'color' => '#000000' // Set tooltip text color to black
                        ]
                    ]
                ],
                'plotOptions' => [
                    'pie' => [
                        'dataLabels' => [
                            'style' => [
                                'color' => '#000000' // Ensure label text inside pie slices is black
                            ]
                        ]
                    ]
                ],
                'labels' => [
                    'style' => [
                        'colors' => ['#000000'], // Label text color outside pie slices
                    ]
                ],
                'legend' => [
                    'labels' => [
                        'colors' => ['#000000'], // Black text for the legend
                    ]
                ],
            ]);
    }
}
