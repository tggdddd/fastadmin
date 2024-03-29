define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'business/public_business/index' + location.search,
                    del_url: 'business/public_business/del',
                    multi_url: 'business/public_business/multi',
                    import_url: 'business/public_business/import',
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
                        {field: 'id', title: __('Id'), searchable: false},
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
                                    name: __('Apply'),
                                    text: __('Apply'),
                                    classname: 'btn btn-xs btn-info btn-ajax',
                                    icon: 'fa fa-hand-paper-o',
                                    url: 'business/public_business/apply',
                                    refresh: true
                                },
                                {
                                    name: __('Assign'),
                                    text: __('Assign'),
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-check',
                                    url: 'business/public_business/allocate',
                                    extend: `data-area='["50%","50%"]'`,
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
        recyclebin: function () {
            Table.api.init({
                extends: {}
            })
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: 'business/public_business/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {
                            field: 'avatar',
                            title: __('Avatar'),
                            operate: false,
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
                        {field: 'province_text', title: __('Province'), operate: false},
                        {field: 'city_text', title: __('City'), operate: false},
                        {field: 'district_text', title: __('District'), operate: false},
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
                                    url: 'business/public_business/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'business/public_business/destroy',
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
        allocate: function () {
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
