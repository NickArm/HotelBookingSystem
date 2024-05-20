<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\CommentController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\RoomExtraController;
use App\Http\Controllers\Backend\RoomListController;
use App\Http\Controllers\Backend\RoomTypeController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\TeamController;
use App\Http\Controllers\Backend\TestimonialController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\ContactUsController;
use App\Http\Controllers\Frontend\FrontendRoomController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'Index']);

Route::get('/dashboard', function () {
    return view('frontend.dashboard.user_dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'UserProfile'])->name('user.profile');
    Route::post('/profile/store', [UserController::class, 'UserStore'])->name('profile.store');
    Route::get('/user/logout', [UserController::class, 'UserLogout'])->name('user.logout');
    Route::get('/user/change/password', [UserController::class, 'UserChangePassword'])->name('user.change.password');
    Route::post('/password/change/password', [UserController::class, 'ChangePasswordStore'])->name('password.change.store');
});

require __DIR__.'/auth.php';

//admin group middleware
Route::middleware(['auth', 'roles:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'AdminDashboard'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');
    Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/admin/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');

    Route::get('/admin/change/password', [AdminController::class, 'AdminChangePassword'])->name('admin.change.password');
    Route::post('/admin/password/update', [AdminController::class, 'AdminPasswordUpdate'])->name('admin.password.update');
});

Route::get('/admin/login', [AdminController::class, 'AdminLogin'])->name('admin.login');

Route::middleware(['auth', 'roles:admin'])->group(function () {
    Route::controller(TeamController::class)->group(function () {

        Route::get('/all/team', 'AllTeam')->name('all.team');
        Route::get('/add/team', 'AddTeam')->name('add.team');
        Route::post('/store/team', 'StoreTeam')->name('team.store');
        Route::get('/edit/team/{id}', 'EditTeam')->name('edit.team');
        Route::post('/update/team', 'UpdateTeam')->name('team.update');
        Route::get('/delete/team/{id}', 'DeleteTeam')->name('delete.team');

    });

    Route::controller(TeamController::class)->group(function () {
        Route::get('/book/area', 'BookArea')->name('book.area');
        Route::post('/book/area/update', 'BookAreaUpdate')->name('book.area.update');

    });

    Route::controller(RoomTypeController::class)->group(function () {
        Route::get('/room/type/list', 'RoomTypeList')->name('room.type.list');
        Route::get('/add/room/type', 'AddRoomType')->name('add.room.type');
        Route::post('/room/type/store', 'RoomTypeStore')->name('room.type.store');
    });

    Route::controller(RoomController::class)->group(function () {

        Route::get('/edit/room/{id}', 'EditRoom')->name('edit.room');
        Route::post('/update/room/{id}', 'UpdateRoom')->name('update.room');
        Route::get('/multi/image/delete/{id}', 'MultiImageDelete')->name('multi.image.delete');
        Route::post('/store/room/no/{id}', 'StoreRoomNumber')->name('store.room.no');
        Route::get('/edit/roomno/{id}', 'EditRoomNumber')->name('edit.roomno');
        Route::post('/update/roomno/{id}', 'UpdateRoomNumber')->name('update.roomno');
        Route::get('/delete/roomno/{id}', 'DeleteRoomNumber')->name('delete.roomno');
        Route::get('/delete/room/{id}', 'DeleteRoom')->name('delete.room');

        Route::post('/store-room-price/{id}', 'StoreRoomPrice')->name('store.room.price');
        Route::get('/delete-room-price/{id}', 'DeleteRoomPrice')->name('delete.room.price');
        Route::post('/room/{id}/add-price', 'addPrice')->name('add.room.price');
        Route::get('/room/price/{priceId}/delete', 'deletePrice')->name('delete.room.price');
    });

    Route::controller(BookingController::class)->group(function () {
        Route::get('/booking/list', 'BookingList')->name('booking.list');
        Route::get('/edit_booking/{id}', 'EditBooking')->name('edit_booking');
    });

    Route::controller(RoomExtraController::class)->group(function () {
        Route::post('/rooms/{roomId}/extras', 'store')->name('extras.store');
        Route::get('/extras/{extraId}/edit', 'edit')->name('extras.edit');
        Route::post('/extras/{extraId}/update', 'update')->name('extras.update');
        Route::delete('/extras/{extraId}', 'destroy')->name('extras.delete');

    });

    Route::controller(PageController::class)->group(function () {
        Route::get('/page/list', 'index')->name('list.pages');
        Route::post('/page/store', 'store')->name('store.page');
        Route::get('/page/create', 'create')->name('create.page');
        Route::get('/page/edit/{id}', 'edit')->name('edit.page');
        Route::post('/page/update/{id}', 'update')->name('update.page');
        Route::post('/page/destroy/{id}', 'destroy')->name('destroy.page');
    });

});

Route::controller(FrontendRoomController::class)->group(function () {

    Route::get('/rooms/', 'AllFrontendRoomList')->name('froom.all');
    Route::get('/room/details/{id}', 'RoomDetailsPage')->name('room.details');
    Route::get('bookings/', 'BookingSearch')->name('booking.search');
    Route::get('/search/room/details/{id}', 'SearchRoomDetails')->name('search_room_details');
    Route::get('/check_room_availability/', 'CheckRoomAvailability')->name('check_room_availability');

    Route::post('/calculate-price-with-extras', 'calculatePriceWithExtras')->name('calculate_price_with_extras');

});

Route::middleware(['auth', 'roles:admin'])->group(function () {
    Route::controller(BookingController::class)->group(function () {
        Route::get('/checkout/', 'Checkout')->name('checkout');
        Route::post('/booking/store/', 'BookingStore')->name('user_booking_store');

        Route::match(['get', 'post'], '/stripe_pay', [BookingController::class, 'stripe_pay'])->name('stripe_pay');
        Route::post('/update/booking/status/{id}', 'UpdateBookingStatus')->name('update.booking.status');
        Route::post('/update/booking/{id}', 'UpdateBooking')->name('update.booking');

        Route::get('/assign_room/{id}', 'AssignRoom')->name('assign_room');
        Route::get('/assign_room/store/{booking_id}/{room_number_id}', 'AssignRoomStore')->name('assign_room_store');
        Route::get('/assign_room_delete/{id}', 'AssignRoomDelete')->name('assign_room_delete');
        Route::get('/download/invoice/{id}', 'DownloadInvoice')->name('download.invoice');

        Route::get('/user/booking', 'UserBooking')->name('user.booking');
        Route::get('/user/invoice/{id}', 'UserInvoice')->name('user.invoice');

        Route::post('/mark-notification-as-read/{notification}', 'MarkAsRead');

    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('/checkout/store/', 'createPayment')->name('checkout.store');

        Route::get('create-transaction', 'createTransaction')->name('createTransaction');
        Route::get('process-transaction', 'processTransaction')->name('processTransaction');
        Route::get('success-transaction', 'successTransaction')->name('paypal.success');
        Route::get('cancel-transaction', 'cancelTransaction')->name('paypal.cancel');
    });

    Route::controller(RoomListController::class)->group(function () {

        Route::get('/view/room/list', 'ViewRoomList')->name('view.room.list');
        Route::get('/add/room/list', 'AddRoomList')->name('add.room.list');
        Route::post('/store/roomlist', 'StoreRoomList')->name('store.roomlist');

    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('/smtp/setting', 'SmtpSetting')->name('smtp.setting');
        Route::post('/smtp/update', 'SmtpUpdate')->name('smtp.update');
        Route::get('/site/setting', 'SiteSetting')->name('site.setting');
        Route::post('/site/update', 'SiteUpdate')->name('site.update');
    });

    Route::controller(TestimonialController::class)->group(function () {

        Route::get('/all/testimonial', 'AllTestimonial')->name('all.testimonial');
        Route::get('/add/testimonial', 'AddTestimonial')->name('add.testimonial');
        Route::post('/store/testimonial', 'StoreTestimonial')->name('testimonial.store');
        Route::get('/edit/testimonial/{id}', 'EditTestimonial')->name('edit.testimonial');
        Route::post('/update/testimonial', 'UpdateTestimonial')->name('testimonial.update');
        Route::get('/delete/testimonial/{id}', 'DeleteTestimonial')->name('delete.testimonial');
    });

    Route::controller(BlogController::class)->group(function () {

        Route::get('/blog/category', 'BlogCategory')->name('blog.category');
        Route::post('/store/blog/category', 'StoreBlogCategory')->name('store.blog.category');
        Route::get('/edit/blog/category/{id}', 'EditBlogCategory');
        Route::post('/update/blog/category', 'UpdateBlogCategory')->name('update.blog.category');
        Route::get('/delete/blog/category/{id}', 'DeleteBlogCategory')->name('delete.blog.category');

        Route::get('/all/blog/post', 'AllBlogPost')->name('all.blog.post');
        Route::get('/add/blog/post', 'AddBlogPost')->name('add.blog.post');
        Route::post('/store/blog/post', 'StoreBlogPost')->name('store.blog.post');
        Route::get('/edit/blog/post/{id}', 'EditBlogPost')->name('edit.blog.post');
        Route::post('/update/blog/post', 'UpdateBlogPost')->name('update.blog.post');
        Route::get('/delete/blog/post/{id}', 'DeleteBlogPost')->name('delete.blog.post');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('/booking/report/', 'BookingReport')->name('booking.report');
        Route::post('/search-by-date', 'SearchByDate')->name('search-by-date');

    });

    Route::controller(GalleryController::class)->group(function () {
        Route::get('/all/gallery', 'AllGallery')->name('all.gallery');
        Route::get('/add/gallery', 'AddGallery')->name('add.gallery');
        Route::post('/store/gallery', 'StoreGallery')->name('store.gallery');
        Route::get('/edit/gallery/{id}', 'EditGallery')->name('edit.gallery');
        Route::post('/update/gallery', 'UpdateGallery')->name('update.gallery');
        Route::get('/delete/gallery/{id}', 'DeleteGallery')->name('delete.gallery');
        Route::post('/delete/gallery/multiple', 'DeleteGalleryMultiple')->name('delete.gallery.multiple');
        Route::get('/gallery', 'ShowGallery')->name('show.gallery');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/all/permission', 'AllPermission')->name('all.permission');
        Route::get('/add/permission', 'AddPermission')->name('add.permission');
        Route::post('/store/permission', 'StorePermission')->name('store.permission');
        Route::get('/edit/permission/{id}', 'EditPermission')->name('edit.permission');
        Route::post('/update/permission', 'UpdatePermission')->name('update.permission');

        Route::get('/delete/permission/{id}', 'DeletePermission')->name('delete.permission');
        Route::get('/import/permission', 'ImportPermission')->name('import.permission');
        Route::get('/export', 'Export')->name('export');
        Route::post('/import', 'Import')->name('import');

        Route::get('/all/roles', 'AllRoles')->name('all.roles');
        Route::get('/add/roles', 'AddRoles')->name('add.roles');
        Route::post('/store/roles', 'StoreRoles')->name('store.roles');
        Route::get('/edit/roles/{id}', 'EditRoles')->name('edit.roles');
        Route::post('/update/roles', 'UpdateRoles')->name('update.roles');
        Route::get('/delete/roles/{id}', 'DeleteRoles')->name('delete.roles');

        Route::get('/all/roles/permission', 'AllRolesPermission')->name('all.roles.permission');
        Route::post('/role/permission/store', 'RolePermissionStore')->name('role.permission.store');
        Route::get('/add/roles/permission', 'AddRolesPermission')->name('add.roles.permission');

    });

    Route::controller(AdminController::class)->group(function () {
        Route::get('/all/admin', 'AllAdmin')->name('all.admin');
        Route::get('/add/admin', 'AddAdmin')->name('add.admin');
        Route::post('/store/admin', 'StoreAdmin')->name('store.admin');
        Route::get('/edit/admin/{id}', 'EditAdmin')->name('edit.admin');
        Route::post('/update/admin/{id}', 'UpdateAdmin')->name('update.admin');
        Route::get('/delete/admin/{id}', 'DeleteAdmin')->name('delete.admin');

    });

});

Route::controller(PageController::class)->group(function () {
    Route::get('/page/{slug}', 'PageDetails');
});

Route::controller(BlogController::class)->group(function () {

    Route::get('/blog/details/{slug}', 'BlogDetails');
    Route::get('/blog/cat/list/{id}', 'BlogCatList');
    Route::get('/blog', 'BlogList')->name('blog.list');

});

Route::controller(CommentController::class)->group(function () {

    Route::post('/store/comment/', 'StoreComment')->name('store.comment');
    Route::get('/all/comment/', 'AllComment')->name('all.comment');

    Route::get('/all/comment/', 'AllComment')->name('all.comment');
    Route::post('/update/comment/status', 'UpdateCommentStatus')->name('update.comment.status');
});

Route::controller(ContactUsController::class)->group(function () {
    Route::get('/contact', 'ContactUs')->name('contact.us');
    Route::post('/store/contact', 'StoreContactUs')->name('store.contact');
    Route::get('/contact/message', 'AdminContactMessage')->name('contact.message');
});
