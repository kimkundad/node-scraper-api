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
                                <th class="match-time">league_name</th>
                                <th class="home-team">home_team</th>
                                <th class="vs">away_team</th>
                                <th class="away-team">event_time</th>
                                <th class="league-name">ราคาบอลไหล</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_ffp">
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
                    
                    {{-- <div class="d-flex alit-center just-between league-info">
                        <div class="team-logo hm">
                            <img src="{{ asset('images/logo-team.jpg') }}" alt="" class="img-fluid">
                        </div>
                        <div class="match-info d-flex df-col">
                            <div class="vs-info d-flex just-between">
                                <span class="h"></span>
                                <span class="mvs text-target"></span>
                                <span class="a"></span>
                            </div>
                            <div class="l-info d-flex just-center">
                                <div class="l-name"></div>
                                <div class="ev-time"></div>
                            </div>
                            <div class="score-info"></div>
                        </div>
                        <div class="team-logo aw">
                            <img src="{{ asset('images/logo-team.jpg') }}" alt="" class="img-fluid">
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />

        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>

        <script>
            var fakeIp = '58.18.145.72';
            var thisHost = $('#base_url').val();

            $(function() {
                callContentApi(fakeIp, thisHost);
            });

            function callContentApi(ip, apiHost) {
                const params = {
                    'ip': ip,
                    detail_id: $('#detail_id').val()
                };
    
                $.ajax({
                    url: apiHost + '/api/prediction', // => arrange-content
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        console.log(response);
                        // <tr>
                        //     <td class="match-time">league_name</td>
                        //     <td class="home-team">home_team</td>
                        //     <td class="vs">away_team</td>
                        //     <td class="away-team">event_time</td>
                        //     <td class="league-name">ราคาบอลไหล</td>
                        // </tr>

                        /*
                        if (response.home_team) {
                            $('.h').html(response.home_team);
                        }
                        // if (response.home_logo) {
                        //     $('.team-logo.hm').html('<img src="' + response.home_logo + '">');
                        // }
                        if (response.away_team) {
                            $('.a').html(response.away_team);
                        }
                        // if (response.away_logo) {
                        //     $('.team-logo.aw').html('<img src="' + response.away_logo + '">');
                        // }

                        $('.mvs').html('');

                        if (response.event_time) {
                            $('.ev-time').html(response.event_time);
                        }

                        if (response.league_name) {
                            $('.l-name').html(response.league_name + ' : ');
                            $('.league-info').css('visibility', 'visible');
                        }

                        if (response.asian_content) {
                            var asian = response.asian_content;
                            showContent(asian.matches, 'asian', ip);
                        }

                        if (response.over_content) {
                            var over = response.over_content;
                            showContent(over.matches, 'over', ip);
                        }

                        if (response.one_content) {
                            var one = response.one_content;
                            showContent(one.matches, 'one', ip);
                        }
                        */
                    },
                    error: function(response) {
                        console.log(response);
                        $('.graph-loading').hide();
                        $('.crd-content').css('visibility', 'visible');
                    }
                });
            }

            function showContent(response, mode, ip) {
                var minusTeam = '';
                var doCount = 0;
                var html = '';

                if (response.length > 0) {
                    for(var i = 0; i < response.length; i++) {
                        var row = response[i];
                        html += '<div class="db-collapse">';
                        html +=     '<div class="db-match">';
                        html +=         '<span class="home-team graph-content d-flex just-between">';
                        html +=             '<span class="w-60-pc">' + row.team_left + '</span>';
                        
                        var leftClass =  (row.score_right_mid) ? 'just-between' : 'just-end';

                        html +=             '<span class="w-40-pc d-flex ' + leftClass + ' graph-score">';
                        if (row.score_left_mid) {
                            html +=            '<span>' + row.score_left_mid + '</span>';
                            if (mode == 'asian' && (row.score_left_mid < 0)) {
                                minusTeam = row.team_left;
                            }
                        }
                        if (row.score_left_last) {
                            html +=            '<span>' + row.score_left_last + '</span>';
                        }
                        html +=            '</span>';
                        html +=        '</span>';
                        

                        if (row.draw_text) {
                            html +=         '<span class="draw-text d-flex just-between">';
                            html +=             '<span>' + row.draw_text + '</span>';
                            html +=             '<span>' + row.draw_score + '</span>';
                            html +=        '</span>';
                        }

                        var rightClass =  (row.score_right_mid) ? 'just-between' : 'just-end';

                        html +=         '<span class="away-team graph-content d-flex just-between">';
                        html +=             '<span class="w-60-pc">' + row.team_right + '</span>';
                        html +=             '<span class="w-40-pc d-flex ' + rightClass + ' graph-score">';
                        if (row.score_right_mid) {
                            html +=             '<span>' + row.score_right_mid + '</span>';
                            if (mode == 'asian' && (row.score_right_mid < 0)) {
                                minusTeam = row.team_right;
                            }
                        }
                        if (row.score_right_last) {
                            html +=             '<span>' + row.score_right_last + '</span>';
                        }
                        html +=             '</span>';
                        html +=         '</span>';
                        html +=     '</div>';
                        html += '</div>';
                    }

                    $('.' + mode + '-content-box').html(html);

                    // console.log(mode, doCount, minusTeam, hName, aName);

                    if ((mode == 'asian' && doCount == 0) || (mode == 'asian' && doCount == 0 && minusTeam == '')) {
                        var hName = $('.h').html();
                        var aName = $('.a').html();

                        if (minusTeam == hName) {
                            $('.h').css('color', 'red');
                            doCount++;
                        }

                        if (minusTeam == aName) {
                            $('.a').css('color', 'red');
                            doCount++;
                        }
                    }
                }
            }

            function scoreInfo(teamSeries, mode) {
                if (teamSeries.length > 0) {
                    var findTheGoal = 0;
                    var teamName = '';
                    var num = 0;
                    var text = '';

                    for (var i = 0; i < teamSeries.length; i++) {
                        num = teamSeries[i].data[0];
                        if (num > findTheGoal && num < 2) {
                            findTheGoal = num;
                            teamName = teamSeries[i].name;
                        }
                    }

                    var showTheTarget = '';
                    if (teamName) {
                        var tgList = teamName.split(':');
                        showTheTarget = tgList[tgList.length - 1];
                    }

                    if (mode == 'asian') {
                        $('.mvs').html(showTheTarget);
                        text = 'Asian Handicap: <span class="text-target">' + showTheTarget + '</span>';
                    } else if (mode == 'over') {
                        text = ' | Over / Under: <span class="text-target">' + showTheTarget + '</span>';
                    }

                    $('.score-info').append(text);
                }
            }
        </script>
    </body>
</html>
