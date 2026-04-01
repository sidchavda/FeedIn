@extends('layouts.master')

@section('styles')

@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">@if(isset($language->id))Edit @else Add @endif Language</h1>
            </div>
            <!-- Page Header Close -->

            <!-- Start:: row-2 -->
            <div class="row">
                <div class="col-xl-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <form @if(isset($language->id)) action="{{ route('admin.language.update',$language->id) }}" @else action="{{ route('admin.language.store') }}" @endif method="post">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="@if(isset($language->id)){{ $language->name }}@elseif(old('name')){{ old('name') }}@endif" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="code" class="form-label">Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="@if(isset($language->id)){{ $language->code }}@elseif(old('code')){{ old('code') }}@endif" required>
                                    @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="languageStatus" name="status" value="1"
                                        @if(old('status') || (!empty($language) && $language->is_active == 'Active') || empty($language)) checked @endif>
                                    <label class="form-check-label" for="languageStatus">Status (Active or Deactive)</label>
                                </div>
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                    <a href="{{route('admin.language.index')}}" class="btn btn-primary">Back</a>
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
