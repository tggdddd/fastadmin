define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/product/index' + location.search,
                    add_url: 'product/product/add',
                    edit_url: 'product/product/edit',
                    del_url: 'product/product/del',
                    multi_url: 'product/product/multi',
                    import_url: 'product/product/import',
                    table: 'product',
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
                        {field: 'id', title: __('Id')},
                        {
                            field: 'name',
                            title: __('Name'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'thumbs', title: __('Thumbs'), formatter: Table.api.formatter.images},
                        {
                            field: 'type_text',
                            title: __('Type.name'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'flag',
                            title: __('Flag'),
                            searchList: {"1": __('NEW PRODUCT'), "2": __('HOT PRODUCT'), "3": __('RECOMMEND')},
                            formatter: Table.api.formatter.flag
                        },
                        {field: 'stock', title: __('Stock')},
                        {
                            field: 'unit_text',
                            title: __('Unit.name'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'price', title: __('Price'), operate: 'BETWEEN'},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"0": __('REMOVED FROM SHELVES'), "1": __('ON THE SHELVES')},
                            formatter: Table.api.formatter.toggle
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
                url: 'product/product/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), align: 'left'},
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
                                    url: 'product/product/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'product/product/destroy',
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
