@extends('layouts.master')

@section('styles')

@endsection

@section('content')

                <!-- Start::app-content -->
                <div class="main-content app-content">
                    <div class="container-fluid">

                        <!-- Page Header -->
                        <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                            <h1 class="page-title fw-medium fs-24 mb-0">@if(isset($user->id))Edit @else Add @endif user</h1>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start:: row-2 -->
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <form @if(isset($user->id)) action="{{ route('admin.user.update',$user->id) }}" @else action="{{ route('admin.user.store') }}"" @endif method="post">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="@if(isset($user->id)){{ $user->name }}@elseif(old('name')){{ old('name') }}@endif" required>
                                                @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="@if(isset($user->id)){{ $user->email }}@elseif(old('email')){{ old('email') }}@endif" required>
                                                @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="password" class="form-label">By default password is 123456 for all users</label>
                                                <!-- <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" @if(!isset($user->id)) required @endif>
                                                @if(isset($user->id))
                                                <small>Password will be updated only if you enter it. Leave it blank to keep it as it is.</small>
                                                @endif
                                                @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror -->
                                            </div>
                                            <!-- <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="isAdmin" name="is_admin" value="1" @if((isset($user->id) && $user->is_admin=='1')) checked @endif>
                                                <label class="form-check-label" for="isAdmin">Is admin?</label>
                                            </div> -->
                                            <button class="btn btn-info" type="submit">Submit</button>
                                            <a href = "{{route('admin.users.index')}}" class="btn btn-info-transparent">Back</a>
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
