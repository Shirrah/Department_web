<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Action Buttons in Table</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f5f7fa;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .btn i {
            margin-right: 5px;
            font-size: 14px;
        }
        
        .btn-view {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .btn-edit {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .btn-delete {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>John Doe</td>
                <td>john@example.com</td>
                <td>Admin</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-view"><i class="fas fa-eye"></i> View</button>
                        <button class="btn btn-edit"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Jane Smith</td>
                <td>jane@example.com</td>
                <td>User</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-view"><i class="fas fa-eye"></i> View</button>
                        <button class="btn btn-edit"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Robert Johnson</td>
                <td>robert@example.com</td>
                <td>Editor</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-view"><i class="fas fa-eye"></i> View</button>
                        <button class="btn btn-edit"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-delete"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>