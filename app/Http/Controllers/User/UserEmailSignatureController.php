<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeEmailSignatureRequest;
use App\Models\Employee;
use App\Models\EmployeeEmailSignature;
use App\Models\HrVacancy;
use App\Models\User;
use Illuminate\Http\Request;

class UserEmailSignatureController extends Controller
{
    /**
     * The two artefacts the tab can produce. Gmail keeps the rounded, modern
     * card; Outlook gets the Word-safe layout (see the blade files for why).
     */
    const VARIANTS = ['gmail', 'outlook'];

    public function index(){
        $employee = $this->currentEmployee();
        $fields = EmployeeEmailSignature::fieldsFor($employee);

        return view('pages.users.my-account.signature', [
            'title' => 'Email Signature - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My HR', 'href' => route('user.account')],
                ['label' => 'Email Signature', 'href' => 'javascript:void(0);'],
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => $employee,
            'vacanties' => HrVacancy::where('active', 1)->get()->count(),
            'fields' => $fields,
            'defaults' => EmployeeEmailSignature::defaultsFor($employee),
            'markup' => $this->renderVariants($fields),
        ]);
    }

    /**
     * Save the employee's overrides. Blank inputs are stored as NULL so the
     * field goes back to tracking the HR record rather than freezing a blank.
     */
    public function update(EmployeeEmailSignatureRequest $request){
        $employee = $this->currentEmployee();

        $data = [];
        foreach(EmployeeEmailSignature::TEXT_FIELDS as $field):
            $value = trim((string) $request->input($field, ''));
            if($value !== ''):
                $data[$field] = $value;
            else:
                // Blanking an extension or mobile is a decision to hide it;
                // blanking anything else means "track my HR record again".
                $data[$field] = (in_array($field, EmployeeEmailSignature::BLANKABLE_FIELDS, true) ? '' : null);
            endif;
        endforeach;

        $signature = EmployeeEmailSignature::where('employee_id', $employee->id)->get()->first();
        if($signature):
            $data['updated_by'] = auth()->user()->id;
            $signature->update($data);
        else:
            $data['employee_id'] = $employee->id;
            $data['created_by'] = auth()->user()->id;
            $signature = EmployeeEmailSignature::create($data);
        endif;

        if(!$signature):
            return response()->json(['suc' => 2], 200);
        endif;

        $fields = EmployeeEmailSignature::fieldsFor($employee);

        return response()->json([
            'suc' => 1,
            'markup' => $this->renderVariants($fields),
            'updated_at' => $fields['updated_at'],
        ], 200);
    }

    /**
     * Drop the overrides entirely so the signature reverts to the HR record.
     */
    public function reset(){
        $employee = $this->currentEmployee();
        EmployeeEmailSignature::where('employee_id', $employee->id)->forceDelete();

        $fields = EmployeeEmailSignature::fieldsFor($employee);

        return response()->json([
            'suc' => 1,
            'fields' => $this->formValues($fields),
            'markup' => $this->renderVariants($fields),
        ], 200);
    }

    /**
     * Re-render both variants from unsaved form values so the preview tracks
     * what is being typed without committing anything to the database.
     */
    public function preview(Request $request){
        $employee = $this->currentEmployee();
        $overrides = [];

        foreach(EmployeeEmailSignature::TEXT_FIELDS as $field):
            if($request->has($field)):
                $overrides[$field] = trim((string) $request->input($field, ''));
                // An emptied box previews exactly as saving it would: hidden
                // for the blankable fields, back to the HR value otherwise.
                if($overrides[$field] === '' && !in_array($field, EmployeeEmailSignature::BLANKABLE_FIELDS, true)):
                    unset($overrides[$field]);
                endif;
            endif;
        endforeach;

        $fields = EmployeeEmailSignature::fieldsFor($employee, $overrides);

        return response()->json(['suc' => 1, 'markup' => $this->renderVariants($fields)], 200);
    }

    protected function renderVariants($fields){
        $markup = [];
        foreach(self::VARIANTS as $variant):
            $markup[$variant] = trim(view('signature.'.$variant, ['sig' => $fields])->render());
        endforeach;

        return $markup;
    }

    /**
     * Only the values that map to a form control — used to repopulate the tab
     * after a reset.
     */
    protected function formValues($fields){
        $values = [];
        foreach(EmployeeEmailSignature::TEXT_FIELDS as $field):
            $values[$field] = (isset($fields[$field]) ? $fields[$field] : '');
        endforeach;

        return $values;
    }

    protected function currentEmployee(){
        return Employee::where('user_id', auth()->user()->id)->get()->first();
    }
}
