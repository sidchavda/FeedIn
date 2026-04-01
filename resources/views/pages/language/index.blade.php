@extends('layouts.master')

@section('styles')

@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">Languages</h1>
                <a class="btn btn-primary shadow-sm btn-wave" href="{{ route('admin.language.add') }}">Add language</a>
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
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Created at</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if ($languages->count() == 0)
                                        <tr>
                                            <td colspan="10">No languages to display.</td>
                                        </tr>
                                    @endif
                                    @foreach($languages as $language)
                                        <tr>
                                            <td>{{ $language->name }}</td>
                                            <td>{{ $language->code }}</td>
                                            <td>{{ $language->is_active }}</td>
                                            <td>{{ $language->created_at->format('d-m-Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.language.edit',$language->id) }}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                                <a href="javascript:void(0);" onclick="deleteLanguage('{{$language->id}}')" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill"><i class="ri-delete-bin-line"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $languages->links() }}
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
        function deleteLanguage(languageId) {
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
                        url: "{{ url('language/delete') }}/" + languageId,
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
