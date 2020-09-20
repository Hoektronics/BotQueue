@extends('layouts.app')

@section('content')
    <div class="w-full rounded-lg border lg:w-1/3 lg:mx-auto">
        <div class="text-center text-xl bg-gray-200">Upload a file</div>
        <div class="p-4">
            <file-upload token="{{ csrf_token() }}"></file-upload>
        </div>
    </div>
@endsection