<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Full-Height Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Website Header</h1>
    </header>

    <div class="content">
        <h2>Welcome to My Website!</h2>
        <p>This content will automatically fit the height of your screen.</p>
    </div>

    <footer>
        <p>&copy; 2024 My Website. All rights reserved.</p>
    </footer>
</body>
</html>


<style>
    /* Reset margin and padding for better control */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Set body to full height of the viewport */
body, html {
    height: 100%;
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
}

/* Header styling */
header {
    background-color: #4CAF50;
    color: white;
    text-align: center;
    padding: 20px;
}

/* Footer styling */
footer {
    background-color: #333;
    color: white;
    text-align: center;
}

/* Main content container */
.content {
    flex-grow: 1; /* Ensures the content takes up remaining space between header and footer */
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    background-color: #f0f0f0;
    padding: 20px;
}

/* Style for headings */
h2 {
    font-size: 2rem;
    color: #333;
}

/* Style for paragraph */
p {
    font-size: 1.2rem;
    color: #666;
}

</style>