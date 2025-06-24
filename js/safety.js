/**
 * safety.js - Daily Activity Briefing System (DABS) Safety Information Editor
 *
 * Description:
 *   This file handles rich text editing for the Safety Information section.
 *   It uses TinyMCE with a full menu bar configuration so you can see a complete toolbar and menu.
 *   In this update, the editor's background is explicitly set to white (instead of yellow) to ensure
 *   that the menu bar is visible. If the yellow background was causing an issue with visibility,
 *   this change should resolve it.
 *
 * All times are in UK format (Europe/London timezone).
 *
 * Author: irlam / Chris Irlam
 * Date: 07/06/2025
 * Version: 1.3
 */

document.addEventListener('DOMContentLoaded', function () {
    const editSafetyBtn = document.getElementById('editSafetyBtn');
    const cancelSafetyBtn = document.getElementById('cancelSafetyBtn');
    const saveSafetyBtn = document.getElementById('saveSafetyBtn');
    const safetyContent = document.getElementById('safetyContent');
    const safetyEditContainer = document.getElementById('safetyEditContainer');
    const safetyLoading = document.getElementById('safetyLoading');

    // Initialize TinyMCE on the safety editor textarea with a full menu bar and explicit white background.
    tinymce.init({
        selector: '#safetyEditor',
        height: 300,
        // Enable full menu bar (this makes options like File, Edit, View, etc. visible)
        menubar: 'file edit view insert format tools table help',
        api_key: 'cx3e21j3t5yv0ukx72zuh02xf9o75o3bgencxrbbzmad1p5c',
        plugins: [
            'advlist autolink lists link image charmap',
            'searchreplace visualblocks code',
            'insertdatetime media table paste help'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                 'alignleft aligncenter alignright alignjustify | bullist numlist | ' +
                 'removeformat | help',
        // Set the content style with a white background to override any yellow background.
        content_style: 'body { font-family: Roboto, Arial, sans-serif; font-size:14px; background-color: #fff !important; }',
        branding: false,
        resize: true,
        setup: function (editor) {
            editor.on('change', function () {
                safetyState.hasChanges = true;
            });
        }
    }).then(function (editors) {
        // TinyMCE is fully initialized on #safetyEditor with a full menu bar.
    });

    // Track the current state of safety content editing
    let safetyState = {
        content: '',       // Current safety content (read-only)
        isEditing: false,  // Whether we are currently in edit mode
        hasChanges: false  // Whether unsaved changes exist
    };

    // Function to load the current safety content from the read-only container into state
    function loadSafetyContent() {
        safetyState.content = safetyContent.innerHTML;
    }
    
    loadSafetyContent();

    // When the "Edit Safety Info" button is clicked, switch to edit mode.
    editSafetyBtn.addEventListener('click', function () {
        if (safetyState.isEditing) return;
        // Hide the read-only view and show the editor container.
        safetyContent.style.display = 'none';
        safetyEditContainer.style.display = 'block';

        // Load the current safety content into the TinyMCE editor.
        if (tinymce.get('safetyEditor')) {
            tinymce.get('safetyEditor').setContent(safetyState.content);
            tinymce.get('safetyEditor').focus();
        }
        safetyState.isEditing = true;
        safetyState.hasChanges = false;
    });

    // When the "Cancel" button is clicked, revert to read-only mode without saving changes.
    cancelSafetyBtn.addEventListener('click', function () {
        if (safetyState.hasChanges && !confirm('You have unsaved changes. Discard them?')) {
            return;
        }
        // Hide the editor and show the read-only content.
        safetyEditContainer.style.display = 'none';
        safetyContent.style.display = 'block';
        safetyState.isEditing = false;
        safetyState.hasChanges = false;
    });

    // When the "Save" button is clicked, send updated safety content via AJAX.
    saveSafetyBtn.addEventListener('click', function () {
        let updatedSafety = '';
        if (tinymce.get('safetyEditor')) {
            updatedSafety = tinymce.get('safetyEditor').getContent();
        }
        
        // Display loading indicator.
        safetyLoading.style.display = 'block';

        // Send the updated content to ajax_safety.php using a POST request.
        fetch('ajax_safety.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=update&safety=' + encodeURIComponent(updatedSafety)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error, status ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.ok) {
                // Update the read-only content and UI state.
                safetyState.content = updatedSafety;
                safetyContent.innerHTML = updatedSafety;
                if (typeof showNotification === 'function') {
                    showNotification('Safety Information updated successfully.', 'success');
                }
            } else {
                if (typeof showNotification === 'function') {
                    showNotification('Error updating Safety Information: ' + data.error, 'danger');
                }
            }
        })
        .catch(error => {
            if (typeof showNotification === 'function') {
                showNotification('Error updating Safety Information: ' + error.message, 'danger');
            } else {
                alert('Error updating Safety Information: ' + error.message);
            }
        })
        .finally(() => {
            safetyLoading.style.display = 'none';
            safetyEditContainer.style.display = 'none';
            safetyContent.style.display = 'block';
            safetyState.isEditing = false;
        });
    });
});