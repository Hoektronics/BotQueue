@extends('layouts.app')

@inject('job_status', 'App\Services\JobStatusService')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="{{ route('files.create') }}">Create a Job</a>
        </div>
        <h1>Jobs</h1>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-md-6">Name</th>
                <th class="col-md-3">Status</th>
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