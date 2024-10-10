<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;
use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;

class MultiValueLineChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\LineChart
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $absenpies = Presence::select(DB::raw('DATE(presence_date) as date'),
            DB::raw('(count(presence_date)-sum(is_permission)-sum(is_leave)- SUM(CASE WHEN TIME(presence_enter_time) > "09:15:00" THEN 1 ELSE 0 END)) AS hadir'),
            DB::raw('('.DB::raw('(select count(distinct user_id) from presences where presence_date BETWEEN "'.$startDate.'" AND "'.$endDate.'")').' - count(presence_date)) AS belumhadir'),
            DB::raw('sum(is_permission) as izin'),
            DB::raw('sum(is_leave) as cuti'),
            DB::raw('SUM(CASE WHEN TIME(presence_enter_time) > "09:15:00" THEN 1 ELSE 0 END) AS terlambat')
        )
        ->whereBetween('presence_date', [$startDate, $endDate])
        ->whereNotIn(DB::raw('DAYOFWEEK(presence_date)'), [1, 7])
        ->groupBy(DB::raw('DATE(presence_date)'))
        ->get();

        $dates = $absenpies->pluck('date')->toArray();
        $hadirData = $absenpies->pluck('hadir')->toArray();
        $belumhadirData = $absenpies->pluck('belumhadir')->toArray();
        $izinData = $absenpies->pluck('izin')->toArray();
        $cutiData = $absenpies->pluck('cuti')->toArray();
        $terlambatData = $absenpies->pluck('terlambat')->toArray();

        return $this->chart->lineChart()
            ->setTitle('Attendance Data for the last 5 Working  Days')
            ->setSubtitle('Daily Absen')
            ->addData('Belum Hadir', $belumhadirData)
            ->addData('Hadir', $hadirData)
            ->addData('Izin', $izinData)
            ->addData('Cuti', $cutiData)
            ->addData('Terlambat', $terlambatData)
            ->setLabels($dates)
            ->setColors(['#808080', '#11c147','#9900ff','#1172c1', '#ffbd33' ])
            ->setOptions([
                'dataLabels' => [
                    'enabled' => true,
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function(value) { return value; }'
                    ]
                ]
            ]);
    }
}
