@extends('laravel-enso/core::layouts.app')

@section('pageTitle', __("Data imports"))

@section('includesCss')

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
        @include('laravel-enso/core::partials.breadcrumbs')
    </section>
    <section class="content" v-cloak>
        <transition-group name="fadeUp" mode="out-in" tag="div">
            <div class="row" v-if="!summary" key="controls">
                <div class="col-md-12">
                    <div class="box box-primary" v-cloak>
                        <div class="box-body">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-lg-3 col-md-4">
                                        <div class="form-group">
                                            <label>{{ __('Import type') }}</label>
                                            <vue-select :options="importTypeList"
                                                        v-model="importType"
                                                        @input="getTemplate"
                                                        ref="importTypeSelect">
                                            </vue-select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2 margin-bottom-xs text-center" v-if="importTypeSelected">
                                        <label style="margin-bottom: 15px">{{ __('Template') }}</label>
                                        <br>
                                        <file-uploader v-if="!template.id"
                                            url="/import/uploadTemplate"
                                            @uploaded="template = $event"
                                            :params="{ 'type': importType }">
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
                                    <div class="col-lg-5 col-md-4" v-if="importTypeSelected">
                                        <div class="form-group">
                                            <label>{{ __('Comments') }}</label>
                                            <div class="input-group">
                                                <input type="text"
                                                    :readonly="!importTypeSelected"
                                                    v-model="comment"
                                                    class="form-control">
                                                <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-2" v-if="importTypeSelected">
                                        <file-uploader
                                            :params="{ 'comment': comment, 'type': importType }"
                                            @uploaded="summary = $event.summary"
                                            url="/import/run">
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
            </div>
            <div class="row" v-if="!summary" key="table">
                <div class="col-md-12">
                    <data-table source="/import" ref="dataImports"
                                id="dataImportsTableId">
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
                                        <span v-if="summary.structureIssues.length">
                                            {{ __("Structure errors") }}
                                        </span>
                                        <span v-if="summary.sheetIssues.length">
                                            {{ __("Content errors") }}
                                        </span>
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
                                <li v-for="structureIssue in summary.structureIssues">
                                    <span v-if="structureIssue.name">
                                        <b>{{ __('Sheet') }}</b> <span class="label label-info">@{{ structureIssue.name }}</span>
                                    </span>
                                    <span v-else>
                                        <b>{{ __('Details') }}:</b>
                                    </span>
                                    <ul class="errors">
                                        <li v-for="category in structureIssue.categories">
                                            <b>{{ __('Error') }}:</b> <span class="label label-warning">@{{ category.name }}</span>
                                            <ul class="errors">
                                                <li>
                                                    <span v-for="issue in category.issues"
                                                        class="label label-danger margin-right-xs">
                                                        @{{ issue.value }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li v-for="sheetIssue in summary.sheetIssues">
                                    <b>{{ __("Sheet") }}:</b> <span class="label label-info">@{{ sheetIssue.name }}</span>
                                    <ul>
                                        <li v-for="category in sheetIssue.categories">
                                            <b>{{ __('Error') }}:</b> <span class="label label-warning">@{{ category.name }}</span>
                                            <ul>
                                                <li v-for="issue in category.issues">
                                                    <b>{{ __("Column") }}:</b> <span class="label label-danger">@{{ issue.column }}</span>
                                                    <b>{{ __("Line") }}:</b> <span class="label label-danger">@{{ issue.rowNumber }}</span>
                                                    <b>{{ __("Value") }}:</b> <span class="label label-danger">@{{ issue.value }}</span>
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
                    importTypeList: {!! $importTypes  !!}
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
                    });
                },
                deleteTemplate(id) {
                    axios.delete('/import/deleteTemplate/' + id).then(reponse => {
                        this.template = {};
                        this.showModal = false;
                    }).catch(error => {
                        this.showModal = false;
                        if (error.response.data.level) {
                            toastr[error.response.data.level](error.response.data.message);
                        }
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
                    axios.get('/import/getSummary/' + dataImportId).then((response) => {
                        this.summary = JSON.parse(response.data.summary);
                    });
                }
            },
            created() {
                //events are needed for the custom buttons of DataTables
                eventHub.$on('showSummary', this.getSummary);
            }
        });

    </script>
@endpush