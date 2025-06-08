<?php
/**
 * Provides the admin-facing view for the Analytics Dashboard.
 *
 * @link       https://novelnetware.com/
 * @since      1.3.0
 *
 * @package    Novel_AI_Chatbot
 * @subpackage Novel_AI_Chatbot/admin/partials
 */
?>

<div class="wrap" id="analytics-app">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p><?php _e( 'آمار و ارقام کلیدی عملکرد چت‌بات خود را در اینجا مشاهده کنید.', 'novel-ai-chatbot' ); ?></p>

    <div id="analytics-loader" style="display: none; text-align: center; padding: 50px;">
        <p>در حال بارگذاری داده‌های تحلیلی...</p>
    </div>

    <div id="analytics-content" style="display: none;">
        <div class="summary-stats">
            <div class="stat-card">
                <h4>کل گفتگوها</h4>
                <p id="stat-total-conversations">۰</p>
            </div>
            <div class="stat-card">
                <h4>کل پیام‌ها</h4>
                <p id="stat-total-messages">۰</p>
            </div>
            <div class="stat-card">
                <h4>میانگین امتیاز</h4>
                <p id="stat-average-rating">۰</p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-container">
                <h3>گفتگوهای ۳۰ روز گذشته</h3>
                <canvas id="daily-chats-chart"></canvas>
            </div>
            <div class="chart-container">
                <h3>توزیع وضعیت چت‌ها</h3>
                <canvas id="status-distribution-chart"></canvas>
            </div>
            <div class="chart-container">
                <h3>توزیع امتیازات</h3>
                <canvas id="ratings-distribution-chart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
    .summary-stats { display: flex; gap: 20px; margin-bottom: 30px; }
    .stat-card { flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; text-align: center; }
    .stat-card h4 { margin: 0 0 10px 0; }
    .stat-card p { font-size: 2em; font-weight: bold; margin: 0; color: #28a745; }
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .chart-container { background: #fff; padding: 20px; border: 1px solid #ddd; }
    @media (max-width: 960px) {
        .charts-grid { grid-template-columns: 1fr; }
    }
</style>