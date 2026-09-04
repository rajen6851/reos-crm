<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GST Payment Receipt & Tax Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e1b4b;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e0e7ff;
            color: #3730a3;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0;
            font-size: 18px;
            color: #4f46e5;
        }
        .two-column {
            width: 100%;
            margin-bottom: 25px;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
        }
        .box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }
        .box-title {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table-data th {
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .table-data td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .amount-box {
            float: right;
            width: 40%;
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 15px;
            text-align: right;
        }
        .amount-title {
            font-size: 11px;
            color: #065f46;
            font-weight: bold;
            text-transform: uppercase;
        }
        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #047857;
            margin-top: 4px;
        }
        .footer {
            margin-top: 60px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
        .stamp-box {
            margin-top: 30px;
            float: left;
            width: 40%;
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-size: 11px;
            color: #475569;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="company-title">{{ $payment->booking->company->name ?? 'REOS Real Estate Developers' }}</div>
                    <span class="badge">Official Payment Receipt & Tax Invoice</span>
                </td>
                <td class="invoice-details">
                    <h2>RECEIPT</h2>
                    <div style="font-weight: bold; margin-top: 5px;">#{{ $payment->receipt_number ?? ('RCT-' . $payment->id) }}</div>
                    <div style="color: #64748b; font-size: 11px; margin-top: 3px;">
                        Date: {{ isset($payment->payment_date) ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y, h:i A') : date('d M Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="two-column">
        <tr>
            <td style="padding-right: 10px;">
                <div class="box">
                    <div class="box-title">Received From (Customer)</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0f172a;">{{ $payment->booking->customer_name ?? ($payment->booking->lead->first_name . ' ' . $payment->booking->lead->last_name) ?? 'Valued Customer' }}</div>
                    <div style="color: #475569; margin-top: 4px;">Phone: {{ $payment->booking->customer_phone ?? ($payment->booking->lead->phone ?? 'N/A') }}</div>
                    <div style="color: #475569;">Email: {{ $payment->booking->customer_email ?? ($payment->booking->lead->email ?? 'N/A') }}</div>
                </div>
            </td>
            <td style="padding-left: 10px;">
                <div class="box">
                    <div class="box-title">Booking & Project Details</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0f172a;">{{ $payment->booking->project->name ?? 'Real Estate Project' }}</div>
                    <div style="color: #475569; margin-top: 4px;">Booking Code: <strong>{{ $payment->booking->booking_code ?? 'BK-N/A' }}</strong></div>
                    <div style="color: #475569;">Unit Assigned: <strong>Unit {{ $payment->booking->unit->unit_number ?? 'N/A' }}</strong> ({{ $payment->booking->unit->unit_type ?? 'Flat' }})</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th>Description</th>
                <th>Payment Mode</th>
                <th>Transaction Ref</th>
                <th style="text-align: right;">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Property Booking Token Payment</strong><br>
                    <span style="font-size: 11px; color: #64748b;">Advance token deposit for Unit {{ $payment->booking->unit->unit_number ?? 'N/A' }}, {{ $payment->booking->project->name ?? '' }}</span>
                </td>
                <td>{{ strtoupper($payment->payment_method ?? 'Razorpay Gateway') }}</td>
                <td style="font-family: monospace;">{{ $payment->transaction_reference ?? ('TXN-' . strtoupper(substr(md5($payment->id), 0, 10))) }}</td>
                <td style="text-align: right; font-weight: bold; font-size: 15px; color: #0f172a;">
                    ₹{{ number_format($payment->amount) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="amount-box">
        <div class="amount-title">Total Amount Received</div>
        <div class="amount-value">₹{{ number_format($payment->amount) }}</div>
        <div style="font-size: 10px; color: #047857; margin-top: 4px; font-weight: bold;">Status: PAYMENT VERIFIED & CONFIRMED</div>
    </div>

    <div class="stamp-box">
        Authorized Signature / Digital Seal<br>
        <strong>{{ $payment->booking->company->name ?? 'REOS Operating System' }}</strong>
    </div>

    <div class="clear"></div>

    <div class="footer">
        This is a computer-generated official payment receipt. Generated via REOS - Real Estate Operating System.<br>
        Thank you for your business!
    </div>

</body>
</html>
