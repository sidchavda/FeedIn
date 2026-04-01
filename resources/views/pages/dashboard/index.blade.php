
@extends('layouts.master')

@section('styles')

        <!-- <link rel="stylesheet" href="{{asset('build/assets/libs/jsvectormap/css/jsvectormap.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}"> -->

@endsection

@section('content')

                <!-- Start::app-content -->
                <div class="main-content app-content">
                    <div class="container-fluid">

                        <!-- Page Header -->
                        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                            <h1 class="page-title fw-medium fs-24 mb-0">Dashboard</h1>
                        </div>
                        <!-- Page Header Close -->

                        <div class="row">
                            <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-primary-transparent">
                                                    <i class="ti ti-user icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Total Users</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">{{$userCount}}</h5>
                                                    <!-- <div class="badge bg-success-transparent py-1">+2.02%</div> -->
                                                </div>
                                                <!-- <a href="javascript:void(0);" class="text-muted fs-12">since started</a> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-success-transparent">
                                                    <i class="ti ti-news icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Total News</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">{{$newsCount}}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-info-transparent">
                                                      <i class="ti ti-folder side-menu__icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Total Categories</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">{{$categoryCount}}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-warning-transparent">
                                                    <i class="ti ti-language icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Total Languages</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">{{$languageCount}}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-success-transparent">
                                                    <i class="ti ti-user-check icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Qualified Leads</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">99</h5>
                                                    <div class="badge bg-danger-transparent py-1">-2.6%</div>
                                                </div>
                                                <a href="javascript:void(0);" class="text-muted fs-12">since started</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!-- <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-secondary-transparent">
                                                    <i class="ti ti-user-exclamation icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Potential Leads</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">99</h5>
                                                    <div class="badge bg-success-transparent py-1">+35.6%</div>
                                                </div>
                                                <a href="javascript:void(0);" class="text-muted fs-12">since started</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!-- <div class="col-xl-3">
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-top gap-2">
                                            <div class="me-1">
                                                <span class="avatar icon-badge bg-danger-transparent">
                                                    <i class="ti ti-user-off icon"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="text-muted fs-13 fw-medium">Disqualified Leads</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="d-block fw-bold fs-20 mb-1">99</h5>
                                                    <div class="badge bg-success-transparent py-1">2%</div>
                                                </div>
                                                <a href="javascript:void(0);" class="text-muted fs-12">since started</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>

                       

                    </div>
                </div>
                <!-- End::app-content -->

@endsection

@section('scripts')

@endsection
