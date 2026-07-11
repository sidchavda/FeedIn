@extends('layouts.master')

@section('styles')
<style>
    .bulk-action-bar { display: none; }
    .bulk-action-bar.show { display: flex; }
</style>
@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">News</h1>
                <a class="btn btn-primary shadow-sm btn-wave" href="{{ route('admin.news.add') }}">Add news</a>
            </div>
            <!-- Page Header Close -->

            <!-- Start:: row-2 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <!-- Search Form -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <form method="GET" action="{{ route('admin.news.index') }}">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="Search by title, category, or author..." value="{{ request('search') }}">
                                            <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Search</button>
                                            @if(request('search'))
                                                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary"><i class="ri-close-line"></i> Clear</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- End Search Form -->

                            <!-- Bulk Actions -->
                            <div class="bulk-action-bar align-items-center gap-2 mb-3" id="bulkActionBar">
                                <span id="selectedCount" class="text-muted">0 selected</span>
                                <button type="button" onclick="confirmBulkDelete()" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i> Delete</button>
                                <button type="button" onclick="confirmBulkStatus(0)" class="btn btn-warning btn-sm"><i class="ri-close-circle-line"></i> Disable</button>
                                <button type="button" onclick="confirmBulkStatus(1)" class="btn btn-success btn-sm"><i class="ri-checkbox-circle-line"></i> Enable</button>
                            </div>

                            <form id="deleteMultipleForm" method="POST" action="{{ route('admin.news.deleteMultiple') }}">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-striped w-100">
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"></th>
                                            <th>Title</th>
                                            <th>Link</th>
                                            <th>Category</th>
                                            <th>Language</th>
                                            <th>Country</th>
                                            <th>State</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Created at</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if ($news->count() == 0)
                                            <tr>
                                                <td colspan="11">No news to display.</td>
                                            </tr>
                                        @endif
                                        @foreach($news as $item)
                                            <tr>
                                                <td><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="news-checkbox" onchange="updateBulkBar()"></td>
                                                <td>{{ $item->title }}</td>
                                                <td><a href="{{ $item->link }}" target="_blank">{{ $item->link }}</a></td>
                                                <td>{{ $item->category?->name ?? '-' }}</td>
                                                <td>{{ $item->language ? $item->language->name : '-' }}</td>
                                                <td>{{ $item->country ? $item->country->name : '-' }}</td>
                                                <td>{{ $item->state ? $item->state->name : '-' }}</td>
                                                <td>{{ $item->author }}</td>
                                                <td>
                                                    <a href="javascript:void(0);" onclick="toggleStatus('{{$item->id}}', {{$item->status}})" class="badge {{ $item->status == 1 ? 'bg-success' : 'bg-danger' }} text-decoration-none">
                                                        {{ $item->status == 1 ? 'Active' : 'Deactive' }}
                                                    </a>
                                                </td>
                                                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.news.edit',$item->id) }}" class="btn btn-icon btn-sm btn-info-transparent rounded-pill"><i class="ri-edit-line"></i></a>
                                                    <a href="javascript:void(0);" onclick="deleteNews('{{$item->id}}')" class="btn btn-icon btn-sm btn-danger-transparent rounded-pill"><i class="ri-delete-bin-line"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>

                            <form id="bulkStatusForm" method="POST" action="{{ route('admin.news.toggleBulkStatus') }}">
                                @csrf
                                <input type="hidden" name="status" id="bulkStatusValue">
                            </form>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end mt-3">
                                {{ $news->links() }}
                            </div>
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
        function deleteNews(newsId) {
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
                    window.location.href = "{{ route('admin.news.delete', '') }}/" + newsId;
                }
            });
        }

        function toggleStatus(id, current) {
            const action = current == 1 ? 'disable' : 'enable';
            Swal.fire({
                title: (action == 'disable' ? 'Disable' : 'Enable') + ' this news?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, ' + action + '!',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('admin.news.toggleStatus', '') }}/" + id;
                    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function toggleSelectAll(source) {
            document.querySelectorAll('.news-checkbox').forEach(cb => cb.checked = source.checked);
            updateBulkBar();
        }

        function updateBulkBar() {
            const checked = document.querySelectorAll('.news-checkbox:checked');
            const bar = document.getElementById('bulkActionBar');
            const count = document.getElementById('selectedCount');
            if (checked.length > 0) {
                bar.classList.add('show');
                count.textContent = checked.length + ' selected';
            } else {
                bar.classList.remove('show');
            }
        }

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.news-checkbox:checked');
            if (checked.length === 0) return;

            Swal.fire({
                title: 'Delete ' + checked.length + ' news?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete all!',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    document.getElementById('deleteMultipleForm').submit();
                }
            });
        }

        function confirmBulkStatus(status) {
            const checked = document.querySelectorAll('.news-checkbox:checked');
            if (checked.length === 0) return;

            const action = status == 1 ? 'enable' : 'disable';
            Swal.fire({
                title: action.charAt(0).toUpperCase() + action.slice(1) + ' ' + checked.length + ' news?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, ' + action + '!',
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = document.getElementById('bulkStatusForm');
                    // Remove old dynamic inputs
                    form.querySelectorAll('.dynamic-id').forEach(el => el.remove());
                    // Add checked IDs as hidden inputs
                    checked.forEach(cb => {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        input.className = 'dynamic-id';
                        form.appendChild(input);
                    });
                    document.getElementById('bulkStatusValue').value = status;
                    Swal.showLoading();
                    form.submit();
                }
            });
        }
    </script>
@endsection
