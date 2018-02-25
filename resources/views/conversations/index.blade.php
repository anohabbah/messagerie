@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            @include('conversations.users', ['users' => $users, 'unread' => $unread])
        </div>
    </div>

@endsection
