<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .invoice-container {
            width: 80%;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .invoice-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .invoice-header .logo img {
            height: 60px; /* اندازه لوگو */
            position: absolute;
            left:20px;
            top: 20px;
        }

        .invoice-header .invoice-info {
            text-align: right;
        }
        .invoice-header h2 {
            margin: 0;
            font-size: 24px;
        }
        .invoice-header p {
            margin: 5px 0;
        }
        .invoice-details,
        .invoice-footer {
            margin-top: 30px;
        }
        .invoice-details p,
        .invoice-footer p {
            margin: 10px 0;
        }
        .invoice-footer {
            text-align: center;
        }
        .invoice-footer button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .invoice-footer button:hover {
            background-color: #555;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        .invoice-table th,.invoice-table-td {
            background-color: #f2f2f2;
        }
        .align-right{
            text-align: right !important;
            font-weight: bold;
        }
        .full-width {
            width: 100%;
            display: block;
            margin-top: 20px;
        }
        .sub-total {
            margin-top: 20px;
        }
        .transaction-table th,
        .transaction-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        .transaction-table {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }
        @media print {
            .invoice-footer button {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-container">

    <div class="invoice-header">
        <div class="logo">
            <img src="{{ asset('storage/'.$order->gateway->logo_path) }}" alt="Company Logo">
        </div>
        <div class="data">
            <h2>Invoice</h2>
            <p>Invoice #: {{ $order->id }}</p>
            <p>Invoice Date: {{ date('l, F j, Y', strtotime($order->created_at)) }}</p>
        </div>
    </div>

    <div class="invoice-details">
        <p><strong>Customer Email:</strong> {{ $order->user->email }}</p>
        <p><strong>Status:</strong> {{ $order->status }}</p>
        <p><strong>Comments:</strong> <span style="width: 100%; display: block;">{{ $order->comment }}</span></p>
    </div>

    <div class="invoice-table">
        <table width="100%">
            <thead>
            <tr>
                <th width="80%">Description</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    {{
                        str_replace(
                            ['#order', '#product'],
                            [$order->id, $order->product->product_invoice],
                            $order->gateway->description
                        )
                    }}
                </td>
                <td>${{ number_format($order->total_amount, 2) }} USD</td>
            </tr>
            <tr class="invoice-table-td">
                <td class="align-right">Sub Total</td>
                <td>${{ number_format($order->total_amount, 2) }} USD</td>
            </tr>
            <tr class="invoice-table-td">
                <td class="align-right">Credit</td>
                <td>0.00 USD</td>
            </tr>
            <tr class="invoice-table-td">
                <td class="align-right">Total</td>
                <td>${{ number_format($order->total_amount, 2) }} USD</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="transaction-table" >
        <h3>Transactions</h3>
        <table width="100%">
            <thead>
            <tr>
                <th>Transaction Date</th>
                <th>Transaction ID</th>
                <th>Gateway</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
                <tr align="center">
                    <td>{{ date('l, F j, Y', strtotime($order->created_at)) }}</td>
                    <td>{{ $order->paypal_order_id }}</td>
                    <td>Paypal</td>
                    <td>${{ number_format($order->total_amount, 2) }} USD</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="invoice-footer">
        <button onclick="window.print()">Print Invoice</button>
    </div>
</div>
</body>
</html>
