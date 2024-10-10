<div>
    <form wire:submit.prevent="savePermissions" method="post" novalidate>
        @include('partials.alerts')
        @foreach ($permissions as $i => $permission)
        <div class="mb-3">
            <div class="w-100">
                <div class="mb-3">               
                    <x-form-label id="user_id{{ $i }}" label='Nama {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="user_id"
                        wire:model.defer="permissions.{{ $i }}.user_id">
                        <option selected >-- Pilih Nama --</option>
                        @php
                        $byDate = now()->toDateString();
                        @endphp
                        @foreach ($users as $user)  
                                          
                        <option value="{{ $user->id }}">{{ ucfirst($user->name) }}</option>
                        
                        @endforeach
                    </select>
                    <x-form-error key="permissions.{{ $i }}.user_id" />
                </div>

                <div class="mb-3">               
                    <x-form-label id="attendance_id{{ $i }}" label='Jenis Absensi {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="attendance_id"
                        wire:model.defer="permissions.{{ $i }}.attendance_id">
                        <option selected>-- Pilih Absensi --</option>
                        @foreach ($attendances as $attendance)                        
                        <option value="{{ $attendance->id }}">{{ ucfirst($attendance->title) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="permissions.{{ $i }}.attendance_id" />
                </div>
                <div class="mb-3">
                    <x-form-label id="title{{ $i }}" label='Judul Izin {{ $i + 1 }}' />
                    <x-form-input id="title{{ $i }}" name="title{{ $i }}" wire:model.defer="permissions.{{ $i }}.title" placeholder="Judul"/>
                    <x-form-error key="permissions.{{ $i }}.title" />
                </div>
                <div class="mb-3">
                    <x-form-label id="description{{ $i }}" label='Keterangan Izin {{ $i + 1 }}' />
                    <x-form-input id="description{{ $i }}" name="description{{ $i }}"
                        wire:model.defer="permissions.{{ $i }}.description" placeholder="Keterangan" />
                    <x-form-error key="permissions.{{ $i }}.description" />
                </div>
                <div class="mb-3">
                    <x-form-label id="permission_date{{ $i }}" label='Tanggal izin({{ $i + 1 }})' />
                    <x-form-input type="date" id="permission_date{{ $i }}" name="permission_date{{ $i }}" class="form-control"
                        wire:model.defer="permissions.{{ $i }}.permission_date" />
                    <small class="text-muted d-block mt-2">Perhatikan format tanggal d (Hari), m (Bulan) dan y
                        (Tahun)</small>
                    <x-form-error key="permissions.{{ $i }}.permission_date" />
                </div>
                <div class="mb-3">
                    <x-form-label id="is_accepted{{ $i }}" label='Role {{ $i + 1 }}' />
                    <select class="form-select" aria-label="Default select example" name="is_accepted"
                        wire:model.defer="permissions.{{ $i }}.is_accepted">
                        <option selected >-- Pilih Role --</option>
                        @foreach ($accept as $accept)
                        <option value="{{ $accept->id }}">{{ ucfirst($accept->name) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="permissions.{{ $i }}.is_accepted" />
                </div>
            </div>
            @if ($i > 0)
            <button class="btn btn-sm btn-danger mt-2" wire:click="removePermissionInput({{ $i }})"
                wire:target="removePermissionInput({{ $i }})" type="button" wire:loading.attr="disabled">Hapus</button>
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