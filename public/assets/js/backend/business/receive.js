define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/receive/index' + location.search,
                    add_url: 'business/receive/add',
                    edit_url: 'business/receive/edit',
                    del_url: 'business/receive/del',
                    multi_url: 'business/receive/multi',
                    import_url: 'business/receive/import',
                    table: 'business_receive',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'applytime',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), visible: false},
                        {field: 'applyid', title: __('Applyid')},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {
                                "apply": __('Apply'),
                                "allot": __('Allot'),
                                "recovery": __('Recovery'),
                                "reject": __('Reject')
                            },
                            formatter: Table.api.formatter.status
                        },
                        {field: 'busid', title: __('Busid')},
                        {field: 'business.nickname', title: __('Business.nickname'), operate: 'LIKE'},
                        {
                            field: 'applytime',
                            title: __('Applytime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
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
