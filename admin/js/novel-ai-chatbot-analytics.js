import chartJs from 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/+esm'

// The script runs automatically as a module, so DOMContentLoaded is not needed.
const app = document.getElementById('analytics-app');
if (app) {
    const loader = document.getElementById('analytics-loader');
    const content = document.getElementById('analytics-content');
    
    // Show loader while fetching data
    if(loader) loader.style.display = 'block';

    const params = new URLSearchParams({
        action: 'nac_get_analytics_data',
        nonce: nac_analytics_vars.nonce
    });

    fetch(nac_analytics_vars.ajax_url, { method: 'POST', body: params })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                
                // --- 1. Populate Summary Stat Cards ---
                document.getElementById('stat-total-conversations').textContent = data.summary_stats.total_conversations || '۰';
                document.getElementById('stat-total-messages').textContent = data.summary_stats.total_messages || '۰';
                document.getElementById('stat-average-rating').textContent = parseFloat(data.summary_stats.average_rating).toFixed(2) || 'N/A';
                
                // --- 2. Render Charts ---
                renderDailyChatsChart(data.daily_counts);
                renderStatusDistributionChart(data.status_dist);
                renderRatingsDistributionChart(data.ratings_dist);

                // Show content and hide loader
                if(loader) loader.style.display = 'none';
                if(content) content.style.display = 'block';

            } else {
                if(loader) loader.innerHTML = '<p>خطا در بارگذاری داده‌های تحلیلی.</p>';
            }
        })
        .catch(error => {
            console.error('Analytics Fetch Error:', error);
            if(loader) loader.innerHTML = '<p>یک خطای شبکه رخ داد.</p>';
        });

    /**
     * Renders the daily chats line chart.
     * @param {Array} apiData - Data from the backend.
     */
    function renderDailyChatsChart(apiData) {
        const ctx = document.getElementById('daily-chats-chart');
        if (!ctx || !apiData) return;

        // Create a map of the last 30 days to ensure all days are present
        const dateMap = new Map();
        for (let i = 29; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            dateMap.set(d.toISOString().split('T')[0], 0);
        }
        apiData.forEach(item => {
            dateMap.set(item.chat_date, parseInt(item.chat_count, 10));
        });

        const labels = [...dateMap.keys()].map(d => new Date(d).toLocaleDateString('fa-IR'));
        const data = [...dateMap.values()];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'تعداد گفتگوها',
                    data: data,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.2
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    }

    /**
     * Renders the chat status doughnut chart.
     * @param {Array} apiData - Data from the backend.
     */
    function renderStatusDistributionChart(apiData) {
        const ctx = document.getElementById('status-distribution-chart');
        if (!ctx || !apiData) return;

        const labels = apiData.map(item => item.status);
        const data = apiData.map(item => item.count);
        const backgroundColors = {
            bot: '#6c757d',
            resolved: '#17a2b8',
            active: '#28a745',
            pending: '#ffc107'
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.map(l => ({'bot': 'ربات', 'resolved': 'حل شده', 'active': 'فعال', 'pending': 'در انتظار'}[l] || l)),
                datasets: [{
                    label: 'توزیع وضعیت‌ها',
                    data: data,
                    backgroundColor: labels.map(l => backgroundColors[l] || '#cccccc')
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    }

    /**
     * Renders the user ratings bar chart.
     * @param {Array} apiData - Data from the backend.
     */
    function renderRatingsDistributionChart(apiData) {
        const ctx = document.getElementById('ratings-distribution-chart');
        if (!ctx || !apiData) return;
        
        const labels = ['⭐ ۱', '⭐ ۲', '⭐ ۳', '⭐ ۴', '⭐ ۵'];
        const data = Array(5).fill(0);
        apiData.forEach(item => {
            // item.rating is 1-5, array index is 0-4
            if(item.rating >= 1 && item.rating <= 5) {
               data[item.rating - 1] = item.count;
            }
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'تعداد امتیازات ثبت شده',
                    data: data,
                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
}