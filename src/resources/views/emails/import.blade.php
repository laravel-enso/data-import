@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('Your :type import is done', ['type' => $type]) }}: {{ $filename }}.

@component('mail::table')
| Entries                 |    Count          |
|:-----------------------:|:-----------------:|
| {{ __('Successfull') }} | {{ $successful }} |
| {{ __('Failed') }}      | {{ $failed }}     |
| {{ __('Total') }}       | {{ $entries }}    |
@endcomponent

{{ __('Thank you') }},<br>
{{ __(config('app.name')) }}
@endcomponent
