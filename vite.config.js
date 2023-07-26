import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { viteStaticCopy } from "vite-plugin-static-copy";

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

                "resources/js/title.js",
                "resources/js/ethnicity.js",
                "resources/js/kins-relation.js",
                "resources/js/sexual-rientation.js",
                "resources/js/religion.js",
                "resources/js/status.js",
                "resources/js/country.js",
                "resources/js/application.js",
                "resources/js/disabilities.js",

                "resources/js/applicant-dahsboard.js",
                "resources/js/bankholiday.js",
                "resources/js/permissioncategory.js",
                "resources/js/department.js",
                "resources/js/roles.js",
                "resources/js/permissiontemplate.js",
                "resources/js/processlist.js",  
                "resources/js/tasklist.js",
                "resources/js/user.js",
                "resources/js/interviewlist.js",
                "resources/js/staff-interview-task.js",

                "resources/js/admission.js",
                "resources/js/admission-process.js",
                "resources/js/admission-uploads.js",
                "resources/js/admission-notes.js"
            ],
            refresh: true,
        }),
    ],
});
