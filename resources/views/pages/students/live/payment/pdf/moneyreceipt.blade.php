<!DOCTYPE html>
<html>
<head>
    <title>Student Payment Money Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;

        }
        table {
            width: auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 5px;
            text-align: left;
            font-size: 16px;
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
    <div class="row div_print_table" style="">
      <div class="header">
        <table class="print_table">
            <tr>
                <td colspan="2"> <img style="max-width: 150px; height: auto;" src="{{ asset("build/assets/images/L1_logo.svg") }}" /></td>
                <td colspan="2" style="text-color:gray; font-size:28px; text-align:right; padding-top:40px; text-transform:uppercase;" colspan="2">Money Receipt</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right; margin-top:10px;">{{ (!empty($payment->payment_date) ? date('jS M, Y', strtotime($payment->payment_date)) : date('jS M, Y')) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right;">Invoice# {{ $payment->invoice_no }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:left;">{{ $student->full_name }}</td>
            </tr>
            {{-- <tr>
                <td colspan="4" style="text-align:left;">Student Mobile: {{ $student->contact->mobile }}</td>
            </tr> --}}
            <tr>
                <td colspan="4" style="text-align:left;">{!! $address !!}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:left;">ID: {{ $student->registration_no }}</td>
            </tr>
        </table>
      </div>
      <div class="body" style="margin-top:20%;">

        <table class="print_table" style="border-top:1px solid #969494;  border-left:1px solid #969494; border-right:1px solid #969494; font-size:12px;">
            <thead>
                <tr style="background-color:#ddd;">
                    <th class="whitespace-nowrap" style="border-right: 1px solid #969494; border-bottom: 1px solid #969494; margin:2px;">ITEM</th>
                    <th class="whitespace-nowrap" style="border-right: 1px solid #969494;  border-bottom: 1px solid #969494;  margin:2px;">TYPE</th>
                    <th class="whitespace-nowrap" style="border-right: 1px solid #969494;  border-bottom: 1px solid #969494;  margin:2px;">METHOD</th>
                    <th class="whitespace-nowrap" style="border-bottom: 1px solid #969494;  margin:2px; text-align:right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($payment) && $payment!=null)
                        <tr>
                            <td style=" border-bottom: 1px solid #969494;">1.</td>
                            <td style=" border-bottom: 1px solid #969494;">{{ isset($payment->payment_type) && !empty($payment->payment_type) ? $payment->payment_type : '' }}</td>
                            <td style=" border-bottom: 1px solid #969494;">{{ isset($payment->method->name) && $payment->slc_payment_method_id > 0 ? $payment->method->name : '' }}</td>
                            <td style="text-align:right; border-bottom: 1px solid #969494;">{{ isset($payment->amount) && $payment->amount > 0 ? '£'.number_format($payment->amount, 2) : '£0.00' }}</td>
                        </tr>
                @else
                    <tr>
                        <td colspan="10" class="text-center">Payments not found for this agreement.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table style="border-top:1px solid #969494;  border-left:1px solid #969494; border-right:1px solid #969494; margin-top:20%;">
            <thead>
                <tr style="background-color:#ddd;">
                    <th class="whitespace-nowrap" colspan="4" style="border-right: 1px solid #969494; border-bottom: 1px solid #969494; margin:2px;">UNPAID INSTALLMENTS</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($payment) && $payment!=null)
                    @if(isset($payment->installments) && $payment->installments->count() > 0)
                        @foreach($payment->installments as $installment)
                            @if($installment->status == 0)
                                <tr>
                                    <td colspan="4" style="border-bottom: 1px solid #969494;">{{ isset($installment->due_date) && !empty($installment->due_date) ? date('jS M, Y', strtotime($installment->due_date)) : '' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="text-align:center; border-bottom: 1px solid #969494;" class="text-center">No unpaid installments found for this agreement.</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td colspan="4" style="text-align:center; border-bottom: 1px solid #969494;" class="text-center">Payments not found for this agreement.</td>
                    </tr>
                @endif
            </tbody>
        </table>
      </div>

      <div class="footer" style="position: absolute; bottom: 0; width: 100%;">
            <table class="print_table" style="text-align: center; margin-top:20px;">
                <tbody>
                    <tr>
                        <td style="text-align: center; margin-top:2px;">London Churchill College</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; margin-top:2px;">Barclay Hall, 156B Green Street E7 8JQ</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; margin-top:2px;">Phone: +44 (0) 2073771077, Email: accounts@lcc.ac.uk</td>
                    </tr>
                    {{-- <tr>
                        <td style="text-align: center; margin-top:2px;">Receiving Officer: {{ isset($payment->received->employee->full_name) && !empty($payment->received->employee->full_name) ? $payment->received->employee->full_name : '' }}</td>
                    </tr> --}}
                </tbody>
            </table>
      </div>
    </div>
</body>
</html>