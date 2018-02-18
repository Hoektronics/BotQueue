@extends('layouts.app')

@section('content')
    <div class="row justify-content-md-center">
        <div class="card w-75">
            <div class="card-header">Upload a file</div>
            <div class="card-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <div class="custom-file">
                            <input id="file" name="file" type="file" class="custom-file-input{{ $errors->has('file') ? ' is-invalid' : '' }}">
                            <label id="file-label" class="custom-file-label" for="file">Choose file</label>
                        </div>

                        @if ($errors->has('file'))
                            <span class="form-text">
                                <strong>{{ $errors->first('file') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group float-right">
                        <button type="submit" class="btn btn-primary">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // We can attach the `fileselect` event to all file inputs on the page
        $(document).on('change', ':file', function () {
            var input = $(this);
            var numFiles = input.get(0).files ? input.get(0).files.length : 1;
            var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        // We can watch for our custom `fileselect` event like this
        $(document).ready(function () {
            $(':file').on('fileselect', function (event, numFiles, label) {
                var input = $('#file-label');
                var log = numFiles > 1 ? numFiles + ' files selected' : label;

                input.text(log);
            });
        });
    </script>
@endsection