@extends('layouts.master')

@section('styles')

@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">@if(isset($smtp->id))Edit @else Add @endif SMTP Credential</h1>
            </div>
            <!-- Page Header Close -->

            <!-- Start:: row-2 -->
            <div class="row">
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <form @if(isset($smtp->id)) action="{{ route('admin.smtp.update',$smtp->id) }}" @else action="{{ route('admin.smtp.store') }}" @endif method="post">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">SMTP Host Name</label>
                                    <input type="text" class="form-control @error('server') is-invalid @enderror" id="name" name="server" value="@if(isset($smtp->server)){{ $smtp->server }}@elseif(old('server')){{ old('server') }}@endif" required>
                                    @error('server')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-2">
                                    <label for="name" class="form-label">Port</label>
                                    <input type="text" class="form-control @error('port') is-invalid @enderror" id="name" name="port" value="@if(isset($smtp->port)){{ $smtp->port }}@else {{ old('port') }}@endif" required>
                                    @error('port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Username</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" id="name" name="username" value="@if(isset($smtp->id)){{ $smtp->username }}@elseif(old('username')){{ old('username') }}@endif" >
                                    @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Password</label>
                                    <input type="text" class="form-control @error('password') is-invalid @enderror" id="name" name="password" value="@if(isset($smtp->password)){{ $smtp->password }}@elseif(old('password')){{ old('password') }}@endif" required>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Security Type</label>
                                    <select class="form-control" name="security">
                                        <option value="0">None</option>
                                        <option value="1">TLS</option>
                                        <option value="2">SSL</option>
                                    </select>

                                    @error('security')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if(!empty($category))
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="isAdmin" name="status" value="1" @if($category->is_active == 'Active')  checked @endif>
                                        <label class="form-check-label" for="isAdmin">Status (Active or Deactive)</label>
                                    </div>
                                @endif
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                    <a href="{{route('admin.category.index')}}" class="btn btn-primary">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End:: row-2 -->

        </div>
    </div>
    <!-- End::app-content -->

@endsection

@section('scripts')
@endsection
