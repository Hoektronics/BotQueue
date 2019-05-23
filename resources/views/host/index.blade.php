@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <span class="text-3xl">Hosts</span>
    </div>

    <div class="flex flex-col mt-4">
        @if(count($hosts) == 0)
            <a class="text-xl mx-auto p-2 border border-red-300 rounded bg-red-300 text-center">
                No hosts available. See help to add one (TODO)
            </a>
        @else
            @foreach($hosts as $host)
                <a {{-- href="{{ route('hosts.show', [$host]) }}" --}}
                   class="text-xl m-2 p-2 border">
                    {{ $host->name }}
                </a>
            @endforeach
        @endif
    </div>
@endsection