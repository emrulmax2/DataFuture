<!DOCTYPE html>
<html>
<head>
    <title>Student Document Request Money Receipt</title>
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
                <td colspan="2"> <img style="max-width: 150px; height: auto;" src="{{ $logoUrl }}" /></td>
                @if($studentOrders->transaction_date != null)
                    
                <td colspan="2" style="text-color:gray; font-size:28px; text-align:right; padding-top:40px; text-transform:uppercase;" colspan="2">MONEYRECEIPT</td>
                @else
                <td colspan="2" style="text-color:gray; font-size:28px; text-align:right; padding-top:40px; text-transform:uppercase;" colspan="2">INVOICE</td>
                @endif
            </tr>
            <tr>
                <td colspan="4" style="text-align:right; margin-top:10px;">{{ (!empty($studentOrders->transaction_date) ? date('jS M, Y', strtotime($studentOrders->transaction_date)) : date('jS M, Y', strtotime($studentOrders->created_at))) }}</td>
            </tr>
            <tr>
                <td colspan="4" style=" position: relative; text-align:right;"> #{{ $studentOrders->invoice_number }}

                    <table style="position: absolute; right:0; top: 20%; border-top:1px solid #969494;  border-left:1px solid #969494; border-right:1px solid #969494; ">
                        
                        <thead>
                            
                            <tr style="background-color:#ddd;">
                                <th class="whitespace-nowrap" colspan="4" style="border-right: 1px solid #969494; border-bottom: 1px solid #969494; margin:2px;">PAYMENT STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($studentOrders->payment_status != 'Completed')
                                            
                                <tr>
                                    <td colspan="4" style="text-align:center; border-bottom: 1px solid #969494;" class="text-center">No payment found for this order.</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="4" style="text-align:center; border-bottom: 1px solid #969494;" class="text-center">Paid By {{ $studentOrders->payment_method }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:left;">{{ $student->full_name }}</td>
            </tr>
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
                    <th class="whitespace-nowrap" style="border-right: 1px solid #969494;  border-bottom: 1px solid #969494;  margin:2px;">QUANTITY</th>
                    <th class="whitespace-nowrap" style="border-bottom: 1px solid #969494;  margin:2px; text-align:right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($studentOrders) && $studentOrders!=null)
                    @if(isset($studentOrders->studentOrderItems) && $studentOrders->studentOrderItems->count() > 0)
                        @foreach($studentOrders->studentOrderItems as $item)
                            <tr>
                                <td style=" border-bottom: 1px solid #969494;">{{ isset($item->letterSet->letter_title) && !empty($item->letterSet->letter_title) ? $item->letterSet->letter_title : '' }}
                                    @if($item->number_of_free > 0) 
                                    <br>
                                        <span style="padding-top:2px; font-size: 12px "> Same Day (£10.00) [ {{ $item->quantity - $item->number_of_free }} ] </span>
                                    <br>
                                        <span style="padding-top:2px; font-size: 12px "> 3 Working Days (free) [ {{ $item->number_of_free }} ] </span>
                                    @endif
                                </td>
                                <td style=" border-bottom: 1px solid #969494;">{{ isset($item->quantity) && !empty($item->quantity) ? ($item->quantity) : '' }}
                                    
                                </td>
                                <td style="text-align:right; border-bottom: 1px solid #969494;">{{ isset($item->total_amount) && $item->total_amount > 0 ? '£'.number_format($item->total_amount, 2) : '£0.00' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center">No items found.</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td colspan="10" class="text-center">Items not found.</td>
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
                        <td style="text-align: center; margin-top:2px;">Phone: +44 (0) 2073771077, Email: accounts@londonchurchillcollege.ac.uk</td>
                    </tr>
                    @if($studentOrders->payment_status == 'Completed')
                    <tr>
                        <td style="text-align: center; margin-top:2px;">Transaction No: {{ $studentOrders->transaction_id }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>