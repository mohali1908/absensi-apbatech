<div>
    <form wire:submit.prevent="savepermissions" method="post" novalidate>
        @include('partials.alerts')
        @foreach ($permissions as $permission)

        <div class="mb-3">
            <div class="w-100">
                <div class="mb-3">
                    <x-form-label id="user_id{{ $permission['id'] }}"
                        label="User ID (ID: {{ $permission['id'] }})" />
                    <x-form-input id="user_id{{ $permission['id'] }}" name="user_id{{ $permission['id'] }}"
                        wire:model.defer="permissions.{{ $loop->index }}.user_id" />
                    <x-form-error key="permissions.{{ $loop->index }}.user_id" />
                </div>
                <div class="mb-3">
                    <x-form-label id="title{{ $permission['id'] }}" label='Title' />
                    <x-form-input id="title{{ $permission['id'] }}" name="title{{ $permission['id'] }}" 
                        wire:model.defer="permissions.{{ $loop->index }}.title" />
                    <x-form-error key="permissions.{{ $loop->index }}.title" />
                </div>
                <div class="mb-3">
                    <x-form-label id="description{{ $permission['id'] }}" label='Description' />
                    <x-form-input id="description{{ $permission['id'] }}" name="description{{ $permission['id'] }}"
                        wire:model.defer="permissions.{{ $loop->index }}.description"  />
                    <x-form-error key="permissions.{{ $loop->index }}.description" />
                </div>
                <div class="mb-3">
                    <x-form-label id="permission_date{{ $permission['id'] }}" label='Permission Date' />
                    <x-form-input id="permission_date{{ $permission['id'] }}" name="permission_date{{ $permission['id'] }}" 
                        wire:model.defer="permissions.{{ $loop->index }}.permission_date"  />
                    <x-form-error key="permissions.{{ $loop->index }}.permission_date"  />
                </div>
                <div class="mb-3">
                    <x-form-label id="is_accepted{{ $permission['id'] }}"
                        label='Status {{ $loop->iteration }}' />
                    <select class="form-select" aria-label="Default select example" name="is_accepted"
                        wire:model.defer="permissions.{{ $loop->index }}.is_accepted">
                        <option selected disabled>-- Pilih Role --</option>
                        @foreach ($accept as $accept)
                        <option value="{{ $accept->id }}">{{ ucfirst($accept->name) }}</option>
                        @endforeach
                    </select>
                    <x-form-error key="permissions.{{ $loop->index }}.is_accepted" />
                </div>   
            </div>
        </div>
        <hr>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mb-5">
            <button class="btn btn-primary">
                Simpan
            </button>
        </div>
    </form>
</div>