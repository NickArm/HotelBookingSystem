@extends('frontend.main_master')
@section('main')
    <!-- Inner Banner -->
    <div class="inner-banner inner-bg3" style="background-image: url('/{{ $page->header_image }}');">
        <div class="container">
            <div class="inner-title">
                <div class="page-title">
                    <h2>{{ $page->title }}</h2>
                </div>
                <ul>
                    <li>
                        <a href="/">Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>Page Details </li>
                </ul>
                <h3>{{ $page->post_titile }}</h3>
            </div>
        </div>
    </div>
    <!-- Inner Banner End -->

    <!-- Blog Details Area -->
    <div class="blog-details-area pt-100 pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="blog-article">
                        <div class="article-content">
                            <p>
                                {!! $page->content !!}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Blog Details Area End -->
@endsection
