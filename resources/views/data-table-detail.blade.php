<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
        <style>
            th, td {
                font-size: 0.8rem !important;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @if (Route::has('login'))
                        <div class="top-right links">
                            @auth
                                <a href="{{ url('/home') }}">Home</a>
                            @else
                                <a href="{{ route('login') }}">Login</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}">Register</a>
                                @endif
                            @endauth
                        </div>
                    @endif

                    <h4 class="m-3">{{ $table_name }}</h4>
                    <hr>
                    <table id="data_detail" class="table table-condensed display">
                        <thead>
                            <tr id="table_thead"></tr>
                        </thead>
                        <tfoot>
                            <tr id="table_tfoot"></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <input type="hidden" id="table_name" value="{{ $table_name }}">
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script src="{{asset('js/datatables.min.js')}}"></script>
        <script>
            var table;
            var tableName = $('#table_name').val();

            $(function() {
                // $('#form_query').on('submit', (function (e) {
                //     ...
                //     return false;
                // });

                allColumn();
            });

            function allColumn() {
                const params = {
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                    'table_name': tableName
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/all-column',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (col) {
                        var columns = [];
                        var theadName = '';
                        var tfootName = '';
                        if (col.length > 0) {
                            for (var i = 0; i < col.length; i++) {
                                columns.push({ "className": 'text-left'});
                                theadName += '<th>' + col[i] + '</th>';
                                tfootName += '<td>' + col[i] + '</td>';
                            }

                            columns.push({ "className": 'text-center'});
                            theadName += '<th>Options</th>';
                            tfootName += '<td>Options</td>';

                            $('#table_thead').html(theadName);
                            $('#table_tfoot').html(tfootName);

                            setTimeout(function() {
                                dataTable(columns);
                                $('.tooltips').tooltip();
                            }, 100);
                        }
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }

            function dataTable(columns) {
                table = $('#data_detail').DataTable({
                    "pagingType": "full_numbers",
                    "lengthMenu": [[10, 15, 20, 25, 30, 50, 100], [10, 15, 20, 25, 30, 50, 100]],
                    "searching": false,
                    "processing": false,
                    "serverSide": true,
                    "ajax": {
                        "url": $('#base_url').val() +'/api/data-table',
                        "type":"POST",
                        "beforeSend": function(xhr){
                            xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                        },
                        "data": function(d){
                            // d.search = $('#search').val();
                            d.table_name = tableName;
                            // d.filter = $('#chk_action').val();
                        },
                        error: function(response) {
                            // console.log(response);
                            // checkReload(response.status, 'article');
                        }
                    },
                    "ordering": true,
                    "fnDrawCallback":  function (oSettings, json) {
                        // $('.tooltips').tooltip({container: "body"});
                        // $('td').removeClass('sorting_1');

                        // $('#result_article_total').html(oSettings.fnRecordsTotal());
                        // $('table.dataTable thead .no-sort.sorting_desc').css('cursor', 'auto');
                        // $('table.dataTable thead .no-sort.sorting_desc').css('position', 'unset');
                    },
                    "createdRow": function(row, data, index){
                        // $('td', row).eq(0).attr('id', 'tr_' + (index + 1));
                        $('td', row).eq(0).addClass('tr-article');
                        // console.log(data[0]);
                    },
                    "pageLength": 20,
                    "columns": columns,
                    "columnDefs": [
                        {
                            "targets"  : 'no-sort',
                            "orderable": false,
                            "order" : []
                        }
                    ]
                    ,"order": [[0, 'desc']] // (columns.length - (columns.length - 1))
                });
            }
        </script>
    </body>
</html>
