<div>
    <form wire:submit.prevent="saveLeaves" method="post">
        @foreach ($leaves as $i => $leave)
        <div class="mb-3">
            <!-- Nama Karyawan -->
            <div>
                <label for="user_id{{ $i }}">Nama Karyawan {{ $i + 1 }}</label>
                <select class="form-select" wire:model="leaves.{{ $i }}.user_id">
                    <option value="">-- Pilih Nama --</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('leaves.' . $i . '.user_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            
            <!-- Jenis Absensi -->
            <div class="mb-3">
                <x-form-label id="attendance_id{{ $i }}" label='Jenis Absensi {{ $i + 1 }}' />
                <select class="form-select" aria-label="Default select example" wire:model.defer="leaves.{{ $i }}.attendance_id">
                    <option selected>-- Pilih Absensi --</option>
                    @foreach ($attendances as $attendance)
                    <option value="{{ $attendance->id }}">{{ ucfirst($attendance->title) }}</option>
                    @endforeach
                </select>
                <x-form-error key="leaves.{{ $i }}.attendance_id" />
            </div>

            <!-- Jenis Cuti -->
            <div>
                <label for="leave_type{{ $i }}">Jenis Cuti {{ $i + 1 }}</label>
                <select class="form-select" wire:model="leaves.{{ $i }}.leave_type">
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @foreach ($leavetypes as $leavetype)
                    <option value="{{ $leavetype->id }}">{{ $leavetype->title }}</option>
                    @endforeach
                </select>
                @error('leaves.' . $i . '.leave_type') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Kondisi Half Cuti -->
            @if ($leave['leave_type'] == '2') <!-- assuming 'half' is the ID for half cuti -->
            <div class="mt-3">
                <label>Half Cuti - Pilih Waktu</label>
                <div>
                    <input type="radio" id="pagi{{ $i }}" value="pagi" wire:model.defer="leaves.{{ $i }}.half_day_type">
                    <label for="pagi{{ $i }}">Pagi</label>
                </div>
                <div>
                    <input type="radio" id="siang{{ $i }}" value="siang" wire:model.defer="leaves.{{ $i }}.half_day_type">
                    <label for="siang{{ $i }}">Siang</label>
                </div>
                @error('leaves.' . $i . '.half_day_type') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            @endif

            <!-- Kondisi Full Cuti -->
            @if ($leave['leave_type'] == '1') <!-- assuming 'full' is the ID for full cuti -->
            <div>
                <label for="number_of_leaves{{ $i }}">Jumlah Cuti {{ $i + 1 }}</label>
                <input type="number" wire:model.defer="leaves.{{ $i }}.number_of_leaves" class="form-control" placeholder="Jumlah Cuti">
                @error('leaves.' . $i . '.number_of_leaves') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <x-form-label id="start_date{{ $i }}" label='Tanggal Mulai cuti({{ $i + 1 }})' />
                <x-form-input type="date" id="start_date{{ $i }}" name="start_date{{ $i }}" class="form-control"
                    wire:model.defer="leaves.{{ $i }}.start_date" />
                <small class="text-muted d-block mt-2">Perhatikan format tanggal d (Hari), m (Bulan) dan y (Tahun)</small>
                <x-form-error key="leaves.{{ $i }}.start_date" />
            </div>
            @endif
            
            <!-- Sisa Cuti -->
            <div>
                <label for="remaining_days_off{{ $i }}">Sisa Cuti {{ $i + 1 }}</label>
                <input type="number" wire:model.defer="leaves.{{ $i }}.remaining_days_off" class="form-control" placeholder="Sisa Cuti">
                @error('leaves.' . $i . '.remaining_days_off') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Alasan Cuti -->
            <div>
                <label for="reason{{ $i }}">Alasan Cuti {{ $i + 1 }}</label>
                <input type="text" wire:model.defer="leaves.{{ $i }}.reason" class="form-control" placeholder="Alasan Cuti">
                @error('leaves.' . $i . '.reason') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Status Cuti -->
            <div>
                <label for="is_accepted{{ $i }}">Status Cuti {{ $i + 1 }}</label>
                <select class="form-select" wire:model.defer="leaves.{{ $i }}.is_accepted">
                    <option value="">-- Pilih Status --</option>
                    @foreach ($accept as $acceptOption)
                    <option value="{{ $acceptOption->id }}">{{ $acceptOption->name }}</option>
                    @endforeach
                </select>
                @error('leaves.' . $i . '.is_accepted') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            @if ($i > 0)
            <button type="button" class="btn btn-danger mt-2" wire:click="removeLeaveInput({{ $i }})">Hapus</button>
            @endif
        </div>
        <hr>
        @endforeach

        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" wire:click="addLeaveInput">Tambah Input</button>
    </form>
</div>
