@component('mail::message')
{{ __('Hi :name', ['name' => $name]) }},

{{ __('Your :name import is done', ['name' => $dataImport->name()]) }}:
{{ $dataImport->file->original_name }}.

@component('mail::table')
| Entries                 |    Count          |
|:-----------------------:|:-----------------:|
| {{ __('Successful') }}  | {{ $dataImport->successful }} |
| {{ __('Failed') }}      | {{ $dataImport->failed }}     |
| {{ __('Total') }}       | {{ $dataImport->entries }}    |
@endcomponent

@if($dataImport->failed > 0)
@component('mail::button', ['url' => $dataImport->rejected->file->temporaryLink()])
    @lang('Download failed report')
@endcomponent
@endif


{{ __('Thank you') }},
<br>
{{ __(config('app.name')) }}
@endcomponent
