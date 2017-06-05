@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>Create Job</h1>
    </div>
    <form class="form-horizontal" role="form" method="POST" action="{{ route('job.file.store') }}">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Info
                    </div>
                    <div class="panel-body">
                        Creator: {{ $file->uploader->username }}
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="panel-group">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" name="add_to_job" checked>
                            </span>
                                <input type="text" value="{{ pathinfo($file->name, PATHINFO_FILENAME) }}"
                                       class="form-control">
                            </div>
                        </div>

                        <div class="panel-body">
                            Cluster/Bot dropdown
                        </div>
                    </div>

                    <div class="panel panel-default panel-indent">
                        <div class="panel-heading">
                            Print!
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection