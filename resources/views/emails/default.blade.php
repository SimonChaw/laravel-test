@component('mail::message')

{{ $message }}

@component('mail::button', ['url' => 'mailto:chawblah@gmail.com'])
Contact Simon
@endcomponent

Regards,<br>
Simon Chawla
@endcomponent
