<div>
    <form wire:submit.prevent="savePresences" method="post" novalidate>
        @include('partials.alerts')
        @foreach ($presences as $i => $presence)
        <div class="mb-3">
            <div class="w-100">
                <div class="mb-3">               
                    <x-form-label id="user_id{{ $i }}" label='Nama {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="user_id"
                        wire:model.defer="presences.{{ $i }}.user_id">
                        <option selected >-- Pilih Nama --</option>

                        @if($users->isEmpty())
                            <option disabled>Karyawaan Sudah Lengkap</option>
                        @else
                            @php
                                $byDate = now()->toDateString();
                            @endphp
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                            @endforeach
                        @endif

                    </select>
                    <x-form-error key="presences.{{ $i }}.user_id" />
                </div>
                <div class="mb-3">               
                    <x-form-label id="attendance_id{{ $i }}" label='Jenis Absensi {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="attendance_id"
                        wire:model.defer="presences.{{ $i }}.attendance_id">
                        <option selected>-- Pilih Absensi --</option>
                        @foreach ($attendances as $attendance)                        
                        <option value="{{ $attendance->id }}">{{ ucfirst($attendance->title) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="presences.{{ $i }}.attendance_id" />
                </div>
                <div class="mb-3">
                    <x-form-label id="presence_date{{ $i }}" label='Tanggal Masuk({{ $i + 1 }})' />
                    <x-form-input type="date" id="presence_date{{ $i }}" name="presence_date{{ $i }}" class="form-control"
                        wire:model.defer="presences.{{ $i }}.presence_date" />
                    <small class="text-muted d-block mt-2">Perhatikan format tanggal d (Hari), m (Bulan) dan y
                        (Tahun)</small>
                    <x-form-error key="presences.{{ $i }}.presence_date" />
                </div>
                <div class="mb-3">
                    <x-form-label id="presence_enter_time{{ $i }}" label='Jam Masuk ({{ $i + 1 }})' />   
                    <!-- Input Jam dan Menit -->
                    <x-form-input type="time" id="presence_enter_time{{ $i }}" name="presence_enter_time{{ $i }}" class="form-control"
                                  wire:model.defer="presences.{{ $i }}.presence_enter_time" />       
                    <!-- Informasi Format Waktu -->
                    <small class="text-muted d-block mt-2">Perhatikan format waktu h (Jam) dan m (Menit)</small>
                    <!-- Tampilkan Error jika ada -->
                    <x-form-error key="presences.{{ $i }}.presence_enter_time" />
                </div>
                <div class="mb-3">
                    <x-form-label id="presence_from{{ $i }}" label='Absen dari : ({{ $i + 1 }})' />
                    
                    <!-- Input yang secara otomatis diisi dengan nilai 0 -->
                    <x-form-input type="hidden" id="presence_from{{ $i }}" name="presence_from{{ $i }}" class="form-control"
                                  wire:model.defer="presences.{{ $i }}.presence_from" /> 
                    <!-- Informasi -->
                    <small class="text-muted d-block mt-2">Absen masuk dari Web </small>          
                    <x-form-error key="presences.{{ $i }}.presence_from" />
                </div>
                
            </div>
            @if ($i > 0)
            <button class="btn btn-sm btn-danger mt-2" wire:click="removepresenceInput({{ $i }})"
                wire:target="removepresenceInput({{ $i }})" type="button" wire:loading.attr="disabled">Hapus</button>
            @endif
        </div>
        <hr>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mb-5">
            <button class="btn btn-primary">
                Simpan
            </button>
            {{-- <button class="btn btn-light" type="button" wire:click="addpresenceInput" wire:loading.attr="disabled">
                Tambah Input
            </button> --}}
        </div>
    </form>
</div>