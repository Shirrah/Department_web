document.addEventListener('DOMContentLoaded', function() {
    const importButton = document.getElementById('importButton');
    const submitButton = document.getElementById('submitImport');
    const fileInput = document.getElementById('studentFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const responseDiv = document.getElementById('response');
    const semesterID = document.getElementById('csv_semester_ID').value;
    const uploadArea = document.querySelector('.file-upload-area');
    
    let selectedFile = null;
    let selectedFileExt = null;

    // Validate semester exists before proceeding
    if (!semesterID) {
        responseDiv.innerHTML = `
            <div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                Please select a semester first.
            </div>`;
        return;
    }

    // Handle click on upload area
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Handle drag and drop events
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-primary', 'bg-light-primary');
    });

    uploadArea.addEventListener('dragleave', function() {
        this.classList.remove('border-primary', 'bg-light-primary');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary', 'bg-light-primary');
        
        if (e.dataTransfer.files.length) {
            handleFileSelection(e.dataTransfer.files[0]);
        }
    });

    importButton.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent triggering the upload area click
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function(e) {
        if (this.files.length === 0) return;
        handleFileSelection(this.files[0]);
    });

    submitButton.addEventListener('click', function() {
        if (!selectedFile) {
            showResponse('error', 'Please select a file first.');
            return;
        }
        
        processFile(selectedFile, selectedFileExt, semesterID);
    });

    function handleFileSelection(file) {
        const fileName = file.name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        if (fileExt !== 'csv' && fileExt !== 'xlsx') {
            showResponse('error', 'Please upload a CSV or Excel file.');
            return;
        }

        // Update UI to show selected file
        document.getElementById('fileName').textContent = fileName;
        fileInfo.style.display = 'block';
        submitButton.disabled = false;
        
        // Store file for later processing
        selectedFile = file;
        selectedFileExt = fileExt;
        
        // Change upload area appearance
        uploadArea.classList.add('border-success', 'bg-light-success');
        uploadArea.querySelector('.file-upload-icon').innerHTML = 
            '<i class="fas fa-file-alt fa-3x text-success"></i>';
        uploadArea.querySelector('h5').textContent = 'File ready for import';
    }

    function processFile(file, fileExt, semesterID) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Importing...';
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('fileType', fileExt);
        formData.append('semester_ID', semesterID);
        
        fetch('php/admin/process-student-csv.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = `${data.inserted} students imported successfully to semester ${semesterID}!`;
                if (data.skipped > 0) {
                    message += ` ${data.skipped} duplicates skipped.`;
                }
                createToast('success', message);
                
                // Reset the form
                resetUploadForm();
                
                // RELOAD STUDENTS AFTER SUCCESSFUL IMPORT
                reloadStudentsAfterImport(semesterID);
                
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('enrollCsvModal'));
                    modal.hide();
                }, 2000);
            } else {
                createToast('error', data.message || 'Error importing students.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-upload me-2"></i>Import Now';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResponse('error', 'An error occurred while processing the file.');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-upload me-2"></i>Import Now';
        });
    }

    function resetUploadForm() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-upload me-2"></i>Import Now';
        selectedFile = null;
        selectedFileExt = null;
        
        // Reset upload area appearance
        uploadArea.classList.remove('border-success', 'bg-light-success');
        uploadArea.querySelector('.file-upload-icon').innerHTML = 
            '<i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>';
        uploadArea.querySelector('h5').textContent = 'Drag & drop your file here';
    }

    function showResponse(type, message) {
        const icon = type === 'error' ? 'exclamation-circle' : 'check-circle';
        responseDiv.innerHTML = `
            <div class="alert alert-${type} d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
            </div>`;
    }

    // Function to handle student reload after import
    function reloadStudentsAfterImport(semesterID) {
        if (typeof loadStudents === 'function') {
            const currentSemester = document.getElementById('semesterDropdown')?.value || semesterID;
            loadStudents(currentSemester);
        } else {
            console.warn('loadStudents function not found. Make sure student-functions.js is loaded.');
        }
    }
});