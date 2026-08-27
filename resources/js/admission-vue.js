import { createIcons, icons } from "lucide";
import {createApp} from 'vue'

("use strict");
const app = {
    data() {
        return {
            progress: 'Processing',
            pageTitle: '#',
            progressPercentage: 5,
            // Conversion steps that failed (from student_conversion_logs via
            // the admission.progress.data payload). While non-empty the modal
            // shows the error panel and admission-global.js skips its
            // auto-close/reload.
            failedJobs: [],
            conversionLogUrl: '',
            cancelTicks: 0,
            params: {
                id: null
            }
        }
    },
    methods: {
        checkIfIdPresent() {
            const urlSearchParams = new URLSearchParams(window.location.search);
            const params = urlSearchParams ? Object.fromEntries(urlSearchParams.entries()) : [];

            if(params.id) {
                this.params.id = params.id;
            } else {

            }
        },
        showConversionFailure(failed, conversion) {
            this.failedJobs = failed;
            let applicantId = (failed[0] && failed[0].applicant_id) || (conversion[0] ? conversion[0].applicant_id : null);
            this.conversionLogUrl = applicantId ? route('admission.conversion.log', applicantId) : '';
        },
        getUploadProgrss() {
            let self = this;

            //let statusId = self.getAttribute('data-statusid');
            self.checkIfIdPresent();
            //console.log(statusId);
            //Get progress data
            let statsThis = document.getElementById("statusAgreement");

            let progressResponse = setInterval(() => {
                window.axios.get(route("admission.progress.data"),{
                    params: {
                        id: self.params.id ? self.params.id : "",
                    }
                }).then(function(response){
                    let data = response.data || {};
                    let totalJobs = parseInt(data.total_jobs);
                    let pendingJobs = parseInt(data.pending_jobs);

                    // Conversion log rows for this batch, seeded at dispatch and
                    // updated live by StudentConversionLogSubscriber.
                    let conversion = Array.isArray(data.conversion) ? data.conversion : [];
                    let failed = conversion.filter(function(job){ return job.status === 'failed'; });

                    // No batch row yet / lost session id -> don't spin forever, release the modal.
                    if(isNaN(totalJobs) || totalJobs <= 0){
                        self.progressPercentage = 100;
                        clearInterval(progressResponse);
                        return;
                    }

                    let completedJobs = totalJobs - pendingJobs;

                    if(failed.length){
                        // Keep the percentage below 100 so the auto-reload in
                        // admission-global.js never hides the error panel.
                        self.showConversionFailure(failed, conversion);
                        self.progressPercentage = Math.min(99, Math.max(5, parseInt(completedJobs / totalJobs * 100)));
                        clearInterval(progressResponse);
                        return;
                    }

                    if(data.cancelled_at){
                        // A batch is only cancelled because a job failed; give the
                        // log row a few ticks to catch up so we can name the step,
                        // then fall back to a generic failure notice.
                        self.cancelTicks = self.cancelTicks + 1;
                        if(self.cancelTicks < 5){
                            return;
                        }
                        self.showConversionFailure([{
                            job_name: 'Student conversion',
                            status: 'failed',
                            message: 'A step failed before it could be recorded. Check the conversion log for details.',
                            applicant_id: (conversion[0] ? conversion[0].applicant_id : null)
                        }], conversion);
                        self.progressPercentage = Math.min(99, Math.max(5, parseInt(completedJobs / totalJobs * 100)));
                        clearInterval(progressResponse);
                        return;
                    }

                    // A finished batch with no failures is a completed conversion.
                    if(pendingJobs <= 0 || data.finished_at){
                        self.progressPercentage = 100;
                    } else {
                        self.progressPercentage = parseInt(completedJobs / totalJobs * 100);
                    }

                    if(parseInt(self.progressPercentage) >= 100)
                    {
                        clearInterval(progressResponse);
                    }
                })
            }, 1000);

        },

    },
    created() {

        this.getUploadProgrss();
    },

}
createApp(app).mount("#app");
