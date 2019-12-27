<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<form action="/login" method="post">
    <div class="form-group">
        <label>Email</label>
        <input type="email" class="form-control" name="email" value="{{old('email')}}">
    </div>
    <div class="form-group">
           <label>Password</label>
        <input type="password" class="form-control" name="password" value="{{old('password')}}">
    </div>
    <button type="submit">Log in</button>
    @if($errors->any())
        <p>
            Credentials Donot match
        </p>
    @endif
</form>
</body>
</html>