/**
 * weather.js
 * 
 * Daily Activity Briefing System (DABS) - Weather Integration Module
 * 
 * WHAT THIS FILE DOES:
 * This file handles all weather-related functions for the DABS system. It retrieves
 * current weather data and forecasts for Manchester, UK using the OpenWeatherMap API,
 * formats everything in UK standards, and displays the information on the dashboard.
 * It includes special warnings for high winds that may affect tower crane operations
 * on construction sites.
 * 
 * KEY FEATURES:
 * - Current weather conditions display with UK units (°C, mph)
 * - 7-day forecast with daily high/low temperatures
 * - High wind alerts for crane operations using UK wind speed thresholds
 * - All dates and times in UK format (DD/MM/YYYY HH:MM) using Europe/London timezone
 * - Automatic periodic refresh of weather data
 * - Responsive design for all device sizes
 * 
 * Current Date and Time (UK Format): 03/06/2025 07:48:08
 * Current User's Login: irlamkeep
 * 
 * @author irlamkeep
 * @version 1.2
 * @date 03/06/2025
 * @website dabs.defecttracker.uk
 */

// Use strict mode for better error catching and to prevent use of undeclared variables
'use strict';

// Weather module encapsulated in an immediately invoked function expression (IIFE) 
// to avoid polluting the global namespace
const WeatherModule = (function() {
    // Configuration constants for the weather module
    const CONFIG = {
        API_KEY: 'dd2124866870912d328b76d161d82efd', // Your API key
        CITY: 'Manchester,uk',
        UNITS: 'metric',        // Use metric units for UK standards (Celsius)
        REFRESH_INTERVAL: 30 * 60 * 1000, // Refresh weather every 30 minutes
        HIGH_WIND_THRESHOLD: 15, // Wind speed in mph that may affect crane operations
        SEVERE_WIND_THRESHOLD: 25, // Wind speed in mph that requires crane shutdown
        DEBUG: true // Enable debug logging
    };
    
    // Cache references to DOM elements to improve performance
    let $weatherWidget = null;
    let $weatherRefreshTime = null;
    let weatherData = null;
    let weatherTimer = null;
    
    /**
     * Log debug messages if debugging is enabled
     * 
     * @param {string} message - Message to log
     * @param {*} data - Optional data to log
     */
    function debugLog(message, data = null) {
        if (CONFIG.DEBUG) {
            if (data) {
                console.log(`[Weather] ${message}`, data);
            } else {
                console.log(`[Weather] ${message}`);
            }
        }
    }
    
    /**
     * Initialize the weather module
     * Sets up DOM references and starts initial data fetch
     */
    function init() {
        debugLog('Initializing Weather Module...');
        
        // Get DOM references
        $weatherWidget = document.getElementById('weatherWidget');
        
        // Check if we found the weather widget
        if ($weatherWidget) {
            debugLog('Weather widget found in DOM');
            
            // Fetch weather data immediately
            fetchWeatherData();
            
            // Set up periodic refresh
            weatherTimer = setInterval(fetchWeatherData, CONFIG.REFRESH_INTERVAL);
            
            // Add event listener for manual refresh
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    // Page is now visible - check if we need to refresh
                    const lastUpdate = localStorage.getItem('weatherLastUpdate');
                    if (lastUpdate && Date.now() - lastUpdate > CONFIG.REFRESH_INTERVAL) {
                        debugLog('Page became visible, refreshing weather data');
                        fetchWeatherData();
                    }
                }
            });
        } else {
            console.error('[Weather] Weather widget element not found in DOM. Make sure an element with ID "weatherWidget" exists.');
        }
    }
    
    /**
     * Fetch weather data from OpenWeatherMap API
     * Gets both current weather and forecast data
     */
    function fetchWeatherData() {
        debugLog('Fetching weather data...');
        
        // Show loading state
        displayLoadingState();
        
        // Construct URLs for current weather and forecast data
        const currentWeatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${CONFIG.CITY}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        const forecastUrl = `https://api.openweathermap.org/data/2.5/forecast?q=${CONFIG.CITY}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        
        debugLog('Current weather URL (API key hidden)', currentWeatherUrl.replace(CONFIG.API_KEY, 'API_KEY_HIDDEN'));
        debugLog('Forecast URL (API key hidden)', forecastUrl.replace(CONFIG.API_KEY, 'API_KEY_HIDDEN'));
        
        // Create promises for both API calls
        const currentWeatherPromise = fetch(currentWeatherUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Weather API returned status ${response.status}: ${response.statusText}`);
                }
                return response.json();
            });
            
        const forecastPromise = fetch(forecastUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Forecast API returned status ${response.status}: ${response.statusText}`);
                }
                return response.json();
            });
        
        // Process both responses when they're available
        Promise.all([currentWeatherPromise, forecastPromise])
            .then(([currentData, forecastData]) => {
                debugLog('Weather data received', { current: currentData, forecast: forecastData });
                
                // Store the combined data
                weatherData = {
                    current: currentData,
                    forecast: forecastData
                };
                
                // Record last update time
                localStorage.setItem('weatherLastUpdate', Date.now().toString());
                
                // Display the weather data
                displayWeatherData();
            })
            .catch(error => {
                console.error('[Weather] Error fetching weather data:', error);
                displayErrorState(error.message);
            });
    }
    
    /**
     * Display loading state in the weather widget
     */
    function displayLoadingState() {
        if ($weatherWidget) {
            debugLog('Displaying loading state');
            $weatherWidget.innerHTML = `
                <div class="d-flex justify-content-center align-items-center h-100 p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading weather data...</span>
                    </div>
                    <span class="ms-2">Loading Manchester weather data...</span>
                </div>
            `;
        }
    }
    
    /**
     * Display error state in the weather widget
     * 
     * @param {string} errorMessage - Optional error message to display
     */
    function displayErrorState(errorMessage = '') {
        if ($weatherWidget) {
            debugLog('Displaying error state', errorMessage);
            $weatherWidget.innerHTML = `
                <div class="alert alert-warning m-2">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span>Weather data could not be loaded. ${errorMessage}</span>
                    <button class="btn btn-sm btn-outline-dark ms-3" onclick="WeatherModule.fetchWeatherData()">
                        <i class="fas fa-sync-alt"></i> Retry
                    </button>
                </div>
                <div class="small text-muted mt-2 px-2">
                    <p>If this problem persists, please check:</p>
                    <ul>
                        <li>Internet connection is working</li>
                        <li>API key is valid and has sufficient quota</li>
                        <li>OpenWeatherMap service is available</li>
                    </ul>
                </div>
            `;
        }
    }
    
    /**
     * Format a UTC timestamp to UK date/time format
     * Displays time in DD/MM/YYYY HH:MM format
     * 
     * @param {number} timestamp - UTC timestamp in milliseconds
     * @param {boolean} timeOnly - Whether to display only the time (not the date)
     * @returns {string} Formatted date/time string
     */
    function formatUKDateTime(timestamp, timeOnly = false) {
        // Create a date object with the UTC timestamp
        const date = new Date(timestamp);
        
        // Create formatter options for UK date/time format
        const options = {
            timeZone: 'Europe/London',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false // Use 24-hour format as standard in UK
        };
        
        // Add date parts if not timeOnly
        if (!timeOnly) {
            options.day = '2-digit';
            options.month = '2-digit';
            options.year = 'numeric';
        }
        
        // Format the date using Intl.DateTimeFormat
        let formatted = new Intl.DateTimeFormat('en-GB', options).format(date);
        
        // Return the formatted date/time string
        return formatted;
    }
    
    /**
     * Get day name from timestamp
     * 
     * @param {number} timestamp - UTC timestamp in milliseconds
     * @returns {string} Day name (e.g., "Monday")
     */
    function getDayName(timestamp) {
        const date = new Date(timestamp);
        // Format as day name in UK English
        return new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/London', weekday: 'long' }).format(date);
    }
    
    /**
     * Convert wind degrees to cardinal direction (N, NE, E, etc.)
     * This is the standard format for wind direction in UK weather reports
     * 
     * @param {number} degrees - Wind direction in degrees
     * @returns {string} Cardinal direction (N, NE, E, etc.)
     */
    function getWindDirection(degrees) {
        // Array of cardinal directions in clockwise order
        const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 
                            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        
        // Calculate the direction index (divide the circle into 16 parts)
        const index = Math.round(degrees / 22.5) % 16;
        
        return directions[index];
    }
    
    /**
     * Check if wind speed requires an alert
     * Different thresholds for different alert levels (using mph which is standard in UK)
     * 
     * @param {number} windSpeed - Wind speed in mph
     * @returns {object|null} Alert data or null if no alert
     */
    function getWindAlert(windSpeed) {
        if (windSpeed >= CONFIG.SEVERE_WIND_THRESHOLD) {
            return {
                level: 'danger',
                message: 'SEVERE WIND ALERT: Tower crane operations should be suspended.',
                icon: 'fa-exclamation-triangle'
            };
        } else if (windSpeed >= CONFIG.HIGH_WIND_THRESHOLD) {
            return {
                level: 'warning',
                message: 'HIGH WIND ALERT: Tower crane operations require caution.',
                icon: 'fa-wind'
            };
        }
        return null;
    }
    
    /**
     * Create a forecast card for a single day
     * All measurements in UK format (°C, mph)
     * 
     * @param {object} dayForecast - Forecast data for a single day
     * @returns {string} HTML for the forecast card
     */
    function createForecastCard(dayForecast) {
        // Get wind alert if applicable
        const windAlert = getWindAlert(dayForecast.wind);
        
        // Capitalize first letter of description
        const description = dayForecast.description.charAt(0).toUpperCase() + dayForecast.description.slice(1);
        
        // Create the card HTML
        return `
            <div class="forecast-day">
                <div class="forecast-date">${dayForecast.day}</div>
                <div class="forecast-icon">
                    <img src="https://openweathermap.org/img/wn/${dayForecast.icon}@2x.png" alt="${description}" class="weather-icon">
                </div>
                <div class="forecast-temps">
                    <span class="forecast-high">${Math.round(dayForecast.tempMax)}°C</span> / 
                    <span class="forecast-low">${Math.round(dayForecast.tempMin)}°C</span>
                </div>
                <div class="forecast-description">${description}</div>
                <div class="forecast-wind">
                    <i class="fas fa-wind"></i> ${Math.round(dayForecast.wind)} mph ${getWindDirection(dayForecast.windDeg)}
                </div>
                ${windAlert ? `
                <div class="forecast-alert alert alert-${windAlert.level} mt-2">
                    <i class="fas ${windAlert.icon}"></i> ${windAlert.message}
                </div>
                ` : ''}
            </div>
        `;
    }
    
    /**
     * Process forecast data to get daily forecasts
     * OpenWeatherMap free API provides 3-hour forecasts, so we need to group them by day
     * 
     * @param {object} forecastData - Raw forecast data from API
     * @returns {Array} Array of daily forecasts
     */
    function processDailyForecasts(forecastData) {
        debugLog('Processing forecast data');
        
        const dailyForecasts = [];
        const dayMap = new Map();
        
        try {
            // Process each 3-hour forecast period
            forecastData.list.forEach(period => {
                // Get the date (without time) in UK timezone
                const date = new Date(period.dt * 1000);
                const ukDate = new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Europe/London',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }).format(date);
                
                // Skip today (since we show it separately)
                if (ukDate === formatUKDateTime(Date.now()).split(' ')[0]) {
                    return;
                }
                
                // Initialize this day's forecast if we haven't seen it yet
                if (!dayMap.has(ukDate)) {
                    dayMap.set(ukDate, {
                        date: date.getTime(),
                        day: getDayName(date),
                        tempMin: period.main.temp_min,
                        tempMax: period.main.temp_max,
                        wind: convertToMPH(period.wind.speed),
                        windDeg: period.wind.deg,
                        windMax: convertToMPH(period.wind.speed),
                        description: period.weather[0].description,
                        icon: period.weather[0].icon,
                        rain: period.rain ? period.rain['3h'] || 0 : 0,
                        periods: []
                    });
                }
                
                // Get the current day's forecast
                const dayForecast = dayMap.get(ukDate);
                
                // Update min/max values if needed
                if (period.main.temp_min < dayForecast.tempMin) dayForecast.tempMin = period.main.temp_min;
                if (period.main.temp_max > dayForecast.tempMax) dayForecast.tempMax = period.main.temp_max;
                
                // Convert wind speed to mph for UK format
                const windSpeedMPH = convertToMPH(period.wind.speed);
                
                if (windSpeedMPH > dayForecast.windMax) {
                    dayForecast.windMax = windSpeedMPH;
                    dayForecast.windDeg = period.wind.deg;
                    // Update description and icon to reflect the highest wind period
                    dayForecast.description = period.weather[0].description;
                    dayForecast.icon = period.weather[0].icon;
                }
                
                // Add this period to the day's periods array
                dayForecast.periods.push({
                    time: formatUKDateTime(date.getTime(), true),
                    temp: period.main.temp,
                    wind: windSpeedMPH,
                    windDeg: period.wind.deg,
                    description: period.weather[0].description,
                    icon: period.weather[0].icon,
                    rain: period.rain ? period.rain['3h'] || 0 : 0
                });
            });
            
            // Convert the map to an array and sort by date
            dayMap.forEach(forecast => dailyForecasts.push(forecast));
            dailyForecasts.sort((a, b) => a.date - b.date);
            
            debugLog(`Processed ${dailyForecasts.length} days of forecast data`);
            
            // Return up to 7 days
            return dailyForecasts.slice(0, 7);
        } catch (error) {
            console.error('[Weather] Error processing forecast data:', error);
            return [];
        }
    }
    
    /**
     * Convert wind speed from m/s to mph
     * UK standard displays wind speed in mph, not km/h
     * 
     * @param {number} speedMS - Wind speed in meters per second
     * @returns {number} Wind speed in miles per hour
     */
    function convertToMPH(speedMS) {
        // Convert from m/s to mph (1 m/s = 2.23694 mph)
        return speedMS * 2.23694;
    }
    
    /**
     * Display the weather data in the widget
     * Formats and renders current conditions and forecast in UK format
     */
    function displayWeatherData() {
        debugLog('Displaying weather data');
        
        if (!weatherData || !$weatherWidget) {
            debugLog('No weather data or widget element');
            return;
        }
        
        try {
            const { current, forecast } = weatherData;
            
            // Process the current weather data
            const currentTemp = Math.round(current.main.temp);  // Already in °C from API
            const feelsLike = Math.round(current.main.feels_like);  // Already in °C from API
            const windSpeed = Math.round(convertToMPH(current.wind.speed)); // Convert m/s to mph
            const windDirection = getWindDirection(current.wind.deg);
            const weatherDescription = current.weather[0].description.charAt(0).toUpperCase() + 
                                      current.weather[0].description.slice(1); // Capitalize first letter
            const weatherIcon = current.weather[0].icon;
            const humidity = current.main.humidity;
            const pressure = current.main.pressure;
            const visibility = current.visibility ? `${(current.visibility / 1609.34).toFixed(1)} miles` : 'N/A';
            
            // Check for wind alerts
            const windAlert = getWindAlert(windSpeed);
            
            // Process forecast data to get daily forecasts
            const dailyForecasts = processDailyForecasts(forecast);
            
            // Format the last update time in UK format
            const lastUpdateTime = formatUKDateTime(current.dt * 1000);
            
            debugLog('Building weather HTML');
            
            // Build HTML for the weather widget with UK units - compact horizontal layout
            let html = `
                <div class="weather-container">
                    <!-- Current Weather Section - Compact Horizontal -->
                    <div class="current-weather">
                        <div class="row align-items-center">
                            <div class="col-lg-3 col-md-4 text-center">
                                <img src="https://openweathermap.org/img/wn/${weatherIcon}@2x.png" alt="${weatherDescription}" class="weather-icon" style="max-width: 80px;">
                                <div class="current-temp fs-3 fw-bold">${currentTemp}°C</div>
                                <div class="weather-description small">${weatherDescription}</div>
                            </div>
                            <div class="col-lg-9 col-md-8">
                                <div class="row g-2">
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-temperature-high me-1"></i> Feels: ${feelsLike}°C</small>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-wind me-1"></i> Wind: ${windSpeed} mph ${windDirection}</small>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-tint me-1"></i> Humidity: ${humidity}%</small>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-eye me-1"></i> Visibility: ${visibility}</small>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-compress-alt me-1"></i> Pressure: ${pressure} hPa</small>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <small><i class="fas fa-clock me-1"></i> Updated: ${formatUKDateTime(current.dt * 1000, true)}</small>
                                    </div>
                                </div>`;
            
            // Add wind alert if applicable
            if (windAlert) {
                html += `
                                <div class="alert alert-${windAlert.level} py-2 px-3 mt-2 mb-0">
                                    <small><i class="fas ${windAlert.icon} me-1"></i><strong>${windAlert.message}</strong></small>
                                </div>`;
            }
            
            html += `
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weekly Forecast Section - Horizontal Scrollable -->
                    <hr class="my-3">
                    <h6 class="mb-2">7-Day Forecast</h6>
                    <div class="weekly-forecast d-flex overflow-auto pb-2" style="gap: 10px;">`;
            
            // Add daily forecast cards - more compact
            if (dailyForecasts.length > 0) {
                dailyForecasts.forEach(dayForecast => {
                    const description = dayForecast.description.charAt(0).toUpperCase() + dayForecast.description.slice(1);
                    const windAlert = getWindAlert(dayForecast.wind);
                    html += `
                        <div class="forecast-day-compact text-center p-2 border rounded" style="min-width: 100px; flex-shrink: 0;">
                            <div class="small fw-bold">${dayForecast.day.substring(0, 3)}</div>
                            <img src="https://openweathermap.org/img/wn/${dayForecast.icon}.png" alt="${description}" style="width: 40px;">
                            <div class="small">
                                <span class="fw-bold">${Math.round(dayForecast.tempMax)}°</span> / ${Math.round(dayForecast.tempMin)}°
                            </div>
                            <div class="small text-muted" style="font-size: 0.7rem;">
                                <i class="fas fa-wind"></i> ${Math.round(dayForecast.wind)} mph
                            </div>
                            ${windAlert ? `<div class="badge bg-${windAlert.level === 'danger' ? 'danger' : 'warning'} mt-1" style="font-size: 0.65rem;"><i class="fas fa-exclamation-triangle"></i></div>` : ''}
                        </div>`;
                });
            } else {
                html += `<p class="text-center w-100 small">Forecast data not available</p>`;
            }
            
            html += `
                    </div>
                </div>`;
            
            // Update the widget with our HTML
            $weatherWidget.innerHTML = html;
            debugLog('Weather data displayed successfully');
        } catch (error) {
            console.error('[Weather] Error displaying weather data:', error);
            displayErrorState(`Error displaying weather: ${error.message}`);
        }
    }
    
    // Return public methods
    return {
        init: init,
        fetchWeatherData: fetchWeatherData
    };
})();

// Initialize the weather module when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Weather] DOM loaded, initializing weather module');
    WeatherModule.init();
});