<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.styles')
    @stack('style')
    

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
                        <button class="btn fw-bold btn-info w-100"  value="Kembali" onclick="history.back(-1)">Kembali</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <x-toast-container />
        <div class="container py-5">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">Histori 30 Hari Terakhir</h5>
                    <h5 class="mb-3">Nama : {{ $user_name->name }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Hari</th>
                                    <th scope="col">Jam Masuk</th>
                                    <th scope="col">Absen Masuk Dari</th>
                                    <th scope="col">Jam Pulang</th>
                                    <th scope="col">Absen pulang Dari</th>                                  
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($priodDate as $date)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    {{-- not presence / tidak hadir --}}
                                    @php
                                    $histo = $history->where('presence_date', $date)->first();
                                    @endphp
                                    @if(!$histo)
                                    <td>{{ $date }}</td>
                                    <td>{{ \Carbon\Carbon::parse($date)->dayName }}</td>
                                    <td colspan="5">
                                        @php
                                            $isHoliday = in_array($date, $holidays);
                                            $today = now()->toDateString();
                                        @endphp
                                        @if($date == now()->toDateString())
                                        <div class="badge text-bg-info">Belum Hadir</div>
                                        @elseif($isHoliday)
                                        <div class="badge text-bg-primary">Hari Libur</div> <!-- Blue for holidays -->
                                        @else
                                        <div class="badge text-bg-danger">Tidak Hadir</div>
                                        @endif
                                    </td>
                                    @else
                                    <td>{{ $histo->presence_date }}</td>
                                    <td>{{ \Carbon\Carbon::parse( $histo->presence_date)->dayName }}</td>
                                    @if ($histo->is_permission == 1)
                                     <td colspan="5">
                                        @php
                                        $permissions = $permissions
                                                        ->where('user_id', $user_name->id)
                                                        ->where('permission_date',  $date)
                                                        ->first();
                                        @endphp                                 
                                        <button class="badge text-bg-warning border-0 permission-detail-modal-triggers"
                                        data-permission-id="{{ $permissions->id }}" data-bs-toggle="modal"
                                        data-bs-target="#permission-detail-modal">Izin</button> 
                                     </td> 
                                            
                                        @else
                                        @if ($histo->is_leave == 1 && $histo->presence_enter_time == Null )
                                        <td colspan="5">               
                                            @php
                                            $leave = $leaves->where('user_id', $histo->user_id)
                                                        ->where('start_date','<=', $date)
                                                        ->where('end_date','>=', $date)
                                                        ->first();                         
                                            @endphp
                                            
                                                <button class="badge text-bg-warning border-0 leave-detail-modal-triggers"
                                                data-leave-id="{{ $leave->id }}" data-bs-toggle="modal"
                                                data-bs-target="#leave-detail-modal">Cuti</button>    
                                        </td> 
                                               
                                           @else
                                            <td>{{ $histo->presence_enter_time }}</td>
                                            <td>@if($histo->presence_enter_time != Null   ) 
                                                    @if($histo->presence_enter_from == 1 )  
                                                        Mesin  
                                                    @else  
                                                        Web                                   
                                                    @endif
                                                @endif
                                            </td>
                                            <td>@if($histo->presence_out_time)
                                                    {{ $histo->presence_out_time }}
                                                @else
                                                    <span class="badge text-bg-danger">Belum Absensi</span>
                                                @endif
                                            </td> 
                                            <td>@if($histo->presence_out_time != Null   ) 
                                                @if($histo->presence_out_from == 1 ) 
                                                        Mesin  
                                                    @else  
                                                        Web 
                                                    @endif
                                          
                                                @endif
                                            </td>  
                                            @if($histo->is_leave  == 2  ) 
                                            <td colspan="5">               
                                                @php
                                                $leave = $leaves->where('user_id', $histo->user_id)
                                                            ->where('start_date','<=', $date)
                                                            ->where('end_date','>=', $date)
                                                            ->first();
                                                @endphp
                                                
                                                    <button class="badge text-bg-warning border-0 leave-detail-modal-triggers"
                                                    data-leave-id="{{ $leave->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#leave-detail-modal">Cuti 1/2 hari</button>    
                                            </td>
                                            @else
                                            <td> @php
                                                $enterTime = \Carbon\Carbon::parse($histo->presence_enter_time);
                                                $lateTime = \Carbon\Carbon::createFromTime(9, 16, 0);
                                                @endphp
                                
                                                @if($enterTime->gt($lateTime))
                                                    <div class="badge text-bg-warning">Hadir</div>
                                                @else
                                                    <div class="badge text-bg-success">Hadir</div>
                                                @endif
                                            </td>
                                            @endif                                
                                        @endif
                                        
                                        @endif 
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="container px-4 mx-auto">

                        <div class="p-6 m-20 bg-white rounded shadow">
                            {!! $monthlyAbsenUserChart->container() !!}
                        </div>
                    
                    </div>
                
                </div>
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
        @include('partials.scripts')
        @stack('script')
    
        
        <script>
        const permissionUrl = "{{ route('api.permissions.show') }}";
        const leaveUrl = "{{ route('api.leaves.show') }}";
        </script>
        <script src="{{ asset('js/presences/permissions.js') }}"></script>
        <script src="{{ asset('js/presences/leaves.js') }}"></script>

        <script src="{{ $monthlyAbsenUserChart->cdn() }}"></script>
        {{ $monthlyAbsenUserChart->script() }}

</body>

</html>