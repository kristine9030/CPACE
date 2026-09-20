{{--
    Generic avatar for any user (not just Auth::user()) — an uploaded photo,
    or their chosen color with initials. Fills whatever box wraps it, same
    as partials.avatar-content.
    Usage: @include('partials.user-avatar', ['user' => $someUser])
--}}
@php
    $avatarColors = [
        'maroon' => '#7B1D1D', 'crimson' => '#c0392b', 'blue' => '#2563eb',
        'teal' => '#0d9488', 'green' => '#059669', 'purple' => '#7c3aed',
        'pink' => '#db2777', 'orange' => '#d97706', 'navy' => '#1e3a5f', 'slate' => '#475569',
    ];
    $avatarBg = $avatarColors[$user->avatar_color ?? ''] ?? $avatarColors['maroon'];
@endphp
@if($user->profile_photo)
    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}">
@else
    <span class="avatar-default" style="background: {{ $avatarBg }};">
        {{ strtoupper(substr($user->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
    </span>
@endif
