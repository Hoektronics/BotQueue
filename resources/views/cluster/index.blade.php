@extends('layouts.app')

@section('content')
    <div class="btn-toolbar float-right">
        <a role="button" class="btn btn-primary btn-lg" href="{{ route('clusters.create') }}">Create a Cluster</a>
    </div>
    <h1>Clusters</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-md-auto">Cluster Name</th>
                <th class="col-md-auto">Offline Bots</th>
                <th class="col-md-auto">Idle Bots</th>
                <th class="col-md-auto">Working Bots</th>
            </tr>
        </thead>
        @foreach($clusters as $cluster)
            <tr>
                <th>
                    <a href="{{ route('clusters.show', [$cluster]) }}">{{ $cluster->name }}</a>
                </th>
                <th>
                    {{ $cluster->offline_bots_count }}
                </th>
                <th>
                    {{ $cluster->idle_bots_count }}
                </th>
                <th>
                    {{ $cluster->working_bots_count }}
                </th>
            </tr>
        @endforeach
    </table>
@endsection