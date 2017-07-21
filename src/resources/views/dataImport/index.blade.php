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

    <section class="content-header">
        @include('laravel-enso/menumanager::breadcrumbs')
    </section>
    <section class="content" v-cloak>
        <transition-group name="fadeUp" mode="out-in" tag="div">
            <div class="row" v-if="!summary" key="controls">
                <div class="col-xs-12 col-md-6 col-md-offset-3">
                    <div class="box box-primary" v-cloak>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label>{{ __('Import type') }}</label>
                                        <vue-select :options="importTypeList"
                                                    v-model="importType"
                                                    @input="getTemplate"
                                                    ref="importTypeSelect">
                                        </vue-select>
                                    </div>
                                </div>
                                <div class="col-xs-4 margin-bottom-xs text-center" v-if="importTypeSelected">
                                    <label style="margin-bottom: 15px">{{ __('Template') }}</label>
                                    <br>
                                    <file-uploader v-if="!template.id"
                                        :url="'/import/uploadTemplate/' + importType"
                                        @upload-successful="template = $event" >
                                        <span slot="upload-button">
                                            <i class="btn btn-xs btn-primary fa fa-upload margin-right-xs"
                                                v-tooltip="'{{ __('Upload a template') }}'"></i>
                                        </span>
                                    </file-uploader>
                                    <a class="btn btn-xs btn-info margin-right-xs"
                                        :href="'/import/downloadTemplate/' + template.id"
                                        v-if="template.id"
                                        v-tooltip="templateTooltip">
                                        <i class="fa fa-table"></i>
                                    </a>
                                    <i class="btn btn-xs btn-danger fa fa-trash margin-right-xs"
                                        v-if="template.id"
                                        @click="showModal = true">
                                    </i>
                                </div>
                                <div class="col-xs-4" v-if="importTypeSelected">
                                    <file-uploader
                                        :params="{ 'comment': comment, 'type': importType }"
                                        @upload-successful="summary = $event"
                                        :url="'/import/run/' + importType">
                                        <span slot="upload-button">
                                            <button class="btn btn-primary btn-block upload-button">
                                                {{ __('Upload') }}
                                            </button>
                                        </span>
                                    </file-uploader>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" v-if="!summary" key="table">
                <div class="col-md-12">
                    <data-table source="/import"
                        id="imports-table"
                        @get-summary="getSummary($event)">
                        <span slot="data-table-title">{{ __('Past Imports') }}</span>
                        @include('laravel-enso/core::partials.modal')
                    </data-table>
                </div>
            </div>
            <div class="row" v-if="summary" key="report">
                <div class="col-md-12">
                    <div class="box box-body"
                        :class="{'box-success' : !summary.hasErrors, 'box-danger' : summary.hasErrors}">
                        <button type="button" class="close float-right margin-right-md"
                            data-dismiss="alert" aria-hidden="true"
                            @click="resetInputs()">×
                        </button>
                        <div class="col-xs-12">
                            <center>
                                <h5>
                                    <span class="label label-danger" v-if="summary.hasErrors">
                                        {{ __("Errors") }}
                                    </span>
                                    <span class="label label-success" v-else>
                                        {{ __("Success") }}
                                    </span>
                                </h5>
                            </center>
                        </div>
                        <div class="col-xs-12">
                            <p><b>{{ __("File") }}:</b> @{{ summary.fileName }} </p>
                            <p><b>{{ __('Imported entries') }}: </b> <span class="label label-success">@{{ summary.successfulEntries }}</span></p>
                            <p><b>{{ __('Errors List') }}: </b></p>
                            <ul class="errors" v-if="summary.hasErrors">
                                <li v-for="issue in summary.issues">
                                    <span v-if="issue.name">
                                        <b>{{ __('Sheet') }}</b> <span class="label label-info">@{{ issue.name }}</span>
                                    </span>
                                    <span v-else>
                                        <b>{{ __('Details') }}:</b>
                                    </span>
                                    <ul class="errors">
                                        <li v-for="category in issue.categories">
                                            <b>{{ __('Error') }}:</b> <span class="label label-warning">@{{ category.name }}</span>
                                            <ul class="errors">
                                                <li v-for="issue in category.issues">
                                                    <span v-if="issue.column">
                                                        <b>{{ __("Column") }}:</b> <span class="label label-danger">@{{ issue.column }}</span>
                                                    </span>
                                                    <span v-if="issue.rowNumber">
                                                        <b>{{ __("Line") }}:</b> <span class="label label-danger">@{{ issue.rowNumber }}</span>
                                                    </span>
                                                    <span>
                                                        <b>{{ __("Value") }}:</b> <span class="label label-danger">@{{ issue.value }}</span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </transition-group>
        <modal :show="showModal"
            @cancel-action="showModal = false"
            @commit-action="deleteTemplate(template.id)">
            @include('laravel-enso/core::partials.modal')
        </modal>
    </section>

@endsection

@push('scripts')

    <script type="text/javascript">

        let vm = new Vue({
            el: "#app",
            data() {
                return {
                    importType: null,
                    comment: '',
                    fileSizeLimit: 8388608,
                    summary: null,
                    template: {},
                    showModal: false,
                    importTypeList: JSON.parse('{!! $importTypes  !!}')
                }
            },
            computed: {
                importTypeSelected() {
                    return this.importType !== null && this.importType !== ''
                },
                templateTooltip() {
                    return "{{ __('File') }}" + ': ' + this.template.original_name
                        + '<br>' + "{{ __('Created at') }}" + ': ' + this.template.created_at;
                }
            },
            methods: {
                getTemplate() {
                    if (!this.importTypeSelected) {
                        return;
                    }

                    axios.get('/import/getTemplate/' + this.importType).then(response => {
                        this.template = response.data;
                    }).catch(error => {
                        this.reportEnsoException(error);
                    });
                },
                deleteTemplate(id) {
                    axios.delete('/import/deleteTemplate/' + id).then(response => {
                        this.template = {};
                        this.showModal = false;
                        toastr.success(response.data.message);
                    }).catch(error => {
                        this.showModal = false;
                        this.reportEnsoException(error);
                    });
                },
                resetInputs() {
                    this.summary = null;
                    this.comment = null;
                    this.$nextTick(function() {
                        this.$refs.importTypeSelect.removeSelection();
                    });
                },
                getSummary(dataImportId) {
                    axios.get('/import/getSummary/' + dataImportId).then(response => {
                        this.summary = response.data;
                    }).catch(error => {
                        this.reportEnsoException(error);
                    });
                }
            }
        });

    </script>
@endpush