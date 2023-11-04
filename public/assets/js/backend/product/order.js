define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/order/index' + location.search,
                    del_url: 'product/order/del',
                    table: 'business_order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'code', title: __('Code'), operate: 'LIKE', formatter: Table.api.formatter.content},
                        {
                            field: 'products', title: __('Products'), operate: false, formatter: (value) => {
                                let ids = (value.map(e => e.product.id))
                                if (ids.length > 0) ids = ids.reduce((a, b) => a + "," + b)
                                let filter = `filterid=${ids}`
                                let url = `product/product/index?${filter}`
                                html = `<a class="btn-dialog btn" data-url="${url}">`
                                for (let i = 0; i < value.length; i++) {
                                    let product = value[i];
                                    html += `<div style="border: 1px #7777776c solid;">${product.product.name}<span class="ml-1">x${product.pronum}</span></div>`
                                }
                                html += "</a>"
                                return html
                            }
                        },
                        {
                            field: 'status_text',
                            operate: false,
                            title: __('Status'),
                            formatter: Table.api.formatter.label
                        },
                        {
                            field: 'status',
                            visible: false,
                            operate: "=",
                            title: __('Status'),
                            searchList: {
                                0: "未支付",
                                1: "已支付",
                                "2": "已发货",
                                "3": "已收货",
                                "4": "已完成",
                                "-1": "仅退款",
                                "-2": "退款退货",
                                "-3": "售后中(待退货)",
                                "-4": "退货成功",
                                "-5": "退货失败"
                            }
                        },
                        {field: 'amount', title: __('Amount')}, {
                        field: 'operate',
                        title: __('Operate'),
                        table: table,
                        events: Table.api.events.operate,
                        formatter: Table.api.formatter.operate,
                        buttons: [
                            {
                                name: __('Shipping'),
                                title: __('Shipping'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-hand-paper-o',
                                extend: 'data-area=\'[\"50%\",\"50%\"]\'',
                                url: 'product/order/send_product',
                                refresh: true,
                                hidden: (data) => data['status'] != '1'
                            },
                            {
                                name: __('Review'),
                                title: __('Review'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-tripadvisor',
                                extend: 'data-area=\'[\"80%\",\"80%\"]\'',
                                url: 'product/order/return_purchase',
                                refresh: true,
                                hidden: (data) => data['status'] != '-2'
                            },
                            {
                                name: __('Confirm'),
                                title: __('Confirm'),
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-grav',
                                extend: 'data-area=\'[\"80%\",\"80%\"]\'',
                                url: 'product/order/receive_purchase',
                                refresh: true,
                                hidden: (data) => data['status'] != '-3'
                            },
                        ]
                    }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        send_product: function () {
            Controller.api.bindevent();
        },
        return_purchase: function () {
            Controller.api.bindevent();
        },
        receive_purchase: function () {
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
