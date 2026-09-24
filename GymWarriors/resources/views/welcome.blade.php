<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymWarriors</title>
</head>
<body>

    <h1>GymWarriors</h1>

    @auth
        <p>
            Sveiki, {{ auth()->user()->first_name }}!
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Iziet</button>
        </form>
    @else
        <p>
            <a href="{{ route('login') }}">Pieslēgties</a>
        </p>

        <p>
            <a href="{{ route('register') }}">Reģistrēties</a>
        </p>
    @endauth

</body>
</html>