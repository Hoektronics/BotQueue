@extends('layouts.app')

@section('content')
    <div class="flex">
        <div class="dialog-box">
            <div class="text-center text-xl bg-gray-200">Delete Bot</div>
            <div class="p-4">
                <form role="form" method="POST" action="{{ route('bots.destroy', [$bot]) }}">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}

                    <span>
                        Deleting this bot will remove it from from BotQueue's system entirely.
                        It will also delete all jobs that this bot worked on.
                    </span>

                    <div class="flex mt-4 justify-end">
                        <a href="{{ route('bots.show', [$bot]) }}" class="btn-plain btn-lg btn-interactive mr-4">
                            Cancel
                        </a>

                        <button type="submit" class="btn-red btn-lg btn-interactive">
                            Delete Forever
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection