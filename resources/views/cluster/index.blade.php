@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/cluster/create">Create a Cluster</a>
        </div>
        <h1>Clusters</h1>
    </div>

    @foreach($clusters as $cluster)
        <div class="row">
            <div class="col-md-3 table-bordered">
                {{ $cluster->name }}
            </div>
            <div class="col-md-3 table-bordered">
                {{ $cluster->bots_count }}
            </div>
        </div>
    @endforeach
@endsection