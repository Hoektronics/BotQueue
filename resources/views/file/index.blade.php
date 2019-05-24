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
            <a class="w-1/3 text-center text-xl p-1 border-t border-b border-r">Actions</a>
        </div>
        @foreach($files as $file)
            <div class="flex w-full p-2 border-l border-r border-b">
                <div class="w-1/3 text-center hover:text-gray-700 overflow-x-hidden">
                    <a href="{{ route('files.show', [$file]) }}">{{ $file->name }}</a>
                </div>
                <div class="w-1/3 text-center">
                    {{ $file->size }}
                </div>
                <div class="flex w-1/3 text-center justify-center">
                    <a href="{{ Storage::url($file->path) }}" class="my-auto hover:text-white hover:bg-blue-300 m-1 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             height="2rem"
                             class="fill-current">
                            <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"></path>
                        </svg>
                    </a>

                    <a href="{{ route('jobs.create.file', $file) }}" class="my-auto hover:text-white hover:bg-blue-300 m-1 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                             height="2rem"
                             class="fill-current">
                            <path d="M5 4a2 2 0 0 0-2 2v6H0l4 4 4-4H5V6h7l2-2H5zm10 4h-3l4-4 4 4h-3v6a2 2 0 0 1-2 2H6l2-2h7V8z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

@endsection