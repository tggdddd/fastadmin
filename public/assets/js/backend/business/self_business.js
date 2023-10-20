define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/self_business/index' + location.search,
                    add_url: 'business/self_business/add',
                    edit_url: 'business/self_business/edit',
                    del_url: 'business/self_business/del',
                    multi_url: 'business/self_business/multi',
                    import_url: 'business/self_business/import',
                    table: 'business',
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
                        {field: 'id', title: __('Id'), searchable: false, visible: true},
                        // {field: 'adminid', title: __('Adminid'), operate: false},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        // {field: 'password', title: __('Password'), searchable: false, operate: 'LIKE'},
                        // {field: 'salt', title: __('Salt'), operate: 'LIKE', searchable: false},
                        {
                            field: 'avatar',
                            title: __('Avatar'),
                            searchable: false,
                            operate: 'LIKE',
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field: 'gender',
                            title: __('Gender'),
                            searchList: {"0": __('Secret'), "1": __('Male'), "2": __('Female')},
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'source_text', title: __('Source')},
                        {
                            field: 'deal',
                            title: __('Deal'),
                            searchList: {"0": __('No'), "1": __('Yes')},
                            formatter: Table.api.formatter.status,
                            custom: {"0": "gray", "1": "info"}
                        },
                        // {field: 'openid', title: __('Openid'), searchable: false, operate: 'LIKE'},
                        {field: 'province_text', title: __('Province'), operate: 'LIKE'},
                        {field: 'city_text', title: __('City'), operate: 'LIKE'},
                        {field: 'district_text', title: __('District'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'money', title: __('Money'), operate: 'BETWEEN'},
                        {
                            field: 'email',
                            title: __('Email'),
                            operate: 'LIKE',
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {
                            field: 'auth',
                            title: __('Auth'),
                            searchList: {"0": __('No'), "1": __('Yes')},
                            formatter: Table.api.formatter.status,
                            custom: {"0": "gray", "1": "info"}
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: __('Detail'),
                                    text: __('Detail'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-address-book',
                                    url: 'business/self_business/detail',
                                    extend: `data-area='["100%","100%"]'`,
                                    refresh: true
                                }, {
                                    name: __('Reclaim'),
                                    text: __('Reclaim'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    confirm: "确认将该客户移入公海吗",
                                    icon: 'fa fa-recycle',
                                    url: 'business/self_business/reclaim',
                                    extend: `data-area='["80%","80%"]'`,
                                    refresh: true
                                }
                            ],
                        }
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            const ids = Fast.api.query("ids")
            $("a[data-toggle='tab']").on("shown.bs.tab", function () {
                const id = $($(this).attr("href")).attr("id")
                Controller.detail_render[id].call(this, ids)
            })
        },
        detail_render: {
            detail: function (ids) {
            },
            visited: function (ids) {
                Table.api.init({
                    extend: {
                        index_url: `business/self_business/business_visited?ids=${ids}`,
                        add_url: `business/visit/add?busid=${ids}`,
                        edit_url: 'business/visit/edit?busid=${ids}',
                        del_url: 'business/visit/del',
                        multi_url: 'business/visit/multi',
                        table: 'business_record',
                    }
                });
                var table = $("#visited_table");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: "#visited_toolbar",
                    fixedRightNumber: 1,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: __('Id')},
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
            application: function (ids) {
                Table.api.init({
                    extend: {
                        index_url: 'business/receive/index' + location.search,
                        table: 'business_receive',
                    }
                });
                var table = $("#application_table");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: "#application_toolbar",
                    columns: [
                        [
                            // {checkbox: true},
                            {field: 'id', title: __('Id'), visible: false},
                            {field: 'admin.nickname', title: __('Applyid')},
                            {
                                field: 'status',
                                title: __('Apply Status'),
                                searchList: {
                                    "apply": __('Apply '),
                                    "allot": __('Allot '),
                                    "recovery": __('Recovery '),
                                    "reject": __('Reject ')
                                },
                                formatter: Table.api.formatter.status
                            },
                            {
                                field: 'applytime',
                                title: __('Applytime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                autocomplete: false,
                                formatter: Table.api.formatter.datetime
                            }
                        ]
                    ]
                });
                Table.api.bindevent(table);
            },
            expensed: function (ids) {
                Table.api.init({
                    extend: {
                        index_url: `business/self_business/business_record?ids=${ids}`,
                        table: 'business_record',
                    }
                });
                var table = $("#expensed_table");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: "#expensed_toolbar",
                    fixedRightNumber: 1,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: "ID", operate: false},
                            // {field: 'business.nickname', title: __('Busid')},
                            {field: 'total', title: __('Total'), operate: 'BETWEEN'},
                            {
                                field: 'content',
                                title: __('Content'),
                                operate: 'LIKE',
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
                            }
                        ]
                    ]
                });
                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            order: function (ids) {
                Table.api.init({
                    extend: {
                        index_url: `business/self_business/subject_order?ids=${ids}`,
                        table: 'subject_order',
                    }
                });
                var table = $("#order_table");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    fixedColumns: true,
                    toolbar: "#order_toolbar",
                    fixedRightNumber: 1,
                    columns: [
                        [
                            {checkbox: true},
                            {field: 'id', title: "ID", operate: false},
                            {field: 'subject.title', title: __('Subid'), operate: 'LIKE',},
                            // {field: 'business.nickname', title: __('Busid')},
                            {field: 'total', title: __('Total'), operate: 'BETWEEN'},
                            {
                                field: 'code',
                                title: __('Code'),
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
                            }
                        ]
                    ]
                });
                // 为表格绑定事件
                Table.api.bindevent(table);
            },
        },
        add: function () {
            Controller.api.bindevent();
            $("#c-area").on("cp:updated", function () {
                const citypicker = $(this).data("citypicker");
                $("#province").val(citypicker.getCode("province"));
                $("#city").val(citypicker.getCode("city"));
                $("#district").val(citypicker.getCode("district"));
            })
        },
        edit: function () {
            Controller.api.bindevent();
            $("#c-area").on("cp:updated", function () {
                const citypicker = $(this).data("citypicker");
                $("#province").val(citypicker.getCode("province"));
                $("#city").val(citypicker.getCode("city"));
                $("#district").val(citypicker.getCode("district"));
            })
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
