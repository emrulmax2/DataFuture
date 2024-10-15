<!DOCTYPE html>
<html>
<head>
    <title>Student Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 3px;
            text-align: left;
            font-size: 11px;
        }

        .print_table{
            width:100%;
        }
        .text-mid{
            font-size: 150%; 
        }
        .text-large{
            font-size: 200%;
        }
        .bold{
            font-weight:bold;
        }
        .right{
            text-align: right;
            width:100%;
        }
        .center{
            text-align: center;
        }

        .clear{
            clear:both;
        }

        .border-top{
            border-top: 1px solid #ddd; 
        }


        .transcript-header {
        text-transform:uppercase;
        text-align:center;
        font-weight:bold;
        font-size:17px;
        }
    </style>
</head>
<body>
    <div class="row div_print_table" style="text-transform:uppercase;">
              
  
        <p class="transcript-header">Interim transcript of academic achievement</p>
        
        <table style="width:360px;float:left;font-size:15px;">
          <tbody><tr>
            <td style="width:140px; font-size: 13px;">STUDENT ID :</td>
            <td style="font-size: 13px;">{{ $student->registration_no }}</td>
          </tr>
          <tr>
            <td valign="top" style="width:140px; font-size: 13px;">STUDENT NAME :</td>
            <td style="font-size: 13px;">{{ $student->full_name }}</td>
          </tr>
      
          <tr>
            <td style="width:140px;font-size: 13px;" valign="top">ADDRESS :</td>
            <td style="font-size: 13px;">{{ $student->contact->termaddress->full_address_pdf }}</td>
          </tr>
          <tr>
            <td style="width:140px;font-size: 13px;">DATE OF BIRTH :</td>
            <td style="font-size: 13px;">{{ $student->date_of_birth }}</td>
          </tr>
      
        </tbody></table>
        <table style="width:360px;float:left">
          <tbody><tr>
            <td valign="top" style="width:170px; font-size: 13px;">PROGRAMME NAME :</td>
            <td style="font-size: 13px;">{{ $student->crel->course->name }}</td>
          </tr>
          <tr>
            <td style="font-size: 13px;">AWARDING BODY :</td>
            <td style="font-size: 13px;">{{ $student->crel->course->body->name }}</td>
          </tr>
      
          <tr>
            <td style="font-size: 13px;">REGISTRATION NO :</td>
            <td style="font-size: 13px;">{{ $student->crel->abody->reference }}</td>
          </tr>
      
          <tr>
            <td style="font-size: 13px;">COURSE LEVEL :</td>
           
            <td style="font-size: 13px;"></td>
          </tr>
          
          <tr>
            <td style="font-size: 13px;">START DATE :</td>
           
            <td style="font-size: 13px;">{{ (isset($student->crel->course_start_date) && !empty($student->crel->course_start_date)) ? $student->crel->course_start_date : $courseCreationStart }}</td>
          </tr>
          
          <tr>
            <td style="font-size: 13px;">DATE OF AWARD :</td>
           
            <td style="font-size: 13px;"></td>
          </tr>        
      
        </tbody></table>
        <div class="vsap" style="height:10px;clear:both;"></div>
        <table border="1" style="margin-top:6px;border-collapse:collapse;width:700px;text-align:center;text-transform:uppercase;font-size:13px;">
            <thead>
                <tr>
                    <th>UNIT NUMBER</th>
                    <th style="width:300px">UNIT NAME</th>
                    <th style="">CREDIT VALUE</th>
                    <th style="">UNIT LEVEL</th>
                    <th style="">UNIT STATUS</th>
                    <th style="">GRADE</th>
                </tr>
            </thead>
            <tbody>
                @php
                $serial = 1;
                $total_cr = 0;
            @endphp
            @foreach($data as $moduleName => $results)
                @php
                    $result = $results[0];
                    $credit_value = $result->plan->creations->credit_value;
                    $unit_value = (isset($result->plan->creations->unit_value) && !empty($result->plan->creations->unit_value)) ? $result->plan->creations->unit_value : $result->plan->creations->module->unit_value;
                    $unit_mode = $result->plan->creations->unit_mode;
                    if($result->grade->code=="D" || $result->grade->code=="M" || $result->grade->code=="P")
                        { $total_cr += $credit_value; }
                @endphp      
                <tr>
                    <td>{{ $result->module_code ? $result->module_code : $result->plan->creations->code }}</td>
                    <td>{{ $moduleName }}</td>
                    <td>{{ $credit_value }}</td>
                    <td>{{ $unit_value }}</td>
                    <td>{{ $unit_mode }}</td>
                    <td>{{ $result->grade->code }}</td>
                    <!-- Add other columns as needed -->
                </tr>
            @endforeach

            @foreach($dataPrevious as $moduleName => $results)
                @php
                    $result = $results[0];
                    $credit_value = $result->courseModule->credit_value;
                    $unit_value = $result->courseModule->unit_value;
                    $unit_mode = $result->courseModule->unit_mode;
                    if($result->grade->code=="D" || $result->grade->code=="M" || $result->grade->code=="P")
                        { $total_cr += $credit_value; }
                @endphp      
                <tr>
                    <td>{{ $result->module_code ? $result->module_code : $result->plan->creations->code }}</td>
                    <td>{{ $moduleName }}</td>
                    <td>{{ $credit_value }}</td>
                    <td>{{ $unit_value }}</td>
                    <td>{{ $unit_mode }}</td>
                    <td>{{ $result->grade->code }}</td>
                    <!-- Add other columns as needed -->
                </tr>
            @endforeach

                <tr>
                <td colspan="2" style="text-align: left;">TOTAL CREDITS ACHIEVED</td>
                <td><?php echo $total_cr; ?></td>
                <td colspan="3"></td>
              </tr>
            </tbody>
        </table>
      
       <div class="vsap" style="height:10px;clear:both;"></div>
       
      
      
        <table id="grade-detail" border="1" style="width:350px;border-collapse:collapse;font-size:9px; margin-right: 10px; float: left;">
            <table id="grade-detail" border="1" style="width:350px;border-collapse:collapse;font-size:9px; margin-right: 10px; float: left;">
                <tr>
                  <td>GRADE</td>
                  <td>DESCRIPTION</td>
                </tr>
                <tr>
                  <td>D</td>
                  <td>DISTINCTION</td>
                </tr>
                <tr>
                  <td>M</td>
                  <td>MERIT</td>
                </tr>
                <tr>
                  <td>P</td>
                  <td>PASS</td>
                </tr>
                <tr>
                  <td>R</td>
                  <td>REFERRED</td>
                </tr>
                <tr>
                  <td>A</td>
                  <td>ABSENT OR NON SUBMISSION</td>
                </tr>
                <tr>
                  <td>C</td>
                  <td>MALPRACTICE/UNFAIR PRACTICE</td>
                </tr>        
                <tr>
                  <td>U</td>
                  <td>UNCLASSIFIED/COMPENSATED</td>
                </tr>        
                <tr>
                  <td>W</td>
                  <td>WITHHOLD</td>
                </tr>        
              </table>
        </table>
        
         <table border="1" style="width:300px;float:left;font-size:9px;border-collapse:collapse;">
         <tbody><tr>
            <td>STATUS</td>
            <td>DESCRIPTION</td>
          </tr>
          <tr>
            <td>C</td>
            <td>CORE</td>
          </tr>
          <tr>
            <td>S</td>
            <td>SPECIALIST</td>
          </tr>     
          
          </tbody></table>  
        
      
        <div class="vsap" style="height:10px;clear:both;"></div>
        @php
        // if(isset($signature->signature) && !empty($signature->signature) && Storage::disk('local')->exists('public/signatories/'.$signature->signature)):
        //                     $signatureImg = url('storage/signatories/'.$signature->signature);
        //                 endif;
        @endphp
        <div style="position: absolute; bottom: 0; top:95%;">
        <p style="border-bottom:1px dotted black;width:200px;"></p>
        <p style="margin-left:20px;margin-bottom:5px; font-size: 10px;"></p>
        <p style="margin:0;margin-left:20px;margin-bottom:5px; font-size: 10px;">Date Issue: {{ date('jS M, Y') }}</p>
        <p style="margin:0;margin-left:20px;margin-bottom:5px; font-size: 7px;"><small>*Please note that the grades may be subject to amendment(s) based on the recommendation(s) by external examiner(s)</small></p>
        </div>
                  
        
      </div>
</body>
</html>