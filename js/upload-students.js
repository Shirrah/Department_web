document.addEventListener('DOMContentLoaded', function() {
  const importButton = document.getElementById('importButton');
  const fileInput = document.getElementById('studentFile');
  const responseDiv = document.getElementById('response');
  const semesterID = document.getElementById('csv_semester_ID').value;
  
  // Validate semester exists before proceeding
  if (!semesterID) {
      responseDiv.innerHTML = '<div class="alert alert-danger">Please select a semester first.</div>';
      return;
  }

  importButton.addEventListener('click', function() {
      fileInput.click();
  });
  
  fileInput.addEventListener('change', function(e) {
      if (this.files.length === 0) return;
      
      const file = this.files[0];
      const fileName = file.name;
      const fileExt = fileName.split('.').pop().toLowerCase();
      
      if (fileExt !== 'csv' && fileExt !== 'xlsx') {
          showResponse('error', 'Please upload a CSV or Excel file.');
          return;
      }
      
      processFile(file, fileExt, semesterID);
  });
  
  function processFile(file, fileExt, semesterID) {
      responseDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div> Processing file...</div>';
      
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
              showResponse('success', message);
              
              // Reload students for the current semester
              if (typeof loadStudents === 'function') {
                  loadStudents(semesterID);
              }
              
              setTimeout(() => {
                  const modal = bootstrap.Modal.getInstance(document.getElementById('enrollCsvModal'));
                  modal.hide();
              }, 2000);
          } else {
              showResponse('error', data.message || 'Error importing students.');
          }
      })
      .catch(error => {
          console.error('Error:', error);
          showResponse('error', 'An error occurred while processing the file.');
      });
  }
  
  function showResponse(type, message) {
      responseDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
  }
});