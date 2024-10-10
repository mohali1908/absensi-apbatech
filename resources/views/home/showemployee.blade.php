@extends('layouts.home')

@section('content')
<div class="row">
    <div class="col-md-7">
        <livewire:employee-edit-form :employees="$employees" />
    </div>
</div>
@endsection
