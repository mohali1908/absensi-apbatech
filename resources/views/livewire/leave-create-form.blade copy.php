<div>
    <form wire:submit.prevent="saveLeaves" method="post" novalidate>
        @include('partials.alerts')
        @foreach ($leaves as $i => $leave)
        <div class="mb-3">
            <div class="w-100">
                <div class="mb-3">               
                    <x-form-label id="user_id{{ $i }}" label='Nama {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="user_id"
                        wire:model.defer="leaves.{{ $i }}.user_id">
                        <option selected >-- Pilih Nama --</option>
                        @php
                        $byDate = now()->toDateString();
                        @endphp
                        @foreach ($users as $user)  
                                          
                        <option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                        
                        @endforeach
                    </select>
                    <x-form-error key="leaves.{{ $i }}.user_id" />
                </div>
                <div class="mb-3">               
                    <x-form-label id="attendance_id{{ $i }}" label='Jenis Absensi {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="attendance_id"
                        wire:model.defer="leaves.{{ $i }}.attendance_id">
                        <option selected>-- Pilih Absensi --</option>
                        @foreach ($attendances as $attendance)                        
                        <option value="{{ $attendance->id }}">{{ ucfirst($attendance->title) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="leaves.{{ $i }}.attendance_id" />
                </div>
                
                <div class="mb-3">               
                    <x-form-label id="leave_type{{ $i }}" label='Jenis Cuti {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="leave_type"
                        wire:model="leaves.{{ $i }}.leave_type"">
                        <option selected>-- Pilih Cuti --</option>
                        @foreach ($leavetypes as $leavetype)                        
                        <option value="{{ $leavetype->id }}">{{ ucfirst($leavetype->title) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="leaves.{{ $i }}.leave_type" />
                </div>

                <!-- Menampilkan input tambahan berdasarkan jenis cuti -->
                @if(!is_null($leaves[$i]['leave_type']) && $leaves[$i]['leave_type'] == 1) <!-- Ganti '1' dengan ID jenis cuti yang sesuai -->
      
                <div class="mb-3">
                    <x-form-label id="start_date{{ $i }}" label='Tanggal Mulai cuti({{ $i + 1 }})' />
                    <x-form-input type="date" id="start_date{{ $i }}" name="start_date{{ $i }}" class="form-control"
                        wire:model.defer="leaves.{{ $i }}.start_date" />
                    <small class="text-muted d-block mt-2">Perhatikan format tanggal d (Hari), m (Bulan) dan y
                        (Tahun)</small>
                    <x-form-error key="leaves.{{ $i }}.start_date" />
                </div>             
                <div class="mb-3">
                    <x-form-label id="end_date{{ $i }}" label='Tanggal Akhir cuti({{ $i + 1 }})' />
                    <x-form-input type="date" id="end_date{{ $i }}" name="end_date{{ $i }}" class="form-control"
                        wire:model.defer="leaves.{{ $i }}.end_date" />
                    <small class="text-muted d-block mt-2">Perhatikan format tanggal d (Hari), m (Bulan) dan y
                        (Tahun)</small>
                    <x-form-error key="leaves.{{ $i }}.end_date" />
                </div>

                @endif

                <div class="mb-3">
                    <x-form-label id="number_of_leaves{{ $i }}" label='Jumlah Cuti  {{ $i + 1 }}' />
                    <x-form-input id="number_of_leaves{{ $i }}" name="number_of_leaves{{ $i }}"
                        wire:model.defer="leaves.{{ $i }}.number_of_leaves" placeholder="number_of_leaves" />
                    <x-form-error key="leaves.{{ $i }}.number_of_leaves" />
                </div>

                <div class="mb-3">
                    <label for="remaining_days_off{{ $i }}">Sisa Cuti {{ $i + 1 }}</label>
                    <input type="number" class="form-control" id="remaining_days_off{{ $i }}"
                        wire:model.defer="leaves.{{ $i }}.remaining_days_off" readonly>
                    @error('leaves.' . $i . '.remaining_days_off') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                
                <div class="mb-3">        
                    <x-form-label id="reason{{ $i }}" label='Alasan Cuti  {{ $i + 1 }}' />
                    <x-form-input id="reason{{ $i }}" name="reason{{ $i }}"
                        wire:model.defer="leaves.{{ $i }}.reason" placeholder="reason" />
                    <x-form-error key="leaves.{{ $i }}.reason" />
                </div>
                <div class="mb-3">
                    <x-form-label id="is_accepted{{ $i }}" label='Role {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="is_accepted"
                        wire:model.defer="leaves.{{ $i }}.is_accepted">
                        <option selected >-- Pilih Role --</option>
                        @foreach ($accept as $accept)
                        <option value="{{ $accept->id }}">{{ ucfirst($accept->name) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="leaves.{{ $i }}.is_accepted" />
                </div>
            </div>
            @if ($i > 0)
            <button class="btn btn-sm btn-danger mt-2" wire:click="removeLeaveInput({{ $i }})"
                wire:target="removeLeaveInput({{ $i }})" type="button" wire:loading.attr="disabled">Hapus</button>
            @endif
        </div>
        <hr>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mb-5">
            <button class="btn btn-primary">
                Simpan
            </button>
            {{-- <button class="btn btn-light" type="button" wire:click="addPermissionInput" wire:loading.attr="disabled">
                Tambah Input
            </button> --}}
        </div>
    </form>
</div>