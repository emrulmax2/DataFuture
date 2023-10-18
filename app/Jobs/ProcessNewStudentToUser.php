<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Applicant;
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\Role;
use App\Models\StudentUser;
use App\Models\UserRole;

class ProcessNewStudentToUser implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $applicant;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( 
        Applicant $applicant
    ){
        $this->applicant = $applicant;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ApplicantUser = ApplicantUser::find($this->applicant->applicant_user_id);
        $user = StudentUser::where(["email"=>$ApplicantUser->email])->get()->first();
        if(!$user) {
            $user = StudentUser::create([ 
                'email' => $ApplicantUser->email,
                'name' => $this->applicant->full_name,
                'password' =>$ApplicantUser->password,
                'photo' =>$this->applicant->photo,
                'gender' => (isset($this->applicant->sexid->name) ? $this->applicant->sexid->name : ''),
                'active' =>1,
            ]);
        }
    }
}
