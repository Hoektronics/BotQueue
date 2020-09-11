@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <span class="text-3xl">Host Requests</span>
    </div>

    <div class="flex flex-row mt-4 mx-2">
        @if(count($host_requests) == 0)
            <a class="text-xl mx-auto p-2 border border-red-300 rounded bg-red-300 text-center">
                No hosts available. See help to add one (TODO)
            </a>
        @else
            @foreach($host_requests as $host_request)
                <div class="m-2 border rounded">
                    <div class="bg-gray-300 p-2">
                        <a href="{{ route('hosts.requests.show', [$host_request]) }}"
                           class="text-xl">
                            Request {{ $host_request->id }}
                        </a>
                    </div>
                    <div class="p-2">
                        <ul>
                            <li>Hostname: {{ e($host_request->hostname ?? "Unknown") }}</li>
                            <li>Local IP: {{ e($host_request->local_ip ?? "Unknown") }}</li>
                            <li>Remote IP: {{ e($host_request->remote_ip ?? "Unknown") }}</li>
                        </ul>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection