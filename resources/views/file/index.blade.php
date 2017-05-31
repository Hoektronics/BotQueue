@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/file/create">Upload a file</a>
        </div>
        <h1>Files</h1>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-md-6">Name</th>
                <th class="col-md-6">Size</th>
            </tr>
        </thead>
        @foreach($files as $file)
            <tr>
                <th>
                    <a href="{{ route('file.show', [$file]) }}">{{ $file->name }}</a>
                </th>
                <th>
                    {{ $file->size }}
                </th>
            </tr>
        @endforeach
    </table>
@endsection