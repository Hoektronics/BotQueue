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

        <div class="flex flex-wrap -mx-2">
            @foreach($clusters as $cluster)
                <div class="w-full pt-4 px-2 md:w-1/3">
                    <div class="border rounded-t shadow-md">
                        <div class="flex p-2 text-lg bg-gray-300">
                            <a href="{{ route('clusters.show', [$cluster]) }}"
                               class="text-lg flex-grow mr-2">
                                {{ $cluster->name }}
                            </a>
                        </div>

                        <div class="p-2 flex justify-around">
                            <div>
                                <div class="text-4xl text-center">
                                    {{ $cluster->bots_count }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Bots
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection