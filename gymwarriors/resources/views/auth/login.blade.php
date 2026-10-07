<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pieslēgšanās — GymWarriors</title>
</head>
<body>

<h1>GymWarriors pieslēgšanās</h1>

@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div>
        <label for="email">E-pasts:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            required
        >
    </div>

    <div>
        <label for="password">Parole:</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >
    </div>

    <button type="submit">Pieslēgties</button>
</form>
<p>
    Nav konta?
    <a href="{{ route('register') }}">Reģistrēties</a>
</p>

</body>
</html>