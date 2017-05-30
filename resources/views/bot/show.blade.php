@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>{{ $bot->name }}</h1>
    </div>

    <div class="row">
        <div class="col-md-9">
            Main content
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Info
                </div>
                <div class="panel-body">
                    Creator: {{ $bot->creator->username }}
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    Clusters
                </div>
                <div class="panel-body">
                    @foreach($bot->clusters as $cluster)
                        <a class="label label-info"
                           href="{{ route('cluster.show', [$cluster]) }}">
                            {{ $cluster->name }}
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection