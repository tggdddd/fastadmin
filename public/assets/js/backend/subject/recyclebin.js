define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject.recyclebin/index' + location.search,
                    destroy_url: 'subject.recyclebin/destroy',
                    restore_url: 'subject.recyclebin/restore',
                    table: 'subject',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                searchFormVisible: true,
                search: false,
                sortName: 'createtime',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'id', title: "ID",
                            searchable: false,
                            sortable: true
                        },
                        {
                            field: 'title',
                            title: __('Title'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'category.name', title: __('Cateid')},
                        {
                            field: 'thumbs', title: __('Thumbs'), formatter: Table.api.formatter.image,
                            searchable: false
                        },
                        {
                            field: 'price', title: __('Price'), operate: 'BETWEEN',
                            searchable: false,
                            sortable: true
                        },
                        {
                            field: 'likes_text', title: __('Likes'),
                            searchable: false
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime,
                            sortable: true,
                            order: 'desc'
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'restore',
                                    icon: 'fa fa-circle-o-notch',
                                    title: __('Restore'),
                                    url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                    extend: 'data-toggle="tooltip"',
                                    refresh: true,
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    confirm: "是否确认恢复数据"
                                },
                                {
                                    name: 'destroy',
                                    icon: 'fa fa-trash',
                                    title: __('Destroy'),
                                    url: $.fn.bootstrapTable.defaults.extend.destroy_url,
                                    extend: 'data-toggle="tooltip"',
                                    refresh: true,
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    confirm: "是否确认真实销毁数据"
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
