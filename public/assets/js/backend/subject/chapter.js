define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            $(Table.config.addbtn).data("area", ['35%', '70%'])
            var subid = location.pathname.match(/\/ids\/(\w+)/)[1]
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/chapter/index' + location.search,
                    add_url: `subject/chapter/add/subid/${subid}`,
                    edit_url: `subject/chapter/edit/subid/${subid}`,
                    del_url: 'subject/chapter/del',
                    multi_url: 'subject/chapter/multi',
                    import_url: 'subject/chapter/import',
                    table: 'subject_chapter',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                queryParams: function (params) {
                    params = params || {}
                    params.ids = subid
                    return params
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: "ID"},
                        {field: 'subject.title', title: __('Subid'), visible: false},
                        {
                            field: 'title',
                            title: __('Title'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'url',
                            title: __('Video'),
                            formatter: function (value, row, index) {
                                return `<video width="40" preload="metadata" height="40" src=${value}></video>`
                            }
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
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
