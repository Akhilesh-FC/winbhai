<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Campaign Summary</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 5px;
        }
        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 20px;
            color: #777;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f2f2f2;
            padding: 8px;
            font-weight: bold;
            border-left: 4px solid #0d6efd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background: #fafafa;
            text-align: left;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h1>Campaign Analytics Report</h1>
<div class="subtitle">
    Generated on {{ date('d M Y, h:i A') }}
</div>

<div class="section">
    <div class="section-title">Campaign Information</div>
    <table>
        <tr><th>Campaign ID</th><td>{{ $data['Campaign_ID'] }}</td></tr>
        <tr><th>Campaign Name</th><td>{{ $data['Campaign_Name'] }}</td></tr>
        <tr><th>Unique Code</th><td>{{ $data['Unique_Code'] }}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Analytics Summary</div>
    <table>
        <tr><th>Registrations</th><td>{{ $data['Registrations'] }}</td></tr>
        <tr><th>First Deposits</th><td>{{ $data['First_Deposits'] }}</td></tr>
        <tr><th>First Deposit Amount</th><td>{{ $data['First_Deposit_Amount'] }}</td></tr>
        <tr><th>Total Deposits</th><td>{{ $data['Total_Deposit'] }}</td></tr>
        <tr><th>Total Withdrawals</th><td>{{ $data['Total_Withdrawal'] }}</td></tr>
        <tr><th>Commission %</th><td>{{ $data['Commission_Percent'] }}%</td></tr>
        <tr><th>Your Commission</th><td>{{ $data['Your_Commission'] }}</td></tr>
        <tr><th>Total Transactions</th><td>{{ $data['Transaction'] }}</td></tr>
        <tr><th>Link Clicks</th><td>{{ $data['Link_Clicks'] }}</td></tr>
    </table>
</div>

<div class="footer">
    © {{ date('Y') }} Campaign Analytics Report
</div>

</body>
</html>
