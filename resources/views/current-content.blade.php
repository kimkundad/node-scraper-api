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
                url: '{{ url('/api/ffp') }}',
                type: 'POST',
                data: params,
                dataType: 'json',
                cache: false,
                success: function (response) {
                    //console.log(response.raw_group)
                    if (response.latest_dir) {
                        $('#title_data').html('ข้อมูล ณ เวลา: ' + response.latest_dir);
                        if (response.raw_group) {
                            var domain = response.domain
                           // console.log(response.raw_group[0]);
                            arrangeContent(response.raw_group, domain);
                        }
                    } else {
                        $('#title_data').html('');
                        // console.log(response);
                        $('#tr_loading > td').html('- ไม่มีข้อมูลการแข่งขัน ในช่วงเวลานี้ -');
                    }
                },
                error: function(response) {
                   // console.log(response);
                    $('#title_data').html('The system is currently unavailable.');
                    $('#tr_loading').remove();
                }
            });
        }

        function arrangeContent(rawGroup, domain) {
            if (rawGroup) {
                console.log(rawGroup);
                if (rawGroup.length > 0) {
                    var firstLink = '';
                    var html = '';
                    var c = 0;
                    c = rawGroup.length;
                    //console.log(c);
                    var arr = [];
                    for(var i = 0; i < c; i++) {
                        var row = rawGroup[i];
                        var rowDatas = row;
                        //arr.push(row.datas);
                       // console.log(row.datas[i].match_datas);
                       // var link = row.link;
                        
                        var d = 0;
                        var d = row.datas.length;

                       // console.log(row.datas.length);
                       var f = 0;
                        for (var j = 0; j < d; j++) {
                            
                            var data = row.datas[j];
                            var link = data.match_datas[0].link;
                            
                            if(i == 0){

                           // console.log(data.match_datas.length);

                            if( j == 0){
                                html += '<tr class="db-collapse">';
                                html += '<td colspan=5>';
                                html += '<span><b>' + row.top_head + '</b></span>';
                                html += '</td>';
                                html += '</tr>';
                            }

                            var k = 0;
                            kk = data.match_datas.length;

                            html += '<tr class="db-collapse">';
                                html += '<td colspan=5>';
                                html += '<span><b>' + data.league_name + '</b></span>';
                                html += '</td>';
                                html += '</tr>';
                            
                            for (var k = 0; k < kk; k++) {

                            var data2 = data.match_datas[k];


                            html += '<tr class="db-collapse">'; // db-match
                            html +=         '<td>';
                            html +=             '<div class="match-time d-flex just-between">';
                            html +=                 '<span>' + data2.time + '</span>';
                            html +=             '</div>';
                            html +=         '</td>';
                            html +=         '<td>';
                            html +=             '<div class="match-time d-flex just-between">';
                            html +=                 '<span>' + data2.left[0] +' <b>(' + data2.left[1] +')</b></span>';
                            html +=             '</div>';
                            html +=         '</td>';
                            html +=         '<td>';
                            html +=             'เสมอ <b> ' + data2.mid[1] + '</b>';
                            html +=         '</td>';
                            html +=         '<td>';
                            html +=             '<div class="match-time d-flex just-between">';
                            html +=                 '<span>' + data2.right[0] +' <b>(' + data2.right[1] +')</b></span>';
                            html +=             '</div>';
                            html +=         '</td>';
                                html +=         '<td class="row-span" ';
                                html +=             '<div class="league-name">';
                                if (link) {
                                    html +=             '<a href="{{ url('/ราคาบอลไหล?link=') }}'+ link +'" target="_BLANK">ดูราคา<br>บอลไหล</a>';
                                }
                                html +=             '</div>';
                                html +=         '</td>';
                            html += '</tr>';  
                            }

                            }else{

                                var kk = 0;
                                kk = data.match_datas.length;

                                if(f === 0){
                                    html += '<tr class="db-collapse">';
                                    html += '<td colspan=5>';
                                    html += '<span><b>' + row.top_head + '</b></span>';
                                    html += '</td>';
                                    html += '</tr>';
                                }
                            
                            ////////////////////////////////

                                html += '<tr>';
                                html += '<td colspan=5>';
                                html += '<span><b>' + data.league_name + '</b></span>';
                                html += '</td>';
                                html += '</tr>';
                          
                            
                                for (var k = 0; k < kk; k++) {
                                var data2 = data.match_datas[k];
                                

                                console.log(data2.left_list.length);
                                ll = data2.left_list.length;
                                //console.log(ll)
                                for (var l = 0; l < ll; l++) {
                                    var data3 = data2.left_list[l]; 
                                    var data4 = data2.right_list[l];  

                                html += '<tr class="db-collapse">'; // db-match
                                html +=         '<td>';
                                html +=             '<div class="match-time d-flex just-between">';
                                html +=                 '<span>' + data2.time + '</span>';
                                html +=             '</div>';
                                html +=         '</td>';

                                html +=         '<td>';
                                html +=             '<div class="match-time d-flex just-between">';
                                html +=                 '<span>' + data3[0] + ' <span style="padding-left:5px; color: #46a; padding-right:5px;">' + data3[1] + '</span> <b>(' + data3[2] + ')</b></span>';
                                html +=             '</div>';
                                html +=         '</td>';

                                html +=         '<td>';
                                html +=             '<div class="match-time d-flex just-between">';
                                html +=                 '<span>' + data4[0] + ' <span style="padding-left:5px; color: #46a; padding-right:5px;">' + data4[1] + '</span> <b>(' + data4[2] + ')</b></span>';
                                html +=             '</div>';
                                html +=         '</td>';

                                html +=         '<td class="row-span" colspan="2" >';
                                html +=             '<div class="league-name">';
                                    if (link) {
                                    html +=             '<a href="{{ url('/ราคาบอลไหล?link=') }}'+ link +'" target="_BLANK">ดูราคา<br>บอลไหล</a>';
                                    }
                                html +=             '</div>';
                                html +=         '</td>';
                                html += '</tr>';  

                                }

                                }
                            



                            }
                            f++
                        }
                    }
                    //console.log(arr)
                    $('#tr_loading').remove();
                    $('#tbody-ffp').append(html);
                }
            }
        }
    </script>
    </body>
</html>
