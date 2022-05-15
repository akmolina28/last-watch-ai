<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Last Watch AI</title>
</head>
<body>
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group mb-3">
        <input type="text" placeholder="Username" id="email" class="form-control" name="email" required
            autofocus>
        @if ($errors->has('email'))
        <span class="text-danger">{{ $errors->first('email') }}</span>
        @endif
    </div>
    <div class="form-group mb-3">
        <input type="password" placeholder="Password" id="password" class="form-control" name="password" required>
        @if ($errors->has('password'))
        <span class="text-danger">{{ $errors->first('password') }}</span>
        @endif
    </div>
    <div class="d-grid mx-auto">
        <button type="submit" class="btn btn-dark btn-block">Signin</button>
    </div>
  </form>
  @if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
  @endif
</body>