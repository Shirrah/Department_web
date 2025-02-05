<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Management Navbar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .student-management-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 1rem;
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
    }
    .student-management-header span {
      font-size: 1.25rem;
      font-weight: 500;
    }
    .location a {
      text-decoration: none;
      color: #007bff;
    }
    .location span {
      color: #6c757d;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <!-- Toggle Button -->
      <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Content -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="?content=admin-index&admin=dashboard">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="?content=admin-index&admin=manage-students">Manage Students</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header Section -->
  <div class="student-management-header">
    <span>Manage Students</span>
    <div class="location">
      <a href="?content=admin-index&admin=dashboard">Dashboard</a> / <span>Manage Students</span>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
