@extends('layouts.master')

@section('styles')

@endsection

@section('content')

    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-2 page-header-breadcrumb">
                <h1 class="page-title fw-medium fs-24 mb-0">@if(isset($news->id))Edit @else Add @endif News</h1>
            </div>
            <!-- Page Header Close -->

            <!-- Start:: row-2 -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card">
                        <div class="card-body">
                            <form @if(isset($news->id)) action="{{ route('admin.news.update',$news->id) }}" @else action="{{ route('admin.news.store') }}" @endif method="post" enctype="multipart/form-data">
                            @csrf
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="@if(isset($news->id)){{ $news->title }}@elseif(old('title')){{ old('title') }}@endif" required>
                                    @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="link" class="form-label">Link</label>
                                    <input type="text" class="form-control @error('link') is-invalid @enderror" id="link" name="link" value="@if(isset($news->id)){{ $news->link }}@elseif(old('link')){{ old('link') }}@endif">
                                    @error('link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="country_id" class="form-label">Country</label>
                                    <select class="form-control @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
                                        <option value="">Select Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->id }}" @if((isset($news->country_id) && $news->country_id == $country->id) || old('country_id') == $country->id) selected @endif>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="state_id" class="form-label">State</label>
                                    <select class="form-control @error('state_id') is-invalid @enderror" id="state_id" name="state_id">
                                        <option value="">Select Country first</option>
                                        @if(isset($news->state_id) && $news->state_id)
                                            @php
                                                $selectedState = \App\Models\State::find($news->state_id);
                                            @endphp
                                            @if($selectedState)
                                                <option value="{{ $selectedState->id }}" selected>{{ $selectedState->name }}</option>
                                            @endif
                                        @endif
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}" @if((isset($news->state_id) && $news->state_id == $state->id) || old('state_id') == $state->id) selected @endif>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="language_id" class="form-label">Language</label>
                                    <select class="form-control @error('language_id') is-invalid @enderror" id="language_id" name="language_id" required>
                                        <option value="">Select Language</option>
                                        @foreach($languages as $language)
                                            <option value="{{ $language->id }}" @if((isset($news->language_id) && $news->language_id == $language->id) || old('language_id') == $language->id) selected @endif>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('language_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                 <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Select Language first</option>
                                        @if(isset($news->category_id) && $news->category_id)
                                            @php
                                                $selectedCategory = \App\Models\Category::find($news->category_id);
                                            @endphp
                                            @if($selectedCategory)
                                                <option value="{{ $selectedCategory->id }}" selected>{{ $selectedCategory->name }}</option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">@if(isset($news->id)){{ $news->description }}@elseif(old('description')){{ old('description') }}@endif</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="author" class="form-label">Author</label>
                                    <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="@if(isset($news->id)){{ $news->author }}@elseif(old('author')){{ old('author') }}@endif">
                                    @error('author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                                    @if(isset($news->id) && $news->image)
                                        <div class="mt-2">
                                            <img src="{{ asset($news->image) }}" alt="News Image" width="100">
                                        </div>
                                    @endif
                                    @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="newsStatus" name="status" value="1"
                                        @if(old('status', isset($news) ? $news->status : 1) == 1) checked @endif>
                                    <label class="form-check-label" for="newsStatus">Status (Active or Deactive)</label>
                                </div>
                                
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                    <a href="{{route('admin.news.index')}}" class="btn btn-primary">Back</a>
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
    <script>
        // Store selected values for edit mode
        const selectedCategoryId = {{ isset($news->category_id) && $news->category_id ? $news->category_id : 'null' }};
        const selectedStateId = {{ isset($news->state_id) && $news->state_id ? $news->state_id : 'null' }};

        // Load categories based on selected language
        document.getElementById('language_id').addEventListener('change', function() {
            const languageId = this.value;
            const categorySelect = document.getElementById('category_id');
            
            if (!languageId) {
                categorySelect.innerHTML = '<option value="">Select Language first</option>';
                return;
            }
            
            // Show loading
            categorySelect.innerHTML = '<option value="">Loading categories...</option>';
            
            // Fetch categories for selected language
            fetch(`{{ route('admin.categories.byLanguage', '') }}/${languageId}`)
                .then(response => response.json())
                .then(data => {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        // Pre-select if matches the saved category
                        if (selectedCategoryId && category.id == selectedCategoryId) {
                            option.selected = true;
                        }
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                });
        });

        // Load states based on selected country
        document.getElementById('country_id').addEventListener('change', function() {
            const countryId = this.value;
            const stateSelect = document.getElementById('state_id');
            
            if (!countryId) {
                stateSelect.innerHTML = '<option value="">Select Country first</option>';
                return;
            }
            
            // Show loading
            stateSelect.innerHTML = '<option value="">Loading states...</option>';
            
            // Fetch states for selected country
            fetch(`{{ route('admin.states.byCountry', '') }}/${countryId}`)
                .then(response => response.json())
                .then(data => {
                    stateSelect.innerHTML = '<option value="">Select State</option>';
                    data.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.id;
                        option.textContent = state.name;
                        // Pre-select if matches the saved state
                        if (selectedStateId && state.id == selectedStateId) {
                            option.selected = true;
                        }
                        stateSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    stateSelect.innerHTML = '<option value="">Error loading states</option>';
                });
        });
    </script>
    // Trigger change event on page load if language is already selected
    @if(old('language_id') || (isset($news) && $news->language_id))
    <script>
        document.getElementById('language_id').dispatchEvent(new Event('change'));
    </script>
    @endif
    // Trigger change event on page load if country is already selected
    @if(old('country_id') || (isset($news) && $news->country_id))
    <script>
        document.getElementById('country_id').dispatchEvent(new Event('change'));
    </script>
    @endif
@endsection
