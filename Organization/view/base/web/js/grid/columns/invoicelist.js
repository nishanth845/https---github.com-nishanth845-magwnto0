define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/url',
    'mage/template',
    'Magento_Ui/js/modal/modal'
], function (Column, $, url) {
    'use strict';

    return Column.extend({

        getLabel: function (row) {
            return row[this.index + '_html']
        },
        getOrderIncremId: function (row) {
            return row[this.index + '_orderIncremId']
        },
        getCustomerId: function (row) {
            return row[this.index + '_customerId']
        },
        getOrgId: function (row) {
            return row[this.index + '_orgId']
        },
        getInvoiceGetUrl: function (row) {
            return row[this.index + '_invoiceGetUrl']
        },
        getInvoicePdfUrl: function (row) {
            return row[this.index + '_invoicePdfUrl']
        },
        preview: function (row) {
            var invoiceGetUrl = this.getInvoiceGetUrl(row);
            var invoicePdfUrl = this.getInvoicePdfUrl(row);
            var incrementId = this.getOrderIncremId(row);
            var customerId = this.getCustomerId(row);
            var param = 'ajax=1&customerId=' + customerId;
            $.ajax({
                showLoader: true,
                url: invoiceGetUrl,
                data: param,
                type: "GET",
                dataType: 'json'
            }).done(function (data) {
                if (data.status === 1) {
                    var html = '<ul>';
                    $.each(data.data, function (i, val) {
                        if(val.number != "") {
                            html += '<li>';
                            html += '<a href="' + invoicePdfUrl + 'customerId/' + customerId + '/invoiceId/' + val.number + '" target="_blank">';
                            html += val.number;
                            html += '</a>'
                            html += '</li>';
                        }
                    });
                    html += '</ul>'
                } else {
                    var html = '<div id="messages"><div class="messages"><div class="message message-warning warning"><div data-ui-id="messages-message-warning">No invoices found.</div></div></div></div>';
                }
                var previewPopup = $('<div/>').html(html);
                previewPopup.modal({
                    title: "Invoice List: #" + incrementId,
                    innerScroll: true,
                    modalClass: '_image-box',
                    buttons: []
                }).trigger('openModal');
            });

        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        }
    });
});
