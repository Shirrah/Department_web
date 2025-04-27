// student-functions.js

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Global function to load students
function loadStudents(semester = null) {
    const tbody = document.getElementById('studentTableBody');
    const table = document.getElementById('studentTable');
    
    // Add loading class to table
    table.classList.add('table-loading');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"></div> Loading students...</td></tr>';
    
    // Build the URL with semester parameter
    let url = 'php/admin/fetch-student-records.php';
    if (semester) {
        url += `?semester=${semester}`;
    }

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            table.classList.remove('table-loading');
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No students found</td></tr>';
                return;
            }
            
            tbody.innerHTML = '';
            data.forEach(student => {
                const row = document.createElement('tr');
                row.id = `student-row-${escapeHtml(student.id_student)}`; // Add unique row id
                row.innerHTML = `
                    <td>${escapeHtml(student.id_student)}</td>
                    <td>
                        <span class="password-mask">${'•'.repeat(student.pass_student.length)}</span>
                        <span class="password-full" style="display:none;">${escapeHtml(student.pass_student)}</span>
                        <button class="toggle-password-btn" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                    </td>
                    <td>${escapeHtml(student.lastname_student)}</td>
                    <td>${escapeHtml(student.firstname_student)}</td>
                    <td>${escapeHtml(student.year_student)}</td>
                    <td>
                        <button class="btn btn-warning edit-student-btn btn-sm" 
                                data-id="${escapeHtml(student.id_student)}"
                                data-password="${escapeHtml(student.pass_student)}"
                                data-lastname="${escapeHtml(student.lastname_student)}"
                                data-firstname="${escapeHtml(student.firstname_student)}"
                                data-year="${escapeHtml(student.year_student)}"
                                data-bs-toggle="modal" 
                                data-bs-target="#editStudentModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger delete-btn btn-sm" 
                            onclick="showDeleteConfirmation('${escapeHtml(student.id_student)}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                        <button class="btn btn-primary show-report-btn btn-sm" 
                                data-id="${escapeHtml(student.id_student)}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#reportModal">
                            Show Report
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Reattach event listeners for the new elements
            attachEventListeners();
        })
        .catch(error => {
            table.classList.remove('table-loading');
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="6" class="text-danger text-center">Error loading students. <button onclick="loadStudents()" class="btn btn-sm btn-outline-primary">Retry</button></td></tr>';
        });
}

// Function to attach event listeners to dynamic elements
function attachEventListeners() {
    // Report button click handlers
    document.querySelectorAll('.show-report-btn').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            document.getElementById('reportContent').innerHTML = "<p class='text-center'>Loading report...</p>";
            
            fetch("php/admin/fetch-student-report.php?id_student=" + studentId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('reportContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = "<p class='text-danger text-center'>Error loading report.</p>";
                    console.error("Error fetching report:", error);
                });
        });
    });

    // Edit button handlers
    document.querySelectorAll('.edit-student-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id_student').value = this.getAttribute('data-id');
            document.getElementById('edit_pass_student').value = this.getAttribute('data-password');
            document.getElementById('edit_lastname_student').value = this.getAttribute('data-lastname');
            document.getElementById('edit_firstname_student').value = this.getAttribute('data-firstname');
            document.getElementById('edit_year_student').value = this.getAttribute('data-year');
        });
    });
}

// Global function for password toggling
function togglePassword(button) {
    const row = button.closest('tr');
    const passwordMask = row.querySelector('.password-mask');
    const passwordFull = row.querySelector('.password-full');
    
    if (passwordMask.style.display === 'none') {
        passwordMask.style.display = '';
        passwordFull.style.display = 'none';
        button.innerHTML = '<i class="fas fa-eye"></i>';
    } else {
        passwordMask.style.display = 'none';
        passwordFull.style.display = '';
        button.innerHTML = '<i class="fas fa-eye-slash"></i>';
    }
}

// Function to find and move cleared students
function setupMoveClearedStudents() {
    const moveButton = document.getElementById('moveClearedStudentsButton');
    if (!moveButton) return;

    moveButton.addEventListener('click', function() {
        // Clear previous results
        document.getElementById('moveStudentsResults').innerHTML = '';
    });

    const confirmButton = document.getElementById('confirmMoveStudents');
    if (!confirmButton) return;

    confirmButton.addEventListener('click', function() {
        const currentSemester = document.getElementById('currentSemester').value;
        const targetSemester = document.getElementById('targetSemester').value;
        const resultsDiv = document.getElementById('moveStudentsResults');

        if (!targetSemester) {
            resultsDiv.innerHTML = '<div class="alert alert-danger">Please select a target semester.</div>';
            return;
        }

        // Show loading state
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div> Finding cleared students...</div>';

        // Make AJAX request
        fetch('php/admin/find-cleared-students.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `current_semester=${encodeURIComponent(currentSemester)}&target_semester=${encodeURIComponent(targetSemester)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else {
                let html = `<div class="alert alert-success">
                    Found ${data.students.length} students who meet the criteria and will be moved to ${targetSemester}.
                </div>`;
                
                if (data.students.length > 0) {
                    html += `<div class="mt-3">
                        <button id="executeMove" class="btn btn-success">Confirm and Execute Move</button>
                    </div>`;
                }
                
                resultsDiv.innerHTML = html;

                // Set up the execute move button if needed
                if (data.students.length > 0) {
                    document.getElementById('executeMove').addEventListener('click', function() {
                        executeStudentMove(currentSemester, targetSemester, data.students);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.innerHTML = `<div class="alert alert-danger">An error occurred: ${error.message}</div>`;
        })
        .finally(() => {
            confirmButton.disabled = false;
            confirmButton.innerHTML = 'Move Students';
        });
    });
}

function executeStudentMove(currentSemester, targetSemester, students) {
    const resultsDiv = document.getElementById('moveStudentsResults');
    const executeButton = document.getElementById('executeMove');
    
    if (executeButton) {
        executeButton.disabled = true;
        executeButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Moving...';
    }

    // Prepare student IDs
    const studentIds = students.map(student => student.id_student);

    fetch('php/admin/move-cleared-students.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `current_semester=${encodeURIComponent(currentSemester)}&target_semester=${encodeURIComponent(targetSemester)}&student_ids=${encodeURIComponent(JSON.stringify(studentIds))}`
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);  // Log the response data for debugging
    
        if (data.success) {
            resultsDiv.innerHTML = `<div class="alert alert-success">
                Successfully moved ${data.moved_count} students to ${targetSemester}.
            </div>`;
    
            // Reload the student list if we're on the current semester
            if (currentSemester === document.getElementById('currentSemester').value) {
                loadStudents(currentSemester);
            }
        } else {
            resultsDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to move students.'}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resultsDiv.innerHTML = `<div class="alert alert-danger">An error occurred during the move: ${error.message}</div>`;
    });
    
}


// Call this function when the page loads
document.addEventListener('DOMContentLoaded', function() {
    setupMoveClearedStudents();
});