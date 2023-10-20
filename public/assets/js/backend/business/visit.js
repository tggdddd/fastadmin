define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/visit/index' + location.search,
                    add_url: 'business/visit/add',
                    edit_url: 'business/visit/edit',
                    del_url: 'business/visit/del',
                    multi_url: 'business/visit/multi',
                    import_url: 'business/visit/import',
                    table: 'business_visit',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'busid', title: __('Busid')}, {
                        field: 'business.nickname',
                        title: __('Business.nickname'),
                        operate: 'LIKE'
                    },

                        {
                            field: 'content',
                            title: __('Content'),
                            operate: 'LIKE',
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'adminid', title: __('Adminid')}, {
                        field: 'admin.nickname',
                        title: __('Admin.nickname'),
                        operate: 'LIKE'
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
            const busid = Fast.api.query("busid")
            if (busid) {
                $("#c-busid").val(busid).closest(".form-group").hide()
            }
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
