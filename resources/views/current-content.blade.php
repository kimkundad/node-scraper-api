<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Search detail</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <table class="table table-condensed table-striped ffp-current-content">
                        <thead>
                            <tr>
                                <th class="match-time">เวลาแข่ง</th>
                                <th class="home-team">ทีมเหย้า</th>
                                <th class="vs">Vs</th>
                                <th class="away-team">ทีมเยือน</th>
                                <th class="league-name">ราคาบอลไหล</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-ffp">
                            <tr id="tr_loading">
                                <td colspan="5">
                                    <div class="graph-loading">
                                        <div class="l-one"></div>
                                        <div class="l-two"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php
        $httpHost = 'http://';
        // uncomment if node_scraper is real domain
        // if (env('APP_ENV') === 'production') {
        //     $httpHost = 'https://';
        // } else {
        //     $httpHost = 'http://';
        // }
    ?>
    <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />

    <script src="{{asset('js/jquery.min.js')}}"></script>
    <script src="{{asset('js/bootstrap.min.js')}}"></script>

    <script>
        var fakeIp = '58.18.145.72';
        var thisHost = $('#base_url').val();

        $(function() {
            callAPICurrentContent(fakeIp);
        });

        function callAPICurrentContent(ip) {
            const params = {
                'ip': ip
            };

            $.ajax({
                url: thisHost + '/api/ffp',
                type: 'POST',
                data: params,
                dataType: 'json',
                cache: false,
                success: function (response) {

                    if (response.latest_dir) {
                        $('#title_data').html('ข้อมูล ณ เวลา: ' + response.latest_dir);
                        if (response.raw_group) {
                            var domain = response.domain
                            arrangeContent(response.raw_group, domain);
                        }
                    } else {
                        $('#title_data').html('');
                        // console.log(response);
                        $('#tr_loading > td').html('- ไม่มีข้อมูลการแข่งขัน ในช่วงเวลานี้ -');
                    }
                },
                error: function(response) {
                    console.log(response);
                    $('#title_data').html('The system is currently unavailable.');
                    $('#tr_loading').remove();
                }
            });
        }

        function arrangeContent(rawGroup, domain) {
            if (rawGroup) {
                if (rawGroup.length > 0) {
                    var firstLink = '';
                    var html = '';
                    for(var i = 0; i < rawGroup.length; i++) {
                        var row = rawGroup[i];
                        var rowDatas = row.match_datas;
                        var link = row.link;
                        for (var j = 0; j < rowDatas.length; j++) {
                            var data = rowDatas[j];
                            // console.log(data);
                            html += '<tr class="db-collapse">'; // db-match
                            html +=         '<td>';
                            html +=             '<div class="match-time d-flex just-between">';
                            html +=                 '<span>' + data.match_result + '</span>';
                            html +=                 '<span>' + data.date_time_before + '</span>';
                            html +=             '</div>';
                            html +=         '</td>';
                            html +=         '<td>' + data.left_team_name + '</td>';
                            html +=         '<td>';
                            html +=             (data.left_team_score) ? data.left_team_score : '<span class="text-bold">(แพ้/ชนะ)</span>';
                            html +=         '</td>';
                            html +=         '<td>';
                            html +=             (data.left_last_num) ? data.left_last_num : '';
                            html +=         '</td>';
                            html +=        '<td>';
                            html +=             '<div class="vs d-flex just-center">Vs</div>';
                            html +=         '</td>';
                            html +=         '<td>' + data.right_team_name + '</td>';
                            html +=         '<td>';
                            html +=             (data.right_team_score) ? data.right_team_score : '<span class="text-bold">(แพ้/ชนะ)</span>';
                            html +=         '</td>';
                            html +=         '<td>';
                            html +=             (data.right_last_num) ? data.right_last_num : '';
                            html +=         '</td>';
                            if (j == 0) {
                                html +=         '<td class="row-span" rowspan="' + rowDatas.length + '">';
                                html +=             '<div class="league-name">';
                                if (data.link) {
                                    firstLink = (firstLink) ? firstLink : data.link;
                                    var link = thisHost + '/ราคาบอลไหล?link=' + data.link;
                                    html +=             '<a href="' + link + '" target="_BLANK">ดูราคา<br>บอลไหล</a>';
                                }
                                html +=             '</div>';
                                html +=         '</td>';
                            }
                            html += '</tr>';
                        }
                    }

                    $('#tr_loading').remove();
                    $('#tbody-ffp').append(html);
                }
            }
        }
    </script>
    </body>
</html>
