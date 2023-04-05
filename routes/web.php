<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\CourseQualificationController;
use App\Http\Controllers\SourceTutionFeeController;
use App\Http\Controllers\AwardingBodyController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\CoursCreationController;
use App\Http\Controllers\ModuleLevelController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\CourseBaseDatafutureCntroller;
use App\Http\Controllers\CourseModuleBaseAssesmentController;
use App\Http\Controllers\ModuleDatafutureController;
use App\Http\Controllers\CourseCreationAvailabilityController;
use App\Http\Controllers\CourseCreationDatafutureController;
use App\Http\Controllers\CourseCreationInstanceController;
use App\Http\Controllers\InstanceTermController;
use App\Http\Controllers\TermModuleCreationController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlansDateListController;
use App\Http\Controllers\BankHolidayController;
use App\Http\Controllers\TitleController;


use App\Http\Controllers\Applicant\Auth\LoginController;
use App\Http\Controllers\Applicant\Auth\ForgetPasswordController;
use App\Http\Controllers\Applicant\Auth\RegisterController;

use App\Http\Controllers\Auth\GoogleSocialiteController;

use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboard;
use App\Http\Controllers\Applicant\Auth\VerificationController;

use App\Models\ApplicantUser;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');

Route::controller(AuthController::class)->middleware('loggedin')->group(function() {

    Route::get('login', 'loginView')->name('login.index');
    Route::post('login', 'login')->name('login.check');
});
// all applicant have a prefix route name applicant.* value
Route::prefix('/applicant')->name('applicant.')->group(function() {

    Route::controller(LoginController::class)->middleware('applicant.loggedin')->group(function() {

        Route::get('login', 'loginView')->name('login');
        Route::post('login', 'login')->name('check');
    });

    Route::controller(ForgetPasswordController::class)->middleware('applicant.loggedin')->group(function() {

        Route::get('forget-password',  'showForgetPasswordForm')->name('forget.password.get');
        Route::post('forget-password','submitForgetPasswordForm')->name('forget.password.post'); 
        Route::get('reset-password/{token}', 'showResetPasswordForm')->name('reset.password.get');
        Route::post('reset-password', 'submitResetPasswordForm')->name('reset.password.post');
    
    });


    Route::controller(RegisterController::class)->middleware('applicant.loggedin')->group(function() {
        Route::get('register', 'index')->name('register');
        Route::post('register', 'store')->name('store.register');
    });

    Route::middleware('auth.applicant')->group(function() {

        Route::get('logout', [LoginController::class, 'logout'])->name('logout');

        Route::controller(ApplicantDashboard::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
        });

    });
    /**
    * Verification Routes
    */
    Route::controller(VerificationController::class)->group(function() {
        
        Route::get('email/verify', 'show')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['signed']);
        
    });

});
    
    Route::post('/applicant/email/verification-notification', function (Request $request) {
        $id = \Auth::guard('applicant')->user()->id;
        $user = ApplicantUser::find($id);
        $user->sendEmailVerificationNotification();
        return back()->with('verifymessage', 'Verification link sent!');

    })->middleware(['auth.applicant', 'throttle:6,1'])->name('verification.send');


    Route::controller(GoogleSocialiteController::class)->middleware('loggedin')->group(function() {

        Route::get('/auth/google/redirect','redirectToGoogle')->name('redirect.google');
        Route::get('/auth/google/callback', 'handleCallback')->name('callback.google');

    });

Route::middleware('auth')->group(function() {
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    
    Route::controller(CoursCreationController::class)->group(function() {
        Route::get('course-creation', 'index')->name('course.creation'); 
        Route::get('course-creation/list', 'list')->name('course.creation.list'); 
        Route::post('course-creation/store', 'store')->name('course.creation.store');
        Route::get('course-creation/show/{id}', 'show')->name('course.creation.show');
        Route::get('course-creation/edit/{id}', 'edit')->name('course.creation.edit');
        Route::post('course-creation/update', 'update')->name('course.creation.update');
        Route::delete('course-creation/delete/{id}', 'destroy')->name('course.creation.destory');
        Route::post('course-creation/restore/{id}', 'restore')->name('course.creation.restore');
    });
    
    Route::controller(CourseCreationAvailabilityController::class)->group(function() {
        Route::post('course-creation-availability/store', 'store')->name('course.creation.availability.store');
        Route::get('course-creation-availability/list', 'list')->name('course.creation.availability.list'); 
        Route::get('course-creation-availability/edit/{id}', 'edit')->name('course.creation.availability.edit');
        Route::post('course-creation-availability/update', 'update')->name('course.creation.availability.update');
    });

    Route::controller(CourseCreationDatafutureController::class)->group(function() {
        Route::post('course-creation-datafuture/store', 'store')->name('course.creation.datafuture.store');
        Route::get('course-creation-datafuture/list', 'list')->name('course.creation.datafuture.list'); 
        Route::get('course-creation-datafuture/edit/{id}', 'edit')->name('course.creation.datafuture.edit');
        Route::post('course-creation-datafuture/update', 'update')->name('course.creation.datafuture.update');
        Route::delete('course-creation-datafuture/delete/{id}', 'destroy')->name('course.creation.datafuture.destory');
        Route::post('course-creation-datafuture/restore/{id}', 'restore')->name('course.creation.datafuture.restore');
        
    });

    Route::controller(CourseCreationInstanceController::class)->group(function() {
        Route::post('course-creation-instance/store', 'store')->name('course.creation.instance.store');
        Route::get('course-creation-instance/list', 'list')->name('course.creation.instance.list'); 
        Route::get('course-creation-instance/edit/{id}', 'edit')->name('course.creation.instance.edit');
        Route::post('course-creation-instance/update', 'update')->name('course.creation.instance.update');
        Route::delete('course-creation-instance/delete/{id}', 'destroy')->name('course.creation.instance.destory');
        Route::post('course-creation-instance/restore/{id}', 'restore')->name('course.creation.instance.restore');
    });

    Route::controller(InstanceTermController::class)->group(function() {
        Route::post('instance-term/store', 'store')->name('instance.term.store');
        Route::get('instance-term/list', 'list')->name('instance.term.list'); 
        Route::get('instance-term/show/{id}', 'show')->name('instance.term.show'); 
        Route::get('instance-term/edit/{id}', 'edit')->name('instance.term.edit');
        Route::post('instance-term/update', 'update')->name('instance.term.update');
        Route::delete('instance-term/delete/{id}', 'destroy')->name('instance.term.destory');
        Route::post('instance-term/restore/{id}', 'restore')->name('instance.term.restore');        
    });
    
    Route::controller(CourseModuleController::class)->group(function() {
        Route::post('course-module/store', 'store')->name('course.module.store');
        Route::get('course-module/list', 'list')->name('course.module.list'); 
        Route::get('course-module/show/{id}', 'show')->name('course.module.show'); 
        Route::post('course-module/update-status', 'updateStatus')->name('course.module.status.update'); 
        Route::get('course-module/edit/{id}', 'edit')->name('course.module.edit');
        Route::post('course-module/update', 'update')->name('course.module.update');
        Route::delete('course-module/delete/{id}', 'destroy')->name('course.module.destory');
        Route::post('course-module/restore/{id}', 'restore')->name('course.module.restore');
        
    });

    Route::controller(CourseBaseDatafutureCntroller::class)->group(function() {
        Route::post('course-datafuture/store', 'store')->name('course.datafuture.store');
        Route::get('course-datafuture/list', 'list')->name('course.datafuture.list'); 
        Route::get('course-datafuture/edit/{id}', 'edit')->name('course.datafuture.edit');
        Route::post('course-datafuture/update', 'update')->name('course.datafuture.update');
        Route::delete('course-datafuture/delete/{id}', 'destroy')->name('course.datafuture.destory');
        Route::post('course-datafuture/restore/{id}', 'restore')->name('course.datafuture.restore');
        
    });

    Route::controller(CourseModuleBaseAssesmentController::class)->group(function() {
        Route::post('course-module-assesment/store', 'store')->name('course.module.assesment.store');
        Route::get('course-module-assesment/list', 'list')->name('course.module.assesment.list'); 
        Route::get('course-module-assesment/edit/{id}', 'edit')->name('course.module.assesment.edit');
        Route::post('course-module-assesment/update', 'update')->name('course.module.assesment.update');

        Route::delete('course-module-assesment/delete/{id}', 'destroy')->name('course.module.assesment.destory');
        Route::post('course-module-assesment/restore/{id}', 'restore')->name('course.module.assesment.restore');
    });

    Route::controller(ModuleDatafutureController::class)->group(function() {
        Route::post('module-datafuture/store', 'store')->name('module.datafuture.store');
        Route::get('module-datafuture/list', 'list')->name('module.datafuture.list'); 
        Route::get('module-datafuture/edit/{id}', 'edit')->name('module.datafuture.edit');
        Route::post('module-datafuture/update', 'update')->name('module.datafuture.update');
        Route::delete('module-datafuture/delete/{id}', 'destroy')->name('module.datafuture.destory');
        Route::post('module-datafuture/restore/{id}', 'restore')->name('module.datafuture.restore');
        
    });

    Route::controller(TermModuleCreationController::class)->group(function() {
        Route::get('term-module-creation', 'index')->name('term.module.creation');
        Route::get('term-module-creation/list', 'list')->name('term.module.creation.list'); 
        Route::get('term-module-creation/show/{instanceTermId}', 'show')->name('term.module.creation.show'); 
        Route::get('term-module-creation/add/{instanceTermId}/{courseId}', 'add')->name('term.module.creation.add');
        Route::post('term-module-creation/store', 'store')->name('term.module.creation.store');
        Route::get('term-module-creation/module-details/{instanceTermId}', 'moduleDetails')->name('term.module.creation.module.details');

        Route::get('term-module-creation/module-list/', 'moduleList')->name('term.module.creation.module.list');
        Route::post('term-module-creation/module-view-assessments/', 'moduleViewAssessments')->name('term.module.creation.module.view.assessments');
        Route::post('term-module-creation/module-add-assessments/', 'moduleAddAssessments')->name('term.module.creation.module.add.assessments');

        Route::get('module-creation/edit/{id}', 'edit')->name('term.module.creation.edit');
        Route::post('module-creation/update', 'update')->name('term.module.creation.update');
        
    });

    Route::controller(AssessmentController::class)->group(function() {
        Route::post('assessment/store', 'store')->name('assessment.store'); 
        Route::post('assessment/update', 'update')->name('assessment.update');
    });

    Route::controller(PlanController::class)->group(function() {
        Route::get('plans', 'index')->name('class.plan'); 
        Route::get('plans/list', 'list')->name('class.plan.list'); 
        Route::post('plans/grid', 'grid')->name('class.plan.grid'); 
        Route::get('plans/add', 'add')->name('class.plan.add');
        Route::get('plans/edit/{id}', 'edit')->name('class.plan.edit');
        Route::post('plans/update', 'update')->name('class.plan.update');
        Route::get('plans/builder/{course}/{instanceterm}/{modulecreation}', 'classPlanBuilder')->name('class.plan.builder');
        Route::post('plans/store', 'store')->name('class.plan.store');

        Route::delete('plans/delete/{id}', 'destroy')->name('class.plan.delete');
        Route::post('plans/restore/{id}', 'restore')->name('class.plan.restore');

        Route::post('plans/get-modules', 'getModulesByCourseTerms')->name('class.plan.get.modules.by.course.terms');
        Route::post('plans/get-plans-box', 'getClassPlanBox')->name('class.plan.get.box');
    });

    Route::controller(PlansDateListController::class)->group(function() {
        Route::get('plan-dates/all/{planId}', 'index')->name('plan.dates'); 
        Route::get('plan-dates/list', 'list')->name('plan.dates.list'); 
        Route::post('plan-dates/generate', 'generate')->name('plan.dates.generate'); 
        Route::post('plan-dates/store', 'store')->name('plan.dates.store'); 
        Route::delete('plan-dates/delete/{id}', 'destroy')->name('plan.dates.destory');
        Route::post('plan-dates/restore/{id}', 'restore')->name('plan.dates.restore');
    });

    Route::controller(TitleController::class)->group(function() {
        Route::get('titles', 'index')->name('titles'); 
        Route::get('titles/list', 'list')->name('titles.list'); 

        /*Route::post('plan-dates/generate', 'generate')->name('plan.dates.generate'); 
        Route::post('plan-dates/store', 'store')->name('plan.dates.store'); 
        Route::delete('plan-dates/delete/{id}', 'destroy')->name('plan.dates.destory');
        Route::post('plan-dates/restore/{id}', 'restore')->name('plan.dates.restore');*/
    });


    Route::controller(PageController::class)->group(function() {
        Route::get('/', 'dashboardOverview1')->name('dashboard');
     
        Route::get('crud-data-list-page', 'crudDataList')->name('crud-data-list');
        Route::get('crud-form-page', 'crudForm')->name('crud-form');
        // Route::get('formdata-types-page', 'formDataTypes')->name('formdata-types');    
        // Added on 05.12.22
        Route::get('formdatatypes', 'formdataindex')->name('formdatatypes');
        Route::get('formdatatypes/list', 'formDataList')->name('formdatatypes.list');
        Route::get('formdatatypes/create', 'formDataCreate')->name('formdatatypes.create');
        Route::post('formdatatypes/store', 'formDataStore')->name('formdatatypes.store');
        Route::get('formdatatypes/edit/{id}', 'formDataEdit')->name('formdatatypes.edit');
        Route::post('formdatatypes/update', 'formDataUpdate')->name('formdatatypes.update');
        Route::delete('formdatatypes/delete/{id}', 'formDataDestroy')->name('formdatatypes.destory');

        Route::get('dashboard-overview-2-page', 'dashboardOverview2')->name('dashboard-overview-2');
        Route::get('dashboard-overview-3-page', 'dashboardOverview3')->name('dashboard-overview-3');
        Route::get('dashboard-overview-4-page', 'dashboardOverview4')->name('dashboard-overview-4');
        Route::get('categories-page', 'categories')->name('categories');
        Route::get('add-product-page', 'addProduct')->name('add-product');
        Route::get('product-list-page', 'productList')->name('product-list');
        Route::get('product-grid-page', 'productGrid')->name('product-grid');
        Route::get('transaction-list-page', 'transactionList')->name('transaction-list');
        Route::get('transaction-detail-page', 'transactionDetail')->name('transaction-detail');
        Route::get('seller-list-page', 'sellerList')->name('seller-list');
        Route::get('seller-detail-page', 'sellerDetail')->name('seller-detail');
        Route::get('reviews-page', 'reviews')->name('reviews');
        Route::get('inbox-page', 'inbox')->name('inbox');
        Route::get('file-manager-page', 'fileManager')->name('file-manager');
        Route::get('point-of-sale-page', 'pointOfSale')->name('point-of-sale');
        Route::get('chat-page', 'chat')->name('chat');
        Route::get('post-page', 'post')->name('post');
        Route::get('calendar-page', 'calendar')->name('calendar');
        Route::get('users-layout-1-page', 'usersLayout1')->name('users-layout-1');
        Route::get('users-layout-2-page', 'usersLayout2')->name('users-layout-2');
        Route::get('users-layout-3-page', 'usersLayout3')->name('users-layout-3');
        Route::get('profile-overview-1-page', 'profileOverview1')->name('profile-overview-1');
        Route::get('profile-overview-2-page', 'profileOverview2')->name('profile-overview-2');
        Route::get('profile-overview-3-page', 'profileOverview3')->name('profile-overview-3');
        Route::get('wizard-layout-1-page', 'wizardLayout1')->name('wizard-layout-1');
        Route::get('wizard-layout-2-page', 'wizardLayout2')->name('wizard-layout-2');
        Route::get('wizard-layout-3-page', 'wizardLayout3')->name('wizard-layout-3');
        Route::get('blog-layout-1-page', 'blogLayout1')->name('blog-layout-1');
        Route::get('blog-layout-2-page', 'blogLayout2')->name('blog-layout-2');
        Route::get('blog-layout-3-page', 'blogLayout3')->name('blog-layout-3');
        Route::get('pricing-layout-1-page', 'pricingLayout1')->name('pricing-layout-1');
        Route::get('pricing-layout-2-page', 'pricingLayout2')->name('pricing-layout-2');
        Route::get('invoice-layout-1-page', 'invoiceLayout1')->name('invoice-layout-1');
        Route::get('invoice-layout-2-page', 'invoiceLayout2')->name('invoice-layout-2');
        Route::get('faq-layout-1-page', 'faqLayout1')->name('faq-layout-1');
        Route::get('faq-layout-2-page', 'faqLayout2')->name('faq-layout-2');
        Route::get('faq-layout-3-page', 'faqLayout3')->name('faq-layout-3');
        Route::get('login-page', 'login')->name('login');
        Route::get('register-page', 'register')->name('register');
        Route::get('error-page-page', 'errorPage')->name('error-page');
        Route::get('update-profile-page', 'updateProfile')->name('update-profile');
        Route::get('change-password-page', 'changePassword')->name('change-password');
        Route::get('regular-table-page', 'regularTable')->name('regular-table');
        Route::get('tabulator-page', 'tabulator')->name('tabulator');
        Route::get('modal-page', 'modal')->name('modal');
        Route::get('slide-over-page', 'slideOver')->name('slide-over');
        Route::get('notification-page', 'notification')->name('notification');
        Route::get('tab-page', 'tab')->name('tab');
        Route::get('accordion-page', 'accordion')->name('accordion');
        Route::get('button-page', 'button')->name('button');
        Route::get('alert-page', 'alert')->name('alert');
        Route::get('progress-bar-page', 'progressBar')->name('progress-bar');
        Route::get('tooltip-page', 'tooltip')->name('tooltip');
        Route::get('dropdown-page', 'dropdown')->name('dropdown');
        Route::get('typography-page', 'typography')->name('typography');
        Route::get('icon-page', 'icon')->name('icon');
        Route::get('loading-icon-page', 'loadingIcon')->name('loading-icon');
        Route::get('regular-form-page', 'regularForm')->name('regular-form');
        Route::get('datepicker-page', 'datepicker')->name('datepicker');
        Route::get('tom-select-page', 'tomSelect')->name('tom-select');
        Route::get('file-upload-page', 'fileUpload')->name('file-upload');
        Route::get('wysiwyg-editor-classic', 'wysiwygEditorClassic')->name('wysiwyg-editor-classic');
        Route::get('wysiwyg-editor-inline', 'wysiwygEditorInline')->name('wysiwyg-editor-inline');
        Route::get('wysiwyg-editor-balloon', 'wysiwygEditorBalloon')->name('wysiwyg-editor-balloon');
        Route::get('wysiwyg-editor-balloon-block', 'wysiwygEditorBalloonBlock')->name('wysiwyg-editor-balloon-block');
        Route::get('wysiwyg-editor-document', 'wysiwygEditorDocument')->name('wysiwyg-editor-document');
        Route::get('validation-page', 'validation')->name('validation');
        Route::get('chart-page', 'chart')->name('chart');
        Route::get('slider-page', 'slider')->name('slider');
        Route::get('image-zoom-page', 'imageZoom')->name('image-zoom');
    });

    // Added on 12.12.22
    Route::controller(CourseController::class)->group(function() {
        Route::get('courses', 'index')->name('courses'); 
        Route::get('courses/list', 'list')->name('courses.list');        
        Route::post('courses/store', 'store')->name('courses.store');
        Route::get('courses/edit/{id}', 'edit')->name('courses.edit');
        Route::post('courses/update/{id}', 'update')->name('courses.update');
        
        Route::get('courses/show/{id}', 'show')->name('courses.show');

        Route::delete('courses/delete/{id}', 'destroy')->name('courses.destory');
        Route::post('courses/restore/{id}', 'restore')->name('courses.restore');
    });
    // Added on 13.12.22
    Route::controller(SemesterController::class)->group(function() {
        Route::get('semester', 'index')->name('semester');
        Route::get('semester/list', 'list')->name('semester.list');     
        Route::post('semester/store', 'store')->name('semester.store');
        Route::get('semester/edit/{id}', 'edit')->name('semester.edit');
        Route::post('semester/update/{id}', 'update')->name('semester.update');
        Route::delete('semester/delete/{id}', 'destroy')->name('semester.destory');
        Route::post('semester/restore/{id}', 'restore')->name('semester.restore');
    });
    // Added on 20.12.22
    Route::controller(CourseQualificationController::class)->group(function() {
        Route::get('coursequalification', 'index')->name('coursequalification'); 
        Route::get('coursequalification/list', 'list')->name('coursequalification.list');        
        Route::post('coursequalification/store', 'store')->name('coursequalification.store');
        Route::get('coursequalification/edit/{id}', 'edit')->name('coursequalification.edit');
        Route::post('coursequalification/update/{id}', 'update')->name('coursequalification.update');
        Route::delete('coursequalification/delete/{id}', 'destroy')->name('coursequalification.destory');
        Route::post('coursequalification/restore/{id}', 'restore')->name('coursequalification.restore');
    });
    // Added on 20.12.22
    Route::controller(SourceTutionFeeController::class)->group(function() {
        Route::get('sourcetutionfees', 'index')->name('sourcetutionfees'); 
        Route::get('sourcetutionfees/list', 'list')->name('sourcetutionfees.list');        
        Route::post('sourcetutionfees/store', 'store')->name('sourcetutionfees.store');

        Route::get('sourcetutionfees/edit/{id}', 'edit')->name('sourcetutionfees.edit');
        Route::post('sourcetutionfees/update/{id}', 'update')->name('sourcetutionfees.update');
        Route::delete('sourcetutionfees/delete/{id}', 'destroy')->name('sourcetutionfees.destory');
        Route::post('sourcetutionfees/restore/{id}', 'restore')->name('sourcetutionfees.restore');
    });

    // Added on 22.12.22
    Route::controller(AwardingBodyController::class)->group(function() {
        Route::get('awardingbody', 'index')->name('awardingbody'); 
        Route::get('awardingbody/list', 'list')->name('awardingbody.list');        
        Route::post('awardingbody/store', 'store')->name('awardingbody.store');
        Route::get('awardingbody/edit/{id}', 'edit')->name('awardingbody.edit');
        Route::post('awardingbody/update/{id}', 'update')->name('awardingbody.update');
        Route::delete('awardingbody/delete/{id}', 'destroy')->name('awardingbody.destory');
        Route::post('awardingbody/restore/{id}', 'restore')->name('awardingbody.restore');
    });

    // Added on 22.12.22
    Route::controller(AcademicYearController::class)->group(function() {
        Route::get('academicyears', 'index')->name('academicyears'); 
        Route::get('academicyears/list', 'list')->name('academicyears.list');    
        Route::get('academicyears/show/{id}', 'show')->name('academicyears.show');    
        Route::post('academicyears/store', 'store')->name('academicyears.store');
        Route::get('academicyears/edit/{id}', 'edit')->name('academicyears.edit');
        Route::post('academicyears/update/{id}', 'update')->name('academicyears.update');
        Route::delete('academicyears/delete/{id}', 'destroy')->name('academicyears.destory');
        Route::post('academicyears/restore/{id}', 'restore')->name('academicyears.restore');
    });

    // Added on 28.12.22
    Route::controller(GroupController::class)->group(function() {
        Route::get('groups', 'index')->name('groups'); 
        Route::get('groups/list', 'list')->name('groups.list');        
        Route::post('groups/store', 'store')->name('groups.store');
        Route::get('groups/edit/{id}', 'edit')->name('groups.edit');
        Route::post('groups/update/{id}', 'update')->name('groups.update');
        Route::delete('groups/delete/{id}', 'destroy')->name('groups.destory');
        Route::post('groups/restore/{id}', 'restore')->name('groups.restore');
    });

    Route::controller(VenueController::class)->group(function() {
        Route::get('venues', 'index')->name('venues'); 
        Route::get('venues/list', 'list')->name('venues.list');        
        Route::post('venues/store', 'store')->name('venues.store');
        Route::get('venues/edit/{id}', 'edit')->name('venues.edit');
        Route::post('venues/update/{id}', 'update')->name('venues.update');
        Route::get('venues/show/{id}', 'show')->name('venues.show');
        Route::delete('venues/delete/{id}', 'destroy')->name('venues.destory');
        Route::post('venues/restore/{id}', 'restore')->name('venues.restore');
    });

    Route::controller(ModuleLevelController::class)->group(function() {
        Route::get('modulelevels', 'index')->name('modulelevels'); 
        Route::get('modulelevels/list', 'list')->name('modulelevels.list');        
        Route::post('modulelevels/store', 'store')->name('modulelevels.store');
        Route::get('modulelevels/edit/{id}', 'edit')->name('modulelevels.edit');
        Route::post('modulelevels/update/{id}', 'update')->name('modulelevels.update');
        Route::delete('modulelevels/delete/{id}', 'destroy')->name('modulelevels.destory');
        Route::post('modulelevels/restore/{id}', 'restore')->name('modulelevels.restore');
    });

    Route::controller(RoomController::class)->group(function() {
        Route::post('room/store', 'store')->name('room.store');
        Route::get('room/list', 'list')->name('room.list'); 
        Route::get('room/show/{id}', 'show')->name('room.show'); 
        Route::get('room/edit/{id}', 'edit')->name('room.edit');
        Route::post('room/update', 'update')->name('room.update');
        Route::delete('room/delete/{id}', 'destroy')->name('room.destory');
        Route::post('room/restore/{id}', 'restore')->name('room.restore');        
    });

    // Added on 08.03.23
    Route::controller(BankHolidayController::class)->group(function() {      
        Route::get('bankholidays/list', 'list')->name('bankholidays.list');
        Route::post('bankholidays/store', 'store')->name('bankholidays.store'); 
        Route::get('bankholidays/edit/{id}', 'edit')->name('bankholidays.edit');
        Route::post('bankholidays/update', 'update')->name('bankholidays.update');
        Route::delete('bankholidays/delete/{id}', 'destroy')->name('bankholidays.destory');
        Route::post('bankholidays/restore/{id}', 'restore')->name('bankholidays.restore');        
    });
});
