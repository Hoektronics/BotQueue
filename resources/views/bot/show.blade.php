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
                        <div class="row">
                            <h4>
                                <a class="label label-default"
                                   href="{{ route('clusters.show', [$cluster]) }}">
                                    {{ $cluster->name }}
                                </a>
                            </h4>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection