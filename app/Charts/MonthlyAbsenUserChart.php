<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;
use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;

class monthlyAbsenUserChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;      
    }

    public function build($userId): \ArielMejiaDev\LarapexCharts\PieChart
    {

     

        // Mendapatkan tanggal 30 hari yang lalu
        $startDate = Carbon::now()->subDays(38)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Ambil data absensi hanya pada hari kerja (weekdays)
        $absensi = DB::table('presences')
            ->select('presence_enter_time', 'presence_date')
            ->where('user_id', $userId) // Filter berdasarkan user_id
            ->whereBetween('presence_date', [$startDate, $endDate])
            ->whereNotIn(DB::raw('DAYOFWEEK(presence_date)'), [1, 7]) // 1 = Sunday, 7 = Saturday
            ->where('is_leave', 0) // Exclude records where is_leave is true
            ->get();

        // Pisahkan data antara tepat waktu dan terlambat
        $tepatWaktuCount = $absensi->filter(function ($item) {
            return $item->presence_enter_time <= '09:15:59';
        })->count();

        $terlambatCount = $absensi->filter(function ($item) {
            return $item->presence_enter_time >= '09:16:00';
        })->count();

        // Data untuk chart
        $data = [
            'Tepat Waktu' => $tepatWaktuCount,
            'Terlambat' => $terlambatCount,
        ];

        return $this->chart->pieChart()
            ->setTitle('Absensi Selama 30 Hari Kerja Terakhir')
            ->setSubtitle('Tepat Waktu vs Terlambat')
            ->addData(array_values($data))
            ->setLabels(array_keys($data))
            ->setColors(['#11c147', '#ffbd33']) // Green for Tepat Waktu, Red for Terlambat
            ->setOptions([
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(value) { return value; }' // Tampilkan nilai asli
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function(value) { return value; }' // Tampilkan nilai asli di tooltip
                    ]
                ]
            ]);

    }
}
