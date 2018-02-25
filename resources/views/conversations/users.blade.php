<div class="col-md-3">
    <div class="list-group">
        @foreach($users as $user)
            <a href="{{ route('conversations.show', $user->id) }}"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                {{ $user->name }}
                @if(isset($unread[$user->id]))
                    <span class="badge badge-pill badge-primary">{{ $unread[$user->id] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>