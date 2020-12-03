@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('Your :name import is done', ['name' => $import->name()]) }}:
{{ $import->file->original_name }}.

@component('mail::table')
| Entries                 |    Count          |
|:-----------------------:|:-----------------:|
| {{ __('Successful') }}  | {{ $import->successful }} |
| {{ __('Failed') }}      | {{ $import->failed }}     |
| {{ __('Total') }}       | {{ $import->entries }}    |
@endcomponent

@if($import->rejected !== null)
    @component('mail::button', ['url' => $import->rejected->file->temporaryLink()])
    @lang('Download failed report')
    @endcomponent
@endif


{{ __('Thank you') }},
<br>
{{ __(config('app.name')) }}
@endcomponent
