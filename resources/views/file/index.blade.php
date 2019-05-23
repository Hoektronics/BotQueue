@extends('layouts.app')

@section('content')
    <div class="mx-4">
        <div class="flex justify-between">
            <span class="text-3xl">Files</span>

            <a role="button"
               href="{{ route('files.create') }}"
               class="btn-lg btn-blue btn-interactive">
                Upload a file
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-col w-full md:w-1/2 md:mx-auto">
        <div class="flex w-full">
            <a class="w-1/3 text-center text-xl p-1 border-t border-b border-l">Name</a>
            <a class="w-1/3 text-center text-xl p-1 border-t border-b">Size</a>
            <a class="w-1/3 text-center text-xl p-1 border-t border-b border-r">Download Link</a>
        </div>
        @foreach($files as $file)
            <div class="flex w-full p-2 border-l border-r border-b">
                <div class="w-1/3 text-center hover:text-gray-700 overflow-x-hidden">
                    <a href="{{ route('files.show', [$file]) }}">{{ $file->name }}</a>
                </div>
                <div class="w-1/3 text-center">
                    {{ $file->size }}
                </div>
                <div class="w-1/3 text-center hover:text-gray-700">
                    <a href="{{ Storage::url($file->path) }}">Download</a>
                </div>
            </div>
        @endforeach
    </div>

@endsection