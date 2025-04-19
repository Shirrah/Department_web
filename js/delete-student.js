document.addEventListener('DOMContentLoaded', () => {
    let currentStudentIdToDelete = null;

    // Make this function globally accessible
    window.showDeleteConfirmation = function(studentId) {
        currentStudentIdToDelete = studentId;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        modal.show();
    };

    async function deleteStudent() {
        if (!currentStudentIdToDelete) return;
        
        try {
            const response = await fetch(`php/admin/delete-student.php?id=${encodeURIComponent(currentStudentIdToDelete)}`);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete student');
            }
            
            createToast('success', 'Student deleted successfully!');
            
            // Remove the student's row dynamically
            const rowToDelete = document.getElementById(`student-row-${currentStudentIdToDelete}`);
            if (rowToDelete) {
                rowToDelete.classList.add('fade-out'); // Optional: add a fade-out effect
                setTimeout(() => rowToDelete.remove(), 300); // Delay the removal for the fade-out effect
            }

        } catch (error) {
            console.error('Error:', error);
            createToast('error', error.message || 'An error occurred while deleting the student');
        } finally {
            currentStudentIdToDelete = null;
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
            modal.hide();
        }
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', deleteStudent);
});
