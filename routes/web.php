<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\Settings\CourseQualificationController;
use App\Http\Controllers\Settings\SourceTutionFeeController;
use App\Http\Controllers\Settings\AcademicYearController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\Applicant\ApplicantEmploymentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Settings\VenueController;
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
use App\Http\Controllers\Settings\RoomController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlansDateListController;
use App\Http\Controllers\BankHolidayController;
use App\Http\Controllers\Settings\Studentoptions\TitleController;
use App\Http\Controllers\Settings\Studentoptions\EthnicityController;
use App\Http\Controllers\Settings\Studentoptions\KinsRelationController;
use App\Http\Controllers\Settings\Studentoptions\SexualOrientationController;
use App\Http\Controllers\Settings\Studentoptions\ReligionController;
use App\Http\Controllers\Settings\StatusController;
use App\Http\Controllers\Settings\Studentoptions\CountryController;
use App\Http\Controllers\Settings\Studentoptions\DisabilityController;
use App\Http\Controllers\Settings\DocumentSettingsController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\PermissionCategoryController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\PermissionTemplateController;
use App\Http\Controllers\Settings\ProcessListController;
use App\Http\Controllers\Settings\TaskListController;

use App\Http\Controllers\InterviewListController;
use App\Http\Controllers\ApplicantInterviewListController;
use App\Http\Controllers\InterviewAssignedController;

use App\Http\Controllers\Applicant\Auth\LoginController;
use App\Http\Controllers\Applicant\Auth\RegisterController;

use App\Http\Controllers\Auth\GoogleSocialiteController;

use App\Http\Controllers\Auth\GoogleSocialiteStudentController;
use App\Http\Controllers\Student\Frontend\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\Frontend\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\Frontend\PersonalDetailController as StudentPersonalDetailController;
use App\Http\Controllers\Student\Frontend\OtherPersonalInformationController as StudentOtherPersonalInformationController;
use App\Http\Controllers\Student\Frontend\ContactDetailController as StudentContactDetailController;
use App\Http\Controllers\Student\Frontend\KinDetailController as StudentKinDetailController;
use App\Http\Controllers\Student\Frontend\ConsentController as StudentConsentController;

use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboard;
use App\Http\Controllers\Staff\DashboardController as StaffDashboard;
use App\Http\Controllers\Applicant\Auth\VerificationController;

use App\Models\ApplicantUser;
use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\ApplicantQualificationCongroller;
use App\Http\Controllers\Applicant\ApplicantVarifyTempEmailController;
use App\Http\Controllers\Settings\CommonSmtpController;
use App\Http\Controllers\Settings\LetterSetController;
use App\Http\Controllers\Settings\SignatoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Settings\SmsTemplateController;
use App\Http\Controllers\Settings\EmailTemplateController;
use App\Http\Controllers\ApplicantProfilePrintController;
use App\Http\Controllers\HR\EmployeeWorkingPatternDetailController;
use App\Http\Controllers\HR\EmployeePaymentSettingsController;
use App\Http\Controllers\HR\EmployeeBankDetailController;
use App\Http\Controllers\HR\EmployeePenssionSchemeController;
use App\Http\Controllers\HR\EmployeeAddressController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\EmployeeEligibilityController;
use App\Http\Controllers\HR\EmployeeEmergencyContactController;
use App\Http\Controllers\HR\EmployeeHolidayController;
use App\Http\Controllers\HR\EmployeeProfileController;
use App\Http\Controllers\HR\EmployeeWorkingPatternController;
use App\Http\Controllers\HR\EmployeeTermController;
use App\Http\Controllers\HR\EmployeeWorkingPatternPayController;
use App\Http\Controllers\HR\EmploymentController;
use App\Http\Controllers\PlanTreeController;
use App\Http\Controllers\Settings\ConsentPolicyController;
use App\Http\Controllers\Settings\LetterHeaderFooterController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\Settings\AwardingBodyController;
use App\Http\Controllers\Student\AwardingBodyDetailController;
use App\Http\Middleware\EnsureExpiredDateIsValid;

use App\Http\Controllers\Settings\Studentoptions\HesaGenderController;
use App\Http\Controllers\Settings\Studentoptions\FeeEligibilityController;
use App\Http\Controllers\Student\ConsentController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\PersonalDetailController;
use App\Http\Controllers\Student\KinDetailController;
use App\Http\Controllers\Student\ContactDetailController;
use App\Http\Controllers\Student\CourseDetailController;
use App\Http\Controllers\Student\EducationQualificationController;
use App\Http\Controllers\Student\EmailController;
use App\Http\Controllers\Student\EmploymentHistoryController;
use App\Http\Controllers\Student\LetterController;
use App\Http\Controllers\Student\NoteController;
use App\Http\Controllers\Student\OtherPersonalInformationController;
use App\Http\Controllers\Student\ProcessController;
use App\Http\Controllers\Student\ProofIdCheckController;
use App\Http\Controllers\Student\SmsController;
use App\Http\Controllers\Settings\Studentoptions\SexIdentifierController;
use App\Http\Controllers\Settings\Studentoptions\TermTimeAccommodationTypeController;
use App\Http\Controllers\Student\UploadController;
use App\Http\Controllers\Settings\StudentOptionController;
use App\Http\Controllers\Settings\Studentoptions\ApelCreditController;
use App\Http\Controllers\Settings\Studentoptions\CountryOfPermanentAddressController;
use App\Http\Controllers\Settings\Studentoptions\HighestQualificationOnEntryController;
use App\Http\Controllers\Settings\Studentoptions\PreviousProviderController;
use App\Http\Controllers\Settings\Studentoptions\QualificationTypeIdentifierController;
use App\Http\Controllers\Settings\Studentoptions\ReasonForEngagementEndingController;
use App\Http\Controllers\Student\Frontend\StudentFirstLoginDataController;
use App\Http\Controllers\Settings\ELearningActivitySettingController;
use App\Http\Controllers\Settings\HolidayYearController;
use App\Http\Controllers\Settings\HrBankHolidayController;
use App\Http\Controllers\Settings\PermissionTemplateGroupController;
use App\Models\BankHoliday;

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

Route::controller(ApplicantVarifyTempEmailController::class)->middleware('applicant.loggedin')->group(function() {
    Route::get('varify-temporary-email/{token}',  'varifyTempEmail')->name('varify.temp.email');
});

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
    
    Route::controller(RegisterController::class)->middleware('applicant.loggedin')->group(function() {
        Route::get('register', 'index')->name('register');
        Route::post('register', 'store')->name('store.register');
    });

    Route::middleware('auth.applicant')->group(function() {

        Route::get('logout', [LoginController::class, 'logout'])->name('logout');

        Route::controller(ApplicantDashboard::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/list', 'list')->name('dashboard.applications.list');
        });

        Route::controller(ApplicationController::class)->group(function() {
            Route::get('application', 'index')->name('application');
            Route::post('application/store-personal-details', 'storePersonalDetails')->name('application.store.personal');
            Route::post('application/store-course-details', 'storeCourseDetails')->name('application.store.course');
            Route::post('application/store-applicant-submission', 'storeApplicantSubmission')->name('application.store.submission');
            Route::post('application/review', 'review')->name('application.review');
            Route::get('application/show/{id}', 'show')->name('application.show');

            Route::post('application/verify-referral-code', 'verifyReferralCode')->name('application.verify.referral.code');
        });

        Route::controller(ApplicantQualificationCongroller::class)->group(function() {
            Route::get('qualification/list', 'list')->name('qualification.list');
            Route::post('qualification/store', 'store')->name('qualification.store');
            Route::get('qualification/edit/{id}', 'edit')->name('qualification.edit');
            Route::post('qualification/update', 'update')->name('qualification.update');
            Route::delete('qualification/delete/{id}', 'destroy')->name('qualification.destory');
            Route::post('qualification/restore/{id}', 'restore')->name('qualification.restore');
        });

        Route::controller(ApplicantEmploymentController::class)->group(function() {
            Route::get('employment/list', 'list')->name('employment.list');
            Route::post('employment/store', 'store')->name('employment.store');
            Route::get('employment/edit/{id}', 'edit')->name('employment.edit');
            Route::post('employment/update', 'update')->name('employment.update');
            Route::delete('employment/delete/{id}', 'destroy')->name('employment.destory');
            Route::post('employment/restore/{id}', 'restore')->name('employment.restore');
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

// all student have a prefix route name student.* value
Route::prefix('/students')->name('students.')->group(function() {

    Route::controller(StudentLoginController::class)->middleware('students.loggedin')->group(function() {
        
        Route::get('login', 'loginView')->name('login');
        Route::post('login', 'login')->name('check');


    });
    

    Route::middleware('auth.students')->group(function() {

        Route::get('logout', [StudentLoginController::class, 'logout'])->name('logout');

        Route::controller(StudentDashboardController::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/profile', 'profileView')->name('dashboard.profile');
        });

        Route::controller(StudentOtherPersonalInformationController::class)->group(function() {
            Route::post('/update-other-personal-details', 'update')->name('update.other.personal.details');
        });

        Route::controller(StudentContactDetailController::class)->group(function() {
            Route::post('/update-contact-details', 'update')->name('update.contact.details'); 
        });

        Route::controller(StudentKinDetailController::class)->group(function() {
            Route::post('/update-kin-details', 'update')->name('update.kin.details');
        });

        Route::controller(StudentConsentController::class)->group(function() {
            Route::post('/update-consent', 'update')->name('update.consent');
        });

        Route::controller(StudentFirstLoginDataController::class)->group(function() {
            Route::post('/first/data', 'firstData')->name('first.data');
            Route::post('/first/address', 'addressesConfirm')->name('address.confirm.data');
            Route::post('/first/consent', 'consentConfirm')->name('consent.confirm.data');
            Route::get('/first/review', 'reviewShows')->name('review.show.data');
            Route::post('/first/review', 'reviewDone')->name('review.done.data');
        });

        Route::controller(AddressController::class)->group(function() {
            Route::post('address/get-address', 'getAddress')->name('address.get');
            Route::post('address/store', 'store')->name('address.store');
        });

    });
    
    /**
    * Verification Routes
    */

    // Route::controller(VerificationController::class)->group(function() {
        
    //     Route::get('email/verify', 'show')->name('verification.notice');
    //     Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['signed']);
        
    // });

    Route::controller(GoogleSocialiteStudentController::class)->middleware('student.loggedin')->group(function() {
        Route::get('/auth/google/redirect','redirectToGoogle')->name('redirect.google');
        Route::get('/auth/google/callback', 'handleCallback')->name('callback.google');
    });

});
    
    Route::post('/applicant/email/verification-notification', function (Request $request) {
        $id = Auth::guard('applicant')->user()->id;
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

    Route::controller(PlanTreeController::class)->group(function() {
        Route::get('plans/tree', 'index')->name('plans.tree');
        Route::post('plans/tree/get-term', 'getTerm')->name('plans.tree.get.terms');
        Route::post('plans/tree/get-course', 'getCourses')->name('plans.tree.get.courses');
        Route::post('plans/tree/get-groups', 'getGroups')->name('plans.tree.get.groups');
        Route::post('plans/tree/get-module', 'getModule')->name('plans.tree.get.module');
        Route::get('plans/tree/list', 'list')->name('plans.tree.list'); 
        Route::get('plans/tree/edit/{id}', 'edit')->name('plans.tree.edit'); 
        Route::post('plans/tree/update', 'update')->name('plans.tree.update'); 
        Route::delete('plans/tree/delete/{id}', 'destroy')->name('plans.tree.destory');
        Route::post('plans/tree/restore/{id}', 'restore')->name('plans.tree.restore');

        Route::post('plans/tree/get-assign-details', 'getAssignDetails')->name('plans.get.assign.details');
        Route::post('plans/tree/assign-participants', 'assignParticipants')->name('plans.assign.participants');
        Route::post('plans/tree/update-visibility', 'updateVisibility')->name('plans.update.visibility');
    });

    Route::controller(PlansDateListController::class)->group(function() {
        Route::get('plan-dates/all/{planId}', 'index')->name('plan.dates'); 
        Route::get('plan-dates/list', 'list')->name('plan.dates.list'); 
        Route::post('plan-dates/generate', 'generate')->name('plan.dates.generate'); 
        Route::post('plan-dates/store', 'store')->name('plan.dates.store'); 
        Route::delete('plan-dates/delete/{id}', 'destroy')->name('plan.dates.destory');
        Route::post('plan-dates/restore/{id}', 'restore')->name('plan.dates.restore');
    });

    Route::controller(StudentController::class)->group(function() {
        Route::get('student', 'index')->name('student'); 
        Route::get('student/list', 'list')->name('student.list'); 
        Route::get('student/show/{id}', 'show')->name('student.show');
        Route::get('student/course-details/{id}', 'courseDetails')->name('student.course');
        Route::get('student/communication/{id}', 'communications')->name('student.communication');
        Route::get('student/uploads/{id}', 'uploads')->name('student.uploads');
        Route::get('student/notes/{id}', 'notes')->name('student.notes');
        Route::get('student/process/{id}', 'process')->name('student.process');

        Route::post('student/upload-student-photo', 'UploadStudentPhoto')->name('student.upload.photo');

        Route::post('student/id-filter', 'StudentIDFilter')->name('student.filter.id');
    });
    
    Route::controller(PersonalDetailController::class)->group(function() {
        Route::post('student/update-personal-details', 'update')->name('student.update.personal.details'); 
        Route::post('student/update-personal-identification-details', 'updateOtherIdentificationDetails')->name('student.update.other.identification'); 
    });
    
    Route::controller(OtherPersonalInformationController::class)->group(function() {
        Route::post('student/update-other-personal-details', 'update')->name('student.update.other.personal.details'); 
    });
    
    Route::controller(ProofIdCheckController::class)->group(function() {
        Route::get('student/proof-list', 'list')->name('student.proof.id.check.list'); 
        Route::post('student/proof-store', 'store')->name('student.proof.id.check.store'); 
        Route::get('student/proof-edit/{id}', 'edit')->name('student.proof.id.check.edit');
        Route::post('student/proof-update', 'update')->name('student.proof.id.check.update');

        Route::delete('student/delete-proof/{id}', 'destroy')->name('student.proof.id.check.destory');
        Route::post('student/restore-proof/{id}', 'restore')->name('student.proof.id.check.restore');
    });

    Route::controller(ContactDetailController::class)->group(function() {
        Route::post('student/update-contact-details', 'update')->name('student.update.contact.details'); 
    });
    
    Route::controller(KinDetailController::class)->group(function() {
        Route::post('student/update-kin-details', 'update')->name('student.update.kin.details');
    });
    
    Route::controller(ConsentController::class)->group(function() {
        Route::post('student/update-consent', 'update')->name('student.update.consent');
    });

    Route::controller(EducationQualificationController::class)->group(function() {
        Route::post('student/update-qualification-status', 'updateStudentQualificationStatus')->name('student.qualification.status.update');

        Route::get('student/qualification-list', 'list')->name('student.qualification.list');
        Route::post('student/qualification-store', 'store')->name('student.qualification.store');
        Route::get('student/qualification-edit/{id}', 'edit')->name('student.qualification.edit');
        Route::post('student/qualification-update', 'update')->name('student.qualification.update');
        Route::delete('student/qualification-delete/{id}', 'destroy')->name('student.qualification.destory');
        Route::post('student/qualification-restore/{id}', 'restore')->name('student.qualification.restore');
    });

    Route::controller(EmploymentHistoryController::class)->group(function() {
        Route::post('student/update-employment-status', 'updateStudentEmploymentStatus')->name('student.employment.status.update');

        Route::get('student/employment-list', 'list')->name('student.employment.list');
        Route::post('student/employment-store', 'store')->name('student.employment.store');
        Route::get('student/employment-edit/{id}', 'edit')->name('student.employment.edit');
        Route::post('student/employment-update', 'update')->name('student.employment.update');
        Route::delete('student/employment-delete/{id}', 'destroy')->name('student.employment.destory');
        Route::post('student/employment-restore/{id}', 'restore')->name('student.employment.restore');
    });

    Route::controller(LetterController::class)->group(function() {
        Route::post('student/get-letter-set', 'getLetterSet')->name('student.get.letter.set');
        Route::post('student/letter-store', 'store')->name('student.send.letter');
        Route::get('student/letter-list', 'list')->name('student.letter.list');
        Route::delete('student/letter-delete', 'destroy')->name('student.letter.destroy');
        Route::post('student/restore-letter', 'restore')->name('student.letter.restore');
    });

    Route::controller(EmailController::class)->group(function() {
        Route::post('student/send-mail', 'store')->name('student.send.mail');
        Route::get('student/mail-list', 'list')->name('student.mail.list');
        Route::post('student/mail-show', 'show')->name('student.mail.show');
        Route::delete('student/destory-mail', 'destroy')->name('student.mail.destroy');
        Route::post('student/restore-mail', 'restore')->name('student.mail.restore');
        Route::post('student/get-mail-template', 'getEmailTemplate')->name('student.get.mail.template');
    });

    Route::controller(SmsController::class)->group(function() {
        Route::post('student/send-sms', 'store')->name('student.send.sms');
        Route::get('student/sms-list', 'list')->name('student.sms.list');
        Route::post('student/sms-show', 'show')->name('student.sms.show');
        Route::delete('student/destory-sms', 'destroy')->name('student.sms.destroy');
        Route::post('student/restore-sms', 'restore')->name('student.sms.restore');
        Route::post('student/get-sms-template', 'getSmsTemplate')->name('student.get.sms.template');
    });

    Route::controller(NoteController::class)->group(function() {
        Route::post('student/store-notes', 'store')->name('student.store.note');
        Route::get('student/notes-list', 'list')->name('student.note.list');
        Route::post('student/show-note', 'show')->name('student.show.note');
        Route::post('student/get-note', 'edit')->name('student.get.note');
        Route::post('student/update-note', 'update')->name('student.update.note');
        Route::delete('student/destory-note', 'destroy')->name('student.destory.note');
        Route::post('student/restore-note', 'restore')->name('student.resotore.note');
    });

    Route::controller(UploadController::class)->group(function() {
        Route::post('student/uploads-documents', 'store')->name('student.upload.documents');
        Route::get('student/uploads-list', 'list')->name('student.uploads.list');
        Route::delete('student/uploads-destroy', 'destroy')->name('student.destory.uploads');
        Route::post('student/uploads-restore', 'restore')->name('student.resotore.uploads');
    });

    Route::controller(ProcessController::class)->group(function() {
        Route::post('student/store-process-task', 'storeProcessTask')->name('student.process.store.task.list');
        Route::post('student/upload-task-documents', 'uploadTaskDocument')->name('student.upload.task.documents');
        Route::delete('student/delete-task', 'deleteTask')->name('student.destory.task');
        Route::post('student/completed-task', 'completedTask')->name('student.completed.task');
        Route::post('student/pending-task', 'pendingTask')->name('student.pending.task');
        Route::get('student/archived-process-list', 'archivedProcessList')->name('student.archived.process.list');
        Route::post('student/restore-task', 'resotreTask')->name('student.resotore.task');
        Route::post('student/show-task-statuses', 'showTaskStatuses')->name('student.show.task.outmoce.statuses');
        Route::post('student/task-result-update', 'taskResultUpdate')->name('student.process.task.result.update');
        Route::get('student/task-log-list', 'taskLogList')->name('student.process.log.list');
    });

    Route::controller(CourseDetailController::class)->group(function() {
        Route::post('student/update-course-details', 'update')->name('student.update.course.details');
    });

    Route::controller(AwardingBodyDetailController::class)->group(function() {
        Route::post('student/update-awarding-body-details', 'update')->name('student.update.awarding.body.details');
        Route::post('student/update-awarding-body-status', 'updateStatus')->name('student.update.awarding.body.status');
    });

    Route::controller(AdmissionController::class)->group(function() {

        Route::get('admission', 'index')->name('admission'); 
        Route::get('admission/list', 'list')->name('admission.list'); 
        Route::get('admission/show/{applicantId}', 'show')->name('admission.show');
        
        //Route::get('admission/qualification-list', 'qualificationList')->name('admission.qualification.list');
        //Route::get('admission/employment-list', 'employmentList')->name('admission.employment.list');

        Route::post('admission/update-personal-details', 'updatePersonalDetails')->name('admission.update.personal.details');
        Route::post('admission/update-contact-details', 'updateContactDetails')->name('admission.update.contact.details');
        Route::post('admission/update-kin-details', 'updateKinDetails')->name('admission.update.kin.details');
        Route::post('admission/update-course-details', 'updateCourseAndProgrammeDetails')->name('admission.update.course.details');
        Route::post('admission/update-qualification-status', 'updateQualificationStatus')->name('admission.update.qualification.status');
        Route::post('admission/update-employment-status', 'updateEmploymentStatus')->name('admission.update.employment.status');

        Route::post('admission/upload-applicant-photo', 'admissionUploadApplicantPhoto')->name('admission.upload.photo');

        Route::get('admission/process/{applicantId}', 'admissionProcess')->name('admission.process');
        Route::post('admission/store-process-task', 'admissionStoreProcessTask')->name('admission.process.store.task.list');
        Route::post('admission/upload-task-documents', 'admissionUploadTaskDocument')->name('admission.upload.task.documents');
        Route::delete('admission/delete-task', 'admissionDeleteTask')->name('admission.destory.task');
        Route::post('admission/completed-task', 'admissionCompletedTask')->name('admission.completed.task');
        Route::post('admission/pending-task', 'admissionPendingTask')->name('admission.pending.task');
        Route::get('admission/archived-process-list', 'admissionArchivedProcessList')->name('admission.archived.process.list');
        Route::post('admission/restore-task', 'admissionResotreTask')->name('admission.resotore.task');
        Route::post('admission/show-task-statuses', 'admissionShowTaskStatuses')->name('admission.show.task.outmoce.statuses');
        Route::post('admission/task-result-update', 'admissionTaskResultUpdate')->name('admission.process.task.result.update');
        Route::get('admission/task-log-list', 'admissionTaskLogList')->name('admission.process.log.list');
        Route::get('admission/interview-log-list', 'admissionInterviewLogList')->name('admission.applicant.interview.log');

        Route::get('admission/uploads/{applicantId}', 'admissionUploads')->name('admission.uploads');
        Route::post('admission/uploads-documents', 'AdmissionUploadDocuments')->name('admission.upload.documents');
        Route::get('admission/uploads-list', 'AdmissionUploadList')->name('admission.uploads.list');
        Route::delete('admission/uploads-destroy', 'AdmissionUploadDestroy')->name('admission.destory.uploads');
        Route::post('admission/uploads-restore', 'AdmissionUploadRestore')->name('admission.resotore.uploads');

        Route::get('admission/notes/{applicantId}', 'admissionNotes')->name('admission.notes');
        Route::post('admission/store-notes', 'admissionStoreNotes')->name('admission.store.note');
        Route::get('admission/notes-list', 'admissionNotesList')->name('admission.note.list');
        Route::post('admission/show-note', 'admissionShowNote')->name('admission.show.note');
        Route::post('admission/get-note', 'admissionGetNote')->name('admission.get.note');
        Route::post('admission/update-note', 'admissionUpdateNote')->name('admission.update.note');
        Route::delete('admission/destory-note', 'admissionDestroyNote')->name('admission.destory.note');
        Route::post('admission/restore-note', 'admissionRestoreNote')->name('admission.resotore.note');

        Route::get('admission/communications/{applicantId}', 'admissionCommunication')->name('admission.communication');
        Route::post('admission/send-mail', 'admissionCommunicationSendMail')->name('admission.communication.send.mail');
        Route::get('admission/mail-list', 'admissionCommunicationMailList')->name('admission.communication.mail.list');
        Route::post('admission/mail-show', 'admissionCommunicationMailShow')->name('admission.communication.mail.show');
        Route::delete('admission/destory-mail', 'admissionDestroyMail')->name('admission.communication.mail.destroy');
        Route::post('admission/restore-mail', 'admissionRestoreMail')->name('admission.communication.mail.restore');
        Route::post('admission/get-mail-template', 'admissionGetMailTemplate')->name('admission.communication.get.mail.template');

        Route::post('admission/send-sms', 'admissionCommunicationSendSms')->name('admission.communication.send.sms');
        Route::get('admission/sms-list', 'admissionCommunicationSmsList')->name('admission.communication.sms.list');
        Route::post('admission/sms-show', 'admissionCommunicationSmsShow')->name('admission.communication.sms.show');
        Route::delete('admission/destory-sms', 'admissionDestroySms')->name('admission.communication.sms.destroy');
        Route::post('admission/restore-sms', 'admissionRestoreSms')->name('admission.communication.sms.restore');
        Route::post('admission/get-sms-template', 'admissionGetSmsTemplate')->name('admission.communication.get.sms.template');

        Route::post('admission/get-letter-set', 'admissionGetLetterSet')->name('admission.communication.get.letter.set');
        Route::post('admission/send-letter', 'admissionSendLetter')->name('admission.communication.send.letter');
        Route::get('admission/letter-list', 'admissionCommunicationLetterList')->name('admission.communication.letter.list');
        Route::delete('admission/destroy-letter', 'admissionDestroyLetter')->name('admission.communication.letter.destroy');
        Route::post('admission/restore-letter', 'admissionRestoreLetter')->name('admission.communication.letter.restore');


        Route::post('admission/update-status', 'admissionStudentUpdateStatus')->name('admission.student.update.status');
        Route::post('admission/status-validation', 'admissionStudentStatusValidation')->name('admission.student.status.validation');

        Route::get('admission/progress/data/{id?}','progressForStudentStoreProcess')->name('admission.progress.data');

        //Route::get('admission/convertstudent','convertStudentDemo')->name('admission.convertstudent');
        
    });

    Route::controller(ApplicantQualificationCongroller::class)->group(function() {
        Route::get('qualification/list', 'list')->name('qualification.list');
        Route::post('qualification/store', 'store')->name('qualification.store');
        Route::get('qualification/edit/{id}', 'edit')->name('qualification.edit');
        Route::post('qualification/update', 'update')->name('qualification.update');
        Route::delete('qualification/delete/{id}', 'destroy')->name('qualification.destory');
        Route::post('qualification/restore/{id}', 'restore')->name('qualification.restore');
    });

    Route::controller(ApplicantEmploymentController::class)->group(function() {
        Route::get('employment/list', 'list')->name('employment.list');
        Route::post('employment/store', 'store')->name('employment.store');
        Route::get('employment/edit/{id}', 'edit')->name('employment.edit');
        Route::post('employment/update', 'update')->name('employment.update');
        Route::delete('employment/delete/{id}', 'destroy')->name('employment.destory');
        Route::post('employment/restore/{id}', 'restore')->name('employment.restore');
    });

    Route::controller(UserController::class)->group(function() {
        Route::get('users', 'index')->name('users'); 
        Route::get('users/list', 'list')->name('users.list'); 
        Route::post('users/store', 'store')->name('users.store'); 
        Route::get('users/edit/{id}', 'edit')->name('users.edit');
        Route::post('users/update/{id}', 'update')->name('users.update');
        Route::delete('users/delete/{id}', 'destroy')->name('users.destory');
        Route::post('users/restore/{id}', 'restore')->name('users.restore');
        
        Route::get('dashboarduser/{userId}', 'useraccess')->name('useraccess');
        Route::get('dashboarduser/staff/{userId}/{roleId}', 'useraccessStaff')->name('useraccess.staff');
    });
    Route::controller(EmployeeController::class)->group(function(){
        Route::get('employee','index')->name('employee');
        Route::post('employee/upload-photo', 'UploadEmployeePhoto')->name('employee.upload.photo');
        Route::get('employee/new','create')->name('employee.create');
        Route::post('employee/save','save')->name('employee.save');
        Route::post('employee/update/{employee}','update')->name('employee.update');
        Route::post('employement/save','saveEmployment')->name('employement.save');
        Route::post('eligibility/save','saveEligibility')->name('eligibility.save');
        Route::post('emergency-contact/save','saveEmergencyContact')->name('emergency-contact.save');

        Route::get('/first/review', 'reviewShows')->name('employeereview.show.data');
        Route::post('/first/review', 'reviewDone')->name('employeereview.done.data');
    });

    Route::controller(EmployeeProfileController::class)->group(function(){
        
        Route::get('employee-profile/view/{id}', 'show')->name('profile.employee.view'); 
    });

    Route::controller(EmployeeAddressController::class)->group(function() {
        Route::post('employee-address/update/{employee}','update')->name('employee.address.update');
    });
    Route::controller(EmploymentController::class)->group(function() {
        Route::post('employment/update/{employment}','update')->name('employment.update');
    });
    Route::controller(EmployeeEligibilityController::class)->group(function() {
        Route::post('employee-eligibility/update/{eligibility}','update')->name('employeeeligibility.update');
    });
    
    Route::controller(EmployeeEmergencyContactController::class)->group(function() {
        Route::post('employee-emergency/update/{contact}','update')->name('employee.emergency.update');
    });

    
    Route::controller(EmployeeTermController::class)->group(function() {
        Route::post('employee-term/update/{term}','update')->name('employee.term.update');
    });

    Route::controller(EmployeePaymentSettingsController::class)->group(function(){
        Route::get('employee-profile/payment-settings/{id}', 'index')->name('employee.payment.settings'); 
        Route::post('employee-profile/payment-settings/store', 'store')->name('employee.payment.settings.store'); 
        Route::post('employee-profile/payment-settings/update', 'update')->name('employee.payment.settings.update'); 
    });

    Route::controller(EmployeeBankDetailController::class)->group(function(){
        Route::post('employee-profile/bank/store', 'store')->name('employee.bank.store'); 
        Route::post('employee-profile/bank/edit', 'edit')->name('employee.bank.edit'); 
        Route::post('employee-profile/bank/update', 'update')->name('employee.bank.update'); 
        Route::get('employee-profile/bank/list', 'list')->name('employee.bank.list'); 
        Route::delete('employee-profile/bank/delete/{id}', 'destroy')->name('employee.bank.destory');
        Route::post('employee-profile/bank/restore/{id}', 'restore')->name('employee.bank.restore');
        Route::post('employee-profile/bank/change-status/{id}', 'changeStatus')->name('employee.bank.changestatus');
    });
    

    Route::controller(EmployeePenssionSchemeController::class)->group(function(){
        Route::get('employee-profile/penssion/list', 'list')->name('employee.penssion.list'); 
        Route::post('employee-profile/penssion/store', 'store')->name('employee.penssion.store'); 
        Route::post('employee-profile/penssion/edit', 'edit')->name('employee.penssion.edit'); 
        Route::post('employee-profile/penssion/update', 'update')->name('employee.penssion.update'); 
        Route::delete('employee-profile/penssion/delete/{id}', 'destroy')->name('employee.penssion.destory');
        Route::post('employee-profile/penssion/restore/{id}', 'restore')->name('employee.penssion.restore');
    });

    Route::controller(EmployeeWorkingPatternController::class)->group(function(){
        Route::get('employee-profile/pattern/list', 'list')->name('employee.pattern.list'); 
        Route::post('employee-profile/pattern/store', 'store')->name('employee.pattern.store'); 
        Route::post('employee-profile/pattern/edit', 'edit')->name('employee.pattern.edit'); 
        Route::post('employee-profile/pattern/update', 'update')->name('employee.pattern.update'); 
        Route::delete('employee-profile/pattern/delete/{id}', 'destroy')->name('employee.pattern.destory');
        Route::post('employee-profile/pattern/restore/{id}', 'restore')->name('employee.pattern.restore');
    });

    Route::controller(EmployeeWorkingPatternDetailController::class)->group(function(){
        Route::get('employee-profile/pattern-details/list', 'list')->name('employee.pattern.details.list'); 
        Route::post('employee-profile/pattern-details/store', 'store')->name('employee.pattern.details.store'); 
        Route::post('employee-profile/pattern-details/edit', 'edit')->name('employee.pattern.details.edit'); 
        Route::post('employee-profile/pattern-details/update', 'update')->name('employee.pattern.details.update'); 
    });

    Route::controller(EmployeeWorkingPatternPayController::class)->group(function(){
        Route::get('employee-profile/pattern-pay/list', 'list')->name('employee.pattern.pay.list'); 
        Route::post('employee-profile/pattern-pay/edit', 'edit')->name('employee.pattern.pay.edit'); 
        Route::post('employee-profile/pattern-pay/update', 'update')->name('employee.pattern.pay.update');
        Route::post('employee-profile/pattern-pay/getPattern', 'getPattern')->name('employee.pattern.pay.get.pattern');
        Route::post('employee-profile/pattern-pay/store', 'store')->name('employee.pattern.pay.store'); 
    });

    Route::controller(EmployeeHolidayController::class)->group(function(){
        Route::get('employee-profile/holidays/{id}', 'index')->name('employee.holiday'); 
        Route::post('employee-profile/holidays/update-adjustment', 'updateAdjustment')->name('employee.holiday.update.adjustment'); 
        //Route::post('employee-profile/payment-settings/update', 'update')->name('employee.payment.settings.update'); 
    });
    
    Route::controller(StaffDashboard::class)->group(function() {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/dashboard', 'index')->name('staff.dashboard');
        Route::get('/dashboard/list', 'list')->name('dashboard.staff.list');
    });


    Route::controller(PageController::class)->group(function() {
        //Route::get('/', 'dashboardOverview1')->name('dashboard');
     
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
   
    Route::controller(SemesterController::class)->group(function() {
        Route::get('semester', 'index')->name('semester');
        Route::get('semester/list', 'list')->name('semester.list');     
        Route::post('semester/store', 'store')->name('semester.store');
        Route::get('semester/edit/{id}', 'edit')->name('semester.edit');
        Route::post('semester/update/{id}', 'update')->name('semester.update');
        Route::delete('semester/delete/{id}', 'destroy')->name('semester.destory');
        Route::post('semester/restore/{id}', 'restore')->name('semester.restore');
    });

    Route::controller(GroupController::class)->group(function() {
        Route::get('groups', 'index')->name('groups'); 
        Route::get('groups/list', 'list')->name('groups.list');        
        Route::post('groups/store', 'store')->name('groups.store');
        Route::get('groups/edit/{id}', 'edit')->name('groups.edit');
        Route::post('groups/update/{id}', 'update')->name('groups.update');
        Route::delete('groups/delete/{id}', 'destroy')->name('groups.destory');
        Route::post('groups/restore/{id}', 'restore')->name('groups.restore');
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

    Route::controller(BankHolidayController::class)->group(function() {      
        Route::get('bankholidays/list', 'list')->name('bankholidays.list');
        Route::post('bankholidays/store', 'store')->name('bankholidays.store'); 
        Route::get('bankholidays/edit/{id}', 'edit')->name('bankholidays.edit');
        Route::post('bankholidays/update', 'update')->name('bankholidays.update');
        Route::delete('bankholidays/delete/{id}', 'destroy')->name('bankholidays.destory');
        Route::post('bankholidays/restore/{id}', 'restore')->name('bankholidays.restore');       
        
        Route::get('bankholidays/export/', 'export')->name('bankholidays.export');
        Route::post('bankholidays/import', 'import')->name('bankholidays.import');
    });

    Route::controller(DepartmentController::class)->group(function() {
        Route::get('site-settings/department', 'index')->name('department'); 
        Route::get('site-settings/department/list', 'list')->name('department.list'); 
        Route::post('site-settings/department/store', 'store')->name('department.store'); 
        Route::get('site-settings/department/edit/{id}', 'edit')->name('department.edit');
        Route::post('site-settings/department/update', 'update')->name('department.update');
        Route::delete('site-settings/department/delete/{id}', 'destroy')->name('department.destory');
        Route::post('site-settings/department/restore/{id}', 'restore')->name('department.restore');
    });

    Route::controller(PermissionCategoryController::class)->group(function() {
        Route::get('site-settings/permissioncategory', 'index')->name('permissioncategory'); 
        Route::get('site-settings/permissioncategory/list', 'list')->name('permissioncategory.list'); 
        Route::post('site-settings/permissioncategory/store', 'store')->name('permissioncategory.store'); 
        Route::get('site-settings/permissioncategory/edit/{id}', 'edit')->name('permissioncategory.edit');
        Route::post('site-settings/permissioncategory/update', 'update')->name('permissioncategory.update');
        Route::delete('site-settings/permissioncategory/delete/{id}', 'destroy')->name('permissioncategory.destory');
        Route::post('site-settings/permissioncategory/restore/{id}', 'restore')->name('permissioncategory.restore');
    });

    
    Route::controller(RoleController::class)->group(function() {
        Route::get('site-settings/roles', 'index')->name('roles'); 
        Route::get('site-settings/roles/list', 'list')->name('roles.list'); 
        Route::post('site-settings/roles/store', 'store')->name('roles.store'); 
        Route::get('site-settings/roles/show/{id}', 'show')->name('roles.show');
        Route::get('site-settings/roles/edit/{id}', 'edit')->name('roles.edit');
        Route::post('site-settings/roles/update', 'update')->name('roles.update');
        Route::delete('site-settings/roles/delete/{id}', 'destroy')->name('roles.destory');
        Route::post('site-settings/roles/restore/{id}', 'restore')->name('roles.restore');
    });

    Route::controller(PermissionTemplateController::class)->group(function() {
        Route::post('site-settings/permissiontemplate/store', 'store')->name('permissiontemplate.store'); 
        Route::post('site-settings/permissiontemplate/update', 'update')->name('permissiontemplate.update');
    });

    Route::controller(PermissionTemplateGroupController::class)->group(function() {
        Route::post('site-settings/permissiontemplate/group/store', 'store')->name('permissiontemplate.group.store'); 
    });

    Route::controller(InterviewListController::class)->group(function() {
        Route::get('interviewlist', 'index')->name('interviewlist');
        Route::get('interviewlist/list', 'list')->name('interviewlist.list');
        //Route::post('interviewlist/assaign', 'assaignInterviewer')->name('interviewlist.assign');
        Route::post('interviewlist/assaign/update', 'updateAssaignInterviewer')->name('interviewlist.assign.update');
        Route::post('interviewlist/unlock', 'unlockInterView')->name('applicant.interview.unlock');
        Route::post('interviewlist/direct/unlock', 'unlockInterViewDirect')->name('applicant.interview.unlock.direct');
        Route::get('interviewlist/profile/{id}/{interview}/{token}', 'profileView')->name('applicant.interview.profile.view')->middleware(EnsureExpiredDateIsValid::class);
        Route::get('interviewlist/staff/{userId}', 'interviewAssignedList')->name('applicant.interview.session.list');
        
        Route::get('interviewlist/showinstances', 'showInstances')->name('interviewlist.showinstances');
        Route::get('interviewlist/completedlist', 'completedList')->name('interviewlist.completedlist');
        Route::post('interviewlist/completedlistunlock', 'unlockInterView')->name('applicant.completedinterview.unlock');
    });

    Route::controller(InterviewAssignedController::class)->group(function() {

        Route::get('interview/assaigned', 'index')->name('interview.assigned');
        Route::get('interview/list', 'list')->name('interview.assigned.list');    
          
    });
    
    Route::controller(ApplicantInterviewListController::class)->group(function() {
        Route::get('applicant_interviewlist', 'index')->name('applicant.interview');
        Route::get('applicant_interviewlist/list', 'list')->name('applicant.interview.list');
        Route::post('applicant_interviewlist/update', 'interviewResultUpdate')->name('applicant.interview.result.update');
        Route::post('applicant_interviewlist/task', 'interviewTaskUpdate')->name('applicant.interview.task.update');
        Route::post('applicant_interviewlist/start', 'interviewStartTimeUpdate')->name('applicant.interview.start');
        Route::post('applicant_interviewlist/end', 'interviewEndTimeUpdate')->name('applicant.interview.end');

        Route::delete('applicant_interviewlist/file/remove/{id}', 'interviewFileRemove')->name('applicant.interview.file.remove');
        
    });
    
    Route::controller(ApplicantProfilePrintController::class)->group(function() {
        Route::get('applicantprofilepdf/{applicantId}', 'generatePDF')->name('applicantprofile.print');
    });
    // a pdf will be saved

    Route::controller(SettingController::class)->group(function(){
        Route::get('site-settings', 'index')->name('site.setting');
        Route::get('site-settings/address-api', 'addressApi')->name('site.setting.addr.api');
        Route::get('site-settings/sms-api', 'smsApi')->name('site.setting.sms.api');
        Route::post('site-settings/update', 'update')->name('site.setting.update');
    });
    
    Route::controller(AwardingBodyController::class)->group(function() {
        Route::get('site-settings/awardingbody', 'index')->name('awardingbody'); 
        Route::get('site-settings/awardingbody/list', 'list')->name('awardingbody.list');        
        Route::post('site-settings/awardingbody/store', 'store')->name('awardingbody.store');
        Route::get('site-settings/awardingbody/edit/{id}', 'edit')->name('awardingbody.edit');
        Route::post('site-settings/awardingbody/update/{id}', 'update')->name('awardingbody.update');
        Route::delete('site-settings/awardingbody/delete/{id}', 'destroy')->name('awardingbody.destory');
        Route::post('site-settings/awardingbody/restore/{id}', 'restore')->name('awardingbody.restore');
    });

    Route::controller(AcademicYearController::class)->group(function() {
        Route::get('site-settings/academicyears', 'index')->name('academicyears'); 
        Route::get('site-settings/academicyears/list', 'list')->name('academicyears.list');    
        Route::get('site-settings/academicyears/show/{id}', 'show')->name('academicyears.show');    
        Route::post('site-settings/academicyears/store', 'store')->name('academicyears.store');
        Route::get('site-settings/academicyears/edit/{id}', 'edit')->name('academicyears.edit');
        Route::post('site-settings/academicyears/update/{id}', 'update')->name('academicyears.update');
        Route::delete('site-settings/academicyears/delete/{id}', 'destroy')->name('academicyears.destory');
        Route::post('site-settings/academicyears/restore/{id}', 'restore')->name('academicyears.restore');
    });
 
    Route::controller(SourceTutionFeeController::class)->group(function() {
        Route::get('site-settings/sourcetutionfees', 'index')->name('sourcetutionfees'); 
        Route::get('site-settings/sourcetutionfees/list', 'list')->name('sourcetutionfees.list');        
        Route::post('site-settings/sourcetutionfees/store', 'store')->name('sourcetutionfees.store');

        Route::get('site-settings/sourcetutionfees/edit/{id}', 'edit')->name('sourcetutionfees.edit');
        Route::post('site-settings/sourcetutionfees/update/{id}', 'update')->name('sourcetutionfees.update');
        Route::delete('site-settings/sourcetutionfees/delete/{id}', 'destroy')->name('sourcetutionfees.destory');
        Route::post('site-settings/sourcetutionfees/restore/{id}', 'restore')->name('sourcetutionfees.restore');
    });
   
    Route::controller(CourseQualificationController::class)->group(function() {
        Route::get('site-settings/coursequalification', 'index')->name('coursequalification'); 
        Route::get('site-settings/coursequalification/list', 'list')->name('coursequalification.list');        
        Route::post('site-settings/coursequalification/store', 'store')->name('coursequalification.store');
        Route::get('site-settings/coursequalification/edit/{id}', 'edit')->name('coursequalification.edit');
        Route::post('site-settings/coursequalification/update/{id}', 'update')->name('coursequalification.update');
        Route::delete('site-settings/coursequalification/delete/{id}', 'destroy')->name('coursequalification.destory');
        Route::post('site-settings/coursequalification/restore/{id}', 'restore')->name('coursequalification.restore');
    });

    Route::controller(ConsentPolicyController::class)->group(function() {
        Route::get('site-settings/consent', 'index')->name('consent'); 
        Route::get('site-settings/consent/list', 'list')->name('consent.list'); 
        Route::post('site-settings/consent/store', 'store')->name('consent.store'); 
        Route::get('site-settings/consent/edit/{id}', 'edit')->name('consent.edit');
        Route::post('site-settings/consent/update', 'update')->name('consent.update');
        Route::delete('site-settings/consent/delete/{id}', 'destroy')->name('consent.destory');
        Route::post('site-settings/consent/restore/{id}', 'restore')->name('consent.restore');
    });

    Route::controller(VenueController::class)->group(function() {
        Route::get('site-settings/venues', 'index')->name('venues'); 
        Route::get('site-settings/venues/list', 'list')->name('venues.list');        
        Route::post('site-settings/venues/store', 'store')->name('venues.store');
        Route::get('site-settings/venues/edit/{id}', 'edit')->name('venues.edit');
        Route::post('site-settings/venues/update/{id}', 'update')->name('venues.update');
        Route::get('site-settings/venues/show/{id}', 'show')->name('venues.show');
        Route::delete('site-settings/venues/delete/{id}', 'destroy')->name('venues.destory');
        Route::post('site-settings/venues/restore/{id}', 'restore')->name('venues.restore');
    });

    Route::controller(RoomController::class)->group(function() {
        Route::post('site-settings/venues/room/store', 'store')->name('room.store');
        Route::get('site-settings/venues/room/list', 'list')->name('room.list'); 
        Route::get('site-settings/venues/room/show/{id}', 'show')->name('room.show'); 
        Route::get('site-settings/venues/room/edit/{id}', 'edit')->name('room.edit');
        Route::post('site-settings/venues/room/update', 'update')->name('room.update');
        Route::delete('site-settings/venues/room/delete/{id}', 'destroy')->name('room.destory');
        Route::post('site-settings/venues/room/restore/{id}', 'restore')->name('room.restore');        
    });

    Route::controller(StatusController::class)->group(function() {
        Route::get('site-settings/statuses', 'index')->name('statuses'); 
        Route::get('site-settings/statuses/list', 'list')->name('statuses.list'); 
        Route::post('site-settings/statuses/store', 'store')->name('statuses.store'); 
        Route::get('site-settings/statuses/edit/{id}', 'edit')->name('statuses.edit');
        Route::post('site-settings/statuses/update', 'update')->name('statuses.update');
        Route::delete('site-settings/statuses/delete/{id}', 'destroy')->name('statuses.destory');
        Route::post('site-settings/statuses/restore/{id}', 'restore')->name('statuses.restore');
    });

    Route::controller(DocumentSettingsController::class)->group(function() {
        Route::get('site-settings/documentsettings', 'index')->name('documentsettings'); 
        Route::get('site-settings/documentsettings/list', 'list')->name('documentsettings.list'); 
        Route::post('site-settings/documentsettings/store', 'store')->name('documentsettings.store'); 
        Route::get('site-settings/documentsettings/edit/{id}', 'edit')->name('documentsettings.edit');
        Route::post('site-settings/documentsettings/update', 'update')->name('documentsettings.update');
        Route::delete('site-settings/documentsettings/delete/{id}', 'destroy')->name('documentsettings.destory');
        Route::post('site-settings/documentsettings/restore/{id}', 'restore')->name('documentsettings.restore');
    });

    Route::controller(ProcessListController::class)->group(function() {
        Route::get('site-settings/processlist', 'index')->name('processlist'); 
        Route::get('site-settings/processlist/list', 'list')->name('processlist.list'); 
        Route::post('site-settings/processlist/store', 'store')->name('processlist.store'); 
        Route::get('site-settings/processlist/edit/{id}', 'edit')->name('processlist.edit');
        Route::post('site-settings/processlist/update', 'update')->name('processlist.update');
        Route::delete('site-settings/processlist/delete/{id}', 'destroy')->name('processlist.destory');
        Route::post('site-settings/processlist/restore/{id}', 'restore')->name('processlist.restore');
    });

    Route::controller(TaskListController::class)->group(function() {
        Route::get('site-settings/tasklist', 'index')->name('tasklist'); 
        Route::get('site-settings/tasklist/list', 'list')->name('tasklist.list'); 
        Route::post('site-settings/tasklist/store', 'store')->name('tasklist.store'); 
        Route::get('site-settings/tasklist/edit/{id}', 'edit')->name('tasklist.edit');
        Route::post('site-settings/tasklist/update', 'update')->name('tasklist.update');
        Route::delete('site-settings/tasklist/delete/{id}', 'destroy')->name('tasklist.destory');
        Route::post('site-settings/tasklist/restore/{id}', 'restore')->name('tasklist.restore');
    });

    Route::controller(SmsTemplateController::class)->group(function() {
        Route::get('site-settings/sms-template', 'index')->name('sms.template'); 
        Route::get('site-settings/sms-template/list', 'list')->name('sms.template.list'); 
        Route::post('site-settings/sms-template/store', 'store')->name('sms.template.store');
        Route::get('site-settings/sms-template/edit/{id}', 'edit')->name('sms.template.edit');
        Route::post('site-settings/sms-template/update', 'update')->name('sms.template.update');

        Route::delete('site-settings/sms-template/delete/{id}', 'destroy')->name('sms.template.destory');
        Route::post('site-settings/sms-template/restore/{id}', 'restore')->name('sms.template.restore');
    });

    Route::controller(EmailTemplateController::class)->group(function() {
        Route::get('site-settings/email-template', 'index')->name('email.template'); 
        Route::get('site-settings/email-template/list', 'list')->name('email.template.list'); 
        Route::post('site-settings/email-template/store', 'store')->name('email.template.store');
        Route::get('site-settings/email-template/edit/{id}', 'edit')->name('email.template.edit');
        Route::post('site-settings/email-template/update', 'update')->name('email.template.update');

        Route::delete('site-settings/email-template/delete/{id}', 'destroy')->name('email.template.destory');
        Route::post('site-settings/email-template/restore/{id}', 'restore')->name('email.template.restore');
    });

    Route::controller(CommonSmtpController::class)->group(function() {
        Route::get('site-settings/common-smtp', 'index')->name('common.smtp'); 
        Route::get('site-settings/common-smtp/list', 'list')->name('common.smtp.list'); 
        Route::post('site-settings/common-smtp/store', 'store')->name('common.smtp.store');
        Route::get('site-settings/common-smtp/edit/{id}', 'edit')->name('common.smtp.edit');
        Route::post('site-settings/common-smtp/update/{id}', 'update')->name('common.smtp.update');

        Route::delete('site-settings/common-smtp/delete/{id}', 'destroy')->name('common.smtp.destory');
        Route::post('site-settings/common-smtp/restore/{id}', 'restore')->name('common.smtp.restore');
    });

    Route::controller(LetterSetController::class)->group(function() {
        Route::get('site-settings/letter-sets', 'index')->name('letter.set'); 
        Route::get('site-settings/letter-sets/list', 'list')->name('letter.set.list'); 
        Route::post('site-settings/letter-sets/store', 'store')->name('letter.set.store');
        Route::get('site-settings/letter-sets/edit/{id}', 'edit')->name('letter.set.edit');
        Route::post('site-settings/letter-sets/update', 'update')->name('letter.set.update');
        Route::delete('site-settings/letter-sets/delete/{id}', 'destroy')->name('letter.set.destory');
        Route::post('site-settings/letter-sets/restore/{id}', 'restore')->name('letter.set.restore');
    });

    Route::controller(SignatoryController::class)->group(function() {
        Route::get('site-settings/signatory', 'index')->name('signatory'); 
        Route::get('site-settings/signatory/list', 'list')->name('signatory.list'); 
        Route::post('site-settings/signatory/store', 'store')->name('signatory.store');
        Route::post('site-settings/signatory/edit', 'edit')->name('signatory.edit');
        Route::post('site-settings/signatory/update', 'update')->name('signatory.update');
        Route::delete('site-settings/signatory/delete/{id}', 'destroy')->name('signatory.destory');
        Route::post('site-settings/signatory/restore/{id}', 'restore')->name('signatory.restore');
    });

    Route::controller(LetterHeaderFooterController::class)->group(function() {
        Route::get('site-settings/letterheaderfooter', 'index')->name('letterheaderfooter'); 
        Route::get('site-settings/letterheaderfooter/headerlist', 'letterheaderlist')->name('letterheader.list');
        Route::get('site-settings/letterheaderfooter/footerlist', 'letterfooterlist')->name('letterfooter.list');
        Route::post('site-settings/letterheaderfooter/upload-letterheader', 'uploadLetterHeader')->name('letterheaderfooter.upload.letterhead'); 
        Route::post('site-settings/letterheaderfooter/upload-letterfooter', 'uploadLetterFooter')->name('letterheaderfooter.upload.letterfoot');
        Route::delete('site-settings/letterheaderfooter/uploads-destroy', 'LetterUploadDestroy')->name('letterheaderfooter.destory.uploads');
        Route::post('site-settings/letterheaderfooter/uploads-restore', 'LetterUploadRestore')->name('letterheaderfooter.resotore.uploads'); 
    });

    Route::controller(ELearningActivitySettingController::class)->group(function() {
        Route::get('site-settings/e-learning', 'index')->name('elearning'); 
        Route::post('site-settings/e-learning/store', 'store')->name('elearning.store'); 
        Route::get('site-settings/e-learning/list', 'list')->name('elearning.list');
        Route::post('site-settings/e-learning/edit', 'edit')->name('elearning.edit');
        Route::post('site-settings/e-learning/update', 'update')->name('elearning.update');
        Route::delete('site-settings/e-learning/delete/{id}', 'destroy')->name('elearning.destory');
        Route::post('site-settings/e-learning/restore/{id}', 'restore')->name('elearning.restore');
        Route::post('site-settings/e-learning/update-status/{id}', 'updateStatus')->name('elearning.update.status');
    });

    Route::controller(StudentOptionController::class)->group(function(){
        Route::get('site-settings/student-options', 'index')->name('student.options');
    });

    Route::controller(TitleController::class)->group(function() {
        Route::get('titles', 'index')->name('titles'); 
        Route::get('titles/list', 'list')->name('titles.list'); 
        Route::post('titles/store', 'store')->name('titles.store'); 
        Route::get('titles/edit/{id}', 'edit')->name('titles.edit');
        Route::post('titles/update', 'update')->name('titles.update');
        Route::delete('titles/delete/{id}', 'destroy')->name('titles.destory');
        Route::post('titles/restore/{id}', 'restore')->name('titles.restore');
        Route::post('titles/update-status/{id}', 'updateStatus')->name('titles.update.status');
    
        Route::get('titles/export', 'export')->name('titles.export');
        Route::post('titles/import', 'import')->name('titles.import');
    });

    Route::controller(EthnicityController::class)->group(function() {
        Route::get('ethnic', 'index')->name('ethnic'); 
        Route::get('ethnic/list', 'list')->name('ethnic.list'); 
        Route::post('ethnic/store', 'store')->name('ethnic.store'); 
        Route::get('ethnic/edit/{id}', 'edit')->name('ethnic.edit');
        Route::post('ethnic/update', 'update')->name('ethnic.update');
        Route::delete('ethnic/delete/{id}', 'destroy')->name('ethnic.destory');
        Route::post('ethnic/restore/{id}', 'restore')->name('ethnic.restore');
        Route::post('ethnic/update-status/{id}', 'updateStatus')->name('ethnic.update.status');
    
        Route::get('ethnic/export', 'export')->name('ethnic.export');
        Route::post('ethnic/import', 'import')->name('ethnic.import');
    });

    Route::controller(KinsRelationController::class)->group(function() {
        Route::get('kin-relations', 'index')->name('kin.relations'); 
        Route::get('kin-relations/list', 'list')->name('kin.relations.list'); 
        Route::post('kin-relations/store', 'store')->name('kin.relations.store'); 
        Route::get('kin-relations/edit/{id}', 'edit')->name('kin.relations.edit');
        Route::post('kin-relations/update', 'update')->name('kin.relations.update');
        Route::delete('kin-relations/delete/{id}', 'destroy')->name('kin.relations.destory');
        Route::post('kin-relations/restore/{id}', 'restore')->name('kin.relations.restore');
        Route::post('kin-relations/update-status/{id}', 'updateStatus')->name('kin.relations.update.status');
    
        Route::get('kin-relations/export', 'export')->name('kin-relations.export');
        Route::post('kin-relations/import', 'import')->name('kin-relations.import');
    });

    Route::controller(SexualOrientationController::class)->group(function() {
        Route::get('sex-orientation', 'index')->name('sex.orientation'); 
        Route::get('sex-orientation/list', 'list')->name('sex.orientation.list'); 
        Route::post('sex-orientation/store', 'store')->name('sex.orientation.store'); 
        Route::get('sex-orientation/edit/{id}', 'edit')->name('sex.orientation.edit');
        Route::post('sex-orientation/update', 'update')->name('sex.orientation.update');
        Route::delete('sex-orientation/delete/{id}', 'destroy')->name('sex.orientation.destory');
        Route::post('sex-orientation/restore/{id}', 'restore')->name('sex.orientation.restore');
        Route::post('sex-orientation/update-status/{id}', 'updateStatus')->name('sex.orientation.update.status');
    
        Route::get('sex-orientation/export', 'export')->name('sex-orientation.export');
        Route::post('sex-orientation/import', 'import')->name('sex-orientation.import');
    });

    Route::controller(ReligionController::class)->group(function() {
        Route::get('religion', 'index')->name('religion'); 
        Route::get('religion/list', 'list')->name('religion.list'); 
        Route::post('religion/store', 'store')->name('religion.store'); 
        Route::get('religion/edit/{id}', 'edit')->name('religion.edit');
        Route::post('religion/update', 'update')->name('religion.update');
        Route::delete('religion/delete/{id}', 'destroy')->name('religion.destory');
        Route::post('religion/restore/{id}', 'restore')->name('religion.restore');
        Route::post('religion/update-status/{id}', 'updateStatus')->name('religion.update.status');
    
        Route::get('religion/export', 'export')->name('religion.export');
        Route::post('religion/import', 'import')->name('religion.import');
    });
    
    Route::controller(HesaGenderController::class)->group(function() {
        Route::get('gender', 'index')->name('gender'); 
        Route::get('gender/list', 'list')->name('gender.list'); 
        Route::post('gender/store', 'store')->name('gender.store'); 
        Route::get('gender/edit/{id}', 'edit')->name('gender.edit');
        Route::post('gender/update', 'update')->name('gender.update');
        Route::delete('gender/delete/{id}', 'destroy')->name('gender.destory');
        Route::post('gender/restore/{id}', 'restore')->name('gender.restore');
        Route::post('gender/update-status/{id}', 'updateStatus')->name('gender.update.status');
    
        Route::get('gender/export', 'export')->name('gender.export');
        Route::post('gender/import', 'import')->name('gender.import');
    });

    Route::controller(CountryController::class)->group(function() {
        Route::get('countries', 'index')->name('countries'); 
        Route::get('countries/list', 'list')->name('countries.list'); 
        Route::post('countries/store', 'store')->name('countries.store'); 
        Route::get('countries/edit/{id}', 'edit')->name('countries.edit');
        Route::post('countries/update', 'update')->name('countries.update');
        Route::delete('countries/delete/{id}', 'destroy')->name('countries.destory');
        Route::post('countries/restore/{id}', 'restore')->name('countries.restore');
        Route::post('countries/update-status/{id}', 'updateStatus')->name('countries.update.status');
    
        Route::get('countries/export', 'export')->name('countries.export');
        Route::post('countries/import', 'import')->name('countries.import');
    });

    Route::controller(DisabilityController::class)->group(function() {
        Route::get('disabilities', 'index')->name('disabilities'); 
        Route::get('disabilities/list', 'list')->name('disabilities.list'); 
        Route::post('disabilities/store', 'store')->name('disabilities.store'); 
        Route::get('disabilities/edit/{id}', 'edit')->name('disabilities.edit');
        Route::post('disabilities/update', 'update')->name('disabilities.update');
        Route::delete('disabilities/delete/{id}', 'destroy')->name('disabilities.destory');
        Route::post('disabilities/restore/{id}', 'restore')->name('disabilities.restore');
        Route::post('disabilities/update-status/{id}', 'updateStatus')->name('disabilities.update.status');
    
        Route::get('disabilities/export', 'export')->name('disabilities.export');
        Route::post('disabilities/import', 'import')->name('disabilities.import');
    });

    Route::controller(FeeEligibilityController::class)->group(function() {
        Route::get('feeeligibilities', 'index')->name('feeeligibilities'); 
        Route::get('feeeligibilities/list', 'list')->name('feeeligibilities.list'); 
        Route::post('feeeligibilities/store', 'store')->name('feeeligibilities.store'); 
        Route::get('feeeligibilities/edit/{id}', 'edit')->name('feeeligibilities.edit');
        Route::post('feeeligibilities/update', 'update')->name('feeeligibilities.update');
        Route::delete('feeeligibilities/delete/{id}', 'destroy')->name('feeeligibilities.destory');
        Route::post('feeeligibilities/restore/{id}', 'restore')->name('feeeligibilities.restore');
        Route::post('feeeligibilities/update-status/{id}', 'updateStatus')->name('feeeligibilities.update.status');
    
        Route::get('feeeligibilities/export', 'export')->name('feeeligibilities.export');
        Route::post('feeeligibilities/import', 'import')->name('feeeligibilities.import');
    });

    Route::controller(ApelCreditController::class)->group(function() {
        Route::get('apelcred', 'index')->name('apelcred'); 
        Route::get('apelcred/list', 'list')->name('apelcred.list'); 
        Route::post('apelcred/store', 'store')->name('apelcred.store'); 
        Route::get('apelcred/edit/{id}', 'edit')->name('apelcred.edit');
        Route::post('apelcred/update', 'update')->name('apelcred.update');
        Route::delete('apelcred/delete/{id}', 'destroy')->name('apelcred.destory');
        Route::post('apelcred/restore/{id}', 'restore')->name('apelcred.restore');
        Route::post('apelcred/update-status/{id}', 'updateStatus')->name('apelcred.update.status');
    
        Route::get('apelcred/export', 'export')->name('apelcred.export');
        Route::post('apelcred/import', 'import')->name('apelcred.import');
    });

    Route::controller(HighestQualificationOnEntryController::class)->group(function() {
        Route::get('highest-qualification-on-entry', 'index')->name('highestqoe'); 
        Route::get('highest-qualification-on-entry/list', 'list')->name('highestqoe.list'); 
        Route::post('highest-qualification-on-entry/store', 'store')->name('highestqoe.store'); 
        Route::get('highest-qualification-on-entry/edit/{id}', 'edit')->name('highestqoe.edit');
        Route::post('highest-qualification-on-entry/update', 'update')->name('highestqoe.update');
        Route::delete('highest-qualification-on-entry/delete/{id}', 'destroy')->name('highestqoe.destory');
        Route::post('highest-qualification-on-entry/restore/{id}', 'restore')->name('highestqoe.restore');
        Route::post('highest-qualification-on-entry/update-status/{id}', 'updateStatus')->name('highestqoe.update.status');
    
        Route::get('highest-qualification-on-entry/export', 'export')->name('highestqoe.export');
        Route::post('highest-qualification-on-entry/import', 'import')->name('highestqoe.import');
    });

    Route::controller(CountryOfPermanentAddressController::class)->group(function() {
        Route::get('country-of-permanent-address', 'index')->name('permaddcountry'); 
        Route::get('country-of-permanent-address/list', 'list')->name('permaddcountry.list'); 
        Route::post('country-of-permanent-address/store', 'store')->name('permaddcountry.store'); 
        Route::get('country-of-permanent-address/edit/{id}', 'edit')->name('permaddcountry.edit');
        Route::post('country-of-permanent-address/update', 'update')->name('permaddcountry.update');
        Route::delete('country-of-permanent-address/delete/{id}', 'destroy')->name('permaddcountry.destory');
        Route::post('country-of-permanent-address/restore/{id}', 'restore')->name('permaddcountry.restore');
        Route::post('country-of-permanent-address/update-status/{id}', 'updateStatus')->name('permaddcountry.update.status');
    
        Route::get('country-of-permanent-address/export', 'export')->name('permaddcountry.export');
        Route::post('country-of-permanent-address/import', 'import')->name('permaddcountry.import');
    });

    Route::controller(PreviousProviderController::class)->group(function() {
        Route::get('previous-provider', 'index')->name('previousprovider'); 
        Route::get('previous-provider/list', 'list')->name('previousprovider.list'); 
        Route::post('previous-provider/store', 'store')->name('previousprovider.store'); 
        Route::get('previous-provider/edit/{id}', 'edit')->name('previousprovider.edit');
        Route::post('previous-provider/update', 'update')->name('previousprovider.update');
        Route::delete('previous-provider/delete/{id}', 'destroy')->name('previousprovider.destory');
        Route::post('previous-provider/restore/{id}', 'restore')->name('previousprovider.restore');
        Route::post('previous-provider/update-status/{id}', 'updateStatus')->name('previousprovider.update.status');
    
        Route::get('previous-provider/export', 'export')->name('previousprovider.export');
        Route::post('previous-provider/import', 'import')->name('previousprovider.import');
    });

    Route::controller(QualificationTypeIdentifierController::class)->group(function() {
        Route::get('qualification-identifier', 'index')->name('qaualtypeid'); 
        Route::get('qualification-identifier/list', 'list')->name('qaualtypeid.list'); 
        Route::post('qualification-identifier/store', 'store')->name('qaualtypeid.store'); 
        Route::get('qualification-identifier/edit/{id}', 'edit')->name('qaualtypeid.edit');
        Route::post('qualification-identifier/update', 'update')->name('qaualtypeid.update');
        Route::delete('qualification-identifier/delete/{id}', 'destroy')->name('qaualtypeid.destory');
        Route::post('qualification-identifier/restore/{id}', 'restore')->name('qaualtypeid.restore');
        Route::post('qualification-identifier/update-status/{id}', 'updateStatus')->name('qaualtypeid.update.status');
    
        Route::get('qualification-identifier/export', 'export')->name('qaualtypeid.export');
        Route::post('qualification-identifier/import', 'import')->name('qaualtypeid.import');
    });

    Route::controller(ReasonForEngagementEndingController::class)->group(function() {
        Route::get('reason-end', 'index')->name('rsnengend'); 
        Route::get('reason-end/list', 'list')->name('rsnengend.list'); 
        Route::post('reason-end/store', 'store')->name('rsnengend.store'); 
        Route::get('reason-end/edit/{id}', 'edit')->name('rsnengend.edit');
        Route::post('reason-end/update', 'update')->name('rsnengend.update');
        Route::delete('reason-end/delete/{id}', 'destroy')->name('rsnengend.destory');
        Route::post('reason-end/restore/{id}', 'restore')->name('rsnengend.restore');
        Route::post('reason-end/update-status/{id}', 'updateStatus')->name('rsnengend.update.status');
    
        Route::get('reason-end/export', 'export')->name('rsnengend.export');
        Route::post('reason-end/import', 'import')->name('rsnengend.import');
    });    
    
    Route::controller(TermTimeAccommodationTypeController::class)->group(function() {
        Route::get('termtimeaccommodationtype', 'index')->name('termtimeaccommodationtype'); 
        Route::get('termtimeaccommodationtype/list', 'list')->name('termtimeaccommodationtype.list'); 
        Route::post('termtimeaccommodationtype/store', 'store')->name('termtimeaccommodationtype.store'); 
        Route::get('termtimeaccommodationtype/edit/{id}', 'edit')->name('termtimeaccommodationtype.edit');
        Route::post('termtimeaccommodationtype/update', 'update')->name('termtimeaccommodationtype.update');
        Route::delete('termtimeaccommodationtype/delete/{id}', 'destroy')->name('termtimeaccommodationtype.destory');
        Route::post('termtimeaccommodationtype/restore/{id}', 'restore')->name('termtimeaccommodationtype.restore');
        Route::post('termtimeaccommodationtype/update-status/{id}', 'updateStatus')->name('termtimeaccommodationtype.update.status');
    
        Route::get('termtimeaccommodationtype/export', 'export')->name('termtimeaccommodationtype.export');
        Route::post('termtimeaccommodationtype/import', 'import')->name('termtimeaccommodationtype.import');
    });

    Route::controller(SexIdentifierController::class)->group(function() {
        Route::get('sexidentifier', 'index')->name('sexidentifier'); 
        Route::get('sexidentifier/list', 'list')->name('sexidentifier.list'); 
        Route::post('sexidentifier/store', 'store')->name('sexidentifier.store'); 
        Route::get('sexidentifier/edit/{id}', 'edit')->name('sexidentifier.edit');
        Route::post('sexidentifier/update', 'update')->name('sexidentifier.update');
        Route::delete('sexidentifier/delete/{id}', 'destroy')->name('sexidentifier.destory');
        Route::post('sexidentifier/restore/{id}', 'restore')->name('sexidentifier.restore');
        Route::post('sexidentifier/update-status/{id}', 'updateStatus')->name('sexidentifier.update.status');
    
        Route::get('studentidentifier/export', 'export')->name('studentidentifier.export');
        Route::post('studentidentifier/import', 'import')->name('studentidentifier.import');
    });
    
    Route::controller(HolidayYearController::class)->group(function() {
        Route::get('site-settings/holiday-year', 'index')->name('holiday.year'); 
        Route::get('site-settings/holiday-year/list', 'list')->name('holiday.year.list');        
        Route::post('site-settings/holiday-year/store', 'store')->name('holiday.year.store');
        Route::post('site-settings/holiday-year/edit', 'edit')->name('holiday.year.edit');
        Route::post('site-settings/holiday-year/update', 'update')->name('holiday.year.update');
        Route::delete('site-settings/holiday-year/delete/{id}', 'destroy')->name('holiday.year.destory');
        Route::post('site-settings/holiday-year/restore/{id}', 'restore')->name('holiday.year.restore');
        Route::post('site-settings/holiday-year/update-status', 'updateStatus')->name('holiday.year.update.status'); 

        Route::get('site-settings/holiday-year/leave-options/{id}', 'leaveOptions')->name('holiday.year.leave.option'); 
        Route::post('site-settings/holiday-year/leave-options', 'updateLeaveOptions')->name('holiday.year.update.leave.option'); 
    });
    
    Route::controller(HrBankHolidayController::class)->group(function() {
        Route::get('site-settings/bank-holiday/all/{id}', 'index')->name('hr.bank.holiday'); 
        Route::get('site-settings/bank-holiday/list', 'list')->name('hr.bank.holiday.list');
        Route::post('site-settings/bank-holiday/edit', 'edit')->name('hr.bank.holiday.edit');
        Route::post('site-settings/bank-holiday/update', 'update')->name('hr.bank.holiday.update');
        Route::delete('site-settings/bank-holiday/delete/{id}', 'destroy')->name('hr.bank.holiday.destory');
        Route::post('site-settings/bank-holiday/restore/{id}', 'restore')->name('hr.bank.holiday.restore');

        Route::get('site-settings/bank-holiday/export/{id}', 'export')->name('hr.bank.holiday.export');
        Route::post('site-settings/bank-holiday/import', 'import')->name('hr.bank.holiday.import');
    });

    Route::controller(AddressController::class)->group(function() {
        Route::post('address/get-address', 'getAddress')->name('address.get');
        Route::post('address/store', 'store')->name('address.store');
    });
});
