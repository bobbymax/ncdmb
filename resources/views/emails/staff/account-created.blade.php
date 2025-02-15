<x-mail::message>
# Account Created!!

Dear {{ $user->firstname }} {{ $user->surname }},

An account on the Enterprise Staff Service Portal has been created in your name. Your password is {{ strtolower($user->firstname) }}.{{ strtolower($user->surname) }}. Please ensure you reset your password after you login for the first time.

<x-mail::button :url="'http://localhost:3000/auth/login'">
Visit Profile
</x-mail::button>

Best Regards,<br>
{{ config('app.name') }}
</x-mail::message>
