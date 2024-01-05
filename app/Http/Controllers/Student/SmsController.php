<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendSmsRequest;
use App\Models\Option;
use App\Models\SmsTemplate;
use App\Models\StudentContact;
use App\Models\StudentSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmsController extends Controller
{
    public function store(SendSmsRequest $request){
        $student_id = $request->student_id;
        $smsTemplateID = (isset($request->sms_template_id) && $request->sms_template_id > 0 ? $request->sms_template_id : NULL);
        $studentSms = StudentSms::create([
            'student_id' => $student_id,
            'sms_template_id' => $smsTemplateID,
            'subject' => $request->subject,
            'sms' => $request->sms,
            'created_by' => auth()->user()->id,
        ]);
        
        if($studentSms):
            $studentContact = StudentContact::where('student_id', $student_id)->get()->first();
            if(isset($studentContact->mobile) && !empty($studentContact->mobile)):
                $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
                $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
                $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
                if($active_api == 1 && !empty($textlocal_api)):
                    $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                        'apikey' => $textlocal_api, 
                        'message' => $request->sms, 
                        'sender' => 'London Churchill College', 
                        'numbers' => $studentContact->mobile
                    ]);
                elseif($active_api == 2 && !empty($smseagle_api)):
                    $response = Http::withHeaders([
                            'access-token' => $smseagle_api,
                            'Content-Type' => 'application/json',
                        ])->withoutVerifying()->withOptions([
                            "verify" => false
                        ])->post('https://79.171.153.104/api/v2/messages/sms', [
                            'to' => [$studentContact->mobile],
                            'text' => $request->sms
                        ]);
                endif;
                $message = 'SMS successfully stored and sent to the student.';
            else:
                $message = 'SMS stored into database but not sent due to missing mobile number.';
            endif;
            return response()->json(['message' => $message], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function list(Request $request){
        $student_id = (isset($request->studentId) && !empty($request->studentId) ? $request->studentId : 0);
        $queryStr = (isset($request->queryStrCMS) && $request->queryStrCMS != '' ? $request->queryStrCMS : '');
        $status = (isset($request->statusCMS) && $request->statusCMS > 0 ? $request->statusCMS : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentSms::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('subject','LIKE','%'.$queryStr.'%');
            $query->orWhere('sms','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'template' => isset($list->template->sms_title) && !empty($list->template->sms_title) ? $list->template->sms_title : '',
                    'subject' => $list->subject,
                    'sms' => (strlen(strip_tags($list->sms)) > 40 ? substr(strip_tags($list->sms), 0, 40).'...' : strip_tags($list->sms)),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function destroy(Request $request){
        $applicant = $request->applicant;
        $recordid = $request->recordid;
        StudentSms::find($recordid)->delete();

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    public function restore(Request $request) {
        $applicant = $request->applicant;
        $recordid = $request->recordid;

        StudentSms::where('id', $recordid)->withTrashed()->restore();
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function getSmsTemplate(Request $request){
        $smsTemplateId = $request->smsTemplateId;
        $smsTemplate = SmsTemplate::find($smsTemplateId);

        return response()->json(['row' => $smsTemplate], 200);
    }

    public function show(Request $request){
        $mailId = $request->recordId;
        $sms = StudentSms::find($mailId);
        $heading = 'Mail Subject: <u>'.$sms->subject.'</u>';
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            if(isset($sms->template->sms_title) && !empty($sms->template->sms_title)):
                $html .= '<div class="col-span-3">';
                    $html .= '<div class="text-slate-500 font-medium">Template</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-9">';
                    $html .= '<div>'.(isset($sms->template->sms_title) ? $sms->template->sms_title : 'Unknown').'</div>';
                $html .= '</div>';
            endif;
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued Date</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($sms->created_at) && !empty($sms->created_at) ? date('jS F, Y', strtotime($sms->created_at)) : '').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">Issued By</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div>'.(isset($sms->user->name) ? $sms->user->name : 'Unknown').'</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-3">';
                $html .= '<div class="text-slate-500 font-medium">SMS Text</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-9">';
                $html .= '<div class="mailContent">'.$sms->sms.'</div>';
            $html .= '</div>';
        $html .= '</div>';

        return response()->json(['heading' => $heading, 'html' => $html], 200);
    }
}
