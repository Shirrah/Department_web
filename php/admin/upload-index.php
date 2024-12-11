<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .import-container {
            margin: 50px auto;
            width: 400px;
            text-align: center;
        }
        input[type="file"] {
            display: none; /* Hide the file input */
        }
        .btn-import {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .btn-import:hover {
            background-color: #45a049;
        }
        #response {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="import-container">
        <h2>Import Students</h2>
        <!-- Hidden file input -->
        <input type="file" id="studentFile" name="studentFile" accept=".csv, .xlsx" required>
        <!-- Import Button that triggers the file input -->
        <button class="btn-import" id="importButton">Import</button>
        <div id="response"></div>
    </div>

    <script>
        document.getElementById('importButton').addEventListener('click', function() {
            // Trigger the file input click when the import button is clicked
            document.getElementById('studentFile').click();
        });

        document.getElementById('studentFile').addEventListener('change', function(event) {
            // Automatically submit the form after the file is selected
            const formData = new FormData();
            formData.append('studentFile', event.target.files[0]);

            fetch('upload-students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(result => {
                document.getElementById('response').innerHTML = result;
            })
            .catch(error => {
                document.getElementById('response').innerHTML = 'Error uploading file: ' + error;
            });
        });
    </script>
</body>
</html>
