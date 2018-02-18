@extends('layouts.app')

@inject('job_status', 'App\Services\JobStatusService')

@section('content')
    <div class="btn-toolbar float-right">
        <a role="button" class="btn btn-primary btn-lg" href="{{ route('files.create') }}">Create a Job</a>
    </div>

    <h1>Jobs</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-md-auto">Name</th>
                <th class="col-md-auto">Status</th>
            </tr>
        </thead>
        @foreach($jobs as $job)
            <tr>
                <th>
                    <a href="{{ route('jobs.show', [$job]) }}">{{ $job->name }}</a>
                </th>
                <th>
                    {!! $job_status->label($job->status) !!}
                </th>
            </tr>
        @endforeach
    </table>
@endsection