
@extends('layouts.master')

@section('styles')

@endsection

@section('content')

                <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">Leads</h1> 
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
                                            <th>Mobile</th>
                                            <th>Father Name</th>
                                            <th>Emergency Contact</th>
                                            <th>Blood Group</th>
                                            <th>Created by</th>
                                            <th>Created at</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($leads->count() == 0)
                                        <tr>
                                            <td colspan="10">No users to display.</td>
                                        </tr>
                                        @endif
                                        @foreach($leads as $lead)
                                        <tr>
                                            <td>{{ $lead->name }}</td>
                                            <td>{{ $lead->mobile }}</td>
                                            <td>{{ $lead->father_name }}</td>
                                            <td>{{ $lead->emergency_contact }}</td>
                                            <td>{{ $lead->blood_group }}</td>
                                            <td>{{ $lead->createdBy->name ?? '-' }}</td>
                                            <td>{{ $lead->created_at->format('d-m-Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.lead.detail',$lead->id) }}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-eye-line"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $leads->links() }}
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
    function deleteUser(userId) {
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
                        url: "{{ url('user/delete') }}/" + userId,
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
