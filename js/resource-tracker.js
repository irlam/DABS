/**
 * =========================================================================
 * NAME: js/resource-tracker.js - Resource Tracker
 * DESCRIPTION:
 *   This file provides a modern, responsive, and accessible resource tracker
 *   for the Daily Activity Briefing System (DABS). It displays today's resource
 *   summary, a subcontractor breakdown, historical trends with a range selector,
 *   and supports CSV export. All dates and times are formatted in UK style.
 *
 *   This version uses a module pattern (IIFE) instead of a class, to avoid 
 *   potential syntax issues that may arise in certain environments.
 *
 * AUTHOR: irlam (System Administrator)
 * LAST UPDATED: 09/06/2025 (UK Time)
 * =========================================================================
 */

const ResourceTracker = (function() {
  // Private configuration and state
  const ukTimeZone = 'Europe/London';
  const ukDateOptions = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: ukTimeZone };
  const ukTimeOptions = { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: ukTimeZone };
  const fadeTransitionTime = 150; // milliseconds
  const dataCache = new Map();
  const cacheExpiry = 5 * 60 * 1000; // 5 minutes in ms
  const maxCacheSize = 40;
  let currentHistoryRange = 4; // default week range selection

  // Public methods exposed by the module
  function init() {
    // Expose the display method globally so it can be called elsewhere
    window.displayResourceStats = displayResourceStats;
    // Wait for DOM readiness if necessary
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function() {
        // Optionally, you can call displayResourceStats here if initial data is available.
      });
    }
  }

  async function displayResourceStats(data) {
    const container = document.getElementById('resourceStats');
    if (!container) return;
    await showLoadingState(container);
    const processedData = processResourceData(data);
    const html = generateResourceHTML(processedData);
    await updateDisplay(container, html);
    attachEventListeners();
    initializeInteractiveComponents();
    initializeTooltips();
  }

  function showLoadingState(container) {
    return new Promise(resolve => {
      container.innerHTML = `
<div class="resource-loading-container" role="status" aria-live="polite" aria-label="Loading resource statistics">
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-gradient bg-primary text-white">
      <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm me-3" role="status">
          <span class="visually-hidden">Loading resource data...</span>
        </div>
        <h5 class="mb-0">Loading Resource Statistics...</h5>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-4 mb-4">
        ${Array.from({ length: 4 }).map(() => `
          <div class="col-md-3">
            <div class="text-center p-3 bg-light rounded-3">
              <div class="placeholder-glow">
                <span class="placeholder col-8 placeholder-lg rounded mb-2"></span>
                <span class="placeholder col-6 rounded"></span>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th><span class="placeholder col-8 rounded"></span></th>
              <th><span class="placeholder col-6 rounded"></span></th>
              <th><span class="placeholder col-4 rounded"></span></th>
            </tr>
          </thead>
          <tbody>
            ${Array.from({ length: 3 }).map(() => `
              <tr>
                <td><span class="placeholder col-10 rounded"></span></td>
                <td><span class="placeholder col-7 rounded"></span></td>
                <td><span class="placeholder col-5 rounded"></span></td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-light">
      <div class="row">
        <div class="col-4 text-center"><span class="placeholder col-8 rounded"></span></div>
        <div class="col-4 text-center"><span class="placeholder col-8 rounded"></span></div>
        <div class="col-4 text-center"><span class="placeholder col-8 rounded"></span></div>
      </div>
    </div>
  </div>
  <div class="text-center mt-3">
    <div class="progress mx-auto" style="max-width: 300px; height: 6px;">
      <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
    </div>
    <small class="text-muted mt-2 d-block">Processing resource data...</small>
  </div>
</div>`;
      if (!document.getElementById('resource-tracker-styles')) {
        const style = document.createElement('style');
        style.id = 'resource-tracker-styles';
        style.textContent = `
  .resource-loading-container { opacity: 0; animation: fadeInUp 0.5s ease-out forwards; }
  @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  .bg-gradient { background: linear-gradient(135deg, var(--bs-primary, #0d6efd) 0%, var(--bs-primary-dark, #0056b3) 100%); }
  .resource-card { transition: transform 0.2s, box-shadow 0.2s; }
  .resource-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  .progress-animate { transition: width 0.6s; }
  .counter-animate { transition: all 0.3s; }
  @media (max-width: 768px) { .resource-card, .resource-metric-card { margin-bottom: 1rem; } }
  .table-history-small th, .table-history-small td { font-size: 0.97em; padding: 0.4rem 0.5rem; }
        `;
        document.head.appendChild(style);
      }
      setTimeout(resolve, 100);
    });
  }

  function processResourceData(data) {
    try {
      const cacheKey = generateCacheKey(data);
      if (dataCache.has(cacheKey)) {
        const cached = dataCache.get(cacheKey);
        if (Date.now() - cached.timestamp < cacheExpiry) return cached.data;
      }
      // Copy data and process as needed
      let processed = Object.assign({}, data);
      processed.total_labor = parseIntSafe(data.total_labor, 0);
      processed.total_contractors = parseIntSafe(data.total_contractors, 0);
      processed.today_subcontractors = Array.isArray(data.today_subcontractors)
        ? data.today_subcontractors.filter(sub => sub && sub.contractor_name)
        : [];
      processed.contractor_history = Array.isArray(data.contractor_history) ? data.contractor_history : [];
      processed.weekly_stats = Array.isArray(data.weekly_stats) ? data.weekly_stats : [];
      processed.prev_weekly_stats = Array.isArray(data.prev_weekly_stats) ? data.prev_weekly_stats : [];
      processed.uk_date = formatDateUK(new Date());
      processed.uk_time = formatTimeUK(new Date());
      processed.uk_datetime = formatDateTimeUK(new Date());
      processed.processed_at = new Date().toISOString();
      processed.cache_key = cacheKey;
      processed.processing_version = '2.0';
      processed.today_subcontractors = processed.today_subcontractors.map(sub => ({
        contractor_name: String(sub.contractor_name || 'Unknown Contractor'),
        trade: String(sub.trade || 'Unknown Trade'),
        resource_count: parseIntSafe(sub.resource_count, 0)
      }));
      if (dataCache.size >= maxCacheSize) {
        dataCache.delete(dataCache.keys().next().value);
      }
      dataCache.set(cacheKey, { data: processed, timestamp: Date.now() });
      return processed;
    } catch (err) {
      throw new Error("Data processing failed: " + err.message);
    }
  }

  function generateResourceHTML(data) {
    const totalResources = data.total_labor + data.total_contractors;
    const maxCapacity = 120;
    const utilizationPercent = Math.min((totalResources / maxCapacity) * 100, 100);
    const todayUK = formatDateUK(new Date());
    const timeUK = formatTimeUK(new Date());

    let contractorRows = '';
    if (data.today_subcontractors.length > 0) {
      contractorRows = data.today_subcontractors.map(sub => `
        <tr>
          <td><strong>${escapeHtml(sub.contractor_name)}</strong></td>
          <td>${escapeHtml(sub.trade)}</td>
          <td>
            <div class="d-flex align-items-center">
              <span class="me-2">${sub.resource_count}</span>
              <div class="progress flex-grow-1" style="height: 7px;">
                <div class="progress-bar bg-info" style="width: ${Math.min(sub.resource_count * 5, 100)}%"></div>
              </div>
            </div>
          </td>
        </tr>
      `).join('');
    } else {
      contractorRows = `<tr><td colspan="3" class="text-center text-muted">No subcontractors recorded today.</td></tr>`;
    }

    let weeklyRows = '';
    if (data.weekly_stats.length > 0) {
      weeklyRows = data.weekly_stats.map(day => `
        <tr${isWeekend(day.day_date) ? ' class="table-secondary"' : ''}>
          <td>${getDayName(day.day_date)}<br><span class="text-muted small">${escapeHtml(day.day_date)}</span></td>
          <td>${day.labor_count}</td>
          <td>${day.contractor_count}</td>
          <td>${day.labor_count + day.contractor_count}</td>
        </tr>
      `).join('');
    } else {
      weeklyRows = `<tr><td colspan="4" class="text-center text-muted">No history available.</td></tr>`;
    }

    const exportBtn = `<button class="btn btn-sm btn-outline-primary float-end" id="exportResourceDataBtn"><i class="fas fa-file-csv me-1"></i>Export</button>`;
    const ranges = [3, 4, 5, 6, 8, 12];
    let rangeHtml = `
      <div class="mb-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="Select weeks to show history">
          ${ranges.map(r =>
            `<button class="btn btn${currentHistoryRange === r ? '' : '-outline'}-secondary history-range" data-range="${r}">${r}w</button>`
          ).join('')}
        </div>
      </div>
    `;

    return `
      <!-- Today's Summary Card -->
      <div class="card mb-4 border-0 shadow-sm resource-card" role="region" aria-labelledby="todays-summary-title">
        <div class="card-header bg-gradient text-white position-relative overflow-hidden">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="todays-summary-title">
              <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>Today's Resource Summary
            </h5>
            <div class="d-flex align-items-center">
              <span class="badge bg-light text-dark me-2">${todayUK}</span>
              <small class="opacity-75">${timeUK}</small>
            </div>
          </div>
        </div>
        <div class="card-body p-4">
          <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
              <div class="resource-metric-card h-100 p-3 bg-light rounded-3 text-center">
                <div class="display-4 fw-bold text-primary mb-1 counter-animate" data-target="${data.total_labor}">${data.total_labor}</div>
                <div class="text-muted mb-2 fw-semibold">Workers</div>
                <div class="progress progress-animate" style="height: 8px;">
                  <div class="progress-bar bg-primary" style="width: ${Math.min(data.total_labor * 2, 100)}%"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6">
              <div class="resource-metric-card h-100 p-3 bg-light rounded-3 text-center">
                <div class="display-4 fw-bold text-info mb-1 counter-animate" data-target="${data.total_contractors}">${data.total_contractors}</div>
                <div class="text-muted mb-2 fw-semibold">Contractors</div>
                <div class="progress progress-animate" style="height: 8px;">
                  <div class="progress-bar bg-info" style="width: ${Math.min(data.total_contractors * 5, 100)}%"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-12">
              <div class="resource-metric-card h-100 p-3 bg-light rounded-3 text-center">
                <div class="display-4 fw-bold text-success mb-1 counter-animate" data-target="${totalResources}">${totalResources}</div>
                <div class="text-muted mb-2 fw-semibold">Total Resources</div>
                <div class="progress progress-animate" style="height: 8px;">
                  <div class="progress-bar bg-success" style="width: ${utilizationPercent}%"></div>
                </div>
                <small class="text-muted mt-1 d-block">Utilization: ${utilizationPercent.toFixed(1)}%</small>
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Subcontractor Breakdown Table -->
      <div class="card mb-4 border-0 shadow-sm resource-card">
        <div class="card-header bg-gradient text-white">
          <i class="fas fa-users me-2"></i>Subcontractor Breakdown (${data.today_subcontractors.length})
          ${exportBtn}
        </div>
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Subcontractor</th>
                <th>Trade</th>
                <th>Resources</th>
              </tr>
            </thead>
            <tbody>
              ${contractorRows}
            </tbody>
          </table>
        </div>
      </div>
  
      <!-- Historical Weekly Trends Table -->
      <div class="card mb-4 border-0 shadow-sm resource-card">
        <div class="card-header bg-gradient text-white">
          <i class="fas fa-history me-2"></i>Resource Trends (Last ${currentHistoryRange} Weeks)
        </div>
        <div class="card-body">
          ${rangeHtml}
          <div class="table-responsive">
            <table class="table table-history-small table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Workers</th>
                  <th>Contractors</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                ${weeklyRows}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  function updateDisplay(container, html) {
    return new Promise(resolve => {
      container.style.opacity = 0.5;
      setTimeout(() => {
        container.innerHTML = html;
        container.style.opacity = 1;
        resolve();
      }, fadeTransitionTime);
    });
  }

  function attachEventListeners() {
    document.querySelectorAll('.history-range').forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const range = parseInt(link.getAttribute('data-range'), 10) || 4;
        currentHistoryRange = range;
        const last = Array.from(dataCache.values()).pop();
        if (last) displayResourceStats(last.data);
      });
    });
    const exportBtn = document.getElementById('exportResourceDataBtn');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        exportTableToCSV('resource_data.csv');
      });
    }
  }

  function initializeInteractiveComponents() {
    document.querySelectorAll('.counter-animate').forEach(el => {
      const target = parseInt(el.getAttribute('data-target') || el.textContent, 10);
      let n = 0;
      const inc = Math.max(1, Math.ceil(target / 60));
      const interval = setInterval(() => {
        n += inc;
        if (n >= target) {
          el.textContent = target;
          clearInterval(interval);
        } else {
          el.textContent = n;
        }
      }, 8);
    });
  }

  function initializeTooltips() {
    if (window.bootstrap && bootstrap.Tooltip) {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
      });
    }
  }

  // Utility Functions
  function formatDateUK(date) {
    if (!date) return '';
    return date.toLocaleDateString('en-GB', ukDateOptions);
  }

  function formatTimeUK(date) {
    if (!date) return '';
    return date.toLocaleTimeString('en-GB', ukTimeOptions);
  }

  function formatDateTimeUK(date) {
    if (!date) return '';
    return `${formatDateUK(date)} ${formatTimeUK(date)}`;
  }

  function parseIntSafe(val, fallback = 0) {
    const n = parseInt(val, 10);
    return isNaN(n) ? fallback : n;
  }

  function generateCacheKey(data) {
    try {
      const keyObj = {
        date: data.date || new Date().toDateString(),
        total_labor: data.total_labor || 0,
        total_contractors: data.total_contractors || 0,
        sub_count: Array.isArray(data.today_subcontractors) ? data.today_subcontractors.length : 0
      };
      return btoa(JSON.stringify(keyObj)).replace(/[^a-zA-Z0-9]/g, '').substring(0, 32);
    } catch {
      return "default";
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
      return ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[m];
    });
  }

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

  function isWeekend(dateString) {
    let date;
    if (dateString.includes('/')) {
      const [day, month, year] = dateString.split('/').map(Number);
      date = new Date(year, month - 1, day);
    } else {
      date = new Date(dateString);
    }
    const dayOfWeek = date.getDay();
    return dayOfWeek === 0 || dayOfWeek === 6;
  }

  function exportTableToCSV(filename) {
    const table = document.querySelector('#resourceStats table');
    if (!table) return;
    let csv = [];
    Array.from(table.rows).forEach(row => {
      let cols = Array.from(row.cells).map(cell => {
        let text = cell.textContent.replace(/(\r\n|\n|\r)/gm, '').trim();
        if (text.includes(',')) text = `"${text}"`;
        return text;
      });
      csv.push(cols.join(','));
    });
    const csvStr = csv.join('\n');
    const blob = new Blob([csvStr], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
      window.URL.revokeObjectURL(url);
      a.remove();
    }, 500);
  }
  
  // Expose public functions
  return {
    init: init,
    displayResourceStats: displayResourceStats
  };
})();

// Initialize the Resource Tracker module and expose it globally.
window.resourceTrackerInstance = ResourceTracker;
ResourceTracker.init();