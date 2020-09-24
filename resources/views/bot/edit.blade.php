@extends('layouts.app')

@section('content')
    @livewire('bot-edit-form', ['botId' => $bot->id])
@endsection