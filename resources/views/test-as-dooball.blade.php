<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Test as dooball</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
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

                    <h4 class="m-3">Test as Dooball</h4>
                    <hr>
                    <input type="text" class="form-control" id="link" value="">
                    <button type="button" class="btn btn-primary" onclick="checkGraphData()">Send</button>
                </div>
            </div>
        </div>
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script>
            $(function() {
                // checkGraphData();
            });

            function checkGraphData() {
                var link = $('#link').val();
                var params = {
                    'link': link
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/data-to-graph',
                    type: 'POST',
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        console.log(response);
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }
        </script>
    </body>
</html>
