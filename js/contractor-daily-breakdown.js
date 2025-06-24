/**
 * js/contractor-daily-breakdown.js - Modern Workers Per Contractor Per Day Table
 * 
 * PURPOSE:
 *   Builds a modern breakdown table below the Activity Schedule showing the number
 *   of workers for each contractor, for each day in the last 7 days, using real data from the backend.
 * 
 * HOW IT WORKS:
 *   - Expects to be called with the 'contractor_daily' array from the main dashboard AJAX response.
 *   - Styles the table using Bootstrap 5 for a clean, modern look.
 * 
 * AUTHOR: irlamkeep (System Admin)
 * LAST UPDATED: 09/06/2025 (UK Time)
 */

(function() {
  /**
   * Call this function with the full JSON returned from ajax_activities.php?action=list.
   * It will find the contractor_daily array and insert the table into #contractorBreakdownTable.
   * 
   * @param {object} data - The JSON data from the backend (should contain data.contractor_daily).
   */
  window.displayContractorWorkerBreakdown = function(data) {
    // Use the real contractor_daily array from backend
    const weeklyContractorData = Array.isArray(data.contractor_daily) ? data.contractor_daily : [];

    // Build a unique list of contractors for the week
    const contractorSet = new Set();
    weeklyContractorData.forEach(day => {
      Object.keys(day.workers).forEach(name => contractorSet.add(name));
    });
    const contractors = Array.from(contractorSet);

    // Helper: UK day name from date string
    function getDayName(dateString) {
      if (!dateString) return '';
      let date;
      if (dateString.includes('/')) {
        const [day, month, year] = dateString.split('/').map(Number);
        date = new Date(year, month - 1, day);
      } else {
        date = new Date(dateString);
      }
      return date.toLocaleDateString('en-GB', { weekday: 'short' });
    }

    function buildContractorBreakdownTable(data, contractors) {
      let html = `
        <div class="card mb-4 shadow-sm">
          <div class="card-header bg-gradient bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Workers per Contractor Per Day</h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-hover mb-0" style="background:#f8fafc;">
                <thead>
                  <tr>
                    <th>Contractor</th>
                    ${data.map(day => `<th>
                      ${getDayName(day.date)}<br>
                      <span class="small text-muted">${day.date}</span>
                    </th>`).join('')}
                  </tr>
                </thead>
                <tbody>
      `;
      contractors.forEach(name => {
        html += `<tr>
          <td class="fw-bold text-primary align-middle">${name}</td>`;
        data.forEach(day => {
          const count = day.workers[name] || 0;
          html += `<td style="vertical-align:middle;">
            ${count > 0
              ? `<span class="badge bg-success fs-6">${count}</span>`
              : `<span class="text-muted">-</span>`}
          </td>`;
        });
        html += `</tr>`;
      });
      html += `
                </tbody>
              </table>
            </div>
          </div>
        </div>
      `;
      return html;
    }

    // Insert the table into the page
    const container = document.getElementById('contractorBreakdownTable');
    if (container) {
      container.innerHTML = buildContractorBreakdownTable(weeklyContractorData, contractors);
    }
  };
})();