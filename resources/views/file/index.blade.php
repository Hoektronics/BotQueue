@extends('layouts.app')

@section('content')
    <div class="btn-toolbar float-right">
        <a role="button" class="btn btn-primary btn-lg" href="{{ route('files.create') }}">Upload a file</a>
    </div>

    <h1>Files</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-md-auto">Name</th>
                <th class="col-md-auto">Size</th>
                <th class="col-md-auto">Download Link</th>
            </tr>
        </thead>
        @foreach($files as $file)
            <tr>
                <th>
                    <a href="{{ route('files.show', [$file]) }}">{{ $file->name }}</a>
                </th>
                <th>
                    {{ $file->size }}
                </th>
                <th>
                    <a href="{{ Storage::url($file->path) }}">Download</a>
                </th>
            </tr>
        @endforeach
    </table>
@endsection