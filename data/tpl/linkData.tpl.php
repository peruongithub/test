<div class="row">
    <div class="col-md-12">
        <table class="table table-hover" id="link" width="100%" cellpadding="0" cellspacing="0"
               border="0">
            <thead>
            <tr>
                <?php
                foreach ($columns as $column) {
                    echo "<th>$column</th>";
                }
                ?>
                <th>Status</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <?php
                foreach ($columns as $column) {
                    echo "<th>$column</th>";
                }
                ?>
                <th>Status</th>
            </tr>

            </tfoot>
        </table>
        <!-- Modal -->
        <div id="linkEdit" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="linkFormTitle" class="modal-title">Modal Header</h4>
                    </div>
                    <div class="modal-body">
                        <form id="linkEdit" enctype="multipart/form-data" action="" method="post">
                            <input class="form-control" type="text" name="link" id="" value=""
                                   placeholder="Enter you url">
                            <input class="form-control" type="date" name="expire" id="" value=""
                                   placeholder="Enter expiration date and time">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group">
                            <input id="cancel" type="button" class="btn btn-default" data-dismiss="modal"
                                   value="Cancel">
                            <input id="saveLink" type="button" class="btn btn-primary" data-dismiss="modal"
                                   value="Save">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.2.1/css/buttons.bootstrap.min.css">
    <script src="https://cdn.datatables.net/buttons/1.2.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.1/js/buttons.bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.0/css/select.bootstrap.min.css">
    <script src="https://cdn.datatables.net/select/1.2.0/js/dataTables.select.min.js"></script>

    <script src="/data/js/jquery.confirm.min.js"></script>

    <style type="text/css" title="currentStyle">
        #link_length {
            display: inline-block;
            padding: 0px 10px;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {
            var editForm = $('form#linkEdit');

            var restURL = function (code = null) {
                var base = "<?php echo $restUrl; ?>";
                return (null == code) ? base : base + '/' + code;
            };
            var Table = $('#link');

            var Dialog = $('div#linkEdit');

            Dialog.on('show.bs.modal', function (e) {
                var title = editForm.data('title');
                $('#linkFormTitle', Dialog).text(title);

                var mode = editForm.data('mode');
                if ('add' === mode) {
                    editForm.prop('action', restURL());
                } else if ('edit' === mode) {
                    editForm.prop('action', restURL(editForm.data('code')));
                }
            });

            Dialog.on('hide.bs.modal', function (e) {
                if ('cancel' != editForm.data('mode')) {
                    Table.api().ajax.reload();
                }
            });

            $('input#saveLink').click(function () {
                editForm.ajaxSubmit({
                    dataType: 'json',
                    resetForm: true,
                    beforeSubmit: function (arr, form) {
                    },
                    success: function (json, statusText, xhr, form) {
                        var message;
                        if (json.url) {
                            message = json.message + ' Short link: "' + json.url + '"';
                        } else {
                            message = json.message;
                        }
                        $.showmessage({
                            type: 'success',
                            message: message
                        });
                    }
                });

                Dialog.modal('hide');
            });

            /* Init the table */
            var DataTableOptions = {
                dom: 'Blrtip',
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: restURL(),
                    method: 'GET',
                    dataSrc: function (json) {
                        var data = json.data;
                        for (var i = 0, ien = data.length; i < ien; i++) {
                            var row = data[i];
                            var expire = Date.parse(row.expire);
                            var now = new Date();
                            $.extend(true, row, {'status': (now < expire)});
                        }

                        return data;
                    }
                },
                rowId: 'link',
                ordering: true,
                order: [[2, 'desc']],
                columnDefs: [
                    {orderable: false, targets: '_all'}
                ],
                columns: [
                    {
                        data: 'url',
                        render: function (data, type, full, meta) {
                            return '<a href="' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: 'link',
                        render: function (data, type, full, meta) {
                            return '<a href="' + window.location.origin + '/' + data + '">' + data + '</a>';
                        }
                    },
                    {
                        data: 'expire',
                        orderable: true
                    },
                    {
                        data: 'status',
                        render: function (data, type, full, meta) {
                            return data ? 'Active' : 'Expired';
                        }
                    }
                ],
                select: true,
                deferRender: true,
                buttons: [
                    {
                        text: 'Reload',
                        action: function (e, dt, node, config) {
                            dt.ajax.reload();
                        }
                    },
                    {
                        text: 'Add new link',
                        action: function (e, dt, node, config) {
                            editForm.data('mode', 'add');
                            editForm.data('title', 'Add new link');
                            Dialog.modal('show');
                        }
                    },
                    {
                        extend: 'selectedSingle',
                        text: 'Edit selected link',
                        action: function (e, dt, node, config) {
                            editLink(dt.row({selected: true}));
                        }
                    },
                    'selectAll',
                    'selectNone',
                    {
                        text: 'Delete',
                        extend: 'selected',
                        enabled: false,
                        action: function (e, dt, node, config) {
                            var rowsIds = dt.rows({selected: true}).ids();
                            var codes = [];
                            for (var i = 0, ien = rowsIds.length; i < ien; i++) {
                                codes.push(rowsIds[i]);
                            }

                            var ajaxData = null;
                            var ajaxURL = restURL();
                            if (1 == codes.length) {
                                ajaxURL = restURL(codes[0]);
                            } else {
                                ajaxData = {codes: codes};
                            }

                            $.confirm({
                                text: 'Are you sure you want to delete selected link' + ((1 == codes.length) ? '.' : 's.'),
                                confirm: function () {
                                    $.ajax({
                                        url: ajaxURL,
                                        method: 'DELETE',
                                        crossDomain: false,
                                        contentType: 'application/json',
                                        dataType: 'json',
                                        processData: false,
                                        data: (null == ajaxData) ? '' : JSON.stringify(ajaxData),
                                        success: function (data, textStatus, jqXHR) {
                                            dt.ajax.reload();
                                        }
                                    });
                                }
                            });
                        }
                    }
                ]
            };

            Table.dataTable(DataTableOptions);

            var editLink = function (row) {
                editForm.data('mode', 'edit');
                editForm.data('code', row.id());

                var data = row.data();
                $('input[name=link]', editForm).val(data.url);
                $('input[name=expire]', editForm).val(data.expire);
                editForm.data('title', 'Edit link');
                Dialog.modal('show');
            };


            $('input[name=expire]').datetimepicker({
                showTodayButton: true,
                inline: true,
                sideBySide: true,
                collapse: false,
                format: 'DD MMMM Y HH:mm'
            });

        });
    </script>
