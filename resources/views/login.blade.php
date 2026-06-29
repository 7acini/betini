<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login | Betini ERP Oficina</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="betini-login-page">
        <div id="login-app"></div>

        @php
            $loginPayload = [
                'action' => route('login.store'),
                'old' => ['email' => old('email')],
                'errors' => [
                    'email' => $errors->first('email'),
                    'password' => $errors->first('password'),
                ],
            ];
        @endphp

        <script>
            window.betiniLogin = {{ Illuminate\Support\Js::from($loginPayload) }};
        </script>
    </body>
</html>
