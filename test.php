<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Collections</title>
    <style>
        :root {
            --primary: #5d5c61;
            --secondary: #379683;
            --accent: #7395ae;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .semester-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--secondary);
        }
        
        h1 {
            color: var(--primary);
            margin: 0;
            font-size: 28px;
        }
        
        .semester-selector {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .semester-card {
            background-color: white;
            border-left: 5px solid var(--secondary);
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .semester-title {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .semester-dates {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .summary-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .summary-card h3 {
            margin-top: 0;
            color: #6c757d;
            font-size: 16px;
        }
        
        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .total-amount {
            color: var(--secondary);
        }
        
        .collectibles-amount {
            color: var(--accent);
        }
        
        .fines-amount {
            color: #b1a296;
        }
        
        .outstanding-amount {
            color: var(--warning);
        }
        
        .monthly-chart {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #aaa;
        }
        
        .collection-table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: var(--light);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .amount-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .type-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .type-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .collectible-icon {
            background-color: var(--accent);
        }
        
        .fine-icon {
            background-color: #b1a296;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .paid-badge {
            background-color: #e8f5e9;
            color: var(--success);
        }
        
        .unpaid-badge {
            background-color: #fff3e0;
            color: var(--warning);
        }
        
        .overdue-badge {
            background-color: #ffebee;
            color: var(--danger);
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        
        .view-btn {
            background-color: var(--light);
            color: var(--primary);
        }
        
        .receipt-btn {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .remind-btn {
            background-color: #fff8e1;
            color: var(--warning);
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            border-bottom: 3px solid var(--secondary);
            color: var(--secondary);
            font-weight: bold;
        }
        
        .add-collection-btn {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="semester-header">
            <h1>Semester Collections</h1>
            <div class="semester-selector">
                <div class="semester-card">
                    <div class="semester-title">Fall 2023 Semester</div>
                    <div class="semester-dates">August 28 - December 15, 2023</div>
                </div>
                <select class="semester-card" style="padding: 10px;">
                    <option>Fall 2023</option>
                    <option>Spring 2023</option>
                    <option>Fall 2022</option>
                </select>
            </div>
        </div>
        
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Collected</h3>
                <div class="amount total-amount">$24,850</div>
                <div>from 142 transactions</div>
            </div>
            <div class="summary-card">
                <h3>Collectibles</h3>
                <div class="amount collectibles-amount">$18,420</div>
                <div>74% of total</div>
            </div>
            <div class="summary-card">
                <h3>Fines</h3>
                <div class="amount fines-amount">$6,430</div>
                <div>26% of total</div>
            </div>
            <div class="summary-card">
                <h3>Outstanding</h3>
                <div class="amount outstanding-amount">$1,275</div>
                <div>5% of expected</div>
            </div>
        </div>
        
        <div class="monthly-chart">
            [Monthly Breakdown Chart - August to December]
        </div>
        
        <button class="add-collection-btn">
            <span>+</span> Add Collection
        </button>
        
        <div class="tabs">
            <div class="tab active">All Collections</div>
            <div class="tab">Collectibles</div>
            <div class="tab">Fines</div>
            <div class="tab">Outstanding</div>
            <div class="tab">Reports</div>
        </div>
        
        <div class="collection-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Reference</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dec 12, 2023</td>
                        <td class="type-cell">
                            <div class="type-icon collectible-icon">C</div>
                            Collectible
                        </td>
                        <td>Art Exhibition Entry Fees</td>
                        <td>#ART-EX-122</td>
                        <td class="amount-cell">$2,450.00</td>
                        <td><span class="status-badge paid-badge">Paid</span></td>
                        <td>
                            <button class="action-btn view-btn">View</button>
                            <button class="action-btn receipt-btn">Receipt</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Dec 5, 2023</td>
                        <td class="type-cell">
                            <div class="type-icon fine-icon">F</div>
                            Fine
                        </td>
                        <td>Library Late Return - Group Study Room</td>
                        <td>#LIB-0452</td>
                        <td class="amount-cell">$75.00</td>
                        <td><span class="status-badge paid-badge">Paid</span></td>
                        <td>
                            <button class="action-btn view-btn">View</button>
                            <button class="action-btn receipt-btn">Receipt</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Nov 28, 2023</td>
                        <td class="type-cell">
                            <div class="type-icon collectible-icon">C</div>
                            Collectible
                        </td>
                        <td>Yearbook Sales</td>
                        <td>#YB-2023-87</td>
                        <td class="amount-cell">$1,850.00</td>
                        <td><span class="status-badge paid-badge">Paid</span></td>
                        <td>
                            <button class="action-btn view-btn">View</button>
                            <button class="action-btn receipt-btn">Receipt</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Nov 15, 2023</td>
                        <td class="type-cell">
                            <div class="type-icon fine-icon">F</div>
                            Fine
                        </td>
                        <td>Equipment Damage Fee</td>
                        <td>#EQP-1123</td>
                        <td class="amount-cell">$350.00</td>
                        <td><span class="status-badge unpaid-badge">Unpaid</span></td>
                        <td>
                            <button class="action-btn view-btn">View</button>
                            <button class="action-btn remind-btn">Remind</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Nov 2, 2023</td>
                        <td class="type-cell">
                            <div class="type-icon collectible-icon">C</div>
                            Collectible
                        </td>
                        <td>Club Membership Dues</td>
                        <td>#CLUB-MEM-11</td>
                        <td class="amount-cell">$1,200.00</td>
                        <td><span class="status-badge paid-badge">Paid</span></td>
                        <td>
                            <button class="action-btn view-btn">View</button>
                            <button class="action-btn receipt-btn">Receipt</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>