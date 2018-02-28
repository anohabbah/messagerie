@extends('layouts.app')

@section('content')

    <div class="container">
        <div id="messagerie" data-base="{{ route('conversations', [], false) }}">
            <messagerie :user="{{ Auth::user()->id }}"></messagerie>
        </div>
    </div>

@endsection
