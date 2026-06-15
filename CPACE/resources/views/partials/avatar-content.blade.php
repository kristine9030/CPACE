@if(Auth::user()->profile_photo)
    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Profile Photo">
@else
    <span class="avatar-default">
        {{ strtoupper(substr(Auth::user()->first_name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->last_name, 0, 1)) }}
    </span>
@endif
