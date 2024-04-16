import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { viteStaticCopy } from "vite-plugin-static-copy";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        origin: "http://127.0.0.1",
    },
    plugins: [
        viteStaticCopy({
            targets: [
                {
                    src: "resources/images",
                    dest: "assets",
                },
                {
                    src: "resources/json",
                    dest: "assets",
                },
            ],
        }),
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/ckeditor-classic.js",
                "resources/js/ckeditor-inline.js",
                "resources/js/ckeditor-balloon.js",
                "resources/js/ckeditor-balloon-block.js",
                "resources/js/ckeditor-document.js",

                "resources/js/status.js",
                "resources/js/application.js",

                "resources/js/applicant-dahsboard.js",
                "resources/js/agent-dahsboard.js",
                "resources/js/agent-crud.js",
                "resources/js/agent-profile.js",
                "resources/js/bankholiday.js",
                "resources/js/permissioncategory.js",
                "resources/js/department.js",
                "resources/js/roles.js",
                "resources/js/permissiontemplate.js",
                "resources/js/processlist.js",  
                "resources/js/tasklist.js",
                "resources/js/user.js",

                "resources/js/interview-assigned.js",
                "resources/js/interviewlist.js",
                "resources/js/staff-interview-task.js",
                "resources/js/staff-interviewlist.js",
                "resources/js/staff-interview-start.js",
                

                "resources/js/admission.js",
                "resources/js/admission-vue.js",
                "resources/js/admission-process.js",
                "resources/js/admission-uploads.js",
                "resources/js/admission-notes.js",

                "resources/js/students.js",
                "resources/js/student-global.js",
                "resources/js/student-profile.js",
                "resources/js/student-proof-id-check.js",
                "resources/js/student-edication-qualification.js",
                "resources/js/student-employment-history.js",
                "resources/js/student-consent.js",
                "resources/js/student-letter.js",
                "resources/js/student-email.js",
                "resources/js/student-sms.js",
                "resources/js/student-note.js",
                "resources/js/student-upload.js",
                "resources/js/student-process.js",
                "resources/js/student-course.js",
                "resources/js/student-results.js",

                "resources/js/settings.js",
                "resources/js/awardingbody.js",
                "resources/js/academicyears.js",
                "resources/js/sourcetuitionfee.js",
                "resources/js/coursequalification.js",
                "resources/js/consent-policy.js",
                "resources/js/venues.js",
                "resources/js/rooms.js",
                "resources/js/documentsettings.js",
                "resources/js/termtype.js",
                "resources/js/internallink.js",
                
                
                "resources/js/student-option.js",
                "resources/js/title.js",
                "resources/js/ethnicity.js",
                "resources/js/kins-relation.js",
                "resources/js/sexual-rientation.js",
                "resources/js/religion.js",
                "resources/js/hesagender.js",
                "resources/js/country.js",
                "resources/js/disabilities.js",
                "resources/js/feeeligibilities.js",
                "resources/js/apelcredit.js",
                "resources/js/highest-qualification-on-entry.js",
                "resources/js/country-fo-permanent-address.js",
                "resources/js/previous-provider.js",
                "resources/js/reason-for-engagement-ending.js",  
                "resources/js/termtimeaccommodationtype.js",
                "resources/js/sexidentifier.js",
                "resources/js/student-awarding-body.js",
                "resources/js/qualification-type-identifier.js",
                "resources/js/sms-template.js",
                "resources/js/email-template.js",
                "resources/js/common-smtp.js",
                "resources/js/letter-set.js",
                "resources/js/signatory.js",
                "resources/js/letterfooter.js",
                "resources/js/letterheader.js",
                "resources/js/e-learning-activity-settings.js",

                "resources/js/plan.js",
                "resources/js/plan-tree.js",
                "resources/js/employee-new.js",
                "resources/js/student-frontend.js",


                "resources/js/student-frontend-dashboard.js",
                "resources/js/student-frontend-profile.js",
                "resources/js/student-frontend-consent.js",

                "resources/js/employee-payment-setting.js",
                "resources/js/employee-penssion-scheem.js",
                "resources/js/employee-working-pattern.js",

                "resources/js/holiday-years.js",
                "resources/js/bank-holiday.js",
                "resources/js/leave-options.js",
                "resources/js/employee-holiday.js",
                "resources/js/employee-upload.js",      
                "resources/js/employee-note.js",
                "resources/js/employee-profile.js",

                "resources/js/attendance.js",
                "resources/js/attendance-studentstaff.js",

                "resources/js/user-holiday.js",
                "resources/js/machine-live.js",
                "resources/js/hr-attedance.js",
                "resources/js/hr-conditions.js",
                "resources/js/hr-deaily-attedance.js",

                "resources/js/employee.js",
                "resources/js/hr-portal.js",
                "resources/js/hr-holiday-manager.js",
                "resources/js/leave-calendar.js",

                "resources/js/employee-appraisal.js",
                "resources/js/employee-appraisal-documents.js",
                "resources/js/hr-upcoming-appraisal.js",
                "resources/js/hr-visa-expiry.js",
                "resources/js/hr-passport-expiry.js",
                
                "resources/js/tutor-dashboard.js",
                "resources/js/tutor-personal.js",
                "resources/js/tutor-dashboard-new.js",
                "resources/js/attendance-feed.js",
                "resources/js/tutor-attendance-feed.js",
                "resources/js/plan-tasks.js",
                "resources/js/plan-tasks-students.js",
                "resources/js/create-module-activity.js",
                "resources/js/employee-global.js",
                "resources/js/employee-banks.js",
                
                "resources/js/hr-absent-today.js",
                "resources/js/hr-live-attendance.js",

                "resources/js/student-slc-registration.js",
                "resources/js/student-slc-attedance.js",
                "resources/js/student-slc-agreement.js",
                "resources/js/student-slc-installment.js",
                "resources/js/student-slc-installment.js",
                "resources/js/student-slc-installment.js",

                "resources/js/plan-add-search.js",

                "resources/js/programme-dashboard.js",
                "resources/js/employee-privilege.js",

                'resources/js/hr-portal-employmentreport.js',
                'resources/js/hr-portal-birthdayreport.js',
                'resources/js/hr-portal-diversityreport.js',
                'resources/js/hr-portal-starterreport.js',
                'resources/js/hr-portal-eligibilityreport.js',
                'resources/js/hr-portal-contactdetail.js',
                'resources/js/hr-portal-lengthservice.js',
                'resources/js/hr-portal-telephonedirectory.js',
                'resources/js/hr-portal-recordcard.js',
                'resources/js/hr-portal-datareport.js',
                'resources/js/df-field-category.js',
                'resources/js/df-field.js',
                'resources/js/venue-datafuture.js',
                'resources/js/employee-time-keeping.js',
                'resources/js/course-management.js',
                'resources/js/semester.js',
                'resources/js/term-declaration.js',
                'resources/js/courses.js',
                'resources/js/course-module.js',
                'resources/js/course-datafuture.js',
                'resources/js/course-module-assesment.js',
                'resources/js/module-datafuture.js',
                'resources/js/course-creation.js',
                'resources/js/course-creation-availability.js',
                'resources/js/course-creation-datafuture.js',
                'resources/js/course-creation-instance.js',
                'resources/js/term-module-creation.js',
                'resources/js/modulelevels.js',
                'resources/js/groups.js',
                'resources/js/plans-date-list.js',
                'resources/js/task-manager.js',
                'resources/js/admission-communication.js',
                'resources/js/unsigned.js',
                'resources/js/assign.js',
                'resources/js/student-slc-coc.js',
                'resources/js/student-slc-payment.js',
                'resources/js/wp-company.js',
                'resources/js/student-workplacement.js',
                'resources/js/student-wbl-profile.js',
                'resources/js/staff-dashboard.js',
                'resources/js/jquery-stopwatch.js',
                'resources/js/employee-archive.js',
                'resources/js/attendance-live.js',
                'resources/js/attendance-report.js',
                'resources/js/hr-add-live-attendance.js',
                'resources/js/attendance-report-details.js',
                
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, when referenced
                    // in Single File Components, to point to the Laravel web
                    // server. Setting this to `null` allows the Laravel plugin
                    // to instead re-write asset URLs to point to the Vite
                    // server instead.
                    base: null,
 
                    // The Vue plugin will parse absolute URLs and treat them
                    // as absolute paths to files on disk. Setting this to
                    // `false` will leave absolute URLs un-touched so they can
                    // reference assets in the public directory as expected.
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: { 
            vue: 'vue/dist/vue.esm-bundler.js'
     }
    }
});
