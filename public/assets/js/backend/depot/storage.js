define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'depot/storage/index' + location.search,
                    add_url: 'depot/storage/add',
                    edit_url: 'depot/storage/edit',
                    del_url: 'depot/storage/del',
                    detail_url: 'depot/storage/detail',
                    approval_url: 'depot/storage/approval',
                    reject_url: 'depot/storage/reject',
                    stock_url: 'depot/storage/stock',
                    table: 'depot_storage',
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
                onLoadSuccess: function () {
                    $('.btn-editone').data('area', ['100%', '100%'])
                    $('.btn-add').data("callback", () => {
                        table.bootstrapTable('refresh');
                    })
                },
                columns: [[{checkbox: true}, {
                    field: 'id',
                    title: __('Id'),
                    visible: false,
                    operate: false
                }, {field: 'code', title: __('Code'), operate: 'LIKE'}, {
                    field: 'type',
                    title: __('Type'),
                    searchList: {"1": __('DIRECT SALES INTO WAREHOUSE'), "2": __('RETURN-TO-WAREHOUSE')},
                    formatter: Table.api.formatter.normal
                }, {field: 'amount', title: __('Amount'), operate: 'BETWEEN'}, {
                    field: 'createtime',
                    title: __('Createtime'),
                    operate: 'RANGE',
                    addclass: 'datetimerange',
                    autocomplete: false,
                    formatter: Table.api.formatter.datetime
                }, {
                    field: 'status', title: __('Status'), searchList: {
                        "0": __('PENDING'),
                        "1": __('APPROVAL FAILED'),
                        "2": __('TO BE STOCKED'),
                        "3": __('WAREHOUSING COMPLETED')
                    }, formatter: Table.api.formatter.status
                }, {field: 'admin.nickname', title: __('Adminid'), operate: false}, {
                    field: 'adminid', title: __('Adminid'), visible: false, extend: `
                                data-source="auth.admin/selectpage"
                                data-field="nickname"
                                data-search-field="nickname,mobile,email"
                                data-primary-key="id"
                                data-page-size="7"`, addClass: "selectpage", operate: '='
                }, {field: 'reviewer.nickname', title: __('Reviewerid'), operate: false}, {
                    field: 'reviewerid', title: __('Reviewerid'), visible: false, extend: `
                                data-source="auth.admin/selectpage"
                                data-field="nickname"
                                data-search-field="nickname,mobile,email"
                                data-primary-key="id"
                                data-page-size="7"`, addClass: "selectpage", operate: '='
                }, {field: 'supplier.name', title: __('Supplierid'), operate: false}, {
                    field: 'supplierid', title: __('Supplierid'), visible: false, extend: `
                                data-source="depot.supplier/selectpage"
                                data-field="name"
                                data-search-field="name,mobile"
                                data-primary-key="id"
                                data-page-size="7"`, addClass: "selectpage", operate: '='
                }, {
                    field: 'operate',
                    title: __('Operate'),
                    table: table,
                    events: Table.api.events.operate,
                    formatter: Table.api.formatter.operate,
                    buttons: [{
                        name: "detail",
                        title: __('Detail'),
                        classname: 'btn btn-xs btn-info btn-dialog',
                        icon: 'fa fa-info-circle',
                        url: $.fn.bootstrapTable.defaults.extend.detail_url,
                        extend: `data-area='["80%","80%"]'`,
                    }, {
                        name: "edit",
                        icon: 'fa fa-pencil',
                        title: __('Edit'),
                        extend: 'data-toggle="tooltip" data-container="body"',
                        classname: 'btn btn-xs btn-success btn-editone',
                        visible: function (row) {
                            return row.status == 0 || row.status == 1 || row.status == 2
                        }
                    }, {
                        name: "approval",
                        title: __('Approval'),
                        classname: 'btn btn-xs btn-success btn-ajax',
                        icon: 'fa fa-check-circle-o',
                        url: $.fn.bootstrapTable.defaults.extend.approval_url,
                        refresh: true,
                        visible: function (row) {
                            return row.status == 0
                        }
                    }, {
                        name: "reject",
                        title: __('Reject'),
                        classname: 'btn btn-xs btn-warning btn-ajax',
                        icon: 'fa fa-times-circle',
                        url: $.fn.bootstrapTable.defaults.extend.reject_url,
                        refresh: true,
                        visible: function (row) {
                            return row.status == 0
                        }
                    }, {
                        name: "stock",
                        title: __('Stock in'),
                        classname: 'btn btn-xs btn-primary btn-ajax',
                        icon: 'fa fa-plus-circle',
                        url: $.fn.bootstrapTable.defaults.extend.stock_url,
                        refresh: true,
                        visible: function (row) {
                            return row.status == 2
                        }
                    }]
                }]]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        }, recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'depot/storage/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [[{checkbox: true}, {field: 'id', title: __('Id')}, {
                    field: 'deletetime',
                    title: __('Deletetime'),
                    operate: 'RANGE',
                    addclass: 'datetimerange',
                    formatter: Table.api.formatter.datetime
                }, {
                    field: 'operate',
                    width: '140px',
                    title: __('Operate'),
                    table: table,
                    events: Table.api.events.operate,
                    buttons: [{
                        name: 'Restore',
                        text: __('Restore'),
                        classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                        icon: 'fa fa-rotate-left',
                        url: 'depot/storage/restore',
                        refresh: true
                    }, {
                        name: 'Destroy',
                        text: __('Destroy'),
                        classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                        icon: 'fa fa-times',
                        url: 'depot/storage/destroy',
                        refresh: true
                    }],
                    formatter: Table.api.formatter.operate
                }]]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            // dom定位
            const table = $("#table");
            const tableHead = $("#table-head");
            const tableBody = $("#table-body");
            const addBtn = $("#addProduct");
            const submitBtn = $("#submit");
            // 商品表格参数
            const columns = [{field: 'id', title: __('Id'), hidden: false}, {
                value: 'name',
                title: __('Product')
            }, {value: 'thumb', title: __('Thumb'), formatter: "image"}, {
                field: 'num',
                title: __('Nums'),
                formatter: "input"
            }, {field: 'price', title: __('Price'), formatter: "input"}, {
                title: __('Operate'), formatter: 'operate', event: [{
                    text: "删除", clickFn: () => {
                        $(this).closest('tr').remove();
                    }, class: "btn-warning"
                }]
            }]
            // 商品数据
            // 提交事件
            submitBtn.on("click", function () {
                const data = {
                    name: $("#name").val(), remark: $("#remark").val(), type: $("#type").val(),
                }
                const products = []
                tableBody.find("tr").each((index, el) => {
                    const product = {}
                    $(el).find("td").each((index2, td) => {
                        if (columns[index2].formatter === "operate" || columns[index2].field == null) {
                            return
                        }
                        if (columns[index2].formatter === "input") {
                            product[columns[index2].field] = $(td).find("input").val()
                        } else {
                            product[columns[index2].field] = $(td).data('value')
                        }
                    })
                    products.push(product)
                })
                data.products = products
                $.ajax({
                    url: Table.api.add_url, data: data, type: "post", success: (res) => {
                        if (res.code) {
                            Fast.api.close()
                            layer.msg(res.msg);
                        } else {
                            layer.msg(res.msg);
                        }
                    }, error: (err) => {
                        layer.msg(err);
                    }
                })
            })
            // 表格头初始化
            columns.forEach(column => {
                tableHead.append(`
                <th class="${column.hidden ? 'hidden' : ''}">${column.title}</th>
                `)
            })
            table.data("data", {})
            // 事件绑定
            addBtn.on("click", () => {
                Fast.api.open("depot/storage/product_select", "选择入库商品", {
                    area: ['80%', '80%'], callback: function (data) {
                        let flag = false
                        tableBody.find("tr").each((index, item) => {
                            if ($(item).data("id") === data.id) {
                                flag = true
                            }
                        })
                        if (flag) {
                            layer.msg("该商品已存在")
                            return
                        }
                        data.id
                        let html = `<tr data-id="${data.id}">`;
                        columns.forEach(column => {
                            html += `<td data-value="${data[column.field]}" class="${column.hidden ? 'hidden' : ''}">`
                            let value = data[column.value || column.field]
                            switch (column.formatter) {
                                case "operate":
                                    if (null == column.event || !Array.isArray(column.event)) {
                                        console.error("operate 事件格式错误");
                                        break;
                                    }
                                    for (let event of column.event) {
                                        const button = `<div onclick="(${event.clickFn || '()=>{}'})()" class="btn ${event.class}">${event.text}</div>`
                                        html += button
                                    }
                                    break;
                                case "image":
                                    html += `<img src="${value}" style="width: 32px;height: 32px;"/>`
                                    break;
                                case "input":
                                    html += `<input value="${value ?? ""}" required class="form-control"/>`
                                    break;
                                default:
                                    html += `${value}`
                            }
                            html += "</td>"
                        })
                        html += `</tr>`;
                        tableBody.append(html)
                    }
                })
            })
            Controller.api.bindevent();
        }, product_select: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/product/index' + location.search, table: 'product',
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
                columns: [[{checkbox: true}, {field: 'id', title: __('Id')}, {
                    field: 'name',
                    title: __('Name'),
                    operate: 'LIKE',
                    table: table,
                    class: 'autocontent',
                    formatter: Table.api.formatter.content
                }, {field: 'thumbs', title: __('Thumbs'), formatter: Table.api.formatter.images}, {
                    field: 'type.name',
                    title: __('Type.name'),
                    operate: 'LIKE',
                    table: table,
                    class: 'autocontent',
                    formatter: Table.api.formatter.content
                }, {
                    field: 'flag',
                    title: __('Flag'),
                    searchList: {"1": __('NEW PRODUCT'), "2": __('HOT PRODUCT'), "3": __('RECOMMEND')},
                    formatter: Table.api.formatter.flag
                }, {field: 'stock', title: __('Stock')}, {
                    field: 'unit.name',
                    title: __('Unit.name'),
                    operate: 'LIKE',
                    table: table,
                    class: 'autocontent',
                    formatter: Table.api.formatter.content
                }, {field: 'price', title: __('Price'), operate: 'BETWEEN'}, {
                    field: 'operate',
                    title: __('Operate'),
                    table: table,
                    events: Table.api.events.operate,
                    formatter: Table.api.formatter.operate,
                    buttons: [{
                        name: __('Select'),
                        text: __('Select'),
                        classname: 'btn btn-xs btn-click btn-warning',
                        icon: 'fa fa-check',
                        click: function (data) {
                            Fast.api.close(Table.api.getrowdata(table, data.rowIndex));
                        }
                    }]
                }]]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        }, edit: function () {
            // dom定位
            const table = $("#table");
            const tableHead = $("#table-head");
            const tableBody = $("#table-body");
            const addBtn = $("#addProduct");
            const submitBtn = $("#submit");
            // 商品表格参数
            const columns = [{field: 'id', title: __('Id'), hidden: false}, {
                value: 'name',
                title: __('Product')
            }, {value: 'thumb', title: __('Thumb'), formatter: "image"}, {
                field: 'num',
                title: __('Nums'),
                formatter: "input"
            }, {field: 'price', title: __('Price'), formatter: "input"}, {
                title: __('Operate'), formatter: 'operate', event: [{
                    text: "删除", clickFn: () => {
                        $(this).closest('tr').remove();
                    }, class: "btn-warning"
                }]
            }]
            // 商品数据
            // 提交事件
            submitBtn.on("click", function () {
                const data = {
                    name: $("#name").val(), remark: $("#remark").val(), type: $("#type").val(),
                }
                const products = []
                tableBody.find("tr").each((index, el) => {
                    const product = {}
                    $(el).find("td").each((index2, td) => {
                        if (columns[index2].formatter === "operate" || columns[index2].field == null) {
                            return
                        }
                        if (columns[index2].formatter === "input") {
                            product[columns[index2].field] = $(td).find("input").val()
                        } else {
                            product[columns[index2].field] = $(td).data('value')
                        }
                    })
                    products.push(product)
                })
                data.products = products
                $.ajax({
                    url: Table.api.add_url, data: data, type: "post", success: (res) => {
                        if (res.code) {
                            Fast.api.close()
                            layer.msg(res.msg);
                        } else {
                            layer.msg(res.msg);
                        }
                    }, error: (err) => {
                        layer.msg(err);
                    }
                })
            })
            // 表格头初始化
            columns.forEach(column => {
                tableHead.append(`
                <th class="${column.hidden ? 'hidden' : ''}">${column.title}</th>
                `)
            })
            table.data("data", {})
            // 事件绑定
            addBtn.on("click", () => {
                Fast.api.open("depot/storage/product_select", "选择入库商品", {
                    area: ['80%', '80%'], callback: function (data) {
                        let flag = false
                        tableBody.find("tr").each((index, item) => {
                            if ($(item).data("id") === data.id) {
                                flag = true
                            }
                        })
                        if (flag) {
                            layer.msg("该商品已存在")
                            return
                        }
                        data.id
                        let html = `<tr data-id="${data.id}">`;
                        columns.forEach(column => {
                            html += `<td data-value="${data[column.field]}" class="${column.hidden ? 'hidden' : ''}">`
                            let value = data[column.value || column.field]
                            switch (column.formatter) {
                                case "operate":
                                    if (null == column.event || !Array.isArray(column.event)) {
                                        console.error("operate 事件格式错误");
                                        break;
                                    }
                                    for (let event of column.event) {
                                        const button = `<div onclick="(${event.clickFn || '()=>{}'})()" class="btn ${event.class}">${event.text}</div>`
                                        html += button
                                    }
                                    break;
                                case "image":
                                    html += `<img src="${value}" style="width: 32px;height: 32px;"/>`
                                    break;
                                case "input":
                                    html += `<input value="${value ?? ""}" required class="form-control"/>`
                                    break;
                                default:
                                    html += `${value}`
                            }
                            html += "</td>"
                        })
                        html += `</tr>`;
                        tableBody.append(html)
                    }
                })
            })
            Controller.api.bindevent();
        }, api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
