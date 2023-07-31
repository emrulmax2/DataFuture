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
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\Applicant\ApplicantEmploymentController;
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
use App\Http\Controllers\EthnicityController;
use App\Http\Controllers\KinsRelationController;
use App\Http\Controllers\SexualOrientationController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DisabilityController;
use App\Http\Controllers\DocumentSettingsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionCategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionTemplateController;
use App\Http\Controllers\ProcessListController;
use App\Http\Controllers\TaskListController;

use App\Http\Controllers\InterviewListController;
use App\Http\Controllers\ApplicantInterviewListController;
use App\Http\Controllers\InterviewAssignedController;

use App\Http\Controllers\Applicant\Auth\LoginController;
use App\Http\Controllers\Applicant\Auth\RegisterController;

use App\Http\Controllers\Auth\GoogleSocialiteController;

use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboard;
use App\Http\Controllers\Staff\DashboardController as StaffDashboard;
use App\Http\Controllers\Applicant\Auth\VerificationController;

use App\Models\ApplicantUser;
use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\ApplicantQualificationCongroller;
use App\Http\Controllers\Applicant\ApplicantVarifyTempEmailController;
use App\Http\Controllers\UserController;

use App\Http\Middleware\EnsureExpiredDateIsValid;

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
        Route::post('titles/store', 'store')->name('titles.store'); 
        Route::get('titles/edit/{id}', 'edit')->name('titles.edit');
        Route::post('titles/update', 'update')->name('titles.update');
        Route::delete('titles/delete/{id}', 'destroy')->name('titles.destory');
        Route::post('titles/restore/{id}', 'restore')->name('titles.restore');
    });

    Route::controller(EthnicityController::class)->group(function() {
        Route::get('ethnic', 'index')->name('ethnic'); 
        Route::get('ethnic/list', 'list')->name('ethnic.list'); 
        Route::post('ethnic/store', 'store')->name('ethnic.store'); 
        Route::get('ethnic/edit/{id}', 'edit')->name('ethnic.edit');
        Route::post('ethnic/update', 'update')->name('ethnic.update');
        Route::delete('ethnic/delete/{id}', 'destroy')->name('ethnic.destory');
        Route::post('ethnic/restore/{id}', 'restore')->name('ethnic.restore');
    });

    Route::controller(KinsRelationController::class)->group(function() {
        Route::get('kin-relations', 'index')->name('kin.relations'); 
        Route::get('kin-relations/list', 'list')->name('kin.relations.list'); 
        Route::post('kin-relations/store', 'store')->name('kin.relations.store'); 
        Route::get('kin-relations/edit/{id}', 'edit')->name('kin.relations.edit');
        Route::post('kin-relations/update', 'update')->name('kin.relations.update');
        Route::delete('kin-relations/delete/{id}', 'destroy')->name('kin.relations.destory');
        Route::post('kin-relations/restore/{id}', 'restore')->name('kin.relations.restore');
    });

    Route::controller(SexualOrientationController::class)->group(function() {
        Route::get('sex-orientation', 'index')->name('sex.orientation'); 
        Route::get('sex-orientation/list', 'list')->name('sex.orientation.list'); 
        Route::post('sex-orientation/store', 'store')->name('sex.orientation.store'); 
        Route::get('sex-orientation/edit/{id}', 'edit')->name('sex.orientation.edit');
        Route::post('sex-orientation/update', 'update')->name('sex.orientation.update');
        Route::delete('sex-orientation/delete/{id}', 'destroy')->name('sex.orientation.destory');
        Route::post('sex-orientation/restore/{id}', 'restore')->name('sex.orientation.restore');
    });

    Route::controller(ReligionController::class)->group(function() {
        Route::get('religion', 'index')->name('religion'); 
        Route::get('religion/list', 'list')->name('religion.list'); 
        Route::post('religion/store', 'store')->name('religion.store'); 
        Route::get('religion/edit/{id}', 'edit')->name('religion.edit');
        Route::post('religion/update', 'update')->name('religion.update');
        Route::delete('religion/delete/{id}', 'destroy')->name('religion.destory');
        Route::post('religion/restore/{id}', 'restore')->name('religion.restore');
    });

    Route::controller(StatusController::class)->group(function() {
        Route::get('statuses', 'index')->name('statuses'); 
        Route::get('statuses/list', 'list')->name('statuses.list'); 
        Route::post('statuses/store', 'store')->name('statuses.store'); 
        Route::get('statuses/edit/{id}', 'edit')->name('statuses.edit');
        Route::post('statuses/update', 'update')->name('statuses.update');
        Route::delete('statuses/delete/{id}', 'destroy')->name('statuses.destory');
        Route::post('statuses/restore/{id}', 'restore')->name('statuses.restore');
    });

    Route::controller(CountryController::class)->group(function() {
        Route::get('countries', 'index')->name('countries'); 
        Route::get('countries/list', 'list')->name('countries.list'); 
        Route::post('countries/store', 'store')->name('countries.store'); 
        Route::get('countries/edit/{id}', 'edit')->name('countries.edit');
        Route::post('countries/update', 'update')->name('countries.update');
        Route::delete('countries/delete/{id}', 'destroy')->name('countries.destory');
        Route::post('countries/restore/{id}', 'restore')->name('countries.restore');
    });

    Route::controller(DisabilityController::class)->group(function() {
        Route::get('disabilities', 'index')->name('disabilities'); 
        Route::get('disabilities/list', 'list')->name('disabilities.list'); 
        Route::post('disabilities/store', 'store')->name('disabilities.store'); 
        Route::get('disabilities/edit/{id}', 'edit')->name('disabilities.edit');
        Route::post('disabilities/update', 'update')->name('disabilities.update');
        Route::delete('disabilities/delete/{id}', 'destroy')->name('disabilities.destory');
        Route::post('disabilities/restore/{id}', 'restore')->name('disabilities.restore');
    });

    Route::controller(CommonSmtpController::class)->group(function() {
        Route::get('common-smtp', 'index')->name('common.smtp'); 
        Route::get('common-smtp/list', 'list')->name('common.smtp.list'); 
        Route::post('common-smtp/store', 'store')->name('common.smtp.store');
        Route::get('common-smtp/edit/{id}', 'edit')->name('common.smtp.edit');
        Route::post('common-smtp/update/{id}', 'update')->name('common.smtp.update');

        Route::delete('common-smtp/delete/{id}', 'destroy')->name('common.smtp.destory');
        Route::post('common-smtp/restore/{id}', 'restore')->name('common.smtp.restore');
    });

    Route::controller(LetterSetController::class)->group(function() {
        Route::get('letter-sets', 'index')->name('letter.set'); 
        Route::get('letter-sets/list', 'list')->name('letter.set.list'); 
        Route::post('letter-sets/store', 'store')->name('letter.set.store');
        Route::get('letter-sets/edit/{id}', 'edit')->name('letter.set.edit');
        Route::post('letter-sets/update', 'update')->name('letter.set.update');

        Route::delete('letter-sets/delete/{id}', 'destroy')->name('letter.set.destory');
        Route::post('letter-sets/restore/{id}', 'restore')->name('letter.set.restore');
    });

    Route::controller(SignatoryController::class)->group(function() {
        Route::get('signatory', 'index')->name('signatory'); 
        Route::get('signatory/list', 'list')->name('signatory.list'); 
        Route::post('signatory/store', 'store')->name('signatory.store');
        Route::post('signatory/edit', 'edit')->name('signatory.edit');
        Route::post('signatory/update', 'update')->name('signatory.update');
        Route::delete('signatory/delete/{id}', 'destroy')->name('signatory.destory');
        Route::post('signatory/restore/{id}', 'restore')->name('signatory.restore');
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
        Route::post('admission/send-sms', 'admissionCommunicationSendSms')->name('admission.communication.send.sms');
        Route::get('admission/sms-list', 'admissionCommunicationSmsList')->name('admission.communication.sms.list');
        Route::post('admission/sms-show', 'admissionCommunicationSmsShow')->name('admission.communication.sms.show');
        Route::delete('admission/destory-sms', 'admissionDestroySms')->name('admission.communication.sms.destroy');
        Route::post('admission/restore-sms', 'admissionRestoreSms')->name('admission.communication.sms.restore');

        Route::post('admission/get-letter-set', 'admissionGetLetterSet')->name('admission.communication.get.letter.set');
        Route::post('admission/send-letter', 'admissionSendLetter')->name('admission.communication.send.letter');
        
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

    Route::controller(StaffDashboard::class)->group(function() {
        Route::get('/dashboard', 'index')->name('staff.dashboard');
        Route::get('/dashboard/list', 'list')->name('dashboard.staff.list');
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
        
        Route::get('bankholidays/export/', 'export')->name('bankholidays.export');
        Route::post('bankholidays/import', 'import')->name('bankholidays.import');
    });

    // Added on 26.04.23
    Route::controller(DocumentSettingsController::class)->group(function() {
        Route::get('documentsettings', 'index')->name('documentsettings'); 
        Route::get('documentsettings/list', 'list')->name('documentsettings.list'); 
        Route::post('documentsettings/store', 'store')->name('documentsettings.store'); 
        Route::get('documentsettings/edit/{id}', 'edit')->name('documentsettings.edit');
        Route::post('documentsettings/update', 'update')->name('documentsettings.update');
        Route::delete('documentsettings/delete/{id}', 'destroy')->name('documentsettings.destory');
        Route::post('documentsettings/restore/{id}', 'restore')->name('documentsettings.restore');
    });

    // Added on 04.05.23
    Route::controller(DepartmentController::class)->group(function() {
        Route::get('department', 'index')->name('department'); 
        Route::get('department/list', 'list')->name('department.list'); 
        Route::post('department/store', 'store')->name('department.store'); 
        Route::get('department/edit/{id}', 'edit')->name('department.edit');
        Route::post('department/update', 'update')->name('department.update');
        Route::delete('department/delete/{id}', 'destroy')->name('department.destory');
        Route::post('department/restore/{id}', 'restore')->name('department.restore');
    });

    Route::controller(PermissionCategoryController::class)->group(function() {
        Route::get('permissioncategory', 'index')->name('permissioncategory'); 
        Route::get('permissioncategory/list', 'list')->name('permissioncategory.list'); 
        Route::post('permissioncategory/store', 'store')->name('permissioncategory.store'); 
        Route::get('permissioncategory/edit/{id}', 'edit')->name('permissioncategory.edit');
        Route::post('permissioncategory/update', 'update')->name('permissioncategory.update');
        Route::delete('permissioncategory/delete/{id}', 'destroy')->name('permissioncategory.destory');
        Route::post('permissioncategory/restore/{id}', 'restore')->name('permissioncategory.restore');
    });

    
    Route::controller(RoleController::class)->group(function() {
        Route::get('roles', 'index')->name('roles'); 
        Route::get('roles/list', 'list')->name('roles.list'); 
        Route::post('roles/store', 'store')->name('roles.store'); 
        Route::get('roles/show/{id}', 'show')->name('roles.show');
        Route::get('roles/edit/{id}', 'edit')->name('roles.edit');
        Route::post('roles/update', 'update')->name('roles.update');
        Route::delete('roles/delete/{id}', 'destroy')->name('roles.destory');
        Route::post('roles/restore/{id}', 'restore')->name('roles.restore');
    });

    Route::controller(PermissionTemplateController::class)->group(function() {
        Route::get('permissiontemplate/list', 'list')->name('permissiontemplate.list'); 
        Route::post('permissiontemplate/store', 'store')->name('permissiontemplate.store'); 
        Route::get('permissiontemplate/edit/{id}', 'edit')->name('permissiontemplate.edit');
        Route::post('permissiontemplate/update', 'update')->name('permissiontemplate.update');
        Route::delete('permissiontemplate/delete/{id}', 'destroy')->name('permissiontemplate.destory');
        Route::post('permissiontemplate/restore/{id}', 'restore')->name('permissiontemplate.restore');
    });

    Route::controller(ProcessListController::class)->group(function() {
        Route::get('processlist', 'index')->name('processlist'); 
        Route::get('processlist/list', 'list')->name('processlist.list'); 
        Route::post('processlist/store', 'store')->name('processlist.store'); 
        Route::get('processlist/edit/{id}', 'edit')->name('processlist.edit');
        Route::post('processlist/update', 'update')->name('processlist.update');
        Route::delete('processlist/delete/{id}', 'destroy')->name('processlist.destory');
        Route::post('processlist/restore/{id}', 'restore')->name('processlist.restore');
    });

    Route::controller(TaskListController::class)->group(function() {
        Route::get('tasklist', 'index')->name('tasklist'); 
        Route::get('tasklist/list', 'list')->name('tasklist.list'); 
        Route::post('tasklist/store', 'store')->name('tasklist.store'); 
        Route::get('tasklist/edit/{id}', 'edit')->name('tasklist.edit');
        Route::post('tasklist/update', 'update')->name('tasklist.update');
        Route::delete('tasklist/delete/{id}', 'destroy')->name('tasklist.destory');
        Route::post('tasklist/restore/{id}', 'restore')->name('tasklist.restore');
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

    });
    
});
