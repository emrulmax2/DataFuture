import { createIcons, icons } from "lucide";
import {createApp} from 'vue'

("use strict");
const app = {
    data() {
        return {
            progress: 'Processing',
            pageTitle: '#',
            progressPercentage: 5,
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

                    // No batch row yet / lost session id -> don't spin forever, release the modal.
                    if(isNaN(totalJobs) || totalJobs <= 0){
                        self.progressPercentage = 100;
                        clearInterval(progressResponse);
                        return;
                    }

                    let completedJobs = totalJobs - pendingJobs;

                    // A chained job that fails halts the chain, so pending never reaches 0; treat a
                    // finished or cancelled batch as done as well so the modal always closes.
                    if(pendingJobs <= 0 || data.finished_at || data.cancelled_at){
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

