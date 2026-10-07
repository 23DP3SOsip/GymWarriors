<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācija — GymWarriors</title>
</head>
<body>

<h1>GymWarriors reģistrācija</h1>

@if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div>
        <label for="first_name">Vārds:</label>
        <input
            type="text"
            id="first_name"
            name="first_name"
            value="{{ old('first_name') }}"
            required
        >
    </div>

    <div>
        <label for="last_name">Uzvārds:</label>
        <input
            type="text"
            id="last_name"
            name="last_name"
            value="{{ old('last_name') }}"
            required
        >
    </div>

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
        <label for="phone">Tālrunis:</label>
        <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone') }}"
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

    <button type="submit">Reģistrēties</button>
</form>

<p>
    Jau ir konts?
    <a href="{{ route('login') }}">Pieslēgties</a>
</p>

</body>
</html>