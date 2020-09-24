@extends('layouts.app')

@inject('job_status', 'App\Services\JobStatusService')

@section('content')
    <div class="flex mx-4">
        <div class="flex flex-col flex-grow mr-4">
            <span class="text-3xl">{{ $job->name }}</span>

            <div class="flex">
                @if($job->status == \App\Enums\JobStatusEnum::QUALITY_CHECK)
                    <form method="post" action="/jobs/{{$job->id}}/pass">
                        @csrf
                        <input type="submit" class="btn-green btn-lg btn-interactive m-2" value="Pass">
                    </form>

                    <form method="post" action="/jobs/{{$job->id}}/fail">
                        @csrf
                        <input type="submit" class="btn-red btn-lg btn-interactive m-2" value="Fail">
                    </form>
                @endif
            </div>
        </div>

        <div class="w-1/8">
            <div class="border rounded mb-4">
                <div class="text-center bg-gray-200">Info</div>
                <div class="p-4">
                    Creator: {{ $job->creator->username }}<br>
                    Status: {!! $job_status->label($job->status) !!}<br>
                    Worker:
                    @if(is_a($job->worker, App\Models\Bot::class))
                        <a
                                href="{{ route('bots.show', [$job->worker]) }}">
                            {{ $job->worker->name }}
                        </a>
                    @else
                        <a class="hover:text-gray-700"
                           href="{{ route('clusters.show', [$job->worker]) }}">
                            {{ $job->worker->name }}
                        </a>
                    @endif
                    <br>

                    @if($job->status == \App\Enums\JobStatusEnum::IN_PROGRESS)
                        <div>
                            Progress: {{ number_format($job->progress, 2) }}%
                        </div>
                    @endif
                </div>
            </div>

            <div class="border rounded mb-4">
                <div class="text-center bg-gray-200">Actions</div>
                <div class="p-4 flex justify-center">
                    <a href="{{ Storage::url($job->file->path) }}"
                       class="my-auto hover:text-white hover:bg-blue-300 m-1 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             height="2rem"
                             class="fill-current">
                            <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"></path>
                        </svg>
                    </a>

                    <a href="{{ route('jobs.create.file', $job->file) }}"
                       class="my-auto hover:text-white hover:bg-blue-300 m-1 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             height="2rem"
                             class="fill-current">
                            <path d="M5 4a2 2 0 0 0-2 2v6H0l4 4 4-4H5V6h7l2-2H5zm10 4h-3l4-4 4 4h-3v6a2 2 0 0 1-2 2H6l2-2h7V8z"></path>
                        </svg>
                    </a>

                    @if($job->status == \App\Enums\JobStatusEnum::QUEUED)
                        <a href="#"
                           onclick="event.preventDefault(); document.getElementById('delete-job-form').submit();"
                           class="my-auto hover:text-white hover:bg-red-500 m-1 p-1 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                 class="h-6 fill-current">
                                <path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"></path>
                            </svg>
                        </a>

                        <form id="delete-job-form" action="{{ route('jobs.destroy', [$job]) }}" method="POST"
                              style="display: none;">
                            @csrf
                            {{ method_field('DELETE') }}
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection