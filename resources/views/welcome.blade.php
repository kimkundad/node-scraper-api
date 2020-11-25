<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

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

                    <h4 class="m-3">Page list</h4>
                    <hr>
                    <a href="/ราคาบอล">ประตูวาร์ป</a>
                    <ul>
                        @if(count($routes) > 0)
                            @foreach($routes as $route)
                                @if($route['type'] != 'API')
                                    <li>
                                        {{ $route['type'] }}&nbsp;|&nbsp;
                                        <a href="{{ $route['link'] }}">{{ $route['link'] }}</a>&nbsp;|&nbsp;
                                        <span>API:&nbsp;<span class="badge badge-light">{{ $route['api'] }}</span></span>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script>
            $(function() {
                // checkAPI('ffp', 'POST');
                // checkAPI('content-detail', 'POST');
            });

            function checkAPI(apiName, apiType) {
                const params = {
                    '_token': $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: $('#base_url').val() + '/api/' + apiName,
                    type: apiType,
                    data: params,
                    dataType: 'json',
                    cache: false,
                    success: function (response) {
                        console.log(response);

                        if (response.total == 1) {
                            // ..
                        } else {
                            // ..
                            // console.log(response);
                        }
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }
        </script>
    </body>
</html>
