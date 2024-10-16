<?php

use App\Http\Controllers\Accounts\AccCsvTransactionController;
use App\Http\Controllers\Accounts\StorageController;
use App\Http\Controllers\Accounts\SummaryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\ColorSchemeController;
use App\Http\Controllers\CourseManagement\CourseController;
use App\Http\Controllers\CourseManagement\SemesterController;
use App\Http\Controllers\Settings\CourseQualificationController;
use App\Http\Controllers\Settings\SourceTutionFeeController;
use App\Http\Controllers\Settings\AcademicYearController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\Agent\AgentController;
use App\Http\Controllers\Agent\AgentDocumentsController;
use App\Http\Controllers\Agent\AgentMyAccountController;
use App\Http\Controllers\Applicant\ApplicantEmploymentController;
use App\Http\Controllers\CourseManagement\GroupController;
use App\Http\Controllers\Settings\VenueController;
use App\Http\Controllers\CourseManagement\CoursCreationController;
use App\Http\Controllers\CourseManagement\ModuleLevelController;
use App\Http\Controllers\CourseManagement\CourseModuleController;
use App\Http\Controllers\CourseManagement\CourseBaseDatafutureCntroller;
use App\Http\Controllers\CourseManagement\CourseModuleBaseAssesmentController;
use App\Http\Controllers\CourseManagement\ModuleDatafutureController;
use App\Http\Controllers\CourseManagement\CourseCreationAvailabilityController;
use App\Http\Controllers\CourseManagement\CourseCreationDatafutureController;
use App\Http\Controllers\CourseManagement\CourseCreationInstanceController;
use App\Http\Controllers\CourseManagement\InstanceTermController;
use App\Http\Controllers\CourseManagement\TermModuleCreationController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Settings\RoomController;
use App\Http\Controllers\CourseManagement\PlanController;
use App\Http\Controllers\CourseManagement\PlansDateListController;
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

use App\Http\Controllers\Agent\Auth\LoginController as AgentLoginController;
use App\Http\Controllers\Agent\Auth\RegisterController as AgentRegisterController;
use App\Http\Controllers\Agent\Auth\ForgetPasswordController as AgentForgetPasswordController;
use App\Http\Controllers\Agent\Frontend\ApplicationCheckController;
use App\Http\Controllers\Agent\Frontend\ApplicationController as FrontendApplicationController;
use App\Http\Controllers\Agent\Frontend\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\Auth\VerificationController as AgentVerificationController;
use App\Http\Controllers\Agent\SubAgentController;
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
use App\Http\Controllers\Applicant\ApplicantQualificationController;
use App\Http\Controllers\Applicant\ApplicantVarifyTempEmailController;
use App\Http\Controllers\Applicant\Auth\ForgetPasswordController;
use App\Http\Controllers\Settings\CommonSmtpController;
use App\Http\Controllers\Settings\LetterSetController;
use App\Http\Controllers\Settings\SignatoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Settings\SmsTemplateController;
use App\Http\Controllers\Settings\EmailTemplateController;
use App\Http\Controllers\ApplicantProfilePrintController;
use App\Http\Controllers\AssessmentPlanController;
use App\Http\Controllers\AssessmentTypeController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Attendance\TutorAttendanceController;
use App\Http\Controllers\AttendanceLiveController;
use App\Http\Controllers\Communication\BulkCommunicationController;
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\CourseManagement\AssignController;
use App\Http\Controllers\CourseManagement\CourseManagementController;
use App\Http\Controllers\HR\EmployeeAbsentTodayController;
use App\Http\Controllers\HR\EmployeeVisaExpiryController;
use App\Http\Controllers\HR\EmployeeWorkingPatternDetailController;
use App\Http\Controllers\HR\EmployeePaymentSettingsController;
use App\Http\Controllers\HR\EmployeeBankDetailController;
use App\Http\Controllers\HR\EmployeePenssionSchemeController;
use App\Http\Controllers\HR\EmployeeAddressController;
use App\Http\Controllers\HR\EmployeeAppraisalController;
use App\Http\Controllers\HR\EmployeeAppraisalDocumentController;
use App\Http\Controllers\HR\EmployeeAttendanceController;
use App\Http\Controllers\HR\EmployeeAttendanceLiveController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\EmployeeDocumentsController;
use App\Http\Controllers\HR\EmployeeEligibilityController;
use App\Http\Controllers\HR\EmployeeEmergencyContactController;
use App\Http\Controllers\HR\EmployeeHolidayController;
use App\Http\Controllers\HR\EmployeeNotesController;
use App\Http\Controllers\HR\EmployeePassportExpiryController;
use App\Http\Controllers\HR\EmployeeProfileController;
use App\Http\Controllers\HR\EmployeeWorkingPatternController;
use App\Http\Controllers\HR\EmployeeTermController;
use App\Http\Controllers\HR\EmployeeWorkingPatternPayController;
use App\Http\Controllers\HR\EmploymentController;
use App\Http\Controllers\HR\EmployeePortalController;
use App\Http\Controllers\HR\EmployeePrivilegeController;
use App\Http\Controllers\HR\EmployeeTimeKeepingController;
use App\Http\Controllers\HR\EmployeeUpcomingAppraisalController;
use App\Http\Controllers\HR\Reports\BirthdayReportController;
use App\Http\Controllers\HR\Reports\DiversityReportController;
use App\Http\Controllers\HR\Reports\EligibilityReportController;
use App\Http\Controllers\HR\Reports\EmployeeContactDetailController;
use App\Http\Controllers\HR\Reports\EmploymentReportController;
use App\Http\Controllers\HR\Reports\StarterReportController;
use App\Http\Controllers\HR\Reports\LengthServiceController;
use App\Http\Controllers\HR\Reports\RecordCardController;
use App\Http\Controllers\HR\Reports\TelephoneDirectoryController;

use App\Http\Controllers\Personal_Tutor\DashboardController;
use App\Http\Controllers\PlanContentUploadController;
use App\Http\Controllers\PlanParticipantController;
use App\Http\Controllers\PlanTaskController;
use App\Http\Controllers\PlanTaskUploadController;
use App\Http\Controllers\CourseManagement\PlanTreeController;
use App\Http\Controllers\Programme\DashboardController as ProgrammeDashboardController;
use App\Http\Controllers\Settings\ConsentPolicyController;
use App\Http\Controllers\Settings\LetterHeaderFooterController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\Settings\AwardingBodyController;
use App\Http\Controllers\Settings\DatafutureFieldCategoryController;
use App\Http\Controllers\Settings\DatafutureFieldController;
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
use App\Http\Controllers\Settings\HrConditionController;
use App\Http\Controllers\Settings\PermissionTemplateGroupController;
use App\Http\Controllers\Settings\TermTypeController;
use App\Http\Controllers\Settings\VenueBaseDatafutureController;
use App\Http\Controllers\Student\SlcAgreementController;
use App\Http\Controllers\Student\SlcAttendanceController;
use App\Http\Controllers\Student\SlcInstallmentController;
use App\Http\Controllers\Student\SlcRegistrationController;
use App\Http\Controllers\Student\StudentAssignController;
use App\Http\Controllers\CourseManagement\TermDeclarationController;
use App\Http\Controllers\CourseManagement\TutorMonitorController;
use App\Http\Controllers\Filemanager\DocumentTagController;
use App\Http\Controllers\Filemanager\FilemanagerController;
use App\Http\Controllers\HR\EmployeeArchiveController;
use App\Http\Controllers\HR\EmployeeAttendancePunchController;
use App\Http\Controllers\HR\EmployeeTrainingController;
use App\Http\Controllers\HR\portal\reports\DataReportController;
use App\Http\Controllers\HR\Reports\AttendanceReportController;
use App\Http\Controllers\HR\Reports\HolidayHourReportController;
use App\Http\Controllers\InternalLinkController;
use App\Http\Controllers\Personal_Tutor\AttendancePercentageController;
use App\Http\Controllers\Reports\Accounts\CollectionReportController;
use App\Http\Controllers\Reports\Accounts\ConnectTransactionController;
use App\Http\Controllers\Reports\Accounts\DueReportController;
use App\Http\Controllers\Reports\Accounts\PaymentUploadManagementController;
use App\Http\Controllers\Reports\ApplicationAnalysisController;
use App\Http\Controllers\Reports\AttendanceReportController as ReportsAttendanceReportController;
use App\Http\Controllers\Reports\ClassStatusByTermController;
use App\Http\Controllers\Reports\IntakePerformance\AttendanceRateReportController;
use App\Http\Controllers\Reports\IntakePerformance\AwardRateReportController;
use App\Http\Controllers\Reports\StudentDataReportController;
use App\Http\Controllers\Reports\IntakePerformance\ContinuationReportController;
use App\Http\Controllers\Reports\IntakePerformance\RetentionRateReportController;
use App\Http\Controllers\Reports\IntakePerformance\SubmissionPassRateReportController;
use App\Http\Controllers\Reports\SlcDataReportController;
use App\Http\Controllers\Reports\SystemReportController;
use App\Http\Controllers\Reports\TermPerformance\TermPerformanceReportController;
use App\Http\Controllers\Settings\Studentoptions\CompanyController;
use App\Http\Controllers\Settings\Studentoptions\CompanySupervisorController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ResultPreviousController;
use App\Http\Controllers\Settings\AccBankController;
use App\Http\Controllers\Settings\AccCategoryController;
use App\Http\Controllers\Settings\AccMethodController;
use App\Http\Controllers\Settings\CommunicationTemplateController;
use App\Http\Controllers\Settings\DocumentRoleAndPermissionController;
use App\Http\Controllers\Settings\StudentFlagController;
use App\Http\Controllers\Settings\Studentoptions\HesaQualificationSubjectController;
use App\Http\Controllers\Staff\FlagManagementController;
use App\Http\Controllers\Staff\FollowupController;
use App\Http\Controllers\Staff\PendingTaskManagerController;
use App\Http\Controllers\Student\Frontend\AttendanceExcuseController;
use App\Http\Controllers\Student\Result\StudentResultController;
use App\Http\Controllers\Student\SlcCocController;
use App\Http\Controllers\Student\SlcMoneyReceiptController;
use App\Http\Controllers\Student\WorkPlacementController;
use App\Http\Controllers\User\UserHolidayController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\Tutor\DashboardController as TutorDashboard;
use App\Http\Controllers\TutorModuleActivityController;
use App\Http\Controllers\User\MyGroupController;
use App\Http\Controllers\User\MyStaffController;
use App\Http\Controllers\WblProfileController;
use App\Models\AgentUser;
use App\Models\EmployeeAttendancePunchHistory;
use App\Models\HesaQualificationSubject;

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
Route::controller(StudentController::class)->group(function() {
    Route::get('old-student/email/verified/{code}','verifiedEmail')->name('student.update.email.verified');
    
});
// Route::controller(CoursCreationController::class)->group(function() {
//     Route::get('global/course-creation/edit/{id}', 'edit')->name('global.course.creation.edit');
// });

Route::controller(EmployeeAttendancePunchController::class)->group(function(){
    Route::get('punch', 'index')->name('attendance.punch');
    Route::post('punch/get-attendance-history', 'getAttendanceHistory')->name('attendance.punch.get.history');
    Route::post('punch/store-attendance', 'store')->name('attendance.punch.store');
    Route::post('punch/store-attendance-dif', 'storeAttendance')->name('attendance.punch.store.dif');
});
Route::impersonate();
//All applicant have a prefix route name applicant.* value
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
        Route::post('change-password', 'submitChangePasswordForm')->name('change.password.post');
    });

    Route::controller(RegisterController::class)->middleware('applicant.loggedin')->group(function() {
        Route::get('register', 'index')->name('register');
        Route::post('register', 'store')->name('store.register');
    });

    /**
    * Verification Routes
    */
    Route::controller(VerificationController::class)->group(function() {
        //Route::get('email/verify', 'show')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['signed']);
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
            Route::get('application/course-creation-edit/{id}', 'CourseCreationList')->name('application.course.creation.edit');
            Route::post('application/review', 'review')->name('application.review');
            Route::get('application/show/{id}', 'show')->name('application.show');

            Route::post('application/verify-referral-code', 'verifyReferralCode')->name('application.verify.referral.code');
            Route::post('application/get-evening-weekend-status', 'getEveningWeekendStatus')->name('application.get.evening.weekend.status');
        });

        Route::controller(ApplicantQualificationController::class)->group(function() {
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
});
// all Agent have a prefix route name agent.* value
Route::prefix('/agent')->name('agent.')->group(function() {

    Route::controller(AgentLoginController::class)->middleware('agent.loggedin')->group(function() {

        Route::get('login', 'loginView')->name('login');
        Route::post('login', 'login')->name('check');
    });
    
    Route::controller(AgentRegisterController::class)->middleware('agent.loggedin')->group(function() {
        Route::get('register', 'index')->name('register');
        Route::post('register', 'store')->name('store.register');
    });

    Route::controller(AgentForgetPasswordController::class)->middleware('agent.loggedin')->group(function() {

        Route::get('forget-password',  'showForgetPasswordForm')->name('forget.password.get');
        Route::post('forget-password','submitForgetPasswordForm')->name('forget.password.post'); 
        Route::get('reset-password/{token}', 'showResetPasswordForm')->name('reset.password.get');
        Route::post('reset-password', 'submitResetPasswordForm')->name('reset.password.post');
        
    
    });
    /**
    * Verification Routes
    */
    Route::controller(AgentVerificationController::class)->group(function() {
        
        //Route::get('email/verify', 'show')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['signed']);
        
    });
    

    Route::middleware('auth.agent')->group(function() {

        Route::get('logout', [AgentLoginController::class, 'logout'])->name('logout');

        Route::controller(AgentForgetPasswordController::class)->group(function() {

            Route::post('change-password', 'submitChangePasswordForm')->name('change.password.post');
            
        });
        
        Route::controller(AgentDashboardController::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/list', 'list')->name('dashboard.applications.list');
        });
        Route::controller(ApplicationCheckController::class)->group(function() {
            Route::post('/store', 'store')->name('apply.check');
            Route::post('/update/{id}', 'update')->name('apply.update');
            Route::post('/verify/mobile', 'verifyMobile')->name('apply.verify');
            Route::post('/verify/email', 'verifyEmail')->name('apply.email.verify');
            Route::delete('/delete/{id}', 'destroy')->name('apply.destory');
            
        });

        Route::controller(FrontendApplicationController::class)->group(function() {
            Route::get('new-application/create/{applicant_user}', 'create')->name('application.create');
            Route::get('application/{checkedApplication}', 'index')->name('application');
            Route::get('application/show/{id}', 'show')->name('application.show');
        });

        Route::controller(ApplicationController::class)->group(function() {
            
            Route::post('agent-application/store-personal-details', 'storePersonalDetails')->name('application.store.personal');
            Route::post('agent-application/store-course-details', 'storeCourseDetails')->name('application.store.course');
            Route::post('agent-application/store-applicant-submission', 'storeApplicantSubmission')->name('application.store.submission');
            Route::get('agent-application/course-creation-edit/{id}', 'CourseCreationList')->name('application.course.creation.edit');
            Route::post('agent-application/review', 'review')->name('application.review');
            Route::post('agent-application/verify-referral-code', 'verifyReferralCode')->name('application.verify.referral.code');

            Route::post('agent-application/get-evening-weekend-status', 'getEveningWeekendStatus')->name('application.get.evening.weekend.status');
        });

        Route::controller(ApplicantQualificationController::class)->group(function() {
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

        Route::controller(AgentMyAccountController::class)->group(function() {
            Route::get('my-account', 'index')->name('account'); 
        });

     
    });


});
Route::post('/agent/email/verification-notification', function (Request $request) {
    $id = Auth::guard('agent')->user()->id;
    $user = AgentUser::find($id);
    $user->sendEmailVerificationNotification();
    return back()->with('verifymessage', 'Verification link sent!');

})->middleware(['auth.agent', 'throttle:6,1'])->name('agent.verification.send');

Route::post('/agent/email/via-staff/verification-notification/{id}', function (Request $request) {
    $id = $request->id;
    $user = AgentUser::find($id);
    $user->sendEmailVerificationNotification();
    return response()->json(["msg"=>'Verification link sent!'],200);

})->middleware(['auth', 'throttle:6,1'])->name('agent.verification.send.from.staff');

// all student have a prefix route name student.* value
Route::prefix('/students')->name('students.')->group(function() {

    Route::controller(StudentLoginController::class)->middleware('students.loggedin')->group(function() {
        
        Route::get('login', 'loginView')->name('login');
        Route::post('login', 'login')->name('check');


    });
    Route::controller(GoogleSocialiteStudentController::class)->middleware('students.loggedin')->group(function() {

        Route::get('/auth/google/redirect','redirectToGoogle')->name('redirect.google');
        Route::get('/auth/google/callback', 'handleCallback')->name('callback.google');
        
    });

    Route::middleware('auth.students')->group(function() {

        Route::get('logout', [StudentLoginController::class, 'logout'])->name('logout');

        Route::controller(StudentDashboardController::class)->group(function() {
            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/profile', 'profileView')->name('dashboard.profile');
            
            Route::get('/dashboard/plan/{plan}', 'showCourseContent')->name('dashboard.plan.module.show'); 
            Route::get('/dashboard/plan-dates/list', 'planDatelist')->name('dashboard.plan.dates.list');

            Route::get('/dashboard/attendance-excuse', 'attendanceExcuse')->name('dashboard.attendance.excuse');
            Route::post('/dashboard/update-awarding-body-status', 'awardingBodyUpdateStatus')->name('awarding.body.status.update');

        });
        Route::controller(StudentController::class)->group(function() {

            Route::post('email/verifyupdate','verifyEmail')->name('verify.email');
            Route::post('mobile/verify','verifyMobile')->name('verify.mobile');
            Route::post('mobile/verifed','verifiedMobile')->name('update.mobile');
            
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

        Route::controller(AttendanceExcuseController::class)->group(function() {
            Route::post('attendance-excuse/store', 'store')->name('excuse.store');
        });

    });
    
    /**
    * Verification Routes
    */

    // Route::controller(VerificationController::class)->group(function() {
        
    //     Route::get('email/verify', 'show')->name('verification.notice');
    //     Route::get('email/verify/{id}/{hash}', 'verify')->name('verification.verify')->middleware(['signed']);
        
    // });

    

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
        Route::get('course-management/course-creation', 'index')->name('course.creation'); 
        Route::get('course-management/course-creation/list', 'list')->name('course.creation.list'); 
        Route::post('course-management/course-creation/store', 'store')->name('course.creation.store');
        Route::get('course-management/course-creation/show/{id}', 'show')->name('course.creation.show');
        Route::get('course-management/course-creation/edit/{id}', 'edit')->name('course.creation.edit');
        Route::post('course-management/course-creation/update', 'update')->name('course.creation.update');
        Route::delete('course-management/course-creation/delete/{id}', 'destroy')->name('course.creation.destory');
        Route::delete('course-management/course-creation/delete/{id}/venue', 'venueDestroy')->name('course.creation.venue.destroy');
        Route::post('course-management/course-creation/restore/{id}', 'restore')->name('course.creation.restore');
        Route::get('course-management/course-creation/courses-by-semester', 'getCourseListBySemester')->name('course.creation.coursesbysemester'); 
    });
    
    Route::controller(CourseCreationAvailabilityController::class)->group(function() {
        Route::post('course-management/course-creation-availability/store', 'store')->name('course.creation.availability.store');
        Route::get('course-management/course-creation-availability/list', 'list')->name('course.creation.availability.list'); 
        Route::get('course-management/course-creation-availability/edit/{id}', 'edit')->name('course.creation.availability.edit');
        Route::post('course-management/course-creation-availability/update', 'update')->name('course.creation.availability.update');
    });

    Route::controller(CourseCreationDatafutureController::class)->group(function() {
        Route::post('course-management/course-creation-datafuture/store', 'store')->name('course.creation.datafuture.store');
        Route::get('course-management/course-creation-datafuture/list', 'list')->name('course.creation.datafuture.list'); 
        Route::get('course-management/course-creation-datafuture/edit/{id}', 'edit')->name('course.creation.datafuture.edit');
        Route::post('course-management/course-creation-datafuture/update', 'update')->name('course.creation.datafuture.update');
        Route::delete('course-management/course-creation-datafuture/delete/{id}', 'destroy')->name('course.creation.datafuture.destory');
        Route::post('course-management/course-creation-datafuture/restore/{id}', 'restore')->name('course.creation.datafuture.restore');
        
    });

    Route::controller(CourseCreationInstanceController::class)->group(function() {
        Route::post('course-management/course-creation-instance/store', 'store')->name('course.creation.instance.store');
        Route::get('course-management/course-creation-instance/list', 'list')->name('course.creation.instance.list'); 
        Route::get('course-management/course-creation-instance/edit/{id}', 'edit')->name('course.creation.instance.edit');
        Route::post('course-management/course-creation-instance/update', 'update')->name('course.creation.instance.update');
        Route::delete('course-management/course-creation-instance/delete/{id}', 'destroy')->name('course.creation.instance.destory');
        Route::post('course-management/course-creation-instance/restore/{id}', 'restore')->name('course.creation.instance.restore');
    });

    Route::controller(InstanceTermController::class)->group(function() {
        Route::post('course-management/instance-term/store', 'store')->name('instance.term.store');
        Route::get('course-management/instance-term/list', 'list')->name('instance.term.list'); 
        Route::get('course-management/instance-term/show/{id}', 'show')->name('instance.term.show'); 
        Route::get('course-management/instance-term/edit/{id}', 'edit')->name('instance.term.edit');
        Route::post('course-management/instance-term/update', 'update')->name('instance.term.update');
        Route::delete('course-management/instance-term/delete/{id}', 'destroy')->name('instance.term.destory');
        Route::post('course-management/instance-term/restore/{id}', 'restore')->name('instance.term.restore');        
    });
    
    Route::controller(CourseModuleController::class)->group(function() {
        Route::post('course-management/course-module/store', 'store')->name('course.module.store');
        Route::get('course-management/course-module/list', 'list')->name('course.module.list'); 
        Route::get('course-management/course-module/show/{id}', 'show')->name('course.module.show'); 
        Route::post('course-management/course-module/update-status', 'updateStatus')->name('course.module.status.update'); 
        Route::get('course-management/course-module/edit/{id}', 'edit')->name('course.module.edit');
        Route::post('course-management/course-module/update', 'update')->name('course.module.update');
        Route::delete('course-management/course-module/delete/{id}', 'destroy')->name('course.module.destory');
        Route::post('course-management/course-module/restore/{id}', 'restore')->name('course.module.restore');
        
        Route::get('course-management/course-module/restore/{course_id}', 'exportCourseModule')->name('course.module.export');
    });

    Route::controller(CourseBaseDatafutureCntroller::class)->group(function() {
        Route::post('course-management/course-datafuture/store', 'store')->name('course.datafuture.store');
        Route::get('course-management/course-datafuture/list', 'list')->name('course.datafuture.list'); 
        Route::get('course-management/course-datafuture/edit/{id}', 'edit')->name('course.datafuture.edit');
        Route::post('course-management/course-datafuture/update', 'update')->name('course.datafuture.update');
        Route::delete('course-management/course-datafuture/delete/{id}', 'destroy')->name('course.datafuture.destory');
        Route::post('course-management/course-datafuture/restore/{id}', 'restore')->name('course.datafuture.restore');
        
    });

    Route::controller(TutorMonitorController::class)->group(function() {
        Route::post('course-management/course-monitor/store', 'store')->name('course.monitor.store');
        Route::get('course-management/course-monitor/list', 'list')->name('course.monitor.list'); 
        Route::get('course-management/course-monitor/edit/{id}', 'edit')->name('course.monitor.edit');
        Route::post('course-management/course-monitor/update', 'update')->name('course.monitor.update');
        Route::delete('course-management/course-monitor/delete/{id}', 'destroy')->name('course.monitor.destory');
        Route::post('course-management/course-monitor/restore/{id}', 'restore')->name('course.monitor.restore');
        
    });

    Route::controller(CourseModuleBaseAssesmentController::class)->group(function() {
        Route::post('course-management/course-module-assesment/store', 'store')->name('course.module.assesment.store');
        Route::get('course-management/course-module-assesment/list', 'list')->name('course.module.assesment.list'); 
        Route::get('course-management/course-module-assesment/edit/{id}', 'edit')->name('course.module.assesment.edit');
        Route::post('course-management/course-module-assesment/update', 'update')->name('course.module.assesment.update');

        Route::delete('course-management/course-module-assesment/delete/{id}', 'destroy')->name('course.module.assesment.destory');
        Route::post('course-management/course-module-assesment/restore/{id}', 'restore')->name('course.module.assesment.restore');
    });

    Route::controller(ModuleDatafutureController::class)->group(function() {
        Route::post('course-management/module-datafuture/store', 'store')->name('module.datafuture.store');
        Route::get('course-management/module-datafuture/list', 'list')->name('module.datafuture.list'); 
        Route::get('course-management/module-datafuture/edit/{id}', 'edit')->name('module.datafuture.edit');
        Route::post('course-management/module-datafuture/update', 'update')->name('module.datafuture.update');
        Route::delete('course-management/module-datafuture/delete/{id}', 'destroy')->name('module.datafuture.destory');
        Route::post('course-management/module-datafuture/restore/{id}', 'restore')->name('module.datafuture.restore');
        
    });

    Route::controller(TermModuleCreationController::class)->group(function() {
        Route::get('course-management/term-module-creation', 'index')->name('term.module.creation');
        Route::get('course-management/term-module-creation/list', 'list')->name('term.module.creation.list'); 
        Route::get('course-management/term-module-creation/show/{instanceTermId}', 'show')->name('term.module.creation.show'); 
        Route::get('course-management/term-module-creation/add/{instanceTermId}/{courseId}', 'add')->name('term.module.creation.add');
        Route::post('course-management/term-module-creation/store', 'store')->name('term.module.creation.store');
        Route::get('course-management/term-module-creation/module-details/{instanceTermId}', 'moduleDetails')->name('term.module.creation.module.details');

        Route::get('course-management/term-module-creation/module-list/', 'moduleList')->name('term.module.creation.module.list');
        Route::post('course-management/term-module-creation/module-view-assessments/', 'moduleViewAssessments')->name('term.module.creation.module.view.assessments');
        Route::post('course-management/term-module-creation/module-add-assessments/', 'moduleAddAssessments')->name('term.module.creation.module.add.assessments');

        Route::get('course-management/module-creation/edit/{id}', 'edit')->name('term.module.creation.edit');
        Route::post('course-management/module-creation/update', 'update')->name('term.module.creation.update');
        Route::post('course-management/module-creation/plan-taskupdate/{id}', 'updatePlanTask')->name('term.module.creation.plantask-update');

        Route::post('course-management/module-creation/get-modul-base-assessment', 'getModulesBaseAssessments')->name('term.module.get.base.assessment');
        Route::post('course-management/term-module-creation/store-individual', 'storeIndividually')->name('term.module.creation.store.individual');
        
    });

    Route::controller(AssessmentController::class)->group(function() {
        Route::post('assessment/store', 'store')->name('assessment.store'); 
        Route::post('assessment/update', 'update')->name('assessment.update');
    });

    Route::controller(PlanController::class)->group(function() {
        Route::get('course-management/plans', 'index')->name('class.plan'); 
        Route::get('course-management/plans/list', 'list')->name('class.plan.list'); 
        Route::post('course-management/plans/grid', 'grid')->name('class.plan.grid'); 
        Route::get('course-management/plans/add', 'add')->name('class.plan.add');
        Route::get('course-management/plans/edit/{id}', 'edit')->name('class.plan.edit');
        Route::post('course-management/plans/update', 'update')->name('class.plan.update');
        Route::post('course-management/plans/store', 'store')->name('class.plan.store');

        //Route::get('course-management/plans/builder/{course}/{instanceterm}/{modulecreation}', 'classPlanBuilder')->name('class.plan.builder');

        Route::get('course-management/plans/builder/{academic}/{term}/{creation}/{group}', 'classPlanBuilder')->name('class.plan.builder');
        Route::post('course-management/plans/get-modul-details', 'getModuleDetails')->name('class.plan.get.module.details');

        Route::delete('course-management/plans/delete/{id}', 'destroy')->name('class.plan.delete');
        Route::post('course-management/plans/restore/{id}', 'restore')->name('class.plan.restore');

        Route::post('course-management/plans/get-modules', 'getModulesByCourseTerms')->name('class.plan.get.modules.by.course.terms');
        Route::post('course-management/plans/get-plans-box', 'getClassPlanBox')->name('class.plan.get.box');
        Route::post('course-management/plans/get-courselist', 'getCourseListByAcademicYear')->name('course.list.by.academic.instance');

        Route::post('course-management/plans/get-term-declaration', 'getTermDeclarationByAcademicYear')->name('termdeclaration.list.by.academic.year');
        Route::post('course-management/plans/get-course-list', 'getCourseByAcademicTerm')->name('course.list.by.academic.term');
        Route::post('course-management/plans/get-group-list', 'getGroupByAcademicTermCourse')->name('group.list.by.academic.term.course');
        Route::post('course-management/plans/get-instanceterm-list', 'getInstanceTermsListByAcademicTermCourse')->name('instanceterm.list.by.academic.term.course');

        Route::post('course-management/plans/get-filtered-group', 'getFilteredGroup')->name('class.plan.get.group.filter');
    });

    Route::controller(PlanTreeController::class)->group(function() {
        Route::get('course-management/plans/tree', 'index')->name('plans.tree');
        Route::post('course-management/plans/tree/get-semesters', 'getAttenDanceSemester')->name('plans.tree.get.semester');
        Route::post('course-management/plans/tree/get-course', 'getCourses')->name('plans.tree.get.courses');
        Route::post('course-management/plans/tree/get-groups', 'getGroups')->name('plans.tree.get.groups');
        Route::post('course-management/plans/tree/get-module', 'getModule')->name('plans.tree.get.module');
        Route::get('course-management/plans/tree/list', 'list')->name('plans.tree.list');  
        Route::get('course-management/plans/tree/edit/{id}', 'edit')->name('plans.tree.edit'); 
        Route::post('course-management/plans/tree/update', 'update')->name('plans.tree.update'); 
        Route::delete('course-management/plans/tree/delete/{id}', 'destroy')->name('plans.tree.destory');
        Route::post('course-management/plans/tree/restore/{id}', 'restore')->name('plans.tree.restore');

        Route::post('course-management/plans/tree/get-assign-details', 'getAssignDetails')->name('plans.get.assign.details');
        Route::post('course-management/plans/tree/assign-participants', 'assignParticipants')->name('plans.assign.participants');
        Route::post('course-management/plans/tree/update-visibility', 'updateVisibility')->name('plans.update.visibility');

        Route::get('course-management/plans/tree/assigned-list', 'assignedList')->name('plans.tree.assigned.list');
    });

    Route::controller(PlansDateListController::class)->group(function() {
        Route::get('course-management/plan-dates/all/{planId}', 'index')->name('plan.dates'); 
        Route::get('course-management/plan-dates/list', 'list')->name('plan.dates.list'); 
        Route::post('course-management/plan-dates/generate', 'generate')->name('plan.dates.generate'); 
        Route::post('course-management/plan-dates/store', 'store')->name('plan.dates.store'); 
        Route::delete('course-management/plan-dates/delete/{id}', 'destroy')->name('plan.dates.destory');
        Route::post('course-management/plan-dates/restore/{id}', 'restore')->name('plan.dates.restore');

        Route::post('course-management/plan-dates/bulk/', 'planDatesBulkActions')->name('plan.dates.bulk.action');
    });

    Route::controller(StudentController::class)->group(function() {
        
        Route::get('student', 'index')->name('student'); 
        Route::get('student/list', 'list')->name('student.list'); 
        
        Route::get('student/show/{id}', 'show')->name('student.show');
        Route::get('student/course-details/{id}', 'courseDetails')->name('student.course');
        Route::get('student/attendance/{student}', 'AttendanceDetails')->name('student.attendance');
        Route::get('student/result/{student}', 'ResultDetails')->name('student.result');
        Route::get('student/attendance/{student}/edit', 'AttendanceEditDetail')->name('student.attendance.edit');
        
        Route::get('student/result/{student}', 'resultDetails')->name('student.result');
        Route::get('student/communication/{id}', 'communications')->name('student.communication');
        Route::get('student/uploads/{id}', 'uploads')->name('student.uploads');
        Route::get('student/notes/{id}', 'notes')->name('student.notes');
        Route::get('student/process/{id}', 'process')->name('student.process');
        Route::get('student/workplacement/{id}', 'workplacement')->name('student.workplacement');

        Route::post('student/upload-student-photo', 'UploadStudentPhoto')->name('student.upload.photo');

        Route::post('student/id-filter', 'StudentIDFilter')->name('student.filter.id');

        Route::get('student/slc-history/{id}', 'slcHistory')->name('student.slc.history');
        Route::get('student/accounts/{id}', 'accounts')->name('student.accounts');

        Route::post('student/send-mobile-verification-code','sendMobileVerificationCode')->name('student.send.mobile.verification.code');
        Route::post('student/send-mobile-verify-code','verifyMobileVerificationCode')->name('student.mobile.verify.code');

        Route::get('student/set-temp-course/{student}/{crel}', 'setTempCourse')->name('student.set.temp.course');
        Route::get('student/set-default-course/{student}', 'setDefaultCourse')->name('student.set.default.course');

        
        Route::post('student/temm-by-academic', 'getAllTerms')->name('student.get.term.by.academics');
        Route::post('student/intake-by-academic-or-term', 'getAllIntakes')->name('student.get.intake.by.academics');

        Route::post('student/get-student-type', 'getAllStudentByGroupType')->name('student.get.all.student.type');

        Route::post('student/course-by-terms', 'getAllCourses')->name('student.get.coureses.by.terms');
        Route::post('student/status-by-groups', 'getAllStatuses')->name('student.get.status.by.groups');

        Route::post('student/courses-by-intake', 'getCoursesByIntakeOrTerm')->name('student.get.coureses.by.intake.or.term');
        
        Route::post('student/group-by-courses', 'getGroupByCourseAndTerms')->name('student.get.groups.by.course');

        Route::post('student/download-document', 'studentDocumentDownload')->name('student.document.download');

        Route::post('student/send-email-verification-code','sendEmailVerificationCode')->name('student.send.email.verification.code');
        Route::post('student/send-email-verify-code','verifyEmailVerificationCode')->name('student.email.verify.code');

        Route::get('student/preint-communications/{student_id}/{type}','printStudentCommunications')->name('student.print.communications');

        Route::post('student/update-status','studentUpdateStatus')->name('student.update.status');

        Route::post('student/email/verifyupdate','verifyEmail')->name('student.verify.email');
        Route::post('student/mobile/verify','verifyMobile')->name('student.verify.mobile');
        Route::post('student/mobile/verifed','verifiedMobile')->name('student.update.mobile');
        

    });
    
    
    Route::controller(StudentResultController::class)->group(function() {

        Route::get('student-results/{student}', 'index')->name('student-results.index'); 
        
        Route::get('student-results/{student}/print', 'print')->name('student-results.print');

        
        
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

        Route::post('student/download-letter', 'studentLetterDownload')->name('student.letter.download');

        //Route::get('student/export-letter-tags', 'studentExportLetterTags')->name('student.export.letter.tags');
    });

    Route::controller(EmailController::class)->group(function() {
        Route::post('student/send-mail', 'store')->name('student.send.mail');
        Route::get('student/mail-list', 'list')->name('student.mail.list');
        Route::delete('student/destory-mail', 'destroy')->name('student.mail.destroy');
        Route::post('student/restore-mail', 'restore')->name('student.mail.restore');
        Route::post('student/get-mail-template', 'getEmailTemplate')->name('student.get.mail.template');

        Route::post('student/download-email-pdf', 'studentEmailPdfDownload')->name('student.email.pdf.download');
        Route::post('student/download-email-attachment', 'studentEmailAttachmentDownload')->name('student.email.attachment.download');
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

        Route::post('student/download-note-document', 'studentNoteDocumentDownload')->name('student.note.document.download');
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

        Route::post('student/process-task-user-list', 'processTaskUserList')->name('student.process.task.users');
        
        Route::post('student/process-task-view-excuse', 'processTaskViewExcuse')->name('student.process.task.view.excuse');
        Route::post('student/update-process-task-and-excuse', 'updateProcessTaskAndExcuse')->name('student.process.update.task.and.excuse');
    });

    Route::controller(CourseDetailController::class)->group(function() {
        Route::post('student/update-course-details', 'update')->name('student.update.course.details');

        Route::post('student/get-semesters-by-academic', 'getSemesterByAcademic')->name('student.get.semesters.by.academic');
        Route::post('student/get-courses-by-academic-semester', 'getCourseByAcademicSemester')->name('student.get.courses.by.academic.semester');
        Route::post('student/assigned-new-course', 'assignedNewCourse')->name('student.assigned.new.course');

        Route::post('student/get-evening-weekend-status', 'getEveningWeekendStatus')->name('student.get.evening.weekend.status');
    });

    Route::controller(AwardingBodyDetailController::class)->group(function() {
        Route::post('student/update-awarding-body-details', 'update')->name('student.update.awarding.body.details');
        Route::post('student/update-awarding-body-status', 'updateStatus')->name('student.update.awarding.body.status');
    });

    Route::controller(SlcRegistrationController::class)->group(function() {
        Route::post('student/get-reg-confirmation-details', 'getRegistrationConfirmationDetails')->name('student.get.registration.confirmation.details');
        Route::post('student/store-slc-registration', 'store')->name('student.store.registration');
        Route::post('student/edit-slc-registration', 'edit')->name('student.edit.registration');
        Route::post('student/update-slc-registration', 'update')->name('student.update.registration');

        Route::post('student/slc-registration-has-data', 'hasData')->name('student.slc.registration.has.data');
        Route::delete('student/slc-registration-destory', 'destroy')->name('student.slc.registration.destroy');
    });

    Route::controller(SlcAttendanceController::class)->group(function() {
        Route::post('student/edit-slc-attendance', 'edit')->name('student.edit.slc.attendance');
        Route::post('student/update-slc-attendance', 'update')->name('student.update.slc.attendance');
        Route::post('student/populate-slc-attendance', 'populateAttendanceForm')->name('student.slc.attendance.populate');
        Route::post('student/store-slc-attendance', 'store')->name('student.store.slc.attendance');

        Route::post('student/store-slc-installment-exist', 'checkInstallmentExistence')->name('student.installment.existence');
        Route::post('student/slc-attendance-has-data', 'hasData')->name('student.slc.attendance.has.data');
        Route::delete('student/slc-attendance-destory', 'destroy')->name('student.slc.attendance.destroy');

        Route::post('student/slc-attendance-sync', 'syncAttendance')->name('student.slc.attendance.sync.to.registration');
    });

    Route::controller(SlcAgreementController::class)->group(function() {
        Route::post('student/edit-slc-agreement', 'edit')->name('student.edit.slc.agreement');
        Route::post('student/update-slc-agreement', 'update')->name('student.update.slc.agreement');
        Route::post('student/get-instance-fees', 'getInstanceFees')->name('student.get.slc.agreement.instance.fees');
        Route::post('student/store-agreement', 'store')->name('student.store.slc.agreement');

        Route::post('student/agreement-has-data', 'hasData')->name('student.slc.agreement.has.data');
        Route::delete('student/destroy-agreement', 'destroy')->name('student.destory.slc.agreement');

        Route::post('student/assign-agreement-to-registration', 'assignAgrToReg')->name('student.assign.slc.agreement.to.registration');
    });

    Route::controller(SlcInstallmentController::class)->group(function() {
        Route::post('student/edit-slc-installment', 'edit')->name('student.edit.slc.intallment');
        Route::post('student/update-slc-installment', 'update')->name('student.update.slc.intallment');
        Route::post('student/get-slc-installment-details', 'getDetails')->name('student.get.slc.intallment.details');
        Route::post('student/store-slc-installment', 'store')->name('student.store.slc.intallment');

        Route::post('student/slc-installment-existence', 'installmentExistence')->name('student.slc.intallment.existence');
        Route::post('student/slc-installment-edit-existence', 'editInstallmentExistence')->name('student.slc.intallment.existence.edit');

        Route::delete('student/slc-installment-destroy', 'destroy')->name('student.destory.slc.intallment');
    });

    Route::controller(SlcCocController::class)->group(function() {
        Route::post('student/edit-slc-coc', 'edit')->name('student.edit.slc.coc');
        Route::post('student/update-slc-coc', 'update')->name('student.slc.coc.update');
        Route::post('student/store-slc-coc', 'store')->name('student.slc.coc.store');

        Route::post('student/destory-slc-coc-doc', 'destroyCocDocument')->name('student.destory.coc.document');
        Route::delete('student/destory-slc-coc', 'destroy')->name('student.destory.coc');

        Route::post('student/sync-coc-to-attendance', 'syncCocToAttendance')->name('student.slc.coc.sync.to.attendance');
    });

    Route::controller(SlcMoneyReceiptController::class)->group(function() {
        Route::post('student/store-slc-payment', 'store')->name('student.store.slc.payment');
        Route::post('student/edit-slc-payment', 'edit')->name('student.edit.slc.payment');
        Route::post('student/update-slc-payment', 'update')->name('student.update.slc.payment');
        Route::delete('student/destroy-slc-payment', 'destroy')->name('student.destory.slc.payment');
        Route::post('student/reassign-slc-payment-to-agreement', 'reAssignPaymentToAgreement')->name('student.sync.slc.payment.to.agreement');
    });

    Route::controller(AdmissionController::class)->group(function() {

        Route::get('admission', 'index')->name('admission'); 
        Route::get('admission/list', 'list')->name('admission.list'); 
        Route::get('admission/export', 'export')->name('admission.export');
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
        Route::post('admission/process-task-user-list', 'admissionPocessTaskUserList')->name('admission.process.task.users');

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

        Route::post('admission/send-mobile-verification-code','sendMobileVerificationCode')->name('admission.send.mobile.verification.code');
        Route::post('admission/send-mobile-verify-code','verifyMobileVerificationCode')->name('admission.mobile.verify.code');

        Route::post('admission/download-document', 'admissionDocumentDownload')->name('admission.document.download');

        Route::post('admission/get-evening-weekend-status', 'getEveningWeekendStatus')->name('admission.get.evening.weekend.status');
        
    });

    Route::controller(ApplicantQualificationController::class)->group(function() {
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

    Route::controller(UserProfileController::class)->group(function() {
        Route::get('my-account', 'index')->name('user.account'); 
        Route::get('my-account/extra-benefit', 'extraBenefit')->name('user.account.extrabenefit'); 
    });

    Route::controller(UserHolidayController::class)->group(function(){
        Route::get('my-account/holidays', 'index')->name('user.account.holiday'); 
        Route::post('my-account/holidays/get-ajax-leave-statistics', 'employeeAjaxLeaveStatistics')->name('user.account.holiday.ajax.statistics'); 
        Route::post('my-account/holidays/get-ajax-leave-limit', 'employeeAjaxLeaveLimit')->name('user.account.holiday.ajax.limit'); 
        Route::post('my-account/holidays/leave-submission', 'employeeLeaveSubmission')->name('user.account.holiday.leave.submission'); 
    });

    Route::controller(EmployeeController::class)->group(function(){
        Route::get('employee','index')->name('employee');
        Route::get('employee/list','list')->name('employee.list');
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
        Route::post('employee-address/get','edit')->name('employee.get.address');
        Route::post('employee-address/update','update')->name('employee.address.update');
    });
    Route::controller(EmploymentController::class)->group(function() {
        Route::post('employment/update/{employment}','update')->name('employee.employment.update');
    });
    Route::controller(EmployeeEligibilityController::class)->group(function() {
        Route::post('employee-eligibility/update/{eligibility}','update')->name('employeeeligibility.update');
    });
    
    Route::controller(EmployeeEmergencyContactController::class)->group(function() {
        Route::post('employee-emergency/update/{contact}','update')->name('employee.emergency.update');
        Route::post('employee-emergency/store','store')->name('employee.emergency.store');
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

        Route::post('employee-profile/holidays/get-ajax-leave-statistics', 'employeeAjaxLeaveStatistics')->name('employee.holiday.ajax.statistics'); 
        Route::post('employee-profile/holidays/get-ajax-leave-limit', 'employeeAjaxLeaveLimit')->name('employee.holiday.ajax.limit'); 
        Route::post('employee-profile/holidays/leave-submission', 'employeeLeaveSubmission')->name('employee.holiday.leave.submission'); 

        Route::post('employee-profile/holidays/get-leave', 'getEmployeeLeave')->name('employee.holiday.get.leave'); 
        Route::post('employee-profile/holidays/update-leave', 'employeeUpdateLeave')->name('employee.holiday.update.leave'); 
        Route::post('employee-profile/holidays/approve-leave', 'employeeApproveLeave')->name('employee.holiday.approve.leave'); 
        Route::post('employee-profile/holidays/reject-leave', 'employeeRejectLeave')->name('employee.holiday.rject.leave'); 

        Route::post('employee-profile/holidays/check-leave-day-is-approved', 'employeeCheckLeaveDayIsApproved')->name('employee.holiday.check.day.is.approved'); 
    });

    Route::controller(EmployeeDocumentsController::class)->group(function(){
        Route::get('employee-profile/documents/{id}', 'index')->name('employee.documents'); 
        Route::post('employee-profile/documents/uploads-documents', 'employeeUploadDocument')->name('employee.documents.upload.documents'); 
        Route::get('employee-profile/documents-upload/uploads-list', 'list')->name('employee.documents.uploads.list');
        Route::get('employee-profile/documents-upload/communication-list', 'communicationList')->name('employee.documents.communication.list');
        Route::delete('employee-profile/documents/uploads-destroy', 'destroy')->name('employee.documents.destory.uploads');
        Route::post('employee-profile/documents/uploads-restore', 'restore')->name('employee.documents.restore.uploads');
        
        Route::post('employee-profile/documents/download-url', 'downloadUrl')->name('employee.documents.download.url');
        Route::post('employee-profile/documents/sent-mail', 'employeeSentMail')->name('employee.documents.sent.mail'); 
        Route::post('employee-profile/documents/get-template', 'employeeGetTemplate')->name('employee.documents.get.template'); 
    });

    Route::controller(EmployeeNotesController::class)->group(function(){
        Route::get('employee-profile/notes/{id}', 'index')->name('employee.notes'); 
       
        Route::post('employee-profile/store-notes', 'store')->name('employee.store.note');
        Route::get('employee-profile/notes-list', 'list')->name('employee.note.list');
        Route::post('employee-profile/show-note', 'show')->name('employee.show.note');
        Route::post('employee-profile/get-note', 'edit')->name('employee.get.note');
        Route::post('employee-profile/update-note', 'update')->name('employee.update.note');
        Route::delete('employee-profile/destory-note', 'destroy')->name('employee.destory.note');
        Route::post('employee-profile/restore-note', 'restore')->name('employee.restore.note');

        Route::post('employee-profile/download-note-doc', 'downloadUrl')->name('employee.note.download.url');
    });

    Route::controller(EmployeeAppraisalController::class)->group(function(){
        Route::get('employee-profile/appraisal/{id}', 'index')->name('employee.appraisal'); 
        Route::post('employee-profile/appraisal-store', 'store')->name('employee.appraisal.store');
        Route::get('employee-profile/appraisal-list', 'list')->name('employee.appraisal.list');
        Route::post('employee-profile/appraisal-edit', 'edit')->name('employee.appraisal.edit');
        Route::post('employee-profile/appraisal-update', 'update')->name('employee.appraisal.update');
        Route::post('employee-profile/get-appraisal', 'getNote')->name('employee.appraisal.get.note');

        Route::delete('employee-profile/appraisal-destroy', 'destroy')->name('employee.appraisal.destory');
        Route::post('employee-profile/appraisal-restore', 'restore')->name('employee.appraisal.restore');
    });

    Route::controller(EmployeeAppraisalDocumentController::class)->group(function(){
        Route::get('employee-profile/appraisal/documents/{id}/{appraisalid}', 'index')->name('employee.appraisal.documents'); 
        Route::post('employee-profile/appraisal-upload-documents', 'uploadDocuments')->name('employee.appraisal.upload.documents'); 
        Route::get('employee-profile/appraisal-document-list', 'list')->name('employee.appraisal.documents.list'); 
        Route::delete('employee-profile/appraisal-document-destroy', 'destroy')->name('employee.appraisal.document.destory');
        Route::post('employee-profile/appraisal-document-restore', 'restore')->name('employee.appraisal.document.restore');
    });

    Route::controller(EmployeePrivilegeController::class)->group(function(){
        Route::get('employee-profile/privilege/{id}', 'index')->name('employee.privilege'); 
        Route::post('employee-profile/store-privilege', 'store')->name('employee.privilege.store');
    });

    Route::controller(EmployeeAttendanceController::class)->group(function(){
        Route::get('hr/attendance', 'index')->name('hr.attendance');
        Route::post('hr/attendance/list-html', 'getListHtml')->name('hr.attendance.sync.listhtml'); 
        Route::post('hr/attendance/syncronise', 'syncronise')->name('hr.attendance.sync');
        Route::get('hr/attendance/show/{date}', 'show')->name('hr.attendance.show');
        Route::post('hr/attendance/update', 'update')->name('hr.attendance.update');
        Route::post('hr/attendance/update-all', 'updateAll')->name('hr.attendance.update.all');
        Route::post('hr/attendance/edit', 'edit')->name('hr.attendance.edit');
        Route::post('hr/attendance/update-break', 'updateBreak')->name('hr.attendance.update.break');

        Route::delete('hr/attendance/delete', 'destroy')->name('hr.attendance.destroy.all');
        
        Route::post('hr/attendance/resyncronise', 'reSyncronise')->name('hr.attendance.re.sync');
    });

    Route::controller(EmployeePortalController::class)->group(function(){
        Route::get('hr/portal', 'index')->name('hr.portal');
        Route::get('hr/portal/manage-holidays', 'manageHolidays')->name('hr.portal.holiday');
        Route::get('hr/portal/holiday-list', 'list')->name('hr.portal.holiday.list'); 
        Route::post('hr/portal/update-absent', 'updateAbsent')->name('hr.portal.update.absent'); 

        Route::get('hr/portal/leave-calendar', 'leaveCalendar')->name('hr.portal.leave.calendar'); 
        Route::post('hr/portal/filter-leave-calendar', 'filterLeaveCalendar')->name('hr.portal.filter.leave.calendar'); 
        Route::post('hr/portal/navigate-leave-calendar', 'navigateLeaveCalendar')->name('hr.portal.navigate.leave.calendar'); 
        
        Route::get('hr/portal/reports', 'employmentReportShow')->name('hr.portal.employment.reports.show');

        Route::post('hr/portal/get-leave-day-details', 'getLeaveDayDetails')->name('hr.portal.get.leave.day.details');

        Route::post('hr/portal/absent-employe-pending-leave', 'checkIfisPendingLeaveExist')->name('hr.portal.check.pending.leave');
        
    });     

    Route::controller(EmploymentReportController::class)->group(function(){
        Route::get('hr/portal/reports/list', 'employmentReportlist')->name('hr.portal.employment.reports.list');
    });

    Route::controller(BirthdayReportController::class)->group(function(){
        Route::get('hr/portal/reports/birthdaylist', 'index')->name('hr.portal.reports.birthdaylist');
        Route::get('hr/portal/reports/birthdaylist/list', 'searchlist')->name('hr.portal.reports.birthdaylist.list'); 
        Route::get('hr/portal/reports/birthdaylist/birthdaylistpdf', 'generatePDF')->name('hr.portal.reports.birthdaylist.pdf');
        
        Route::get('hr/portal/reports/birthdaylist/birthdaylistexcel', 'generateBirthdayExcel')->name('hr.portal.reports.birthdaylist.excel');
        Route::get('hr/portal/reports/birthdaylist/birthdaylistbysearchexcel', 'generateBirthdayListbySearchExcel')->name('hr.portal.reports.birthdaylistbysearch.excel');
        Route::get('hr/portal/reports/birthdaylist/birthdaylistbysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.birthdaylistbysearch.pdf');
    });

    Route::controller(DiversityReportController::class)->group(function(){
        Route::get('hr/portal/reports/diversityreport', 'index')->name('hr.portal.reports.diversityreport');
        Route::get('hr/portal/reports/diversityreport/list', 'list')->name('hr.portal.reports.diversityreport.list'); 
        Route::get('hr/portal/reports/diversityreport/diversitypdf', 'generatePDF')->name('hr.portal.reports.diversityreport.pdf');
        Route::get('hr/portal/reports/diversityreport/diversitybysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.diversitybysearch.pdf');
    });         

    Route::controller(EmployeeContactDetailController::class)->group(function(){
        Route::get('hr/portal/reports/contactdetail', 'index')->name('hr.portal.reports.contactdetail');
        Route::get('hr/portal/reports/contactdetail/list', 'searchlist')->name('hr.portal.reports.contactdetail.list'); 
        Route::get('hr/portal/reports/contactdetail/contactpdf', 'generatePDF')->name('hr.portal.reports.contactdetail.pdf');
        Route::get('hr/portal/reports/contactdetail/contactexcel', 'generateContactExcel')->name('hr.portal.reports.contactdetail.excel');
        Route::get('hr/portal/reports/contactdetail/contactbysearchexcel', 'generateSearchExcel')->name('hr.portal.reports.contactbysearch.excel');
        Route::get('hr/portal/reports/contactdetail/contactbysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.contactbysearch.pdf');
    }); 
    
    Route::controller(LengthServiceController::class)->group(function(){
        Route::get('hr/portal/reports/lengthservice', 'index')->name('hr.portal.reports.lengthservice');
        Route::get('hr/portal/reports/lengthservice/list', 'searchlist')->name('hr.portal.reports.lengthservice.list'); 
        Route::get('hr/portal/reports/lengthservice/lengthservicepdf', 'generatePDF')->name('hr.portal.reports.lengthservice.pdf');
        
        Route::get('hr/portal/reports/lengthservice/lengthserviceexcel', 'generateBirthdayExcel')->name('hr.portal.reports.lengthservice.excel');
        Route::get('hr/portal/reports/lengthservice/lengthservicebysearchexcel', 'generateLengthServicebySearchExcel')->name('hr.portal.reports.lengthservicebysearch.excel');
        Route::get('hr/portal/reports/lengthservice/lengthservicebysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.lengthservicebysearch.pdf');
    });

    Route::controller(StarterReportController::class)->group(function(){
        Route::get('hr/portal/reports/starterreport', 'index')->name('hr.portal.reports.starterreport');
        Route::get('hr/portal/reports/starterreport/list', 'list')->name('hr.portal.reports.starterreport.list'); 
        Route::get('hr/portal/reports/starterreport/starterpdf', 'generatePDF')->name('hr.portal.reports.starterreport.pdf');
        Route::get('hr/portal/reports/starterreport/starterreportbysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.starterreportbysearch.pdf');
    });            
    
    Route::controller(DataReportController::class)->group(function(){
        Route::get('hr/portal/reports/datareport', 'index')->name('hr.portal.reports.datareport');
        Route::post('hr/portal/reports/datareport', 'genrateDataReport')->name('employee.hr.datareport');
        Route::get('hr/portal/reports/datareport/list', 'list')->name('hr.portal.reports.datareport.list'); 
    });       

    Route::controller(RecordCardController::class)->group(function(){
        Route::get('hr/portal/reports/recordcard', 'index')->name('hr.portal.reports.recordcard');
        Route::get('hr/portal/reports/recordcard/list', 'searchlist')->name('hr.portal.reports.recordcard.list'); 
        Route::get('hr/portal/reports/recordcard/recordcardpdf', 'generatePDF')->name('hr.portal.reports.recordcard.pdf');
        Route::get('hr/portal/reports/recordcard/recordcardexcel', 'generateRecordCardExcel')->name('hr.portal.reports.recordcard.excel');
        Route::get('hr/portal/reports/recordcard/recordcardbysearchexcel', 'generateSearchExcel')->name('hr.portal.reports.recordcardbysearch.excel');
        Route::get('hr/portal/reports/recordcard/recordcardbysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.recordcardbysearch.pdf');
    });     

    Route::controller(TelephoneDirectoryController::class)->group(function(){
        Route::get('hr/portal/reports/telephonedirectory', 'index')->name('hr.portal.reports.telephonedirectory');
        Route::get('hr/portal/reports/telephonedirectory/list', 'searchlist')->name('hr.portal.reports.telephonedirectory.list'); 
        Route::get('hr/portal/reports/telephonedirectory/telephonedirectorypdf', 'generatePDF')->name('hr.portal.reports.telephonedirectory.pdf');
        
        Route::get('hr/portal/reports/telephonedirectory/telephonedirectoryexcel', 'generateTelephoneDirectoryExcel')->name('hr.portal.reports.telephonedirectory.excel');
        Route::get('hr/portal/reports/telephonedirectory/telephonedirectorybysearchexcel', 'generateTelephoneDirectorybySearchExcel')->name('hr.portal.reports.telephonedirectorybysearch.excel');
        Route::get('hr/portal/reports/telephonedirectory/telephonedirectorybysearchpdf', 'generateSearchPDF')->name('hr.portal.reports.telephonedirectorybysearch.pdf');
    });

    Route::controller(EligibilityReportController::class)->group(function(){
        Route::get('hr/portal/reports/eligibilityreport', 'index')->name('hr.portal.reports.eligibilityreport');
        Route::get('hr/portal/reports/eligibilityreport/visaexpiry-list', 'visaList')->name('hr.portal.reports.eligibilityreport.visaexpirylist'); 
        Route::get('hr/portal/reports/eligibilityreport/passportexpiry-list', 'passportList')->name('hr.portal.reports.eligibilityreport.passportexpirylist'); 
        Route::get('hr/portal/reports/eligibilityreport/eligibilitypdf/visa', 'generateVisaPDF')->name('hr.portal.reports.eligibilityreport.visa.pdf');
        Route::get('hr/portal/reports/eligibilityreport/eligibilitypdf/passport', 'generatePassportPDF')->name('hr.portal.reports.eligibilityreport.passport.pdf');
    });

    Route::controller(HolidayHourReportController::class)->group(function(){
        Route::any('hr/portal/employee-holiday-hour-reports', 'index')->name('hr.portal.reports.holiday.hour');
        Route::get('hr/portal/employee-holiday-hour-export/{from_date}/{to_date?}', 'exportExcel')->name('hr.portal.reports.holiday.hour.export');
    });

    Route::controller(EmployeeUpcomingAppraisalController::class)->group(function(){
        Route::get('hr/portal/upcoming-appraisal', 'index')->name('hr.portal.upcoming.appraisal');
        Route::get('hr/attendance/upcoming-appraisal/list', 'list')->name('hr.portal.upcoming.appraisal.list');
    });

    Route::controller(EmployeeVisaExpiryController::class)->group(function(){
        Route::get('hr/portal/visa-expiry', 'index')->name('hr.portal.visa.expiry');
        Route::get('hr/portal/visa-expiry/list', 'list')->name('hr.portal.visa.expiry.list');
    });

    Route::controller(EmployeePassportExpiryController::class)->group(function(){
        Route::get('hr/portal/passport-expiry', 'index')->name('hr.portal.passport.expiry');
        Route::get('hr/portal/passport-expiry/list', 'list')->name('hr.portal.passport.expiry.list');
    });

    Route::controller(EmployeeAbsentTodayController::class)->group(function(){
        Route::get('hr/portal/absent-employee/{date}', 'index')->name('hr.portal.absent.employee');
    });

    Route::controller(EmployeeAttendanceLiveController::class)->group(function(){
        Route::get('hr/portal/live', 'index')->name('hr.portal.live.attedance');
        Route::post('hr/portal/live/data', 'ajaxLiveData')->name('hr.portal.live.attedance.ajax');
        Route::get('hr/portal/live/add', 'add')->name('hr.portal.live.attedance.add');
        Route::post('hr/portal/live/get-day-data', 'getDayAttendanceData')->name('hr.portal.live.attedance.get.day.data');
        Route::post('hr/portal/live/feed-live-attendance', 'feeAttendanceLive')->name('hr.portal.live.attedance.fee.data');

        Route::post('hr/portal/live/get-employee-mail', 'getEmployeeEmail')->name('hr.portal.live.get.employee.mail');
        Route::post('hr/portal/live/sent-mail', 'sentEmail')->name('hr.portal.live.attedance.sent.mail');
    });
    
    Route::controller(StaffDashboard::class)->group(function() {
        Route::get('/', 'index')->name('dashboard');
        Route::get('/dashboard', 'index')->name('staff.dashboard');
        Route::get('/dashboard/account', 'getAccountDashBoard')->name('staff.dashboard.account');
        Route::get('/dashboard/list', 'list')->name('dashboard.staff.list');
        Route::post('/dashboard/fee-attendance', 'feeAttendance')->name('dashboard.feed.attendance');
        Route::post('/dashboard/ignore-feed-attendance', 'ignoreFeeAttendance')->name('dashboard.ignore.feed.attendance');
        
        Route::get('/dashboard/internal-link/{id}', 'parentLinkBox')->name('dashboard.internal-link.parent');

        Route::post('/dashboard/get-departments-employees', 'getDeptEmployeeIds')->name('dashboard.get.dept.employee.ids');
        Route::post('/dashboard/send-group-mail', 'sendGroupEmail')->name('dashboard.get.send.group.mail');

        Route::post('/dashboard/start-proxy-class', 'startProxyClass')->name('dashboard.start.proxy.class');
        Route::post('/dashboard/end-proxy-class', 'endProxyClass')->name('dashboard.end.proxy.class');
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
        Route::get('course-management/courses', 'index')->name('courses'); 
        Route::get('course-management/courses/list', 'list')->name('courses.list');        
        Route::post('course-management/courses/store', 'store')->name('courses.store');
        Route::get('course-management/courses/edit/{id}', 'edit')->name('courses.edit');
        Route::post('course-management/courses/update/{id}', 'update')->name('courses.update');
        
        Route::get('course-management/courses/show/{id}', 'show')->name('courses.show');

        Route::delete('course-management/courses/delete/{id}', 'destroy')->name('courses.destory');
        Route::post('course-management/courses/restore/{id}', 'restore')->name('courses.restore');
        Route::post('course-management/courses/update-status/{id}', 'updateStatus')->name('courses.update.status');
    });
   
    Route::controller(SemesterController::class)->group(function() {
        Route::get('course-management/semester', 'index')->name('semester');
        Route::get('course-management/semester/list', 'list')->name('semester.list');     
        Route::post('course-management/semester/store', 'store')->name('semester.store');
        Route::get('course-management/semester/edit/{id}', 'edit')->name('semester.edit');
        Route::post('course-management/semester/update/{id}', 'update')->name('semester.update');
        Route::delete('course-management/semester/delete/{id}', 'destroy')->name('semester.destory');
        Route::post('course-management/semester/restore/{id}', 'restore')->name('semester.restore');
    });

    Route::controller(GroupController::class)->group(function() {
        Route::get('course-management/groups', 'index')->name('groups'); 
        Route::get('course-management/groups/list', 'list')->name('groups.list');        
        Route::post('course-management/groups/store', 'store')->name('groups.store');
        Route::get('course-management/groups/edit/{id}', 'edit')->name('groups.edit');
        Route::post('course-management/groups/update/{id}', 'update')->name('groups.update');
        Route::delete('course-management/groups/delete/{id}', 'destroy')->name('groups.destory');
        Route::post('course-management/groups/restore/{id}', 'restore')->name('groups.restore');

        Route::get('course-management/groups/courselist/{term}', 'getCourseListByTerm')->name('group.courselist.by.term');
        
        Route::post('course-management/groups/bulk/', 'groupBulkActions')->name('groups.bulk.action');
    });

    Route::controller(ModuleLevelController::class)->group(function() {
        Route::get('course-management/modulelevels', 'index')->name('modulelevels'); 
        Route::get('course-management/modulelevels/list', 'list')->name('modulelevels.list');        
        Route::post('course-management/modulelevels/store', 'store')->name('modulelevels.store');
        Route::get('course-management/modulelevels/edit/{id}', 'edit')->name('modulelevels.edit');
        Route::post('course-management/modulelevels/update/{id}', 'update')->name('modulelevels.update');
        Route::delete('course-management/modulelevels/delete/{id}', 'destroy')->name('modulelevels.destory');
        Route::post('course-management/modulelevels/restore/{id}', 'restore')->name('modulelevels.restore');
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
        Route::post('interviewlist/only/unlock', 'unlockInterViewOnly')->name('applicant.interview.unlock.only');
        Route::get('interviewlist/profile/{id}/{interview}/{token}', 'profileView')->name('applicant.interview.profile.view')->middleware(EnsureExpiredDateIsValid::class);
        Route::get('interviewlist/profileview/{id}/{applicant_task}/{token}', 'profileViewOnly')->name('applicant.interview.profile.viewonly')->middleware(EnsureExpiredDateIsValid::class);
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
    Route::group(['prefix'=>'site-settings'], function(){
        Route::resource('term-type', TermTypeController::class);

        Route::controller(TermTypeController::class)->group(function() {
            Route::get('term-type-list', 'list')->name('term-type.list');     
            Route::post('term-type/{id}/restore', 'restore')->name('term-type.restore');
        });
        
        Route::resource('assessment-type', AssessmentTypeController::class);

        Route::controller(AssessmentTypeController::class)->group(function() {
            Route::get('assessment-type-list', 'list')->name('assessment-type.list');     
            Route::post('assessment-type/{id}/restore', 'restore')->name('assessment-type.restore');
        });

        Route::resource('internal-link', InternalLinkController::class,[
            'except' => ['update']
        ]);

        Route::controller(InternalLinkController::class)->group(function() {
            Route::get('internal-link-list', 'list')->name('internal-link.list');     
            Route::post('internal-link/{id}/restore', 'restore')->name('internal-link.restore');
            Route::get('internal-link/{id}/parent', 'parentLinkBox')->name('internal-link.parent');
            Route::post('internal-link-update', 'update')->name('internal-link.update');
        });

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
        Route::get('site-settings/venues/all', 'getAll')->name('venues.all');  
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

    Route::controller(VenueBaseDatafutureController::class)->group(function() {
        Route::post('site-settings/venues/datafuture/store', 'store')->name('venue.datafuture.store');
        Route::get('site-settings/venues/datafuture/list', 'list')->name('venue.datafuture.list'); 
        Route::get('site-settings/venues/datafuture/edit/{id}', 'edit')->name('venue.datafuture.edit');
        Route::post('site-settings/venues/datafuture/update', 'update')->name('venue.datafuture.update');
        Route::delete('site-settings/venues/datafuture/delete/{id}', 'destroy')->name('venue.datafuture.destory');
        Route::post('site-settings/venues/datafuture/restore/{id}', 'restore')->name('venue.datafuture.restore');
        
    });

    Route::controller(StatusController::class)->group(function() {
        Route::get('site-settings/statuses', 'index')->name('statuses'); 
        Route::get('site-settings/statuses/list', 'list')->name('statuses.list'); 
        Route::post('site-settings/statuses/store', 'store')->name('statuses.store'); 
        Route::get('site-settings/statuses/edit/{id}', 'edit')->name('statuses.edit');
        Route::post('site-settings/statuses/update', 'update')->name('statuses.update');
        Route::delete('site-settings/statuses/delete/{id}', 'destroy')->name('statuses.destory');
        Route::post('site-settings/statuses/restore/{id}', 'restore')->name('statuses.restore');

        Route::post('site-settings/statuses/get-process', 'getProcess')->name('statuses.get.process');
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
        Route::post('site-settings/tasklist/restore', 'getAssignedUserList')->name('tasklist.users');
    });

    Route::controller(SmsTemplateController::class)->group(function() {
        Route::get('site-settings/sms-template', 'index')->name('sms.template'); 
        Route::get('site-settings/sms-template/list', 'list')->name('sms.template.list'); 
        Route::post('site-settings/sms-template/store', 'store')->name('sms.template.store');
        Route::get('site-settings/sms-template/edit/{id}', 'edit')->name('sms.template.edit');
        Route::post('site-settings/sms-template/update', 'update')->name('sms.template.update');

        Route::delete('site-settings/sms-template/delete/{id}', 'destroy')->name('sms.template.destory');
        Route::post('site-settings/sms-template/restore/{id}', 'restore')->name('sms.template.restore');

        Route::post('site-settings/sms-template/update-status', 'updateStatus')->name('sms.template.update.status');
        Route::post('site-settings/sms-template/update-phase-status', 'updatePhaseStatus')->name('sms.template.update.phase.status');
    });

    Route::controller(EmailTemplateController::class)->group(function() {
        Route::get('site-settings/email-template', 'index')->name('email.template'); 
        Route::get('site-settings/email-template/list', 'list')->name('email.template.list'); 
        Route::post('site-settings/email-template/store', 'store')->name('email.template.store');
        Route::get('site-settings/email-template/edit/{id}', 'edit')->name('email.template.edit');
        Route::post('site-settings/email-template/update', 'update')->name('email.template.update');

        Route::delete('site-settings/email-template/delete/{id}', 'destroy')->name('email.template.destory');
        Route::post('site-settings/email-template/restore/{id}', 'restore')->name('email.template.restore');

        Route::post('site-settings/email-template/update-status', 'updateStatus')->name('email.template.update.status');
        Route::post('site-settings/email-template/update-phase-status', 'updatePhaseStatus')->name('email.template.update.phase.status');
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
        Route::get('site-settings/letter-sets/get-row/{id}', 'getRow')->name('letter.set.get.row');
        Route::post('site-settings/letter-sets/update', 'update')->name('letter.set.update');
        Route::delete('site-settings/letter-sets/delete/{id}', 'destroy')->name('letter.set.destory');
        Route::post('site-settings/letter-sets/restore/{id}', 'restore')->name('letter.set.restore');

        Route::post('site-settings/letter-sets/update-status', 'updateStatus')->name('letter.set.update.status');
        Route::post('site-settings/letter-sets/update-phase-status', 'updatePhaseStatus')->name('letter.set.update.phase.status');
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

    
    Route::controller(HesaQualificationSubjectController::class)->group(function() {
        Route::get('hesa-qualification-subject', 'index')->name('hesaQualificationSubject.index'); 
        Route::get('hesa-qualification-subject/list', 'list')->name('hesaQualificationSubject.list'); 
        Route::post('hesa-qualification-subject/store', 'store')->name('hesaQualificationSubject.store'); 
        Route::get('hesa-qualification-subject/edit/{id}', 'edit')->name('hesaQualificationSubject.edit');
        Route::post('hesa-qualification-subject/update', 'update')->name('hesaQualificationSubject.update');
        Route::delete('hesa-qualification-subject/delete/{id}', 'destroy')->name('hesaQualificationSubject.destory');
        Route::post('hesa-qualification-subject/update-status/{id}', 'updateStatus')->name('hesaQualificationSubject.update.status');
        Route::post('hesa-qualification-subject/restore/{id}', 'restore')->name('hesaQualificationSubject.restore');
    
        Route::get('hesa-qualification-subject/export', 'export')->name('hesaQualificationSubject.export');
        Route::post('hesa-qualification-subject/import', 'import')->name('hesaQualificationSubject.import');
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
    
    Route::controller(HrConditionController::class)->group(function() {
        Route::get('site-settings/hr-condition', 'index')->name('hr.condition'); 
        Route::post('site-settings/hr-condition/store', 'store')->name('hr.condition.store');
    });

    Route::controller(AddressController::class)->group(function() {
        Route::post('address/get-address', 'getAddress')->name('address.get');
        Route::post('address/store', 'store')->name('address.store');
    });

    Route::controller(AttendanceController::class)->group(function() {
        Route::get('attendance', 'index')->name('attendance'); 
        Route::get('attendance/list', 'list')->name('attendance.list'); 
        Route::get('attendance/create/{data}', 'create')->name('attendance.create'); 
        Route::post('attendance/save', 'store')->name('attendance.store'); 
        Route::post('attendance/create-and-store', 'createAndStore')->name('attendance.create.and.store'); 
        Route::post('attendance/update-all', 'updateAll')->name('attendance.update.all'); 
        Route::delete('attendance/delete/{id}', 'destroy')->name('attendance.destory');
        Route::post('attendance/restore', 'restore')->name('attendance.restore');
        Route::get('attendance/{data}', 'generatePDF')->name('attendance.print');
    });

    //GET|HEAD        tutor-attendance ................................................................................. tutor-attendance.index › Attendance\TutorAttendanceController@index  
    //POST            tutor-attendance ................................................................................. tutor-attendance.store › Attendance\TutorAttendanceController@store  
    //GET|HEAD        tutor-attendance/create ........................................................................ tutor-attendance.create › Attendance\TutorAttendanceController@create  
    //GET|HEAD        tutor-attendance/{tutor_attendance} ................................................................ tutor-attendance.show › Attendance\TutorAttendanceController@show  
    //PUT|PATCH       tutor-attendance/{tutor_attendance} ............................................................ tutor-attendance.update › Attendance\TutorAttendanceController@update  
    //DELETE          tutor-attendance/{tutor_attendance} .......................................................... tutor-attendance.destroy › Attendance\TutorAttendanceController@destroy  
    //GET|HEAD        tutor-attendance/{tutor_attendance}/edit ........................................................... tutor-attendance.edit › Attendance\TutorAttendanceController@edit 
    Route::resource('tutor-attendance', TutorAttendanceController::class);

    Route::controller(TutorAttendanceController::class)->group(function() {

        Route::post('tutor-attendance/check', 'check')->name('tutor-attendance.check'); 
        Route::post('tutor-attendance/start-class', 'startClass')->name('tutor-attendance.startClass'); 
    });

    Route::controller(TutorDashboard::class)->group(function() {
        Route::get('tutor-dashboard/list', 'list')->name('tutor-dashboard.list'); 
        //Route::get('tutor-dashboard/term/list', 'tutorTermShowsList')->name('tutor-dashboard.term.list'); 
        Route::get('tutor-dashboard/term/list/{instance_term}/{tutor}', 'tutorTermlistShowByInstance')->name('tutor-dashboard.tutor.modulelist');
        Route::get('tutor-dashboard/show', 'show')->name('tutor-dashboard.show'); 
        Route::get('tutor-dashboard/plan/{plan}', 'showCourseContent')->name('tutor-dashboard.plan.module.show'); 
        Route::get('tutor-dashboard/show/{tutor}/attendance/{plandate}/{type?}', 'attendanceFeedShow')->name('tutor-dashboard.attendance'); 
        
        Route::get('tutor-dashboard/show-new', 'showNew')->name('tutor-dashboard.show.new');  
    });

    Route::resource('plan-assessment', AssessmentPlanController::class);

    Route::controller(AssessmentPlanController::class)->group(function() {
        Route::get('plan-assessment-list', 'list')->name('assessment.plan.list');
        Route::get('plan-assessment/{plan_assessment}/restore', 'restore')->name('plan-assessment.restore');
        
    });
    // GET|HEAD        tutor_module_activity ............................ tutor_module_activity.index › TutorModuleActivityController@index  
    // POST            tutor_module_activity ............................ tutor_module_activity.store › TutorModuleActivityController@store
    // GET|HEAD        tutor_module_activity/create ................... tutor_module_activity.create › TutorModuleActivityController@create  
    // GET|HEAD        tutor_module_activity/{tutor_module_activity} ...... tutor_module_activity.show › TutorModuleActivityController@show  
    // PUT|PATCH       tutor_module_activity/{tutor_module_activity} .. tutor_module_activity.update › TutorModuleActivityController@update  
    // DELETE          tutor_module_activity/{tutor_module_activity} tutor_module_activity.destroy › TutorModuleActivityController@destroy   
    // GET|HEAD        tutor_module_activity/{tutor_module_activity}/edit . tutor_module_activity.edit › TutorModuleActivityController@edit  
    //Route::resource('tutor_module_activity', TutorModuleActivityController::class);

    Route::controller(TutorModuleActivityController::class)->group(function() {

        Route::get('tutor_module_activity/create/{plansDateList}/{activity}', 'create')->name('tutor_module_activity.create'); 
        Route::post('tutor_module_activity', 'store')->name('tutor_module_activity.store'); 
        
        
    });
    // GET|HEAD        plan-contentupload .................................... plan-contentupload.index › PlanContentUploadController@index  
    // POST            plan-contentupload .................................... plan-contentupload.store › PlanContentUploadController@store  
    // GET|HEAD        plan-contentupload/create ........................... plan-contentupload.create › PlanContentUploadController@create  
    // GET|HEAD        plan-contentupload/{plan_contentupload} ................. plan-contentupload.show › PlanContentUploadController@show  
    // PUT|PATCH       plan-contentupload/{plan_contentupload} ............. plan-contentupload.update › PlanContentUploadController@update
    // DELETE          plan-contentupload/{plan_contentupload} ........... plan-contentupload.destroy › PlanContentUploadController@destroy  
    // GET|HEAD        plan-contentupload/{plan_contentupload}/edit ............ plan-contentupload.edit › PlanContentUploadController@edit  
    
    Route::resource('plan-contentupload', PlanContentUploadController::class);

    // GET|HEAD        plan-taskupload .............................................................. plan-taskupload.index › PlanTaskUploadController@index  
    // POST            plan-taskupload .............................................................. plan-taskupload.store › PlanTaskUploadController@store  
    // GET|HEAD        plan-taskupload/create ..................................................... plan-taskupload.create › PlanTaskUploadController@create  
    // GET|HEAD        plan-taskupload/{plan_taskupload} .............................................. plan-taskupload.show › PlanTaskUploadController@show  
    // PUT|PATCH       plan-taskupload/{plan_taskupload} .......................................... plan-taskupload.update › PlanTaskUploadController@update  
    // DELETE          plan-taskupload/{plan_taskupload} ........................................ plan-taskupload.destroy › PlanTaskUploadController@destroy  
    // GET|HEAD        plan-taskupload/{plan_taskupload}/edit ......................................... plan-taskupload.edit › PlanTaskUploadController@edit  
    
    Route::resource('plan-taskupload', PlanTaskUploadController::class,[
        'except' => ['create']
    ]);

    Route::resource('plan-module-task', PlanTaskController::class,[
        'except' => ['create']
    ]);
  
    
    // GET|HEAD        plan-module-task ............................................................................................................................. plan-module-task.index › PlanTaskController@index  
    // POST            plan-module-task ............................................................................................................................. plan-module-task.store › PlanTaskController@store  
    // GET|HEAD        plan-module-task/create/{plan}/{activity} ................................................................................................... plan-module-task.create › PlanTaskController@create  
    // GET|HEAD        plan-module-task/{plan_module_task} ............................................................................................................ plan-module-task.show › PlanTaskController@show  
    // PUT|PATCH       plan-module-task/{plan_module_task} ........................................................................................................ plan-module-task.update › PlanTaskController@update  
    // DELETE          plan-module-task/{plan_module_task} ...................................................................................................... plan-module-task.destroy › PlanTaskController@destroy  
    // GET|HEAD        plan-module-task/{plan_module_task}/edit
    Route::controller(PlanTaskController::class)->group(function() {

        Route::get('plan-module-task/create/{plan}/{activity}', 'create')->name('plan-module-task.create'); 

        Route::post('plan-task/{id}', 'updatePlanTask')->name('plan-module-task.auto.sync');
        
    });

    // GET|HEAD        plan-participant ...................................................................................................................... plan-participant.index › PlanParticipantController@index  
    // POST            plan-participant ...................................................................................................................... plan-participant.store › PlanParticipantController@store  
    // GET|HEAD        plan-participant/create ............................................................................................................. plan-participant.create › PlanParticipantController@create  
    // GET|HEAD        plan-participant-list ................................................................................................................... plan-participant.list › PlanParticipantController@list  
    // GET|HEAD        plan-participant/{plan_participant} ..................................................................................................... plan-participant.show › PlanParticipantController@show  
    // PUT|PATCH       plan-participant/{plan_participant} ................................................................................................. plan-participant.update › PlanParticipantController@update  
    // DELETE          plan-participant/{plan_participant} ............................................................................................... plan-participant.destroy › PlanParticipantController@destroy  
    // GET|HEAD        plan-participant/{plan_participant}/edit 

    Route::resource('plan-participant', PlanParticipantController::class);

    Route::controller(PlanParticipantController::class)->group(function() {

        Route::get('plan-participant-list', 'list')->name('plan-participant.list'); 
        
    }); 



    Route::resource('student-assign',StudentAssignController::class);

    Route::controller(StudentAssignController::class)->group(function() {

        Route::get('student-assign-list', 'list')->name('student-assign.list'); 
        Route::post('student-assign-export', 'exportStudentList')->name('student.assign.export'); 
        
    });


    Route::resource('term-declaration', TermDeclarationController::class,[
        'except' => ['create','update']
    ]);
    Route::controller(TermDeclarationController::class)->group(function() {

        Route::get('term-declaration-list', 'list')->name('term-declaration.list'); 
        Route::post('term-declaration/{term}/update', 'update')->name('term-declaration.update'); 
        
    });


    Route::controller(DashboardController::class)->group(function() {
        Route::get('personal-tutor-dashboard', 'index')->name('pt.dashboard'); 
        Route::post('personal-tutor-dashboard/get-classes', 'getClassess')->name('pt.get.classes'); 
        Route::post('personal-tutor-dashboard/search-student', 'searchStudent')->name('pt.student.filter.id'); 
        Route::post('personal-tutor-dashboard/get-class-info', 'getClassInformations')->name('pt.dashboard.class.info');
        Route::post('personal-tutor-dashboard/update-class-status', 'UpdateClassStatus')->name('pt.dashboard.class.status.update'); 
        Route::get('personal-tutor-dashboard/outstanding-upload-count', 'totalUndecidedCount')->name('pt.dashboard.class.outstanding.count');

        Route::post('personal-tutor-dashboard/student-attendance-tracking', 'getStudentAttenTrackingHtml')->name('pt.dashboard.get.student.attn.tracking'); 
        
    });

    Route::controller(ProgrammeDashboardController::class)->group(function() {
        Route::get('programme-dashboard', 'index')->name('programme.dashboard'); 
        Route::post('programme-dashboard/get-class-info', 'getClassInformations')->name('programme.dashboard.class.info'); 

        Route::get('programme-dashboard/tutors/{id}/{course?}', 'tutors')->name('programme.dashboard.tutors'); 
        Route::get('programme-dashboard/tutors/details/{id}/{tutorid}', 'tutorsDetails')->name('programme.dashboard.tutors.details'); 

        Route::get('programme-dashboard/personal-tutors/{id}/{course?}', 'personalTutors')->name('programme.dashboard.personal.tutors'); 
        Route::get('programme-dashboard/personal-tutors/details/{id}/{tutorid}', 'personalTutorDetails')->name('programme.dashboard.personal.tutors.details'); 

        Route::post('programme-dashboard/cancel-class', 'cancelClass')->name('programme.dashboard.cancel.class'); 
        Route::post('programme-dashboard/end-class', 'endClass')->name('programme.dashboard.end.class'); 

        Route::post('programme-dashboard/reassign-class', 'reAssignClass')->name('programme.dashboard.reassign.class'); 
        Route::post('programme-dashboard/get-undecided-class', 'getUndecidedClass')->name('programme.dashboard.get.undecided.class'); 
    });

    Route::controller(DatafutureFieldCategoryController::class)->group(function() {
        Route::get('site-settings/df-field-categories', 'index')->name('df.field.categories'); 
        Route::get('site-settings/df-field-categories/list', 'list')->name('df.field.categories.list'); 
        Route::post('site-settings/df-field-categories/store', 'store')->name('df.field.categories.store'); 
        Route::get('site-settings/df-field-categories/edit/{id}', 'edit')->name('df.field.categories.edit');
        Route::post('site-settings/df-field-categories/update', 'update')->name('df.field.categories.update');
        Route::delete('site-settings/df-field-categories/delete/{id}', 'destroy')->name('df.field.categories.destory');
        Route::post('site-settings/df-field-categories/restore', 'restore')->name('df.field.categories.restore');
    });

    Route::controller(DatafutureFieldController::class)->group(function() {
        Route::get('site-settings/df-fields', 'index')->name('df.fields'); 
        Route::get('site-settings/df-fields/list', 'list')->name('df.fields.list'); 
        Route::post('site-settings/df-fields/store', 'store')->name('df.fields.store'); 
        Route::get('site-settings/df-fields/edit/{id}', 'edit')->name('df.fields.edit');
        Route::post('site-settings/df-fields/update', 'update')->name('df.fields.update');
        Route::delete('site-settings/df-fields/delete/{id}', 'destroy')->name('df.fields.destory');
        Route::post('site-settings/df-fields/restore', 'restore')->name('df.fields.restore');
    });

    Route::controller(EmployeeTimeKeepingController::class)->group(function(){
        Route::get('employee-profile/time-keeper/{id}', 'index')->name('employee.time.keeper'); 
        Route::get('employee-profile/time-keeper/download-pdf/{id}/{month}/{year}', 'downloadPdf')->name('employee.time.keeper.download.pdf'); 
        Route::post('employee-profile/time-keeper/generate-recored', 'generateRecored')->name('employee.time.keeper.generate.recored'); 
    });

    // GET............agent-user....................agent-user.index.............App\Http\Controllers\Agent\AgentController@index
    // GET............agent-user/create.............agent-user.create............App\Http\Controllers\Agent\AgentController@create
    // POST...........agent-user....................agent-user.store.............App\Http\Controllers\Agent\AgentController@store
    // GET............agent-user/{agent_user}.......agent-user.show..............App\Http\Controllers\Agent\AgentController@show
    // GET............agent-user/{agent_user}/edit..agent-user.edit..............App\Http\Controllers\Agent\AgentController@edit
    // POST............agent-user/{agent_user}.......agent-user.update............App\Http\Controllers\Agent\AgentController@update
    // DELETE.........agent-user/{agent_user}.......agent-user.destroy...........App\Http\Controllers\Agent\AgentController@destroy
    // GET............agent-user-list...............agent-user.list..............App\Http\Controllers\Agent\AgentController@list
    
    Route::resource('agent-user', AgentController::class,[
        'except' => ['update']
    ]);
    
    Route::controller(AgentController::class)->group(function() {

        Route::post('agent-user/{agent_user}', 'update')->name('agent-user.update'); 
        Route::post('agent-user/{agent_user}/address', 'addressUpdate')->name('agent-user.address.store'); 
        
        Route::get('agent-user-list', 'list')->name('agent-user.list');
        Route::get('agent-user-applicantlist/{id}', 'listByQuery')->name('agent-user.query.list');
        Route::get('agent-user-termlist/{id}', 'ApplicantionList')->name('agent-user.termlist'); 
        
        Route::post('agent-user/{agent_user}/restore', 'restore')->name('agent-user.restore');
        Route::get('agent-user/{agent_user}/sub', 'subAgentShow')->name('agent-user.sub.show');
        

    });
    // GET|HEAD        sub-agent ............................. sub-agent.index › Agent\SubAgentController@index  
    // POST            sub-agent ............................. sub-agent.store › Agent\SubAgentController@store  
    // GET|HEAD        sub-agent-list ........................ sub-agent.list › Agent\AgentController@list  
    // GET|HEAD        sub-agent/create ...................... sub-agent.create › Agent\SubAgentController@create  
    // GET|HEAD        sub-agent/{sub_agent} ................. sub-agent.show › Agent\SubAgentController@show  
    // DELETE          sub-agent/{sub_agent} ................. sub-agent.destroy › Agent\SubAgentController@destroy  
    // GET|HEAD        sub-agent/{sub_agent}/edit ............ sub-agent.edit › Agent\SubAgentController@edit  
    Route::resource('sub-agent', SubAgentController::class,[
        'except' => ['update']
    ]);
    Route::controller(SubAgentController::class)->group(function() {

        Route::post('sub-agent/{sub_agent}', 'update')->name('sub-agent.update'); 

        Route::get('sub-agent-list', 'list')->name('sub-agent.list');
        Route::post('sub-agent/{sub_agent}/restore', 'restore')->name('sub-agent.restore');
    });
    Route::controller(AgentDocumentsController::class)->group(function(){
        Route::get('agent-profile/documents/{id}', 'index')->name('agent-user.documents'); 
        Route::post('agent-profile/documents/uploads-documents', 'employeeUploadDocument')->name('agent-user.documents.upload.documents'); 
        Route::get('agent-profile/documents-upload/uploads-list', 'list')->name('agent-user.documents.uploads.list');
        Route::get('agent-profile/documents-upload/communication-list', 'communicationList')->name('agent-user.documents.communication.list');
        Route::delete('agent-profile/documents/uploads-destroy', 'destroy')->name('agent-user.documents.destory.uploads');
        Route::post('agent-profile/documents/uploads-restore', 'restore')->name('agent-user.documents.restore.uploads');
        Route::post('agent-profile/documents/download-url', 'downloadUrl')->name('agent-user.documents.download.url');
    });


    Route::controller(CourseManagementController::class)->group(function() {
        Route::get('course-management', 'index')->name('course.management'); 
    });

    Route::controller(PendingTaskManagerController::class)->group(function() {
        Route::get('task-manager', 'index')->name('task.manager'); 
        Route::get('task-manager/all', 'allTasks')->name('task.manager.all'); 
        Route::get('task-manager/show/{id}', 'show')->name('task.manager.show'); 
        Route::get('task-manager/list', 'list')->name('task.manager.list'); 

        Route::get('task-manager/download-task-students-emails', 'downloadTaskStudentEmailListExcel')->name('task.manager.students.email.excel'); 
        Route::post('task-manager/complete-email-id-task', 'completeTaskStudentEmailTask')->name('task.manager.comlete.students.email.id.task'); 
        Route::post('task-manager/download-id-cards', 'downloadIdCard')->name('task.manager.download.id.card'); 

        Route::post('task-manager/update-task-status', 'updateTaskStatus')->name('task.manager.update.task.status'); 
        Route::get('task-manager/download-task-students-list', 'downloadTaskStudentListExcel')->name('task.manager.students.list.excel'); 
        Route::post('task-manager/canceled-task', 'canceledTask')->name('task.manager.canceled.task'); 

        Route::post('task-manager/upload-task-documents', 'uploadTaskDocument')->name('task.manager.upload.document');
        Route::post('task-manager/task-outcome-statuses', 'taskOutcomeStatuses')->name('task.manager.outcome.statuses');
        Route::post('task-manager/update-task-outcome', 'updateTaskOutcome')->name('task.manager.update.outcome');

        Route::post('task-manager/download-document', 'documentDownload')->name('task.manage.document.download');
        Route::post('task-manager/create-pearson-registration-task', 'createPearsonRegistrationTask')->name('task.manager.create.pearson.registration');
        Route::get('task-manager/pearson-registration-student-export', 'pearsonRegStudentListExport')->name('task.manager.pearson.registration.excel');
        
        Route::post('task-manager/upload-pearson-registration-confirmation', 'uploadPearsonRegistrationConfirmation')->name('student.process.upload.registration.confirmations');
        Route::post('task-manager/update-bulk-status', 'updateBulkStatus')->name('task.manager.update.bulk.status');
    });

    Route::controller(AssignController::class)->group(function() {
        Route::get('course-management/assign/{acid}/{tdid}/{crid}/{grid}', 'index')->name('assign'); 

        Route::post('course-management/assign/get-existing-student-list-by-module', 'getExistingStudentListByModule')->name('assign.get.existing.student.list.by.module'); 
        Route::post('course-management/assign/get-potential-student-list-by-search', 'getPotentialStudentListBySearch')->name('assign.get.potential.student.list.by.search'); 
        Route::post('course-management/assign/get-group-list', 'getGroupList')->name('assign.get.group.list'); 
        Route::post('course-management/assign/get-module-and-student-list', 'getModuleAndStudentList')->name('assign.get.module.student.list'); 
        Route::post('course-management/assign/get-student-list-by-module', 'getStudentListByModule')->name('assign.get.student.list.by.module'); 

        Route::get('course-management/assign/unsignned-list', 'unsignnedList')->name('assign.unsignned.list'); 

        Route::post('course-management/assign/module-list-html', 'getModulListHtml')->name('assign.get.module.list.html'); 

        Route::post('course-management/assign/students-to-plan', 'assignStudentsToPlan')->name('assign.students.to.plan'); 
        Route::post('course-management/assign/remove-students-from-plan', 'deassignStudentsFromPlan')->name('assign.remove.students.from.plan'); 

        Route::post('course-management/assign/get-potential-student-list-from-unsigned-list', 'getPotentialStudentListFromUnsignedList')->name('assign.generage.potential.list.from.unsigned.list'); 
        Route::post('course-management/assign/get-modules-for-reassigns', 'getModulesForReassign')->name('assigns.get.modules.for.reassign'); 

        Route::post('course-management/assign/re-assign-student-new-groups', 'reAssignStudentNewGroup')->name('assigns.re.assign.students.new.group'); 
    });

    Route::resource('result', ResultController::class,[
        'except' => ['index']
    ]);
    
    Route::controller(ResultController::class)->group(function() {
        
        Route::post('result-single/', 'storeSingle')->name('result.store.single'); 
        Route::get('result-index/{assessmentPlan}', 'index')->name('result.index'); 
        //Route::post('result/update-all', 'updateAll')->name('result.update.all');
        
        Route::post('result/resubmit', 'resubmit')->name('result.resubmit');
        Route::post('result/resubmit-all', 'resubmitAll')->name('result.resubmit.all');
        Route::post('result/{id}/restore', 'restore')->name('result.restore');
        Route::post('result/{id}/default', 'default')->name('result.default');
        
        Route::get('result-list/{assessment_plan}', 'list')->name('result.list'); 
        Route::post('result-list/{assessment_plan}/restore', 'restore')->name('result.restore.all');

        Route::get('result-list/{assessmentPlan}/download', 'downloadStudentListExcel')->name('result.download-excel');
        Route::get('result-list/{assessmentPlan}/download-result', 'downloadStudentResultExcel')->name('result.downloadresult-excel');
        Route::post('result-list-upload', 'uploadStudentExcel')->name('result.upload-excel');
        Route::delete('result-list/{assessmentPlan}/delete-all', 'destroyByAssessmentPlan')->name('result.all.delete');
        Route::get('result-list/{assessmentPlan}/show/{student}', 'resultByAssessmentAndStudent')->name('result.show.assessment');
        
        Route::post('results/update-bulk', 'updateBulk')->name('result.update.bulk');
        
    });
    Route::controller(ResultPreviousController::class)->group(function() {
        Route::get('result-previous', 'index')->name('student.result.previous.index');
        Route::get('result-previous/list', 'list')->name('student.result.previous.list');
        Route::post('result-previous/store', 'store')->name('student.result.previous.store');
        Route::get('result-previous/edit/{id}', 'edit')->name('student.result.previous.edit');
        Route::post('result-previous/update', 'update')->name('student.result.previous.update');
        Route::delete('result-previous/delete/{id}', 'destroy')->name('student.result.previous.destory');
        Route::post('result-previous/restore/{id}', 'restore')->name('student.result.previous.restore');
        Route::get('result-previous/attempt', 'attempt')->name('student.result.previous.attemptlist');
        
        
    });
    

    Route::controller(CompanyController::class)->group(function() {
        Route::get('companies/list', 'list')->name('companies.list'); 
        Route::post('companies/store', 'store')->name('companies.store'); 
        Route::get('companies/edit/{id}', 'edit')->name('companies.edit');
        Route::post('companies/update', 'update')->name('companies.update');
        Route::delete('companies/delete/{id}', 'destroy')->name('companies.destory');
        Route::post('companies/restore/{id}', 'restore')->name('companies.restore');
        Route::post('companies/update-status/{id}', 'updateStatus')->name('companies.update.status');
    });

    Route::controller(CompanySupervisorController::class)->group(function() {
        Route::get('companies/supervisor/list', 'list')->name('companies.supervisor.list'); 
        Route::post('companies/supervisor/store', 'store')->name('companies.supervisor.store'); 
        Route::post('companies/supervisor/edit', 'edit')->name('companies.supervisor.edit');
        Route::post('companies/supervisor/update', 'update')->name('companies.supervisor.update');
        Route::delete('companies/supervisor/delete/{id}', 'destroy')->name('companies.supervisor.destroy');
    });

    Route::controller(WorkPlacementController::class)->group(function() {
        Route::post('student/get-company-supervisor', 'getSupervisorByCompany')->name('student.get.company.supervisor'); 
        Route::post('student/store-work-placement-hour', 'storeHour')->name('student.store.work.placement.hour'); 
        Route::get('student/store-work-placement-hour-list', 'hourList')->name('student.work.placement.hour.list'); 
        Route::get('student/edit-work-placement-hour/{id}', 'editHour')->name('student.edit.work.placement.hour'); 
        Route::post('student/update-work-placement-hour', 'updateHour')->name('student.update.work.placement.hour'); 

        Route::delete('student/destroy-work-placement-hour/{id}', 'destroyHour')->name('student.destroy.work.placement.hour'); 
        Route::post('student/restore-work-placement-hour', 'restoreHour')->name('student.restore.work.placement.hour'); 
    });

    Route::controller(WblProfileController::class)->group(function() {
        Route::post('student/store-wbl-profile', 'store')->name('student.store.wbl.profile'); 
        Route::get('student/wbl-profile-list', 'list')->name('student.wbl.profile.list'); 
        Route::get('student/edit-wbl-profile/{id}', 'edit')->name('student.edit.wbl.profile'); 
        Route::post('student/update-wbl-profile', 'update')->name('student.update.wbl.profile'); 

        Route::delete('student/destroy-wbl-profile/{id}', 'destroy')->name('student.destroy.wbl.profile'); 
        Route::post('student/restore-wbl-profile', 'restore')->name('student.restore.wbl.profile'); 
    });

    Route::controller(MyStaffController::class)->group(function(){
        Route::get('my-account/staffs', 'index')->name('user.account.staff'); 
        Route::post('my-account/staffs/update-leave', 'staffsUpdateLeave')->name('user.account.staff.update.leave'); 
        Route::get('my-account/staffs/team-holiday', 'myTeamHoliday')->name('user.account.staff.team.holiday'); 
        Route::post('my-account/staffs/ajax-team-holiday', 'ajaxTeamHoliday')->name('user.account.staff.team.holiday.ajax'); 
    });

    Route::controller(EmployeeArchiveController::class)->group(function(){
        Route::get('employee-profile/archive/{id}', 'index')->name('employee.archive'); 
        Route::get('employee-profile/archive-list', 'list')->name('employee.archive.list'); 
    });          
    
    Route::controller(AttendanceReportController::class)->group(function(){
        Route::get('hr/portal/reports/attendance/list/{date}', 'index')->name('hr.portal.reports.attendance');
        Route::post('hr/portal/reports/attendance/filter', 'filterReport')->name('hr.portal.reports.attendance.filter');
        Route::get('hr/portal/reports/attendance/show/{id}/{date}', 'show')->name('hr.portal.reports.attendance.show');
        Route::get('hr/portal/reports/attendance/export/{date}', 'exportExcel')->name('hr.portal.reports.attendance.export');
    });

    Route::controller(AccMethodController::class)->group(function() {
        Route::get('site-settings/methods', 'index')->name('site.settings.methods'); 
        Route::get('site-settings/methods/list', 'list')->name('site.settings.methods.list'); 
        Route::post('site-settings/methods/store', 'store')->name('site.settings.methods.store');
        Route::post('site-settings/methods/edit', 'edit')->name('site.settings.methods.edit');
        Route::post('site-settings/methods/update', 'update')->name('site.settings.methods.update');

        Route::delete('site-settings/methods/delete/{id}', 'destroy')->name('site.settings.methods.destory');
        Route::post('site-settings/methods/restore/{id}', 'restore')->name('site.settings.methods.restore');
        Route::post('site-settings/methods/update-status/{id}', 'updateStatus')->name('site.settings.methods.update.status');
    });

    Route::controller(AccBankController::class)->group(function() {
        Route::get('site-settings/banks', 'index')->name('site.settings.banks'); 
        Route::get('site-settings/banks/list', 'list')->name('site.settings.banks.list'); 
        Route::post('site-settings/banks/store', 'store')->name('site.settings.banks.store');
        Route::post('site-settings/banks/edit', 'edit')->name('site.settings.banks.edit');
        Route::post('site-settings/banks/update', 'update')->name('site.settings.banks.update');

        Route::delete('site-settings/banks/delete/{id}', 'destroy')->name('site.settings.banks.destory');
        Route::post('site-settings/banks/restore/{id}', 'restore')->name('site.settings.banks.restore');
        Route::post('site-settings/banks/update-status/{id}', 'updateStatus')->name('site.settings.banks.update.status');
    });

    Route::controller(AccCategoryController::class)->group(function() {
        Route::get('site-settings/category', 'index')->name('site.settings.category'); 
        Route::post('site-settings/category/filter-dropdown', 'filterDropdown')->name('site.settings.category.filter.dropdown'); 
        Route::post('site-settings/category/store', 'store')->name('site.settings.category.store');
        Route::post('site-settings/category/edit', 'edit')->name('site.settings.category.edit');
        Route::post('site-settings/category/update', 'update')->name('site.settings.category.update');

        Route::delete('site-settings/category/delete/{id}', 'destroy')->name('site.settings.category.destory');
    });

    Route::controller(SummaryController::class)->group(function() {
        Route::get('accounts', 'index')->name('accounts'); 
        Route::post('accounts/search', 'search')->name('accounts.search'); 
        Route::get('accounts/report/{start}/{end}', 'report')->name('accounts.report'); 
        Route::post('accounts/report-details', 'reportDetails')->name('accounts.report.details'); 
    });

    Route::controller(StorageController::class)->group(function() {
        Route::get('accounts/storage/transactions/{id}', 'index')->name('accounts.storage'); 
        Route::post('accounts/storage/store', 'store')->name('accounts.storage.trans.store'); 
        Route::get('accounts/storage/list', 'list')->name('accounts.storage.trans.list'); 
        Route::post('accounts/storage/edit', 'edit')->name('accounts.storage.trans.edit'); 
        Route::post('accounts/storage/update', 'update')->name('accounts.storage.trans.update'); 

        Route::get('accounts/storage/export/{querystr}/{storage_id}', 'export')->name('accounts.storage.trans.export'); 

        Route::delete('accounts/storage/delete/{id}', 'destroy')->name('accounts.storage.trans.destroy');

        Route::get('accounts/storage/update-document-url', 'updateDocumentUrl')->name('accounts.storage.update.doc.url');
        Route::post('accounts/storage/trans-document-download-url', 'documentDownloadUrl')->name('accounts.storage.trans.download.link');
    });

    Route::controller(AccCsvTransactionController::class)->group(function() {
        Route::get('accounts/csv/transactions/{bank}/{csv?}', 'index')->name('accounts.csv.transactions'); 
        Route::post('accounts/csv/store', 'csvStore')->name('accounts.csv.store'); 
        Route::post('accounts/csv/update', 'csvUpdate')->name('accounts.csv.update'); 
    });

    Route::controller(DocumentRoleAndPermissionController::class)->group(function() {
        Route::get('site-settings/documents-role-and-permission', 'index')->name('site.settings.doc.role.permission'); 
        Route::post('site-settings/documents-role-and-permission/store', 'store')->name('site.settings.doc.role.permission.store');
        Route::get('site-settings/documents-role-and-permission/list', 'list')->name('site.settings.doc.role.permission.list'); 
        Route::post('site-settings/documents-role-and-permission/edit', 'edit')->name('site.settings.doc.role.permission.edit');
        Route::post('site-settings/documents-role-and-permission/update', 'update')->name('site.settings.doc.role.permission.update');
        Route::delete('site-settings/documents-role-and-permission/delete/{id}', 'destroy')->name('site.settings.doc.role.permission.destory');
        Route::post('site-settings/documents-role-and-permission/restore/{id}', 'restore')->name('site.settings.doc.role.permission.restore');
    });

    Route::controller(FilemanagerController::class)->group(function() {
        Route::get('file-manager/{params?}', 'index')->where('params', '(.*)')->name('file.manager'); 
        Route::post('file-manager/create-folder', 'createFolder')->name('file.manager.create.folder'); 
        Route::post('file-manager/employee-permission-set', 'employeePermissionSet')->name('file.manager.get.employee.permission.set'); 
        Route::post('file-manager/permission-set', 'permissionSet')->name('file.manager.get.permission.set'); 
        Route::post('file-manager/edit-folder', 'editFolder')->name('file.manager.edit.folder'); 
        Route::post('file-manager/update-folder', 'updateFolder')->name('file.manager.update.folder'); 
        Route::post('file-manager/edit-folder-permission', 'editFolderPermission')->name('file.manager.edit.folder.permission'); 
        Route::post('file-manager/update-folder-permission', 'updateFolderPermission')->name('file.manager.update.folder.permission'); 
        Route::delete('file-manager/destroy-folder', 'destroyFolder')->name('file.manager.destroy.folder'); 

        Route::post('file-manager/upload-file', 'uploadFile')->name('file.manager.upload.file'); 
        Route::post('file-manager/get-file-data', 'getFileData')->name('file.manager.get.file.data'); 
        Route::post('file-manager/update-file', 'updateFile')->name('file.manager.update.file'); 
        Route::post('file-manager/upload-new-version', 'uploadNewVersion')->name('file.manager.upload.new.version'); 
        Route::post('file-manager/file-version-history-list', 'fileVersionHistoryList')->name('file.manager.file.version.history'); 
        Route::post('file-manager/file-restore-version', 'fileRestoreVersion')->name('file.manager.file.restore.version'); 
        Route::post('file-manager/edit-file-permission', 'editFilePermission')->name('file.manager.edit.file.permission'); 
        Route::post('file-manager/update-file-permission', 'updateFilePermission')->name('file.manager.update.file.permission'); 
        Route::post('file-manager/store-file-reminder', 'storeFileReminder')->name('file.manager.store.file.reminder'); 
        Route::post('file-manager/edit-file-reminder', 'editFileReminder')->name('file.manager.edit.file.reminder'); 
        Route::delete('file-manager/destroy-file', 'destroyFile')->name('file.manager.destroy.file'); 
    });

    Route::controller(DocumentTagController::class)->group(function() {
        Route::post('file-manager/tags/store', 'store')->name('file.manager.store.tags'); 
        Route::post('file-manager/tags/search', 'searchTags')->name('file.manager.search.tags'); 
    });

    Route::controller(CommunicationTemplateController::class)->group(function() {
        Route::get('site-settings/communication-templates', 'index')->name('communication.template'); 
        Route::get('site-settings/communication-templates/list', 'list')->name('communication.template.list'); 
        Route::post('site-settings/communication-templates/store', 'store')->name('communication.template.store');
        Route::get('site-settings/communication-templates/edit/{id}', 'edit')->name('communication.template.edit');
        Route::post('site-settings/communication-templates/update', 'update')->name('communication.template.update');
        Route::delete('site-settings/communication-templates/delete/{id}', 'destroy')->name('communication.template.destory');
        Route::post('site-settings/communication-templates/restore/{id}', 'restore')->name('communication.template.restore');
    });

    Route::controller(EmployeeTrainingController::class)->group(function(){ 
        Route::post('employee-profile/training-store', 'store')->name('employee.training.store');
        Route::get('employee-profile/training-list', 'list')->name('employee.training.list');
        Route::post('employee-profile/training-edit', 'edit')->name('employee.training.edit');
        Route::post('employee-profile/training-update', 'update')->name('employee.training.update');
        Route::delete('employee-profile/training-destroy', 'destroy')->name('employee.training.destory');
        Route::post('employee-profile/training-restore', 'restore')->name('employee.training.restore');
    });

    Route::controller(AttendanceLiveController::class)->group(function(){
        Route::get('live', 'index')->name('attendance.live'); 
        Route::post('live/attendance-data', 'ajaxLiveData')->name('attendance.live.attedance.ajax');
    
        Route::post('live/get-employee-mail', 'getEmployeeEmail')->name('attendance.live.get.employee.mail');
        Route::post('live/sent-mail', 'sentEmail')->name('attendance.live.attedance.sent.mail');
    });

    Route::controller(MyGroupController::class)->group(function(){
        Route::get('my-account/groups', 'index')->name('user.account.group'); 
        Route::post('my-account/groups/store', 'store')->name('user.account.group.store'); 
        Route::get('my-account/groups/list', 'list')->name('user.account.group.list'); 
        Route::post('my-account/groups/edit', 'edit')->name('user.account.group.edit'); 
        Route::post('my-account/groups/update', 'update')->name('user.account.group.update'); 

        Route::delete('my-account/groups/delete/{id}', 'destroy')->name('user.account.group.destory');
        Route::post('my-account/groups/restore/{id}', 'restore')->name('user.account.group.restore');
    });

    Route::controller(ApplicationAnalysisController::class)->group(function(){
        Route::any('reports/application-analysis-report', 'index')->name('report.application.analysis'); 
        Route::get('reports/application-analysis-report/print-personal-data/{semester}', 'printPersonalData')->name('report.application.analysis.print.pd'); 
    });

    Route::controller(BulkCommunicationController::class)->group(function(){
        Route::get('bulk-communication/communication/{classplans}', 'index')->name('bulk.communication'); 
        Route::get('bulk-communication/list', 'list')->name('bulk.communication.student.list'); 

        Route::post('bulk-communication/get-sms-template', 'getSmsTemplate')->name('bulk.communication.get.sms.template'); 
        Route::post('bulk-communication/send-sms', 'sendSms')->name('bulk.communication.send.sms'); 

        Route::post('bulk-communication/get-email-template', 'getEmailTemplate')->name('bulk.communication.get.mail.template'); 
        Route::post('bulk-communication/send-email', 'sendEmail')->name('bulk.communication.send.email'); 
        Route::post('bulk-communication/send-group-email', 'sendGroupEmail')->name('bulk.communication.send.group.email'); 

        Route::post('bulk-communication/get-letter-template', 'getLetterTemplate')->name('bulk.communication.get.letter.set'); 
        Route::post('bulk-communication/send-letter', 'sendLetter')->name('bulk.communication.send.letter'); 
    });
    
    Route::controller(StudentDataReportController::class)->prefix('reports')->group(function(){
        Route::get('student-data-reports', 'index')->name('report.student.data.view'); 
        Route::post('student-reports-list', 'totalCount')->name('report.student.data.total'); 
        Route::post('student-excel-download', 'excelDownload')->name('report.student.data.excel'); 
    });

    Route::controller(ReportsAttendanceReportController::class)->group(function(){
        Route::get('reports/attendance-reports', 'index')->name('report.attendance.reports'); 
        Route::get('reports/attendance-reports-list', 'list')->name('report.attendance.reports.list'); 
        Route::post('reports/attendance-excel-download', 'excelDownload')->name('report.attendance.reports.excel'); 
    });

    Route::controller(SystemReportController::class)->group(function(){
        Route::get('reports', 'index')->name('reports'); 
        Route::get('reports/accounts', 'accountsReports')->name('reports.accounts'); 
        Route::get('reports/intake-performance', 'intakePerformance')->name('reports.intake.performance'); 
    });

    Route::controller(CollectionReportController::class)->group(function(){
        Route::post('reports/accounts/export-collection-report', 'exportCollectionReport')->name('reports.account.collection.export'); 
    });

    Route::controller(PaymentUploadManagementController::class)->group(function(){
        Route::get('reports/accounts/slc-payment-history-list', 'slcPaymentHistoryList')->name('reports.account.payment.history.list'); 
        Route::post('reports/accounts/upload-payment-csv', 'uploadCSV')->name('reports.account.payment.upload.csv'); 
        Route::post('reports/accounts/save-csv-transactions', 'storeCsvTransactions')->name('reports.account.payment.save.csv.transactions'); 
        Route::post('reports/accounts/history-recheck-errors', 'historyReCheckError')->name('reports.account.payment.recheck.errors'); 
        Route::post('reports/accounts/history-recheck-insert', 'historyReCheckInsert')->name('reports.account.payment.recheck.insert'); 
        Route::post('reports/accounts/history-find-agreements', 'historyFindAgreements')->name('reports.account.payment.find.agreements'); 
        Route::post('reports/accounts/history-payment-force-insert', 'historyPaymentForceInsert')->name('reports.account.payment.force.insert'); 
    });

    Route::controller(DueReportController::class)->group(function(){
        Route::post('reports/accounts/due/export', 'exportExcel')->name('reports.account.due.export'); 
        Route::post('reports/accounts/due/get-course-status-by-semester', 'getCourseStatusBySemester')->name('reports.account.due.get.course.status'); 
        Route::post('reports/accounts/due/get-status-by-semester-course', 'getStatusBySemesterCourse')->name('reports.account.due.get.statuses'); 
    });

    Route::controller(ContinuationReportController::class)->group(function(){
        Route::post('reports/intake-performance/get-continuation-report', 'getContinuationReport')->name('reports.intake.performance.get.continuation.report'); 
        Route::get('reports/intake-performance/print-continuation-rate/{semesters?}', 'printContinuationRateReport')->name('reports.intake.performance.print.continuation.rate'); 
    });

    Route::controller(ConnectTransactionController::class)->group(function(){
        Route::post('reports/accounts/search-transactions', 'searchTransactions')->name('reports.accounts.search.transaction'); 
        Route::get('reports/accounts/connections/{transaction_id}', 'transactionConnection')->name('reports.accounts.transaction.connection'); 
        Route::post('reports/accounts/connections/store', 'store')->name('reports.accounts.transaction.connection.store'); 
        Route::get('reports/accounts/connections/export/{transaction_id}', 'exportList')->name('reports.accounts.transaction.connection.export'); 
    });

    Route::controller(AttendanceRateReportController::class)->group(function(){
        Route::post('reports/intake-performance/get-attendance-rate', 'getAttendanceRateReport')->name('reports.intake.performance.get.attendance.rate'); 
        Route::get('reports/intake-performance/print-attendance-rate/{semesters?}', 'printAttendanceRateReport')->name('reports.intake.performance.print.attendance.rate'); 
    });

    Route::controller(RetentionRateReportController::class)->group(function(){
        Route::post('reports/intake-performance/get-retention-report', 'getRetentionReport')->name('reports.intake.performance.get.retention.report'); 
        Route::get('reports/intake-performance/print-retention-rate/{semesters?}', 'printRetentionRateReport')->name('reports.intake.performance.print.retention.rate'); 
        Route::get('reports/intake-performance/export-retention-rate/{semesters?}', 'exportRetentionRateReport')->name('reports.intake.performance.export.retention.rate'); 
    });

    Route::controller(TermPerformanceReportController::class)->group(function(){
        Route::any('reports/term-performance', 'index')->name('reports.term.performance'); 
        Route::any('reports/term-performance/trend/{terms}', 'viewTermTrend')->name('reports.term.performance.term.trend'); 

        Route::any('reports/term-performance/course/{terms}/{course}', 'courseView')->name('reports.term.performance.course.view'); 
        Route::any('reports/term-performance/course/trend/{terms}/{course}', 'courseTrendView')->name('reports.term.performance.course.trend.view'); 


        Route::any('reports/term-performance/group/{terms}/{course}/{group}', 'groupView')->name('reports.term.performance.group.view'); 
        Route::any('reports/term-performance/group/trend/{terms}/{course}/{group}', 'groupTrendView')->name('reports.term.performance.group.trend.view'); 
    });

    Route::controller(StudentFlagController::class)->group(function() {
        Route::get('site-settings/flags', 'index')->name('flags'); 
        Route::get('site-settings/flags/list', 'list')->name('flags.list'); 
        Route::post('site-settings/flags/store', 'store')->name('flags.store'); 
        Route::get('site-settings/flags/edit/{id}', 'edit')->name('flags.edit');
        Route::post('site-settings/flags/update', 'update')->name('flags.update');
        Route::delete('site-settings/flags/delete/{id}', 'destroy')->name('flags.destory');
        Route::post('site-settings/flags/restore/{id}', 'restore')->name('flags.restore');
    });
    Route::controller(SlcDataReportController::class)->group(function(){

        Route::get('reports/slc-report', 'index')->name('reports.slc.index'); 
        Route::any('reports/slc-attendance/excel-export', 'SLCAttendanceExcelDownload')->name('reports.slc.attendance.excel.export'); 
        Route::any('reports/slc-register/excel-export', 'SlcRegistrationHistoryExcelDownload')->name('reports.slc.register.excel.export'); 
        Route::any('reports/slc-coc/excel-export', 'SlcCocHistoryExcelDownload')->name('reports.slc.coc.excel.export'); 
        
    });
    Route::controller(ClassStatusByTermController::class)->group(function(){

        Route::get('reports/class-status-report', 'index')->name('reports.class-status.index'); 
        Route::any('reports/class-status-report/list', 'list')->name('reports.class-status.list'); 
        Route::any('reports/class-status-report/schedule-list/{group}/{course}/{term}', 'scheduleList')->name('reports.class-status.schedule'); 
        
        
    });
    Route::controller(FollowupController::class)->group(function(){
        Route::get('followups', 'index')->name('followups'); 
        Route::get('followups/list', 'list')->name('followups.list'); 
        Route::post('followups/completed', 'completeFollowup')->name('followups.completed'); 
        Route::get('followups/all', 'showAllFollowups')->name('followups.all'); 
        Route::get('followups/list-all', 'listAll')->name('followups.list.all'); 

        Route::post('followups/get-comment-list', 'getCommentList')->name('followups.comment.list'); 
        Route::post('followups/store-comment', 'storeComment')->name('followups.comment.store'); 
    });

    Route::controller(FlagManagementController::class)->group(function(){
        Route::get('raised-flags', 'index')->name('raised.flags'); 
        Route::get('raised-flags/list', 'list')->name('raised.flags.list'); 
    });
    
    /*Route::controller(ConsoleController::class)->group(function(){
        Route::get('console', 'index')->name('console'); 
    });*/

    Route::controller(AttendancePercentageController::class)->group(function(){
        Route::get('attendance-percentage/{tutor_id}/{term_id}', 'index')->name('attendance.percentage'); 
        Route::get('attendance-percentage/list', 'list')->name('attendance.percentage.list'); 
    });

    Route::controller(SubmissionPassRateReportController::class)->group(function(){
        Route::post('reports/intake-performance/get-submission-pass-rate-report', 'getSubmissionPassRatReport')->name('reports.intake.performance.get.submission.pass.rate.report'); 
        Route::get('reports/intake-performance/print-submission-pass-rate-report/{semesters?}', 'printSubmissionPassRatReport')->name('reports.intake.performance.print.submission.pass.rate.report'); 
        //Route::get('reports/intake-performance/export-retention-rate/{semesters?}', 'exportRetentionRateReport')->name('reports.intake.performance.export.retention.rate'); 
    });

    Route::controller(AwardRateReportController::class)->group(function(){
        Route::post('reports/intake-performance/get-award-rate-report', 'getAwardRatReport')->name('reports.intake.performance.get.award.rate.report'); 
        Route::get('reports/intake-performance/print-award-rate-report/{semesters?}', 'printAwardRatReport')->name('reports.intake.performance.print.award.rate.report'); 
        //Route::get('reports/intake-performance/export-retention-rate/{semesters?}', 'exportRetentionRateReport')->name('reports.intake.performance.export.retention.rate'); 
    });
    
});