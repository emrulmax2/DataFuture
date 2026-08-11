<?php
$plan = App\Models\Plan::find(5461) ?? App\Models\Plan::whereHas('tasks')->latest('id')->first();
if (!$plan) { echo "NO PLAN FOUND"; exit; }
echo "Using plan: ".$plan->id.PHP_EOL;
$studentId = App\Models\Assign::where('plan_id',$plan->id)->value('student_id');
echo "Student: ".var_export($studentId,true).PHP_EOL;
$c = new App\Http\Controllers\Api\Student\PlanDetailsController();
$m = new ReflectionMethod($c, 'planDetails');
$m->setAccessible(true);
$data = $m->invoke($c, $plan, $studentId ?? 0);
$res = (new App\Http\Resources\PlanDetailsResource($data))->toArray(new Illuminate\Http\Request());
echo json_encode([
  'module_details' => $res['module_details'],
  'course_content_count' => count($res['course_content']),
  'first_content' => $res['course_content'][0] ?? null,
  'class_dates_count' => count($res['class_dates']),
  'first_date' => $res['class_dates'][0] ?? null,
], JSON_PRETTY_PRINT);
