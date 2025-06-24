/**
 * Daily Activity Briefing System (DABS)
 * Email functionality module
 * Handles sending notifications to subcontractors
 */

/**
 * Composes and sends an email to the selected subcontractors
 * @param {Array} recipients - List of email addresses
 * @param {string} subject - Email subject
 * @param {string} message - Additional message text
 * @param {Object} briefingData - The briefing data to include
 * @returns {Promise} Promise resolving to the email send status
 */
function sendEmail(recipients, subject, message, briefingData) {
    // Validate inputs
    if (!recipients || recipients.length === 0) {
        return Promise.reject('No recipients specified');
    }
    
    // Create email content
    const emailData = {
        to: recipients,
        subject: subject || 'Daily Activity Briefing',
        message: message || '',
        briefingData: briefingData,
        format: 'html' // Send as HTML email
    };
    
    // Send via AJAX to server
    return fetch('api/sendEmail.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(emailData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Email sending failed');
        }
        return response.json();
    });
}

/**
 * Creates an HTML template for the briefing email
 * @param {Object} briefingData - The briefing content
 * @param {string} additionalMessage - Any additional message
 * @returns {string} HTML email content
 */
function createEmailTemplate(briefingData, additionalMessage) {
    // Get formatted date
    const dateStr = new Date().toLocaleDateString('en-GB');
    
    // Start HTML template
    let html = `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .section { margin-bottom: 20px; padding: 15px; background-color: white; border-radius: 5px; }
            .section-title { margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .footer { text-align: center; font-size: 12px; color: #888; padding: 20px; }
            .activity { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .activity:last-child { border-bottom: none; }
            .time { font-weight: bold; color: #3498db; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Daily Activity Briefing</h1>
                <p>${dateStr}</p>
            </div>
            <div class="content">
    `;
    
    // Add message if provided
    if (additionalMessage) {
        html += `
        <div class="section">
            <p>${additionalMessage}</p>
        </div>
        `;
    }
    
    // Add overview section
    html += `
    <div class="section">
        <h2 class="section-title">Overview</h2>
        <p>${briefingData.overview || 'No overview provided.'}</p>
    </div>
    `;
    
    // Add activities section
    html += `
    <div class="section">
        <h2 class="section-title">Today's Activities</h2>
    `;
    
    if (briefingData.activities && briefingData.activities.length > 0) {
        briefingData.activities.forEach(activity => {
            html += `
            <div class="activity">
                <span class="time">${activity.time}</span>: <strong>${activity.title}</strong>
                <p>${activity.description}</p>
            </div>
            `;
        });
    } else {
        html += '<p>No activities scheduled for today.</p>';
    }
    html += '</div>';
    
    // Add safety information
    html += `
    <div class="section">
        <h2 class="section-title">Safety Information</h2>
        ${briefingData.safety || '<p>No specific safety information for today.</p>'}
    </div>
    `;
    
    // Add footer and close HTML
    html += `
            </div>
            <div class="footer">
                <p>This is an automated email from the Daily Activity Briefing System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    `;
    
    return html;
}

/**
 * Gets a list of available subcontractor emails
 * @returns {Array} List of subcontractor email addresses
 */
function getSubcontractorEmails() {
    if (!DABS || !DABS.subcontractors) {
        console.error('Subcontractor data not available');
        return [];
    }
    
    return DABS.subcontractors.map(sub => sub.email);
}