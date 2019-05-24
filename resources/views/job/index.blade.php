@extends('layouts.app')

@inject('job_status', 'App\Services\JobStatusService')

@section('content')
    <div class="mx-4">
        <div class="flex justify-between">
            <span class="text-3xl">Jobs</span>
            <a role="button"
               href="{{ route('files.create') }}"
               class="btn-lg btn-blue btn-interactive">
                Create a Job
            </a>
        </div>

        <div class="mt-4 flex flex-col w-full md:w-1/2 md:mx-auto">
            <div class="flex w-full">
                <a class="w-1/2 text-center text-xl p-1 border-l border-t border-b rounded-tl">Name</a>
                <a class="w-1/2 text-center text-xl p-1 border-r border-t border-b rounded-tr">Status</a>
            </div>
            @foreach($jobs as $job)
                <div class="flex w-full p-2 border-l border-r border-b">
                    <div class="w-1/2 text-center hover:text-gray-700 overflow-x-hidden">
                        <a href="{{ route('jobs.show', [$job]) }}">{{ $job->name }}</a>
                    </div>
                    <div class="w-1/2 text-center">
                        {!! $job_status->label($job->status) !!}
                    </div>
                </div>
            @endforeach

            <div class="mt-4 flex justify-center">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
@endsection