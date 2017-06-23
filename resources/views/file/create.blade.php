@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Upload a file</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="col-md-12 form-group{{ $errors->has('file') ? ' has-error' : '' }}">
                                <div class="input-group">
                                    <label class="input-group-btn">
                                        <span class="btn btn-primary">
                                            Browse&hellip; <input name="file" type="file" style="display: none;">
                                        </span>
                                    </label>
                                    <input name="file_name" type="text" class="form-control" readonly>
                                </div>

                                @if ($errors->has('file'))
                                    <span class="help-block">
                                            <strong>{{ $errors->first('file') }}</strong>
                                        </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Upload
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // We can attach the `fileselect` event to all file inputs on the page
        $(document).on('change', ':file', function () {
            let input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        // We can watch for our custom `fileselect` event like this
        $(document).ready(function () {
            $(':file').on('fileselect', function (event, numFiles, label) {
                console.log(event);
                let input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;

                if (input.length) {
                    input.val(log);
                } else {
                    if (log) alert(log);
                }

            });
        });
    </script>
@endsection