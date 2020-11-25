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
        <div class="container">
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

                    <input type="hidden" id="link_code" value="{{ $link }}">
                    <input type="hidden" id="dir_name" value="{{ $dir_name }}">
                    <div class="row">
                        <div class="col-12">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist"></div>
                            </nav>  
                            <div class="row">
                                <div class="col-12 raw-content"></div>
                            </div>
                            <div class="tab-content" id="nav-tabContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script>
            $(function() {
                var linkCode = $('#link_code').val();
                var urlDirName = $('#dir_name').val();

                loadDirList(linkCode, urlDirName);
            });

            function loadDirList(linkCode, urlDirName) {
                var params = {
                    'link': linkCode,
                    'dir_name': urlDirName
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/dir-list',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        var selectedDir = '';
                        if (response.length > 0) {
                            var tabs = '';
                            var content = '';

                            var aClass = '';
                            var ariaSelected = '';
                            var contentShow = '';
                            var countSelected = 0;
                            var dName = '';
                        
                            for(var i = 0; i < response.length; i++) {
                                dName = response[i].dir_name;

                                if (dName === urlDirName) {
                                    ariaSelected = 'true';
                                    aClass = 'active';
                                    contentShow = 'show';
                                    selectedDir = dName;
                                    countSelected++;
                                } else {
                                    ariaSelected = 'false';
                                    aClass = '';
                                    contentShow = '';
                                }

                                tabs += '<a class="nav-item nav-link ' + aClass + '" id="nav-' + dName + '-tab" data-toggle="tab" href="#nav-' + dName + '" role="tab" aria-controls="nav-' + dName + '" aria-selected="' + ariaSelected + '" onclick="loadContentDetail(\'' + linkCode + '\', \'' + dName + '\')">' + dName + '</a>';
                                content += '<div class="tab-pane fade ' + contentShow + ' ' + aClass + '" id="nav-' + dName + '" role="tabpanel" aria-labelledby="nav-' + dName + '-tab"></div>';
                            }

                            $('#nav-tab').html(tabs);
                            $('#nav-tabContent').html(content);

                            if (countSelected === 0) {
                                $('#nav-tab .nav-item:first-child').attr('aria-selected', 'true');
                                $('#nav-tab .nav-item:first-child').addClass('active');
                                $('#nav-tabContent .tab-pane:first-child').addClass('show');
                                $('#nav-tabContent .tab-pane:first-child').addClass('active');
                                selectedDir = $('#nav-tab .nav-item:first-child').html();
                            }

                            loadContentDetail(linkCode, selectedDir);
                        }
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }

            function loadContentDetail(linkCode, selectedDir) {
                $('.raw-content').html('');
                $('#nav-tabContent #nav-' + selectedDir).html('');

                var params = {
                    'link': linkCode,
                    'dir_name': selectedDir
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/content-detail',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        // console.log(response);
                        arrangeContent(response, selectedDir);
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }

            function arrangeContent(response, selectedDir) {
                // console.log(response);
                var masterData = response.master_data;
                var arrangeData = response.arrange_data;
                // console.log(arrangeData);
                var htmlContent = '';
                var htmlArrange = '';
                // if (masterData) {
                //     htmlContent = masterData.content;
                //     $('.raw-content').html(htmlContent);
                // }
                if (arrangeData.length > 0) {
                    var tableHtml = '';
                    tableHtml += '<table class="table table-condensed ct-dt">';
                    tableHtml +=    '<thead>';
                    tableHtml +=        '<tr>';
                    tableHtml +=            '<th class="mwdth-100">Home team</th>';
                    tableHtml +=            '<th>Mid score</th>';
                    tableHtml +=            '<th>Score</th>';
                    tableHtml +=            '<th colspan="2">Draw</th>';
                    tableHtml +=            '<th>Away team</th>';
                    tableHtml +=            '<th>Score mid</th>';
                    tableHtml +=            '<th>Score</th>';
                    tableHtml +=        '</tr>';
                    tableHtml +=    '</thead>';
                    for(var i = 0; i < arrangeData.length; i++) {
                        var row = arrangeData[i];
                        var matches = arrangeData[i].matches;
                        if (matches.length > 0) {
                            tableHtml +=        '<tr>';
                            tableHtml +=            '<td colspan="8" class="top-head">' + row.top_head + '</td>';
                            tableHtml +=        '<tr>';

                            for(var j = 0; j < matches.length; j++) {
                                tableHtml +=        '<tr>';
                                tableHtml +=            '<td>' + matches[j].team_left + '</td>';
                                tableHtml +=            '<td>' + matches[j].score_left_mid + '</td>';
                                tableHtml +=            '<td>' + matches[j].score_left_last + '</td>';
                                tableHtml +=            '<td>' + matches[j].draw_text + '</td>';
                                tableHtml +=            '<td>' + matches[j].draw_score + '</td>';
                                tableHtml +=            '<td>' + matches[j].team_right + '</td>';
                                tableHtml +=            '<td>' + matches[j].score_right_mid + '</td>';
                                tableHtml +=            '<td>' + matches[j].score_right_last + '</td>';
                                tableHtml +=        '<tr>';
                            }
                        }
                    }
                    $('#nav-tabContent #nav-' + selectedDir).html(tableHtml);
                } else {
                    $('#nav-tabContent #nav-' + selectedDir).html('');
                }
            }
        </script>
    </body>
</html>
