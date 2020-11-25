<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Inner content</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <input type="hidden" id="link" value="{{ $link }}">
                    <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
                    <div class="price-graph">
                        <div class="graph-loading">
                            <div class="l-one"></div>
                            <div class="l-two"></div>
                        </div>
                        <h4 class="top-head-graph">
                            <span id="asian_top_title"></span>
                        </h4>
                        <div id="asian_graph" class="graph-layout"></div>
                        <br>
                        <h4 class="top-head-graph">
                            <span id="over_top_title"></span>
                        </h4>
                        <div id="over_graph" class="graph-layout"></div>
                        <br>
                        <h4 class="top-head-graph">
                            <span id="one_top_title"></span>
                        </h4>
                        <div id="one_graph" class="graph-layout"></div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script type="text/javascript" src="{{ asset('js/highcharts.js') }}"></script>
        <script>
            var fakeIp = '58.18.145.72';

            $(function() {
                callGraphApi(fakeIp);
            });

            function callGraphApi(ip) {
                const params = {
                    'ip': ip,
                    detail_id: $('#link').val()
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/data-to-graph',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        // console.log(response);
                        $('.graph-loading').hide();
                        $('.top-head-graph').css('visibility', 'visible');

                        if (response) {
                            var asian = response.asian;
                            var over = response.over;
                            var one = response.one;
                            arrangeAsianGraphData(asian);
                            arrangeOverGraphData(over);
                            arrangeOneGraphData(one);
                        }
                    },
                    error: function(response) {
                        console.log(response);
                        $('.graph-loading').hide();
                        $('.top-head-graph').css('visibility', 'visible');
                    }
                });
            }

            function arrangeAsianGraphData(response) {
                const gTopTitle = response.name;
                const timeList = response.time_list;
                const teamSeries = response.team_series;
                // const theMin = response.min; // Math.floor(response.min);

                let graphTitle = ''; // 'Information at: ' + dateTime; // 20200201-1727

                $('#asian_top_title').html(gTopTitle);

                const graphDatas = {
                    title: {
                        text: graphTitle
                    },
                    subtitle: {
                        text: '' // Source: thesolarfoundation.com
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        // min: theMin
                        tickInterval: 0.5
                    },
                    xAxis: {
                        categories: timeList
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle'
                    },

                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: true
                        },
                        series: {
                            label: {
                                connectorAllowed: false
                            }
                        }
                    },

                    series: teamSeries,

                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                };

                plotGraph(graphDatas);
            }

            function plotGraph(graphDatas) {
                // document.addEventListener('DOMContentLoaded', function () {
                    var myChart = Highcharts.chart('asian_graph', graphDatas);
                // });
            }

            function arrangeOverGraphData(response) {
                const gTopTitle = response.name;
                const timeList = response.time_list;
                const teamSeries = response.team_series;
                // const theMin = response.min; // Math.floor(response.min);

                let graphTitle = ''; // 'Information at: ' + dateTime; // 20200201-1727

                $('#over_top_title').html(gTopTitle);

                const graphDatas = {
                    title: {
                        text: graphTitle
                    },
                    subtitle: {
                        text: '' // Source: thesolarfoundation.com
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        // min: theMin
                        tickInterval: 0.5
                    },
                    xAxis: {
                        categories: timeList
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle'
                    },

                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: true
                        },
                        series: {
                            label: {
                                connectorAllowed: false
                            }
                        }
                    },

                    series: teamSeries,

                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                };

                plotOverGraph(graphDatas);
            }

            function plotOverGraph(graphDatas) {
                // document.addEventListener('DOMContentLoaded', function () {
                    var myChart = Highcharts.chart('over_graph', graphDatas);
                // });
            }
            
            function arrangeOneGraphData(response) {
                const gTopTitle = response.name;
                const timeList = response.time_list;
                const teamSeries = response.team_series;
                // const theMin = response.min; // Math.floor(response.min);

                let graphTitle = ''; // 'Information at: ' + dateTime; // 20200201-1727

                $('#one_top_title').html(gTopTitle);

                const graphDatas = {
                    title: {
                        text: graphTitle
                    },
                    subtitle: {
                        text: '' // Source: thesolarfoundation.com
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        // min: theMin
                        tickInterval: 0.5
                    },
                    xAxis: {
                        categories: timeList
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle'
                    },

                    plotOptions: {
                        line: {
                            dataLabels: {
                                enabled: true
                            },
                            enableMouseTracking: true
                        },
                        series: {
                            label: {
                                connectorAllowed: false
                            }
                        }
                    },

                    series: teamSeries,

                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                };

                plotOneGraph(graphDatas);
            }

            function plotOneGraph(graphDatas) {
                // document.addEventListener('DOMContentLoaded', function () {
                    var myChart = Highcharts.chart('one_graph', graphDatas);
                // });
            }
        </script>
    </body>
</html>
