
@extends('layouts.master')

@section('styles')

@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">Categories</h1>
                <a class="btn btn-primary shadow-sm btn-wave" href="{{ route('admin.category.add') }}">Add category</a>
            </div>
            <!-- Page Header Close -->

            <!-- Start:: row-2 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table text-nowrap table-striped w-100">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Language</th>
                                        <th>Status</th>
                                        <th>Created at</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if ($categories->count() == 0)
                                        <tr>
                                            <td colspan="10">No users to display.</td>
                                        </tr>
                                    @endif
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>{{ $category->name }}</td>
                                            <td>{{ !empty($category->language) ? $category->language->name : '-' }}</td>
                                            <td>{{ $category->is_active }}</td>
                                            <td>{{ $category->created_at->format('d-m-Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.category.edit',$category->id) }}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                                <a href="javascript:void(0);" onclick="deleteCatgeory('{{$category->id}}')" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill"><i class="ri-delete-bin-line"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $categories->links() }}
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
    <script>
        function deleteCatgeory(catId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        type: "GET",
                        url: "{{ route('admin.category.delete', '') }}/" + catId,
                        success: function( response ) {
                            if(response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.msg,
                                    'success'
                                )
                                location.reload();
                            } else {
                                Swal.showValidationMessage(`Request failed: ${response.msg}`)
                            }
                        }
                    });

                }
            });
        }
    </script>
@endsection
