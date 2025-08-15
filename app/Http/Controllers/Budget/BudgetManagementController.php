<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequisitionStoreRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AccTransaction;
use App\Models\BudgetName;
use App\Models\BudgetNameApprover;
use App\Models\BudgetNameHolder;
use App\Models\BudgetNameRequester;
use App\Models\BudgetRequisition;
use App\Models\BudgetRequisitionDocument;
use App\Models\BudgetRequisitionHistory;
use App\Models\BudgetRequisitionItem;
use App\Models\BudgetRequisitionTransaction;
use App\Models\BudgetSet;
use App\Models\BudgetSetDetail;
use App\Models\BudgetYear;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class BudgetManagementController extends Controller
{
    public function index(){
        $holders = BudgetNameHolder::pluck('user_id')->unique()->toArray();
        $requester = BudgetNameRequester::pluck('user_id')->unique()->toArray();
        $approver = BudgetNameApprover::pluck('user_id')->unique()->toArray();
        $allApprover = array_merge($requester, $holders, $approver);
        $allApprover = (!empty($allApprover) ? array_unique($allApprover) : [0]);
        return view('pages.budget.index', [
            'title' => 'Budget Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Budget Management', 'href' => 'javascript:void(0);']
            ],
            'years' => BudgetYear::whereHas('budget')->orderBy('start_date', 'DESC')->get(),
            'names' => BudgetName::orderBy('name', 'ASC')->get(),
            'vendors' => Vendor::orderBy('name', 'ASC')->get(),
            'budgets' => BudgetSet::with('details')->whereHas('year', function($q){
                $q->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->where('active', 1);
            })->get()->first(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'approvers' => User::whereIn('id', $allApprover)->where('active', 1)->orderBy('name', 'ASC')->get(),
            'venues' => Venue::orderBy('name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $user_id = auth()->user()->id;
        $assigned_budget_name_ids = $this->getAssignedBudgetNameIds($user_id);
        $date_range = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        $start_date = (isset($date_range[0]) && !empty($date_range[0]) ? date('Y-m-d', strtotime($date_range[0])) : '');
        $end_date = (isset($date_range[1]) && !empty($date_range[1]) ? date('Y-m-d', strtotime($date_range[1])) : '');
        $budget_year_ids = (isset($request->budget_year_ids) && $request->budget_year_ids > 0 ? $request->budget_year_ids : 0);
        $budget_name_ids = (isset($request->budget_name_ids) && $request->budget_name_ids > 0 ? $request->budget_name_ids : 0);
        $active = (isset($request->req_active) ? $request->req_active : 6);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = BudgetRequisition::with('year', 'budget', 'requisitioners', 'vendor')->orderByRaw(implode(',', $sorts))->where(function($q) use($user_id, $budget_name_ids, $assigned_budget_name_ids){
            $q->where('first_approver', $user_id)->orWhere('final_approver', $user_id)->orWhere('created_by', $user_id);
            if($budget_name_ids > 0 || !empty($assigned_budget_name_ids)):
                $q->orWhereHas('budget', function($sq) use($budget_name_ids, $assigned_budget_name_ids){
                    if($budget_name_ids > 0 && !empty($assigned_budget_name_ids)):
                        $sq->where('budget_name_id', $budget_name_ids)->orWhereIn('budget_name_id', $assigned_budget_name_ids);
                    elseif($budget_name_ids == 0 && !empty($assigned_budget_name_ids)):
                        $sq->whereIn('budget_name_id', $assigned_budget_name_ids);
                    elseif($budget_name_ids > 0 && empty($assigned_budget_name_ids)):
                        $sq->where('budget_name_id', $budget_name_ids);
                    endif;
                });
            endif;
        });
        if($budget_year_ids > 0):
            $query->where('budget_year_id', $budget_year_ids);
        endif;
        if(!empty($start_date) && !empty($end_date)):
            $query->where(function($q) use($start_date, $end_date){
                $q->whereBetween('date', [$start_date, $end_date])->orWhereBetween('required_by', [$start_date, $end_date]);
            });
        endif;
        if($active == 5):
            $query->onlyTrashed();
        elseif($active < 5):
            $query->where('active', $active);
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $canEdit = 0;
                if($list->active > 1 && ($list->first_approver == auth()->user()->id || $list->final_approver == auth()->user()->id) && (isset(auth()->user()->priv()['budget_edit']) && auth()->user()->priv()['budget_edit'] == 1 )):
                    $canEdit = 1;
                elseif($list->active < 2 && (isset(auth()->user()->priv()['budget_edit']) && auth()->user()->priv()['budget_edit'] == 1 )):
                    $canEdit = 1;
                endif;

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'reference_no' => $list->reference_no,
                    'date' => (!empty($list->date) ? date('jS M, Y', strtotime($list->date)) : ''),
                    'required_by' => (!empty($list->required_by) ? date('jS M, Y', strtotime($list->required_by)) : ''),
                    'year' => (isset($list->year->title) && !empty($list->year->title) ? $list->year->title : ''),
                    'budget' => (isset($list->budget->names->name) && !empty($list->budget->names->name) ? $list->budget->names->name.(isset($list->budget->names->code) && !empty($list->budget->names->code) ? ' ('.$list->budget->names->code.')' : '') : ''),
                    'total' => (isset($list->items) && $list->items->count() > 0 ? '£'.number_format($list->items->sum('total'), 2) : '£0.00'),
                    'requisitioners' => (isset($list->requisitioners->employee->full_name) && !empty($list->requisitioners->employee->full_name) ? $list->requisitioners->employee->full_name : $list->requisitioners->name),
                    'vendor' => (isset($list->vendor->name) && !empty($list->vendor->name) ? $list->vendor->name : ''),
                    'venue' => (isset($list->venue->name) && !empty($list->venue->name) ? $list->venue->name : ''),
                    'active' => $list->active,
                    'deleted_at' => $list->deleted_at,
                    'url' => route('budget.management.show.req', $list->id),
                    'can_edit' => $canEdit,
                    'can_delete' => (isset(auth()->user()->priv()['budget_delete']) && auth()->user()->priv()['budget_delete'] == 1 ? 1 : 0),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, $assigned_budget_name_ids]);
    }

    public function storeRequisition(RequisitionStoreRequest $request){
        $budget_year_id = $request->budget_year_id;
        $budgetYear = BudgetYear::find($budget_year_id);
        $budget_set_id = $request->budget_set_id;
        $items = (isset($request->items) && !empty($request->items) ? $request->items : []);
        $requisitioner = User::find(auth()->user()->id);
        $requisitionerEmail = $requisitioner->email;
        $requisitionerName = (isset($requisitioner->employee->full_name) && !empty($requisitioner->employee->full_name) ? $requisitioner->employee->full_name : $requisitioner->name);
        
        $first_approver = $request->first_approver > 0 ? $request->first_approver : 0;
        $final_approver = $request->final_approver > 0 ? $request->final_approver : 0;
        $budgetSetDetail = BudgetSetDetail::find($request->budget_set_detail_id);
        $venue = ($request->venue_id > 0 ? Venue::find($request->venue_id) : []);

        $requisition = BudgetRequisition::create([
            'budget_year_id' => $budget_year_id,
            'budget_set_id' => $budget_set_id,
            'vendor_id' => (isset($request->vendor_id) && !empty($request->vendor_id) ? $request->vendor_id : null),
            'date' => date('Y-m-d'),
            'requisitioner' => auth()->user()->id,
            'budget_set_detail_id' => $request->budget_set_detail_id,
            'required_by' => (isset($request->required_by) && !empty($request->required_by) ? date('Y-m-d', strtotime($request->required_by)) : null),
            'venue_id' => isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : null,
            'first_approver' => $request->first_approver > 0 ? $request->first_approver : null,
            'final_approver' => $request->final_approver > 0 ? $request->final_approver : null,
            'note' => (!empty($request->note) ? $request->note : null),
            'active' => 1,
            'created_by' => auth()->user()->id,
        ]);
        if($requisition->id):
            $itemHtml = '';
            if(!empty($items)):
                $description = (isset($items['description']) && !empty($items['description']) ? $items['description'] : []);
                $quantity = (isset($items['quantity']) && !empty($items['quantity']) ? $items['quantity'] : []);
                $price = (isset($items['price']) && !empty($items['price']) ? $items['price'] : []);
                $total = (isset($items['total']) && !empty($items['total']) ? $items['total'] : []);
                if(!empty($description)):
                    foreach($description as $key => $desc):
                        BudgetRequisitionItem::create([
                            'budget_requisition_id' => $requisition->id,
                            'description' => $desc,
                            'quantity' => (isset($quantity[$key]) && !empty($quantity[$key]) ? $quantity[$key] : null),
                            'price' => (isset($price[$key]) && !empty($price[$key]) ? $price[$key] : null),
                            'total' => (isset($total[$key]) && !empty($total[$key]) ? $total[$key] : null),
                            'active' => 1,
                            'created_by' => auth()->user()->id,
                        ]);
                        $itemHtml .= '<tr>';
                            $itemHtml .= '<td>'.$desc.'</td>';
                            $itemHtml .= '<td>'.(isset($price[$key]) && !empty($price[$key]) ? $price[$key] : null).'</td>';
                            $itemHtml .= '<td>'.(isset($quantity[$key]) && !empty($quantity[$key]) ? $quantity[$key] : null).'</td>';
                            $itemHtml .= '<td>'.(isset($total[$key]) && !empty($total[$key]) ? $total[$key] : null).'</td>';
                        $itemHtml .= '</tr>';
                    endforeach;
                endif;
            endif;

            if($request->hasFile('document')):
                foreach($request->file('document') as $file):
                    $documentName = 'REQ_'.$requisition->id.'_'.time().'.'.$file->extension();
                    $path = $file->storeAs('public/requisitions/'.$requisition->id, $documentName, 'local');
    
                    $data = [];
                    $data['budget_requisition_id'] = $requisition->id;
                    $data['display_file_name'] = $documentName;
                    $data['hard_copy_check'] = 1;
                    $data['doc_type'] = $file->getClientOriginalExtension();
                    $data['disk_type'] = 'local';
                    $data['current_file_name'] = $documentName;
                    $data['created_by'] = auth()->user()->id;
                    BudgetRequisitionDocument::create($data);
                endforeach;
            endif;

            if($first_approver > 0):
                $approver = User::find($first_approver);
                $approverName = (isset($approver->employee->full_name) && !empty($approver->employee->full_name) ? $approver->employee->full_name : $approver->name);
                $to = [$approver->email];
                

                $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
                $configuration = [
                    'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                    'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                    'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                    'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                    'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                    
                    'from_email'    => $commonSmtp->smtp_user,
                    'from_name'    =>  'Accounts Team',
                ];
                
                $subject = 'New Budget Requisition Needs Approval';
                $MAILBODY = 'Dear '.$approverName.', <br/><br/>';
                $MAILBODY .= '<p>A new requisition has been submitted by '.$requisitionerName.', and your review is required. Please find the details below.</p>';
                $MAILBODY .= '<table style="border-collapse: collapse; border-spacing: 0; width: 100%; margin: 0 0 15px">';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Budget Year</td>';
                        $MAILBODY .= '<td>'.$budgetYear->title.'</td>';
                        $MAILBODY .= '<td>Budget</td>';
                        $MAILBODY .= '<td>'.(isset($budgetSetDetail->names->name) && !empty($budgetSetDetail->names->name) ? $budgetSetDetail->names->name : '').'</td>';
                    $MAILBODY .= '</tr>';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Requisitioner</td>';
                        $MAILBODY .= '<td>'.$requisitionerName.'</td>';
                        $MAILBODY .= '<td>Date</td>';
                        $MAILBODY .= '<td>'.date('jS F, Y').'</td>';
                    $MAILBODY .= '</tr>';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Required By</td>';
                        $MAILBODY .= '<td>'.(isset($request->required_by) && !empty($request->required_by) ? date('Y-m-d', strtotime($request->required_by)) : null).'</td>';
                        $MAILBODY .= '<td>Delivery Location</td>';
                        $MAILBODY .= '<td>'.(isset($venue->name) && !empty($venue->name) ? $venue->name : '').'</td>';
                    $MAILBODY .= '</tr>';
                $MAILBODY .= '</table>';
                $MAILBODY .= '<p><strong>Item Informations</strong></p>';
                if(!empty($itemHtml)):
                    $MAILBODY .= '<table style="border-collapse: collapse; border-spacing: 0; width: 100%; margin: 0 0 15px">';
                        $MAILBODY .= '<thead>';
                            $MAILBODY .= '<tr>';
                                $MAILBODY .= '<th>Description</th>';
                                $MAILBODY .= '<th>Unit Price</th>';
                                $MAILBODY .= '<th>Quantity</th>';
                                $MAILBODY .= '<th>Total</th>';
                            $MAILBODY .= '</tr>';
                        $MAILBODY .= '</thead>';
                        $MAILBODY .= '<tbody>';
                            $MAILBODY .= $itemHtml;
                        $MAILBODY .= '</tbody>';
                    $MAILBODY .= '</table>';
                endif;

                $MAILBODY .= '<p>Please click <a href="'.route('budget.management.show.req', $requisition->id).'">here</a> and take a action.</p>';
                $MAILBODY .= '<p>If the "Click here" button isn\'t working, please copy the following link and paste it into your web browser.<br/>'.route('budget.management.show.req', $requisition->id).'</p>';
                $MAILBODY .= '<br/>Regards<br/>';
                $MAILBODY .= 'London Churchill College';

                //$tmpTo[] = 'limon@churchill.ac';
                UserMailerJob::dispatch($configuration, $to, new CommunicationSendMail($subject, $MAILBODY, []));
            endif;
            return response()->json(['msg' => 'Budget requisition successfully inserted.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try later or contact with the administrator.'], 422);
        endif;
    }

    public function editRequisition(Request $request){
        $row_id = $request->row_id;
        $requisition = BudgetRequisition::with('items')->find($row_id);
        $budgetSet = BudgetSet::with('details')->where('budget_year_id', $requisition->budget_year_id)->get()->first();
        
        $budgets = [];
        if(isset($budgetSet->details) && $budgetSet->details->count() > 0):
            $i = 1;
            foreach($budgetSet->details as $det):
                $budgets[$i]['id'] = $det->id;
                $budgets[$i]['name'] = (isset($det->names->name) && !empty($det->names->name) ? $det->names->name : 'Undefined').(isset($det->names->code) && !empty($det->names->code) ? ' ('.$det->names->code.')' : ''); 
                
                $i++;
            endforeach;
        endif;

        return response()->json(['row' => $requisition, 'budget_names' => $budgets], 200);
    }

    public function updateRequisition(RequisitionStoreRequest $request){
        $requisition_id = $request->id;
        $items = (isset($request->items) && !empty($request->items) ? $request->items : []);

        $requisition = BudgetRequisition::where('id', $requisition_id)->update([
            'vendor_id' => (isset($request->vendor_id) && !empty($request->vendor_id) ? $request->vendor_id : null),
            'budget_set_detail_id' => $request->budget_set_detail_id,
            'required_by' => (isset($request->required_by) && !empty($request->required_by) ? date('Y-m-d', strtotime($request->required_by)) : null),
            'venue_id' => isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : null,
            'first_approver' => $request->first_approver > 0 ? $request->first_approver : null,
            'final_approver' => $request->final_approver > 0 ? $request->final_approver : null,
            'note' => (!empty($request->note) ? $request->note : null),
            
            'updated_by' => auth()->user()->id,
        ]);

        
        if(!empty($items)):
            $exist_item_ids = BudgetRequisitionItem::where('budget_requisition_id', $requisition_id)->pluck('id')->unique()->toArray();
            $item_ids = [];
            foreach($items as $sl => $item):
                $data = [];
                $data = [
                    'budget_requisition_id' => $requisition_id,
                    'description' => (isset($item['description']) && !empty($item['description']) ? $item['description'] : null),
                    'quantity' => (isset($item['quantity']) && !empty($item['quantity']) ? $item['quantity'] : null),
                    'price' => (isset($item['price']) && !empty($item['price']) ? $item['price'] : null),
                    'total' => (isset($item['total']) && !empty($item['total']) ? $item['total'] : null)
                ];
                if(isset($item['id']) && $item['id'] > 0):
                    $data['updated_by'] = auth()->user()->id;

                    BudgetRequisitionItem::where('id', $item['id'])->where('budget_requisition_id', $requisition_id)->update($data);
                    $item_ids[] = $item['id'];
                else:
                    $data['active'] = 1;
                    $data['created_by'] = auth()->user()->id;
                    BudgetRequisitionItem::create($data);
                endif;
            endforeach;
            $should_delete = array_diff($exist_item_ids, $item_ids);
            if(!empty($should_delete)):
                BudgetRequisitionItem::where('budget_requisition_id', $requisition_id)->whereIn('id', $should_delete)->forceDelete();
            endif;
        else:
            BudgetRequisitionItem::where('budget_requisition_id', $requisition_id)->forceDelete();
        endif;

        if($request->hasFile('document')):
            foreach($request->file('document') as $file):
                $documentName = 'REQ_'.$requisition_id.'_'.time().'.'.$file->extension();
                $path = $file->storeAs('public/requisitions/'.$requisition_id, $documentName, 'local');

                $data = [];
                $data['budget_requisition_id'] = $requisition_id;
                $data['display_file_name'] = $documentName;
                $data['hard_copy_check'] = 1;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['disk_type'] = 'local';
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                BudgetRequisitionDocument::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Budget requisition successfully updated.'], 200);
    }

    public function showRequisition(BudgetRequisition $requisition){
        $requisition->load('items', 'year');
        return view('pages.budget.requisition.show', [
            'title' => 'Budget Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Budget Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Requisition', 'href' => 'javascript:void(0);'],
            ],
            'years' => BudgetYear::orderBy('start_date', 'DESC')->get(),
            'requisition' => $requisition,
        ]);
    }

    public function updateRequisitionStatus(Request $request){
        $requisition_id = $request->record_id;
        $active = $request->status;
        $approver = $request->approver;
        $note = (isset($request->note) && !empty($request->note) ? $request->note : null);

        if($requisition_id > 0):
            $requisition = BudgetRequisition::find($requisition_id);

            BudgetRequisition::where('id', $requisition_id)->update([
                'active' => $active,
                'updated_by' => auth()->user()->id
            ]);

            BudgetRequisitionHistory::create([
                'budget_requisition_id' => $requisition_id,
                'approver' => $approver,
                'status' => $active,
                'note' => $note,

                'created_by' => auth()->user()->id,
            ]);

            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => $commonSmtp->smtp_user,
                'from_name'    =>  'Accounts Team',
            ];

            if($active == 2 && isset($requisition->final_approver) && $requisition->final_approver > 0):
                $approver = User::find($requisition->final_approver);
                $approverName = (isset($approver->employee->full_name) && !empty($approver->employee->full_name) ? $approver->employee->full_name : $approver->name);
                $to = [$approver->email];

                $subject = 'New Budget Requisition Needs Approval';
                $MAILBODY = 'Dear '.$approverName.', <br/><br/>';
                $MAILBODY .= '<p>A new requisition has been submitted by '.(isset($requisition->requisitioners->employee->full_name) && !empty($requisition->requisitioners->employee->full_name) ? $requisition->requisitioners->employee->full_name : $requisition->requisitioners->name).', and your review is required. Please find the details below.</p>';
                $MAILBODY .= '<table style="border-collapse: collapse; border-spacing: 0; width: 100%; margin: 0 0 15px">';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Budget Year</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->year->title) && !empty($requisition->year->title) ? $requisition->year->title : '').'</td>';
                        $MAILBODY .= '<td>Budget</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->budget->names->name) && !empty($requisition->budget->names->name) ? $requisition->budget->names->name : '').'</td>';
                    $MAILBODY .= '</tr>';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Requisitioner</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->requisitioners->employee->full_name) && !empty($requisition->requisitioners->employee->full_name) ? $requisition->requisitioners->employee->full_name : $requisition->requisitioners->name).'</td>';
                        $MAILBODY .= '<td>Date</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->date) && !empty($requisition->date) ? date('jS F, Y', strtotime($requisition->date)) : '').'</td>';
                    $MAILBODY .= '</tr>';
                    $MAILBODY .= '<tr>';
                        $MAILBODY .= '<td>Required By</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->required_by) && !empty($requisition->required_by) ? date('Y-m-d', strtotime($requisition->required_by)) : null).'</td>';
                        $MAILBODY .= '<td>Delivery Location</td>';
                        $MAILBODY .= '<td>'.(isset($requisition->venue->name) && !empty($requisition->venue->name) ? $requisition->venue->name : '').'</td>';
                    $MAILBODY .= '</tr>';
                $MAILBODY .= '</table>';
                $MAILBODY .= '<p><strong>Item Informations</strong></p>';
                if(isset($requisition->items) && $requisition->items->count() > 0):
                    $MAILBODY .= '<table style="border-collapse: collapse; border-spacing: 0; width: 100%; margin: 0 0 15px">';
                        $MAILBODY .= '<thead>';
                            $MAILBODY .= '<tr>';
                                $MAILBODY .= '<th>Description</th>';
                                $MAILBODY .= '<th>Unit Price</th>';
                                $MAILBODY .= '<th>Quantity</th>';
                                $MAILBODY .= '<th>Total</th>';
                            $MAILBODY .= '</tr>';
                        $MAILBODY .= '</thead>';
                        $MAILBODY .= '<tbody>';
                            foreach($requisition->items as $item):
                                $MAILBODY .= '<tr>';
                                    $MAILBODY .= '<td>'.$item->description.'</td>';
                                    $MAILBODY .= '<td>'.(isset($item->price) && !empty($item->price) ? Number::currency($item->price, 'GBP') : '').'</td>';
                                    $MAILBODY .= '<td>'.(isset($item->quantity) && !empty($item->quantity) ? $item->quantity : '').'</td>';
                                    $MAILBODY .= '<td>'.(isset($item->total) && !empty($item->total) ? Number::currency($item->total, 'GBP') : '').'</td>';
                                $MAILBODY .= '</tr>';
                            endforeach;
                        $MAILBODY .= '</tbody>';
                    $MAILBODY .= '</table>';
                endif;

                $MAILBODY .= '<p>Please <a href="'.route('budget.management.show.req', $requisition->id).'">click here</a> to take action.</p>';
                $MAILBODY .= '<p>If the "click here" button does not work, copy and paste the following link into your web browser.<br/>'.route('budget.management.show.req', $requisition->id).'</p>';

                $MAILBODY .= '<br/>Regards<br/>';
                $MAILBODY .= 'London Churchill College';

                
                //$tmpTo[] = 'limon@churchill.ac';
                UserMailerJob::dispatch($configuration, $to, new CommunicationSendMail($subject, $MAILBODY, []));
            endif;
            if($active == 3):
                $subject = 'Requisition Approved - Action Needed';
                $to = ['accounts@lcc.ac.uk'];

                $MAILBODY = 'Hi, <br/><br/>';
                $MAILBODY .= '<p>Your requisition has been approved. Please complete the actions below to proceed.</p>';
                $MAILBODY .= '<p>Please <a href="'.route('budget.management.show.req', $requisition->id).'">click here</a> to view.</p>';
                $MAILBODY .= '<p>If the "click here" button does not work, copy and paste the following link into your web browser.<br/>'.route('budget.management.show.req', $requisition->id).'</p>';
                $MAILBODY .= '<p>Let me know if you have any questions. Kindly complete these steps by '.(isset($requisition->required_by) && !empty($requisition->required_by) ? date('Y-m-d', strtotime($requisition->required_by)) : null).'.</p>';

                $MAILBODY .= 'Best regards,<br/>';  
                $MAILBODY .= 'London Churchill College<br/>';

                UserMailerJob::dispatch($configuration, $to, new CommunicationSendMail($subject, $MAILBODY, []));
            endif;

            return response()->json(['msg' => 'Status successfully updated.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 422);
        endif;
    }

    public function getFilteredTransactions(Request $request){
        $SearchVal = $request->SearchVal;

        $html = '';
        $Query = AccTransaction::orderBy('transaction_code', 'ASC')->where('parent', '0')->where('transaction_code', 'LIKE', '%'.$SearchVal.'%')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="javascript:void(0);" data-id="'.$qr->id.'" data-transactioncode="'.$qr->transaction_code.'" class="dropdown-item">'.$qr->transaction_code.' - £'.number_format($qr->transaction_amount, 2).'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" class="dropdown-item disable">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getTransaction(Request $request){
        $transaction_id = $request->transaction_id;
        $transaction_code = $request->transaction_code;

        $html = '';
        $amount = 0;
        $trans = AccTransaction::where('id', $transaction_id)->where('transaction_code', $transaction_code)->get()->first();
        
        if(isset($trans->id) && $trans->id > 0):
            $amount = (isset($trans->transaction_amount) && $trans->transaction_amount > 0 ? $trans->transaction_amount : 0);
            $html .= '<tr class="transaction_row" id="transaction_row_'.$trans->id.'">';
                $html .= '<td>';
                    $html .= '<span class="font-medium text-success">'.$trans->transaction_code.'</span><br/>';
                    $html .= '<span class="text-slate-500 text-xs">'.date('jS M, Y', strtotime($trans->transaction_date_2)).'</span>';
                    $html .= '<input type="hidden" name="trans[]" value="'.$trans->id.'"/>';
                $html .= '</td>';
                $html .= '<td>';
                    $html .= '<div class="whitespace-normal">';
                        $html .= $trans->detail;
                    $html .='</div>';
                $html .= '</td>';
                $html .= '<td>'.(isset($trans->category->category_name) && !empty($trans->category->category_name) ? $trans->category->category_name : '').'</td>';
                $html .= '<td>'.(isset($trans->bank->bank_name) && !empty($trans->bank->bank_name) ? $trans->bank->bank_name : '').'</td>';
                $html .= '<td class="relative">';
                    $html .= '£'.number_format($trans->transaction_amount, 2);
                    $html .= '<button type="button" class="remove_trans_row btn btn-danger w-[25px] h-[25px] btn-sm text-white rounded-full absolute t-0 r-0 b-0 m-auto p-0" style="margin-right: -13px;"><i data-lucide="trash-2" class="w-3 h-3"></i></button>';
                    $html .= '<input type="hidden" name="row_amount" class="theAmount" value="'.$amount.'" />';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function markAsCompleted(Request $request){
        $budget_requisition_id = $request->budget_requisition_id;
        $transactions = (isset($request->trans) && !empty($request->trans) ? $request->trans : []);

        if(!empty($transactions)):
            foreach($transactions as $transaction_id):
                BudgetRequisitionTransaction::create([
                    'budget_requisition_id' => $budget_requisition_id,
                    'acc_transaction_id' => $transaction_id,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;

            BudgetRequisition::where('id', $budget_requisition_id)->update(['active' => 4, 'updated_by' => auth()->user()->id]);
            return response()->json(['msg' => 'Status successfully updated.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 304);
        endif;
    }

    public function transactionList(Request $request){
        $requisition_id = (isset($request->requisition_id) && $request->requisition_id > 0 ? $request->requisition_id : 0);
        $transaction_ids = BudgetRequisitionTransaction::where('budget_requisition_id', $requisition_id)->pluck('acc_transaction_id')->unique()->toArray();
        $transaction_ids = (!empty($transaction_ids) ? $transaction_ids : [0]);
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AccTransaction::orderByRaw(implode(',', $sorts))->whereIn('id', $transaction_ids);


        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $transaction_type = ($list->transaction_type > 0 ? $list->transaction_type : 0);
                $flow = (isset($list->flow) && $list->flow != '' ? $list->flow : 0);
                $transaction_amount = (isset($list->transaction_amount) && $list->transaction_amount > 0 ? $list->transaction_amount : 0);

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'transaction_code' => $list->transaction_code,
                    'connected' => (isset($list->receipts) && $list->receipts->count() > 0 ? 1 : 0),
                    'transaction_date_2' => (!empty($list->transaction_date_2) ? date('jS F, Y', strtotime($list->transaction_date_2)) : ''),
                    'invoice_no' => (!empty($list->invoice_no) ? $list->invoice_no : ''),
                    'detail' => (!empty($list->detail) ? $list->detail : ''),
                    'description' => (!empty($list->description) ? $list->description : ''),
                    'acc_category_id' => ($list->acc_category_id > 0 ? $list->acc_category_id : ''),
                    'category_name' => (isset($list->category->category_name) && !empty($list->category->category_name) ? $list->category->category_name : ''),
                    'acc_bank_id' => ($list->acc_bank_id > 0 ? $list->acc_bank_id : ''),
                    'bank_name' => (isset($list->bank->bank_name) && !empty($list->bank->bank_name) ? $list->bank->bank_name : ''),
                    'audit_status' => ($list->audit_status > 0 ? $list->audit_status : '0'),
                    'transaction_type' => $transaction_type,
                    'flow' => $flow,
                    'transfer_bank_id' => ($list->transfer_bank_id > 0 ? $list->transfer_bank_id : ''),
                    'transfer_bank_name' => (isset($list->tbank->bank_name) && !empty($list->tbank->bank_name) ? $list->tbank->bank_name : ''),
                    'transaction_amount' => ($transaction_amount > 0 ? '£'.number_format($transaction_amount, 2) : ''),
                    'doc_url' => (isset($list->transaction_doc_name) && !empty($list->transaction_doc_name) ? $list->transaction_doc_name : ''),
                    'has_assets' => (isset($list->assets->id) && $list->assets->id > 0 ? 1 : 0),
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function getBudgetSetDetails(Request $request){
        $budget_year_id = $request->budget_year_id;
        $budgetSet = BudgetSet::with('details', 'details.names')->where('budget_year_id', $budget_year_id)->get()->first();

        return response()->json(['row' => $budgetSet], 200);
    }

    public function getAssignedBudgetNameIds($user_id){
        $holders = BudgetNameHolder::where('user_id', $user_id)->pluck('budget_name_id')->unique()->toArray();
        $requester = BudgetNameRequester::where('user_id', $user_id)->pluck('budget_name_id')->unique()->toArray();
        $approver = BudgetNameApprover::where('user_id', $user_id)->pluck('budget_name_id')->unique()->toArray();

        $budget_name_ids = array_merge($requester, $holders, $approver);

        return $budget_name_ids;
    }
}
