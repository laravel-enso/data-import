@extends('laravel-enso/core::layouts.app')

@section('pageTitle', __("Data imports"))

@section('css')

    <style>

        ul.errors, ul.errors ul {
            list-style-type: none;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        ul.errors li {
            border-top: 1px solid #eee;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        span.error {
            color:red;
        }

        .upload-button {
            margin-top: 24px;
        }

    </style>

@endsection

@section('content')

    <page v-cloak>
        <transition-group name="slideUp" tag="div">
            <div class="col-xs-12"
                v-if="!summary" key="controls">
                <box theme="primary"
                    icon="fa fa-upload"
                    title="{{ __('Import type') }}"
                    open collapsible removable border
                    :overlay="loadingTemplate">
                    <div class="row">
                        <div class="col-xs-6">
                            <vue-select :options="importTypes"
                                v-model="importType"
                                @input="getTemplate"
                                key-map="string"
                                ref="importTypeSelect">
                            </vue-select>
                        </div>
                        <transition name="fade">
                            <div class="col-xs-3 text-center" v-if="importType">
                                <label class="margin-right-xs margin-top-xs">{{ __('Template') }}: </label>
                                <file-uploader v-if="!template.id"
                                    :url="'/import/uploadTemplate/' + importType"
                                    @upload-start="loadingTemplate=true"
                                    @upload-successful="template=$event;loadingTemplate=false"
                                    @upload-error="loadingTemplate=false">
                                    <span slot="upload-button">
                                        <i class="btn btn-xs btn-primary fa fa-upload margin-right-xs"
                                            v-tooltip="'{{ __('Upload a template') }}'">
                                        </i>
                                    </span>
                                </file-uploader>
                                <a class="btn btn-xs btn-info margin-right-xs fa fa-download"
                                    :href="'/import/downloadTemplate/' + template.id"
                                    v-if="template.id"
                                    v-tooltip="templateTooltip">
                                </a>
                                <i class="btn btn-xs btn-danger fa fa-trash margin-right-xs"
                                    v-if="template.id"
                                    @click="showModal = true">
                                </i>
                            </div>
                        </transition>
                        <transition name="fade">
                            <div class="col-xs-3 text-center" v-if="importType">
                                <label class="margin-right-xs margin-top-xs">{{ __('Import') }}: </label>
                                <file-uploader @upload-start="importing=true"
                                    @upload-successful="summary=$event;importing=false"
                                    @upload-error="importing=false;importType=null"
                                    :url="'/import/run/' + importType">
                                    <span slot="upload-button">
                                         <i class="btn btn-xs btn-primary fa fa-upload margin-right-xs"
                                            v-tooltip="'{{ __('Upload a file') }}'">
                                        </i>
                                    </span>
                                </file-uploader>
                            </div>
                        </transition>
                    </div>
                </box>
            </div>
            <div class="col-xs-12"
                v-if="!summary" key="table">
                <data-table source="/import"
                    id="imports-table"
                    :custom-render="customRender"
                    @get-summary="getSummary($event)">
                </data-table>
            </div>
            <div class="col-xs-12"
                v-if="summary" key="report">
                <div class="row">
                    <div class="col-xs-12 col-md-4">
                         <box-widget theme="bg-orange"
                            image="/images/excel_logo.svg"
                            name="{{ __(('Excel Import')) }}"
                            position="{{ __('Summary') }}"
                            :items="[{'label': 'File', 'value': summary.fileName, 'badge': 'bg-blue'}, {'label': 'Created At', 'value': summary.date + ', ' + summary.time, 'badge': 'bg-blue'}, {'label': 'Imported Entries', 'value': summary.successful, 'badge': 'bg-green'}, {'label': 'Errors', 'value': summary.errors, 'badge': 'bg-red'}]">
                        </box-widget>
                    </div>
                    <div class="col-xs-12 col-md-8">
                        <box :theme="summary.errors ? 'danger' : 'primary'"
                            icon="fa fa-file-book"
                            title="{{ __('Errors') }}"
                            open collapsible removable solid
                            @remove="summary=null">
                            <tabs title="{{ __('Summary') }}"
                                reverse
                                icon="fa fa-file-excel-o"
                                :tabs="summary.issues.pluck('name')">
                                <span v-for="sheet in summary.issues"
                                    :slot="sheet.name">
                                    <tabs :tabs="sheet.categories.pluck('name')">
                                        <span v-for="category in sheet.categories"
                                            :slot="category.name">
                                            <h5>{{ __('Error List') }}</h5>
                                            <ul class="errors">
                                                <li v-for="issue in category.issues">
                                                    <span v-if="issue.column">
                                                        {{ __("Column") }}: <b class="text-warning">@{{ issue.column }}</b>
                                                    </span>
                                                    <span v-if="issue.rowNumber">
                                                        {{ __("Line") }}: <b class="text-warning">@{{ issue.rowNumber }}</b>
                                                    </span>
                                                    <span v-if="issue.value">
                                                        {{ __("Value") }}: <b class="text-danger">@{{ issue.value }}</b>
                                                    </span>
                                                </li>
                                            </ul>
                                        </span>
                                    </tabs>
                                </span>
                            </tabs>
                        </box>
                    </div>
                </div>
            </div>
        </transition-group>
        <modal :show="showModal"
            @cancel-action="showModal = false"
            @commit-action="deleteTemplate(template.id)">
        </modal>
    </page>

@endsection

@push('scripts')

    <script type="text/javascript">

        const vm = new Vue({
            el: "#app",

            data() {
                return {
                    importType: null,
                    summary: null,
                    template: {},
                    showModal: false,
                    loadingTemplate: false,
                    importing: false,
                    importTypes: {!! $importTypes  !!}
                }
            },

            computed: {
                templateTooltip() {
                    return "{{ __('File') }}" + ': ' + this.template.original_name
                        + '<br>' + "{{ __('Created at') }}" + ': ' + this.template.created_at;
                }
            },

            methods: {
                getTemplate() {
                    if (!this.importType) {
                        return;
                    }

                    this.loadingTemplate = true;

                    axios.get('/import/getTemplate/' + this.importType).then(response => {
                        this.template = response.data;
                        this.loadingTemplate = false;
                    }).catch(error => {
                        this.loadingTemplate = false;
                        this.reportEnsoException(error);
                    });
                },
                deleteTemplate(id) {
                    this.loadingTemplate = true;
                    axios.delete('/import/deleteTemplate/' + id).then(response => {
                        this.template = {};
                        this.showModal = false;
                        toastr.success(response.data.message);
                        this.loadingTemplate = false;
                    }).catch(error => {
                        this.showModal = false;
                        this.loadingTemplate = false;
                        this.reportEnsoException(error);
                    });
                },
                getSummary(id) {
                    this.loading = true;

                    axios.get('/import/getSummary/' + id).then(response => {
                        this.loading = false;

                        if (response.data.errors === 0) {
                            return toastr.info('The import has no errors');
                        }

                        this.summary = response.data;
                    }).catch(error => {
                        this.loading = false;
                        this.reportEnsoException(error);
                    });
                },
                customRender(column, data, type, row, meta) {
                    switch(column) {
                        case 'successful':
                            return '<b class="text-green">' + data + '</b>';
                        case 'errors':
                            return '<b class="text-red">' + data + '</b>';
                        default:
                            toastr.warning('render for column ' + column + ' is not defined.' );
                            return data;
                    }
                }
            }
        });

    </script>

@endpush