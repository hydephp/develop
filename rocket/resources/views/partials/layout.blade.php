<!DOCTYPE html>
<html lang="en">
<head @class(['windows' => (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')])>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Hyde Rocket - @yield('title', 'Dashboard')</title>
</head>
<body>
	<main>
		@yield('content')
	</main>
</body>
</html>
