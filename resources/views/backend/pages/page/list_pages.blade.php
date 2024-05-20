@extends('admin.admin_dashboard')
@section('admin')
    <div class="page-content">
        <div class="page-breadcrumb d-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pages List</div>
            <div class="ms-auto">
                <a href="{{ route('create.page') }}" class="btn btn-primary">Add New Page</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pages as $page)
                                <tr>
                                    <td>{{ $page->title }}</td>
                                    <td>/{{ $page->page_slug }}</td>
                                    <td>
                                        <a href="{{ route('edit.page', $page->id) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('destroy.page', $page->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
