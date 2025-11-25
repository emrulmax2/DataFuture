<html>
    <head>
        <title>{{ $report_title }}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body{
                /* font-family: Tahoma, sans-serif; font-size: 14px; line-height: 20px; color: #475569; padding-top: 0; */
                font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
                color: #413E48;
                font-size: 14px;
                line-height: 1.5;
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .bg1{background-color: #F7F2F2;}
            .bg2{background-color: #E2CDCE;}

            @page{margin: 60px 0 60px; padding: 0;}
            @page :first {margin-top: 0;}
            /* header{position: fixed;left: 0px;right: 0px;top: -20px;height: 20px; background: #4A4A4A;} */
            footer{position: fixed;left: 0px;right: 0px;bottom: -40px; height: 20px; padding: 0 60px;}
            footer p{margin: 0 0 5px; font-size: 10px; line-height: 1; text-align: center;}
            footer .page:after { content: counter(page, decimal); }

            table{margin-left: 0px; width: 100%; border-collapse: collapse; border-spacing: 0;}
            table tr td{padding: 0;}
            figure{margin: 0;}
            .text-center{text-align: center;}
            .text-left{text-align: left;}
            .text-right{text-align: right;}
            @media print{ .pageBreak{page-break-after: always;} }
            
            .pageBreak{page-break-after: always;}
            .font-medium{font-weight: bold; }
            .font-bold{font-weight: bold;}
            .font-normal{font-weight: normal;}
            .v-top{vertical-align: top;}
            .v-middle{vertical-align: middle;}
            .text-primary{color: #164e63;}
            .font-sm{font-size: 12px;}
            .text-slate-400{color: #94a3b8;}
            .uppercase{text-transform: uppercase;}
            .italic{font-style: italic;}
            
            .mr-3{margin-right: 3px;}
            .mr-1{margin-right: 4px;}
            .pt-10{padding-top: 10px;}
            .pt-9{padding-top: 9px;}
            .mt-5{margin-top: 5px;}
            .mb-0{margin-bottom: 0;}
            .mb-3{margin-bottom: 3px;}
            .mb-4{margin-bottom: 4px;}
            .mb-1{margin-bottom: 1px;}
            .mb-8{margin-bottom: 8px;}
            .mb-5{margin-bottom: 5px;}
            .mb-15{margin-bottom: 15px;}
            .mb-10{margin-bottom: 10px;}
            .mb-50{margin-bottom: 50px;}
            .mb-60{margin-bottom: 60px;}
            .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
            .table-sm th, .table-sm td{padding: 5px 10px;}
            .w-20{width: 20%;}
            .w-25{width: 25%;}
            .w-30{width: 30%;}
            .w-50{width: 50%;}
            .w-70{width: 70%;}
            .w-80{width: 80%;}
            .w-75{width: 75%;}
            .w-100{width: 100px;}
            .w-120{width: 120px;}
            .w-130{width: 130px;}
            .w-140{width: 140px;}
            .h-15{height: 15px;}
            .h-20{height: 20px;}
            .inline-block{display: inline-block;}
            .block{display: block;}

            .invoiceInfos{padding: 70px 60px 35px;}
            .borderRow{height: 1px; background-color: #413E48; margin: 20px 0 30px;}
            .labelHeading{font-size: 13px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; line-height: 13px; margin: 0 0 5px;}
            .invoiceTotal{font-size: 21px; line-height: 21px; font-weight: bold; margin: 0 0 15px;}
            .invBar{width: 45px; height: 4px; background: #413E48; margin: 5px 0 15px;}
            .invoiceText{line-height: 14px;}

            .invoiceItemsWrap{padding: 40px 60px 0;}
            .invoiceItemsTable thead tr th{font-size: 13px; text-transform: uppercase; font-weight: bold; letter-spacing: 0; line-height: 13px; padding: 12px 15px; background-color: #E2CDCE;}
            .invoiceItemsTable tbody tr td{font-size: 13px; padding: 7px 15px 9px; background-color: #F7F2F2; border-bottom: 1px solid #E2CDCE;}
            .invoiceItemsTable tbody tr:last-child td{border-bottom-color: #413E48;}

            .invoiceCalcWrap{padding: 40px 60px 0;}
            .subtotalRow td{font-size: 13px; line-height: 13px; padding: 0 15px 15px;}
            .totalRow td{font-size: 13px; text-transform: uppercase; font-weight: bold; letter-spacing: 0; line-height: 13px; padding: 12px 15px; background-color: #E2CDCE;}
            
            .paymentInfo{margin: 5px 0 80px;}
            .labelHeading2{font-size: 14px; font-weight: bold; line-height: 14px; margin: 0 0 10px;}
            .paymentMothod{font-size: 13px; line-height: 14px;}
            .termsAndCondition{font-size: 14px; line-height: 18px;}
        </style>
    </head>
    <body>
        <footer>
            <table>
                <tr>
                    <td class="w-20"></td>
                    <td class="w-80">
                        <p>London Churchill College | Barclay Hall, 156B Green Street, London, E7 8JQ.</p>
                        <p>0207 377 0177  | accounts@lcc.ac.uk</p>
                    </td>
                    <td class="w-20">
                        <p class="page text-right">Page </p>
                    </td>
                </tr>
            </table>
        </footer>
        <main class="wrapper">
            <div class="invoiceInfos bg1">
                <table>
                    <tr>
                        <td class="w-50 v-middle">
                            <img src="{{ $logoBase64 }}" alt="logo" style="height: 70px; width: auto;"/>
                        </td>
                        <td class="w-50 text-right v-middle">
                            <h2 class="uppercase font-bold">INVOICE</h2>
                        </td>
                    </tr>
                    <tr><td colspan="100%"><div class="borderRow"></div></td></tr>
                    <tr>
                        <td class="w-50 v-top">
                            <h5 class="labelHeading uppercase">Total Due</h5>
                            <h2 class="invoiceTotal">{{ Number::currency($claim->invoice_total, 'GBP') }}</h2>
                            <div class="invBar"></div>
                            <div class="invoiceText">
                                <div class="mb-3">Date: {{ !empty($claim->invoiced_at) ? date('d/m/Y', strtotime($claim->invoiced_at)) : 'N/A' }}</div>
                                <div class="mb-0">Invoice no: {{ !empty($claim->invoice_no) ? $claim->invoice_no : 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="w-50 text-right v-top">
                            <h5 class="labelHeading uppercase">Invoice To</h5>
                            <h2 class="invoiceTotal">{{ $claim->vendor->name }}</h2>
                            <div class="invoiceText text-right">
                                @if(isset($claim->vendor->phone) && !empty($claim->vendor->phone))
                                    <div class="mb-3">{{ $claim->vendor->phone }}</div>
                                @endif
                                @if(isset($claim->vendor->email) && !empty($claim->vendor->email))
                                    <div class="mb-3">{{ $claim->vendor->email }}</div>
                                @endif
                                @if(isset($claim->vendor->address) && !empty($claim->vendor->address))
                                    <div class="mb-0">{{ $claim->vendor->address }}</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="invoiceItemsWrap">
                <table class="invoiceItemsTable">
                    <thead>
                        <tr>
                            <th class="text-left">STUDENT REFERENCE</th>
                            <th class="text-center">Liability Period</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($claim->installments) && $claim->installments->count() > 0)
                            @foreach($claim->installments as $inst)
                                @if($inst->status == 2)
                                <tr>
                                    <td class="text-left">
                                        <div style="line-height: 13px; margin-bottom: 2px;">{{ isset($inst->student->full_name) && !empty($inst->student->full_name) ? $inst->student->full_name : '' }}</div>
                                        <div style="font-size: 11px; line-height: 11px;">
                                            {{ isset($inst->student->award->reference) && !empty($inst->student->award->reference) ? $inst->student->award->reference : '' }}
                                            {{ isset($inst->student->activeCR->semester->name) && !empty($inst->student->activeCR->semester->name) ? ' - '.$inst->student->activeCR->semester->name: '' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ isset($inst->installment->session_term) && !empty($inst->installment->session_term) ? 'Term '.$inst->installment->session_term : '' }}
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ isset($inst->installment->amount) && $inst->installment->amount > 0 ? Number::currency($inst->installment->amount, 'GBP') : Number::currency(0, 'GBP') }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="invoiceCalcWrap">
                <table>
                    <tr>
                        <td class="w-70 v-top">
                            @if((isset($claim->bank->ac_name) && !empty($claim->bank->ac_name)) || (isset($claim->bank->sort_code) && !empty($claim->bank->sort_code)) || (isset($claim->bank->ac_number) && !empty($claim->bank->ac_number)))
                            <div class="paymentInfo">
                                <h5 class="labelHeading2">Payment Details:</h5>
                                <div class="paymentMothod">
                                    @if(isset($claim->bank->ac_name) && !empty($claim->bank->ac_name))
                                        <div class="mb-3">Account Name: {{ $claim->bank->ac_name }}</div>
                                    @endif
                                    @if(isset($claim->bank->sort_code) && !empty($claim->bank->sort_code))
                                        <div class="mb-3 italic">Sort Code: {{ $claim->bank->sort_code }}</div>
                                    @endif
                                    @if(isset($claim->bank->ac_number) && !empty($claim->bank->ac_number))
                                        <div class="mb-0 italic">Account No: {{ $claim->bank->ac_number }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if(isset($payment_term) && !empty($payment_term))
                                <div class="termsAndConditions">
                                    <h5 class="labelHeading2">Payment Terms:</h5>
                                    <div class="termsAndCondition">
                                        {!! $payment_term !!}
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="w-30 v-top">
                            <table>
                                <tr class="subtotalRow">
                                    <td>Subtotal:</td>
                                    <td class="text-right">{{ Number::currency($claim->invoice_total, 'GBP') }}</td>
                                </tr>
                                <tr class="totalRow">
                                    <td>Total:</td>
                                    <td class="text-right">{{ Number::currency($claim->invoice_total, 'GBP') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </main>
    </body>
</html>