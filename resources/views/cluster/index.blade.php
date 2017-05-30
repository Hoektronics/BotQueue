@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/cluster/create">Create a Cluster</a>
        </div>
        <h1>Clusters</h1>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Cluster Name</th>
                <th>Offline Bots</th>
                <th>Idle Bots</th>
                <th>Working Bots</th>
            </tr>
        </thead>
        @foreach($clusters as $cluster)
            <tr>
                <th>
                    {{ $cluster->name }}
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