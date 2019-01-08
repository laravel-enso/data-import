@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('Your import is done') }}: {{ $filename }}.

@component('mail::table')
| Entries                 |    Count          |
| ----------------------- |:-----------------:|
| {{ __('Successfull') }} | {{ $successful }} |
| {{ __('Failed') }}      | {{ $failed }}     |
| {{ __('Total') }}       | {{ $entries }}    |
@endcomponent

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent
