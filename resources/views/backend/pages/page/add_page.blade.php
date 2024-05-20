@extends('admin.admin_dashboard')
@section('admin')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <div class="page-content">
        <div class="page-breadcrumb d-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Add New Page</div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('store.page') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="header_image" class="form-label">Header Image</label>
                        <input type="file" class="form-control" id="header_image" name="header_image">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="myeditorinstance" name="content" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Page</button>
                </form>
            </div>
        </div>
    </div>
@endsection
