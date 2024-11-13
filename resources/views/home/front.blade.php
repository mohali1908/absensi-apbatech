<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        .chart-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-container h2 {
            margin-top: 0;
        }
        .chart {
            width: 100%;
            height: 500px;
        }
    </style>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <title>{{ $title }} | Absensi App</title>

</head>

<body>

    <nav class="navbar navbar-expand-md bg-dark navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand bg-transparent fw-bold" href="{{ route('home.front') }}">Absensi Apbatech</a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav align-items-md-center gap-md-4 py-2 py-md-0">
                    <li class="nav-item px-4 py-1 px-md-0 py-md-0">
                        <a class="nav-link {{ request()->routeIs('home.*') ? 'active fw-bold' : '' }}" aria-current="page"
                            href="{{ route('auth.login') }}">Login</a>
                    </li>
                    <li class="nav-item px-4 py-1 px-md-0 py-md-0">
                        <form action="{{ route('auth.logout') }}" method="post">
                            @method('DELETE')
                            @csrf
    
                            <button class="btn fw-bold btn-danger w-100">Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <x-toast-container />
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Absen : </h5>
                <div>Hari : <span class="fw-bold">
                {{ \Carbon\Carbon::parse($date)->dayName }}
                {{ \Carbon\Carbon::parse($date)->isCurrentDay() ? '(Hari ini)' : '' }}
                </span>
                </div>
                <div>Tanggal : <span class="fw-bold">{{ $date }}</span></div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Jam Masuk</th> 
                                <th>Absen Masuk Dari</th>
                                <th>Jam Pulang</th>
                                <th>Absen pulang Dari</th> 
                                <th>Status</th>
                                                     
                            </tr>
                        </thead>
                        <tbody>                   
                        @foreach ($absenall as $absen)
                        <tr>
                            <th>{{ $loop ->iteration}} </th>
                            <td> <a href="{{ route('home.frontdetail', ['id'=>$absen->id , 'attendanceId'=>1])}}"> {{ $absen->name}} </a> </td>
                            @if ($absen->presence_enter_time  == null) 
                                
                                @if($absen->is_leave  == 1) 
                                <td colspan="5">               
                                    @php
                                    $leave = $leaves->where('user_id', $absen->user_id)
                                                ->where('start_date','<=', $date)
                                                ->where('end_date','>=', $date)
                                                ->first();
                                    
                                    @endphp
                                    
                                        <button class="badge text-bg-info border-0 leave-detail-modal-triggers"
                                        data-leave-id="{{ $leave->id }}" data-bs-toggle="modal"
                                        data-bs-target="#leave-detail-modal">Cuti</button>    
                                </td>
                                @else
                                    <td colspan="5">
                                        <div class="badge text-bg-secondary">Belum Hadir</div>   
                                    </td>
                                @endif
                            @else
                                @if ($absen->is_permission == 1)
                                <td colspan="5">               
                                    @php
                                    $permissions = $permissions->where('user_id', $absen->user_id)
                                                ->where('permission_date', $date)
                                                ->first();
                                    @endphp
                                    
                                        <button class="badge text-bg-primary border-0 permission-detail-modal-triggers"
                                        data-permission-id="{{ $permissions->id }}" data-bs-toggle="modal"
                                        data-bs-target="#permission-detail-modal">Izin</button>    
                                </td>  
                                @else
                                    <td>{{ $absen->presence_enter_time}}</td>
                                    <td>{{ $absen->presence_enter_from}}</td>
                                    <td>{{ $absen->presence_out_time}}</td>
                                    <td>{{ $absen->presence_out_from}}</td>
                                    @if($absen->is_leave  == 2) 
                                    

                                    <td colspan="5">               
                                        @php
                                        $leave = $leaves->where('user_id', $absen->user_id)
                                                    ->where('start_date','<=', $date)
                                                    ->where('end_date','>=', $date)
                                                    ->first();
                                        @endphp
                                        
                                            <button class="badge text-bg-info border-0 leave-detail-modal-triggers"
                                            data-leave-id="{{ $leave->id }}" data-bs-toggle="modal"
                                            data-bs-target="#leave-detail-modal">Cuti 1/2 hari</button>    
                                    </td>
                                    @else
                                    <td>
                                        @php
                                            $enterTime = \Carbon\Carbon::parse($absen->presence_enter_time);
                                            $lateTime = \Carbon\Carbon::createFromTime(9, 16, 0);
                                            $isLate = $enterTime->gt($lateTime); // True if enterTime is later than lateTime
                                        @endphp
                                    
                                        <button class="badge border-0 absen-detail-modal-triggers 
                                                       {{ $isLate ? 'text-bg-warning' : 'text-bg-success' }}" 
                                                data-absen-id="{{ $absen->presence_id }}"  
                                                data-bs-toggle="modal" 
                                                data-bs-target="#absen-detail-modal">Hadir1
                                        </button>
                                    </td>
                                    @endif 

                                @endif                        
                            @endif                     
                        </tr>
                        @endforeach   
                    </table>
                </div>
            </div>
            <div class="col-md-6">

                <div class="container px-4 mx-auto">
                    <div class="chart-container p-6 m-20 bg-white rounded shadow">
                        {!! $dailyPieChart->container(['id' => 'dailyPieChart']) !!}
                    </div>
                </div>
                
                {{-- <div class="container px-4 mx-auto">
                    <div class="chart-container p-6 m-20 bg-white rounded shadow" style="width: 100%; height: 500px;">
                        {!! $multiValueLineChart ->container(['id' => 'multiValueLineChart']) !!}
                    </div>
                </div> --}}
                
                {{-- <div class="align-self-center wow bounceInUp" data-aos="zoom-in" data-aos-delay="100">
                    <div class="box">
                      <div class="card-body">
                        <!-- /.d-flex -->
        
                        <div class="position-relative mb-15">
        
                        {!! $dailyAbsenChart->container() !!}
        
                        </div>
        
                        <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            <i class="fas fa-square text-primary"></i> Absen Masuk
                        </span>
        
                        </div>
                      </div>
                    </div>
                </div>   --}}
                
        </div>
    </div>
    <div class="modal fade" id="permission-detail-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Izin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>Judul Izin : <span id="permission-title"></span></li>
                        <li>Keterangan Izin : <p id="permission-description"></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="leave-detail-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Cuti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li><strong>Keterangan Cuti:</strong>  <p id="leave-reason"></p></li>
                        <li><strong>Tanggal Mulai:</strong>  <span id="leave-start-date"></span></li>
                        <li><strong>Tanggal Akhir:</strong>  <span id="leave-end-date"></span></li>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="absen-detail-modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Presence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>Keterangan: <p id="absen-notes"></p></li> <!-- This will be updated dynamically -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
        

    @include('partials.scripts')
    @stack('script')

    
    <script>
    const permissionUrl = "{{ route('api.permissions.show') }}";
    const leaveUrl = "{{ route('api.leaves.show') }}";
    const absenUrl = "{{ route('api.absen.show') }}";
    </script>




    <script src="{{ asset('js/presences/permissions.js') }}"></script>
    <script src="{{ asset('js/presences/leaves.js') }}"></script>
    <script src="{{ asset('js/presences/absen.js') }}"></script>


    
<!-- Di bawah sini. pastikan script dimasukkan -->
<script src="{{ $dailyPieChart->cdn() }}"></script>
{{ $dailyPieChart->script() }}

<script src="{{ $multiValueLineChart->cdn() }}"></script>
{{ $multiValueLineChart->script() }}



   
       
   

</body> 

</html>


