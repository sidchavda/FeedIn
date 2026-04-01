
@extends('layouts.custom-master1')

@section('styles')

@endsection

@section('content')

        <div class="container-lg">
            <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
                <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
                    <div class="my-4 d-flex justify-content-center">
                        <a href="{{url('index')}}">
                            <!-- <img src="{{asset('build/assets/img/brand-logos/desktop-white.png')}}" alt="logo"> -->
                            <h2 class="text-white">Feed In - Admin</h2>
                        </a>
                    </div>
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="authentication-cover">
                                <div class="aunthentication-cover-content">
                                    <p class="h4 fw-bold mb-2 text-center">Sign in</p>
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="row gy-3">
                                            <div class="col-xl-12">
                                                <label for="signup-Email" class="form-label text-default op=8">Email address</label>
                                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email" autofocus>
                                                @error('email')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="col-xl-12">
                                                <label class="form-label text-default d-block">Password</label>
                                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required placeholder="password" autocomplete="current-password">
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="col-xl-12 d-grid mt-2">
                                                <button type="submit" class="btn btn-lg btn-primary">Sign In</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection

@section('scripts')



@endsection
