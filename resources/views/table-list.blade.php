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

                    <h4 class="m-3">ข้อมูลรายชื่อตารางในฐานข้อมูล</h4>
                    <hr>
                    <table class="table table-dark table-condensed">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>ชื่อตาราง</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($tables) > 0)
                                @foreach($tables as $key => $tb)
                                    <tr>
                                        <td>{{ ($key + 1) }}</td>
                                        <td>
                                            <a href="{{ url('/' . $tb) }}">{{ $tb }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type="hidden" id="base_url" value="{{ URL::to('/') }}" />
        <script src="{{asset('js/jquery.min.js')}}"></script>
        <script src="{{asset('js/bootstrap.min.js')}}"></script>
        <script>
            $(function() {
                // checkAPI('ffp', 'POST');
            });

            /*
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
            */
        </script>
    </body>
</html>
