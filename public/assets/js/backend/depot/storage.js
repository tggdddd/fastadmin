define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/storage/index' + location.search,
                    add_url: 'depot/storage/add',
                    edit_url: 'depot/storage/edit',
                    del_url: 'depot/storage/del',
                    multi_url: 'depot/storage/multi',
                    import_url: 'depot/storage/import',
                    table: 'depot_storage',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible: false, operate: false},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {
                            field: 'type',
                            title: __('Type'),
                            searchList: {"1": __('DIRECT SALES INTO WAREHOUSE'), "2": __('RETURN-TO-WAREHOUSE')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "0": __('PENDING'),
                                "1": __('APPROVAL FAILED'),
                                "2": __('TO BE STOCKED'),
                                "3": __('WAREHOUSING COMPLETED')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {field: 'admin.nickname', title: __('Adminid'), operate: false},
                        {
                            field: 'adminid', title: __('Adminid'), visible: false, extend: `
                                data-source="auth.admin/selectpage"
                                data-field="nickname"
                                data-search-field="nickname,mobile,email"
                                data-primary-key="id"
                                data-page-size="7"`,
                            addClass: "selectpage",
                            operate: '='
                        },
                        {field: 'reviewer.nickname', title: __('Reviewerid'), operate: false},
                        {
                            field: 'reviewerid', title: __('Reviewerid'), visible: false, extend: `
                                data-source="auth.admin/selectpage"
                                data-field="nickname"
                                data-search-field="nickname,mobile,email"
                                data-primary-key="id"
                                data-page-size="7"`,
                            addClass: "selectpage",
                            operate: '='
                        },
                        {field: 'supplier.name', title: __('Supplierid'), operate: false},
                        {
                            field: 'supplierid', title: __('Supplierid'), visible: false, extend: `
                                data-source="depot.supplier/selectpage"
                                data-field="name"
                                data-search-field="name,mobile"
                                data-primary-key="id"
                                data-page-size="7"`,
                            addClass: "selectpage",
                            operate: '='
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'depot/storage/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'depot/storage/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'depot/storage/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
