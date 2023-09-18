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
            console.log(statsThis);
            let progressResponse = setInterval(() => {
                window.axios.get(route("admission.progress.data"),{
                    params: {
                        id: self.params.id ? self.params.id : "",
                    }
                }).then(function(response){
                    console.log(response.data);

                    let totalJobs = parseInt(response.data.total_jobs);
                    let pendingJobs = parseInt(response.data.pending_jobs);
                    let completedJobs = totalJobs - pendingJobs;

                    if(pendingJobs==0){
                        self.progressPercentage = 100;
                    } else{
                        self.progressPercentage = parseInt(completedJobs/totalJobs * 100).toFixed(0);
                    }

                    if(parseInt(self.progressPercentage) >= 100)
                    {
                        clearInterval(progressResponse);
                    }
                    
                })
            }, 1000);

        }
    },
    created() {
        this.getUploadProgrss();
    },
}
createApp(app).mount("#app");

