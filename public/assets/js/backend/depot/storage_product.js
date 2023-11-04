define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var ids = Fast.api.query('ids');
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/storage_product/index/ids/' + ids,
                    add_url: 'depot/storage_product/add/storageid/' + ids,
                    edit_url: 'depot/storage_product/edit',
                    del_url: 'depot/storage_product/del',
                    multi_url: 'depot/storage_product/multi',
                    import_url: 'depot/storage_product/import',
                    table: 'depot_storage_product',
                    ids: ids
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
                        {field: 'id', title: __('Id'), visible: false, operate: false},
                        {field: 'proid_text', title: __('Product'), operate: false},
                        {
                            field: 'proid', title: __('Product'), visible: false, operate: "=",
                            extend: `
                                data-source="product.product/selectpage"
                                data-field="name"
                                data-search-field="name"
                                data-primary-key="id"
                                data-page-size="7"`,
                            addClass: "selectpage"
                        },
                        {field: 'nums', title: __('Nums')},
                        {field: 'price', title: __('Price'), operate: 'BETWEEN'},
                        {field: 'total', title: __('Total'), operate: 'BETWEEN'},
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
