@extends('admin.admin_dashboard')
@section('admin')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Edit Page</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('list.pages') }}"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Page</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-4">
                                <form class="row g-3" action="{{ route('update.page', $page->id) }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf


                                    <div class="col-md-12">
                                        <label for="title" class="form-label">Page Title</label>
                                        <input type="text" name="title" class="form-control" id="title"
                                            value="{{ $page->title }}">
                                    </div>

                                    <div class="col-md-12">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea name="content" class="form-control" id="myeditorinstance" rows="4">{{ $page->content }}</textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="header_image" class="form-label">Header Image</label>
                                        <input class="form-control" name="header_image" type="file" id="header_image">
                                        @if ($page->header_image)
                                            <img src="{{ asset('storage/' . $page->header_image) }}" alt="Header Image"
                                                class="img-fluid mt-2" style="width: 100px;">
                                        @endif
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary px-4">Update Page</button>
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
