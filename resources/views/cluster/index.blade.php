@extends('layouts.app')

@section('content')
    <div class="mx-4">
        <div class="flex justify-between">
            <span class="text-3xl">Clusters</span>
            <a role="button"
               href="{{ route('clusters.create') }}"
               class="btn-lg btn-blue btn-interactive">
                Create a Cluster
            </a>
        </div>

        <div class="flex flex-col w-1/2 mx-auto">
            <div class="flex w-full">
                <div class="w-1/2 text-center text-xl p-1 border-l border-t border-b rounded-tl">Cluster Name</div>
                <div class="flex w-1/2 border-r border-t border-b rounded-tr">
                    <div class="w-1/3 text-center text-xl p-1">Offline Bots</div>
                    <div class="w-1/3 text-center text-xl p-1">Idle Bots</div>
                    <div class="w-1/3 text-center text-xl p-1">Working Bots</div>
                </div>
            </div>

            @foreach($clusters as $cluster)
                <div class="flex w-full p-2 border-l border-r border-b">
                    <div class="w-1/2 text-center">
                        <a href="{{ route('clusters.show', [$cluster]) }}"
                           class="hover:text-gray-700">{{ $cluster->name }}</a>
                    </div>
                    <div class="flex w-1/2">
                        <div class="w-1/3 text-center">
                            {{ $cluster->offline_bots_count }}
                        </div>
                        <div class="w-1/3 text-center">
                            {{ $cluster->idle_bots_count }}
                        </div>
                        <div class="w-1/3 text-center">
                            {{ $cluster->working_bots_count }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection