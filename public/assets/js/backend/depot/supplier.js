define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/supplier/index' + location.search,
                    add_url: 'depot/supplier/add',
                    edit_url: 'depot/supplier/edit',
                    del_url: 'depot/supplier/del',
                    multi_url: 'depot/supplier/multi',
                    import_url: 'depot/supplier/import',
                    table: 'depot_supplier',
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
                        {field: 'id', title: __('Id'), operate: false},
                        {
                            field: 'name',
                            title: __('Name'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'mobile',
                            title: __('Mobile'),
                            operate: 'LIKE',
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
                        {
                            field: 'province_text',
                            title: __('Province'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'city_text',
                            title: __('City'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'district_text',
                            title: __('District'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'area',
                            title: __('Area'),
                            operate: '=',
                            extend: `data-toggle="city-picker"`,
                            visible: false
                        },
                        {
                            field: 'address',
                            title: __('Address'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'province', operate: '=', addClass: "hidden", visible: false},
                        {field: 'city', operate: '=', addClass: "hidden", visible: false},
                        {field: 'district', operate: '=', addClass: "hidden", visible: false},
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
            $("#area").on("cp:updated", function () {
                var citypicker = $(this).data("citypicker");
                $("#district").val(citypicker.getCode("district"))
                $("#city").val(citypicker.getCode("city"))
                $("#province").val(citypicker.getCode("province"))
            });
        },
        add: function () {
            $("#c-area").on("cp:updated", function () {
                const citypicker = $(this).data("citypicker");
                $("#province").val(citypicker.getCode("province"));
                $("#city").val(citypicker.getCode("city"));
                $("#district").val(citypicker.getCode("district"));
            })
            Controller.api.bindevent();
        },
        edit: function () {
            $("#c-area").on("cp:updated", function () {
                const citypicker = $(this).data("citypicker");
                $("#province").val(citypicker.getCode("province"));
                $("#city").val(citypicker.getCode("city"));
                $("#district").val(citypicker.getCode("district"));
            })
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
