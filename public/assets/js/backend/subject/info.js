define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var ids = Fast.api.query('ids');
    var Controller = {
        index: function () {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                Controller.table[$($(this).attr("href")).attr("id")].call(this)
            })
            const nowActive = $(".tab-pane.active").attr("id")
            Controller.table[nowActive].call(this)
        },
        comment_add: function () {
            Controller.api.bindevent($('form[role="form"]'))
        },
        comment_edit: function () {
            Controller.api.bindevent($('form[role="form"]'))
        },
        table: {
            order: function () {
                Table.api.init({
                    extend: {
                        index_url: `subject/info/order?ids=${ids}`,
                        add_url: 'subject/info/order_add',
                        edit_url: 'subject/info/order_edit',
                        del_url: 'subject/info/order_del',
                        multi_url: 'subject/info/order_multi',
                        table: 'subject_order',
                    }
                });
                var table = $("#orderTable");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: "#orderToolbar",
                    columns: [
                        {field: 'id', title: 'ID', operate: false, sortable: true},
                        {field: 'code', title: __('OrderCode'), operate: 'LIKE'},
                        {field: 'total', title: __('OrderTotal'), operate: false, sortable: true},
                        {field: 'business.nickname', title: __('BusinessNickname'), operate: 'LIKE'},
                        {
                            field: 'createtime',
                            title: __('OrderTime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            formatter: Table.api.formatter.datetime
                        }
                    ]
                });
                Table.api.bindevent(table);
            },
            comment: function () {
                Table.api.init({
                    extend: {
                        index_url: `subject/info/comment?ids=${ids}`,
                        add_url: `subject/info/comment_add?subid=${ids}`,
                        edit_url: 'subject/info/comment_edit',
                        del_url: 'subject/info/comment_del',
                        multi_url: 'subject/info/comment_multi',
                        table: 'subject_comment',
                    }
                });
                var table = $("#commentTable");
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,
                    pk: 'id',
                    sortName: 'id',
                    toolbar: "#commentToolbar",
                    columns: [
                        {field: 'id', title: 'ID', operate: false, sortable: true},
                        {field: 'business.nickname', title: __('BusinessNickname'), operate: 'LIKE'},
                        {
                            field: 'content',
                            title: __('CommentContent'),
                            formatter: value => value.replaceAll(/<.*?>/g, ''),
                            operate: 'LIKE'
                        },
                        {
                            field: 'createtime',
                            title: __('CommentTime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            sortable: true,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: []
                        }
                    ]
                });
                Table.api.bindevent(table);
            }
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
