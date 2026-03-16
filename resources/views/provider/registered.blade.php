@extends('layouts.app')

@section('title', 'Registration Successful')

@section('content')
<div class="container py-5 text-center">
    <h3>Registration Successful 🎉</h3>
    <p>Your account is pending approval.</p>
    <a href="{{ route('provider.login') }}" class="btn btn-primary">
        Proceed to Login
    </a>
</div>
@endsection
