<template>
    <div>
        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState == 0">
            <span>Loading data, please wait...</span>
        </div>

        <div v-show="loadingState != 0">
            <div class="row">
                <div class="col-md-7 ">
                    <add-sub-watch></add-sub-watch>
                </div>
                <div class="col-md-5">
                    <filter-bar
                            :globalEvt="false"
                            v-on:filter-set="onFilterSet"
                            v-on:filter-reset="onFilterReset"
                    ></filter-bar>
                </div>
            </div>

            <edit-sub-watch></edit-sub-watch>

            <div class="table-responsive table-xfull" v-bind:class="{'loading' : loadingState==2}">
                <vuetable ref="vuetable"
                          api-url="/home/subs/get"
                          :fields="fields"
                          pagination-path=""
                          :css="css.table"
                          :sort-order="sortOrder"
                          :multi-sort="true"
                          :per-page="50"
                          :append-params="moreParams"
                          @vuetable:cell-clicked="onCellClicked"
                          @vuetable:pagination-data="onPaginationData"
                          @vuetable:loaded="onLoaded"
                          @vuetable:loading="onLoading"
                >
                    <template slot="actions" scope="props">
                        <div class="custom-actions">
                            <button class="btn btn-sm btn-primary"
                                    @click="editItemAction('edit-item', props.rowData, props.rowIndex)"><i class="glyphicon glyphicon-pencil"></i></button>
                            <button class="btn btn-sm btn-danger"
                                    @click="deleteItemAction('delete-item', props.rowData, props.rowIndex)"><i class="glyphicon glyphicon-trash"></i></button>
                        </div>
                    </template>
                    <template slot="autoadd" scope="props">
                        <span class="label label-success" v-if="props.rowData.auto_fill_watches">On</span>
                        <span class="label label-default" v-else="">Off</span>
                    </template>
                </vuetable>
            </div>

            <div class="vuetable-pagination">
                <vuetable-pagination-info ref="paginationInfo"
                                          info-class="pagination-info"
                                          :css="css.info"
                ></vuetable-pagination-info>
                <vuetable-pagination-bootstrap ref="pagination"
                                               :css="css.pagination"
                                               @vuetable-pagination:change-page="onChangePage"
                ></vuetable-pagination-bootstrap>
            </div>
        </div>
    </div>
</template>
<script>
    import accounting from 'accounting';
    import moment from 'moment';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
    import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

    import FilterBar from '../servers/FilterBar';
    import AddSubWatch from './watch/AddSubWatch.vue';
    import EditSubWatch from './watch/EditSubWatch.vue';

    Vue.use(VueEvents);
    Vue.component('filter-bar', FilterBar);
    Vue.component('add-sub-watch', AddSubWatch);
    Vue.component('edit-sub-watch', EditSubWatch);

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
            VuetablePaginationBootstrap
        },
        data () {
            return {
                loadingState: 0,
                fields: [
                    {
                        name: '__sequence',
                        title: '#',
                        titleClass: 'text-right',
                        dataClass: 'text-right'
                    },
                    {
                        name: 'scan_host',
                        sortField: 'scan_host',
                        title: 'Active domain',
                    },
                    {
                        name: '__slot:autoadd',
                        sortField: 'auto_fill_watches',
                        title: 'Watch Now',
                    },
                    {
                        name: 'created_at',
                        title: 'Created',
                        sortField: 'created_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY HH:mm'
                    },
                    {
                        name: 'updated_at',
                        title: 'Update',
                        sortField: 'updated_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY HH:mm'
                    },
                    {
                        name: 'last_scan_at',
                        title: 'Last scan',
                        sortField: 'last_scan_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY HH:mm'
                    },
                    {
                        name: '__slot:actions',
                        title: 'Actions',
                        titleClass: 'text-center',
                        dataClass: 'text-center'
                    }
                ],
                css: {
                    table: {
                        tableClass: 'table table-bordered table-striped table-hover',
                        ascendingIcon: 'glyphicon glyphicon-chevron-up',
                        descendingIcon: 'glyphicon glyphicon-chevron-down'
                    },
                    pagination: {
                        wrapperClass: 'pagination pull-right',
                        activeClass: 'active',
                        disabledClass: 'disabled',
                        pageClass: 'page',
                        linkClass: 'link',
                    },
                    info: {
                        infoClass: "pull-left"
                    },
                    icons: {
                        first: 'glyphicon glyphicon-step-backward',
                        prev: 'glyphicon glyphicon-chevron-left',
                        next: 'glyphicon glyphicon-chevron-right',
                        last: 'glyphicon glyphicon-step-forward',
                    },
                },
                sortOrder: [
                    {field: 'scan_host', sortField: 'scan_host', direction: 'asc'}
                ],
                moreParams: {}
            }
        },

        methods: {
            allcap (value) {
                return value.toUpperCase()
            },
            formatNumber (value) {
                return accounting.formatNumber(value, 2)
            },
            formatDate (value, fmt = 'DD-MM-YYYY') {
                return (value === null) ? '' : moment(value, 'YYYY-MM-DD HH:mm').format(fmt);
            },
            onPaginationData (paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
                this.$refs.paginationInfo.setPaginationData(paginationData);
            },
            onChangePage (page) {
                this.$refs.vuetable.changePage(page);
            },
            onCellClicked (data, field, event) {
                this.$refs.vuetable.toggleDetailRow(data.id);
            },
            onLoading(){
                if (this.loadingState != 0){
                    this.loadingState = 2;
                }
            },
            onLoaded(){
                this.loadingState = 1;
            },
            onDeleteServer(data){
                swal({
                    title: 'Are you sure?',
                    text: "Server will be permanently removed",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((function () {
                    this.onDeleteServerConfirmed(data);
                }).bind(this)).catch(() => {});
            },
            onDeleteServerConfirmed(data){
                const onFail = (function(){
                    this.moreParams.deleteState = -1;
                    swal('Delete error', 'Server delete failed :(', 'error');
                }).bind(this);

                const onSuccess = (function(data){
                    this.moreParams.deleteState = 1;
                    Vue.nextTick(() => this.$refs.vuetable.refresh());
                    this.$emit('onServerDeleted', data);
                    this.$events.fire('on-server-deleted', data);
                    toastr.success('Server deleted successfully.', 'Success');
                }).bind(this);

                this.moreParams.deleteState = 2;
                axios.post('/home/subs/del', data)
                    .then(response => {
                        if (!response || !response.data || response.data['status'] !== 'success'){
                            onFail();
                        } else {
                            onSuccess(response.data);
                        }
                    })
                    .catch(e => {
                        console.log( "Del server failed: " + e );
                        onFail();
                    });

            },

            renderPagination(h) {
                console.log('pagpag');
                return h(
                    'div',
                    { class: {'vuetable-pagination': true} },
                    [
                        h('vuetable-pagination-info', { ref: 'paginationInfo', props: { css: this.css.paginationInfo } }),
                        h('vuetable-pagination-bootstrap', {
                            ref: 'pagination',
                            class: { 'pull-right': true },
                            props: {
                            },
                            on: {
                                'vuetable-pagination:change-page': this.onChangePage
                            }
                        })
                    ]
                )
            },
            deleteItemAction (action, data, index) {
                this.$emit('onDeleteSubWatch', data);
                this.$events.fire('on-delete-sub-watch', data);
            },
            editItemAction (action, data, index) {
                this.$emit('onEditSubWatch', data);
                this.$events.fire('on-edit-sub-watch', data);
            },
            onFilterSet(filterText){
                this.moreParams = {
                    filter: filterText
                };
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            onFilterReset(){
                this.moreParams = {};
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            }
        },
        events: {
            'on-sub-added' (data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-sub-updated'(data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-delete-sub-watch'(data) {
                this.onDeleteServer(data);
            },
            'on-manual-refresh'(){
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            }
        }
    }
</script>
<style>
    .pagination {
        margin: 0;
        float: right;
    }
    .pagination a.page {
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 10px;
        margin-right: 2px;
    }
    .pagination a.page.active {
        color: white;
        background-color: #337ab7;
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 10px;
        margin-right: 2px;
    }
    .pagination a.btn-nav {
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 7px;
        margin-right: 2px;
    }
    .pagination a.btn-nav.disabled {
        color: lightgray;
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 7px;
        margin-right: 2px;
        cursor: not-allowed;
    }
    .pagination-info {
        float: left;
    }
    i.sort-icon {
        /*padding-left: 5px;*/
        font-size: 11px;
        padding-top: 4px;
    }
    .loading .vuetable {

    }
    .vuetable-pagination{
        min-height: 40px;
    }

    .table-xfull {
        margin-left: -10px;
        margin-right: -10px;
        width: auto;
    }

    .table-xfull > .table > thead > tr > th,
    .table-xfull > .table > tbody > tr > td
    {
        padding-left: 12px;
    }

</style>
