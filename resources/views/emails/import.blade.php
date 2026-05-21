@component('mail::message')
@component('mail::title')
{{ __(':type import done', ['type' => $import->type()]) }}
@endcomponent

{{ __('Hi :name', ['name' => $name]) }},

{{ __('The :name import is done', ['name' => $import->type()]) }}:
{{ $import->file->original_name }}.

@component('mail::file')
{{ $import->file->original_name }}
@endcomponent

@component('mail::table')
|        Entries          |            Count          |
|:-----------------------:|:-------------------------:|
| {{ __('Successful') }}  | {{ $import->successful }} |
| {{ __('Failed') }}      | {{ $import->failed }}     |
| {{ __('Total') }}       | {{ $import->entries }}    |
@endcomponent

@if($import->rejected)
@component('mail::button', ['url' => $import->rejected->file->temporaryLink()])
@lang('Download failed report')
@endcomponent
@endif

@component('mail::signature')
@endcomponent
@endcomponent
