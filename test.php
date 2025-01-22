<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Attendances Accordion</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2 class="mb-4">Events and Attendances</h2>
    <div class="accordion" id="accordionExample">
      <!-- Event 1 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Event 1 - 2025-01-01
          </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
          <div class="accordion-body">
            <p><strong>Description:</strong> This is the first event.</p>
            <p><strong>Created On:</strong> 2024-12-01</p>
            <h5>Attendances:</h5>
            <table class="table table-sm table-striped">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Penalty Type</th>
                  <th>Penalty Requirements</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Regular</td>
                  <td><span class="badge bg-primary">Ongoing</span></td>
                  <td>09:00 AM</td>
                  <td>05:00 PM</td>
                  <td><span class="badge bg-info">Fine</span></td>
                  <td>$0</td>
                  <td><button class="btn btn-primary btn-sm">Show Records</button></td>
                </tr>
                <tr>
                  <td>Late</td>
                  <td><span class="badge bg-secondary">Ended</span></td>
                  <td>10:30 AM</td>
                  <td>04:00 PM</td>
                  <td><span class="badge bg-success">Community Service</span></td>
                  <td>5 Hours</td>
                  <td><button class="btn btn-primary btn-sm">Show Records</button></td>
                </tr>
                <tr>
                  <td>Regular</td>
                  <td><span class="badge bg-warning text-dark">Pending</span></td>
                  <td>-</td>
                  <td>-</td>
                  <td><span class="badge bg-primary">Donation</span></td>
                  <td>$50</td>
                  <td><button class="btn btn-primary btn-sm">Show Records</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
