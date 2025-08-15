/**
 * Phosphor Icons Data
 * This file contains a curated list of commonly used Phosphor icons
 * Documentation: https://phosphoricons.com/
 */

const eakcIconData = {
    regular: [
        // Common UI & Navigation
        'ph-house', 'ph-magnifying-glass', 'ph-user', 'ph-gear', 'ph-bell', 'ph-envelope',
        'ph-calendar', 'ph-clock', 'ph-star', 'ph-heart', 'ph-thumbs-up', 'ph-chat-circle',
        'ph-arrow-right', 'ph-arrow-left', 'ph-arrow-up', 'ph-arrow-down', 'ph-caret-right',
        'ph-caret-left', 'ph-caret-up', 'ph-caret-down', 'ph-check', 'ph-x', 'ph-plus', 'ph-minus',
        
        // Energy & Environment
        'ph-lightning', 'ph-lightbulb', 'ph-sun', 'ph-moon', 'ph-cloud-sun', 'ph-wind',
        'ph-drop', 'ph-fire', 'ph-leaf', 'ph-tree', 'ph-flower', 'ph-plant',
        'ph-plug', 'ph-battery-full', 'ph-battery-charging', 'ph-solar-panel', 'ph-factory',
        'ph-thermometer', 'ph-gauge', 'ph-lightning-slash', 'ph-power',
        
        // Documents & Education
        'ph-file', 'ph-file-text', 'ph-file-pdf', 'ph-book', 'ph-book-open', 'ph-notebook',
        'ph-graduation-cap', 'ph-newspaper', 'ph-clipboard-text', 'ph-pen', 'ph-pencil',
        'ph-folder', 'ph-folder-open', 'ph-archive', 'ph-file-doc', 'ph-certificate',
        'ph-bookmark', 'ph-bookmark-simple', 'ph-note', 'ph-note-pencil', 'ph-article',
        
        // Business & Finance
        'ph-chart-line', 'ph-chart-bar', 'ph-chart-pie-slice', 'ph-trend-up', 'ph-trend-down',
        'ph-currency-dollar', 'ph-coins', 'ph-money', 'ph-briefcase', 'ph-buildings',
        'ph-handshake', 'ph-scales', 'ph-gavel', 'ph-bank', 'ph-calculator',
        'ph-receipt', 'ph-invoice', 'ph-wallet', 'ph-piggy-bank', 'ph-credit-card',
        
        // Technology & Innovation
        'ph-desktop', 'ph-laptop', 'ph-device-mobile', 'ph-tablet', 'ph-monitor',
        'ph-wifi-high', 'ph-database', 'ph-cloud', 'ph-cloud-arrow-up', 'ph-cloud-arrow-down',
        'ph-cpu', 'ph-robot', 'ph-code', 'ph-terminal-window', 'ph-globe',
        'ph-broadcast', 'ph-rss', 'ph-link', 'ph-git-branch', 'ph-rocket',
        
        // People & Teams
        'ph-users', 'ph-users-three', 'ph-user-circle', 'ph-user-plus', 'ph-user-gear',
        'ph-identification-card', 'ph-address-book', 'ph-hand-waving', 'ph-handshake',
        'ph-hands-clapping', 'ph-person', 'ph-person-simple-walk', 'ph-hard-hat',
        
        // Transportation & Infrastructure
        'ph-car', 'ph-truck', 'ph-bus', 'ph-train', 'ph-airplane', 'ph-boat',
        'ph-bicycle', 'ph-charging-station', 'ph-gas-pump', 'ph-traffic-sign',
        'ph-road-horizon', 'ph-bridge', 'ph-lighthouse', 'ph-factory',
        
        // Communication
        'ph-phone', 'ph-phone-call', 'ph-chat', 'ph-chats', 'ph-video-camera',
        'ph-microphone', 'ph-megaphone', 'ph-broadcast', 'ph-antenna', 'ph-rss-simple',
        'ph-envelope', 'ph-envelope-open', 'ph-paper-plane', 'ph-telegram-logo',
        
        // Tools & Settings
        'ph-wrench', 'ph-screwdriver', 'ph-hammer', 'ph-toolbox', 'ph-gear-six',
        'ph-sliders', 'ph-faders', 'ph-equalizer', 'ph-toggle-left', 'ph-toggle-right',
        
        // Info & Status
        'ph-info', 'ph-question', 'ph-warning', 'ph-warning-circle', 'ph-check-circle',
        'ph-x-circle', 'ph-shield', 'ph-shield-check', 'ph-lock', 'ph-lock-open',
        'ph-key', 'ph-fingerprint', 'ph-eye', 'ph-eye-slash', 'ph-flag',
        
        // Maps & Location
        'ph-map-pin', 'ph-map-trifold', 'ph-navigation-arrow', 'ph-compass', 'ph-globe-hemisphere-west',
        'ph-signpost', 'ph-path', 'ph-target', 'ph-crosshair', 'ph-map-pin-line',
        
        // Actions
        'ph-download', 'ph-upload', 'ph-share', 'ph-share-network', 'ph-printer',
        'ph-floppy-disk', 'ph-trash', 'ph-pencil-simple', 'ph-copy', 'ph-scissors',
        'ph-paperclip', 'ph-link-simple', 'ph-play', 'ph-pause', 'ph-stop'
    ],
    
    fill: [
        // Common filled versions
        'ph-house-fill', 'ph-user-fill', 'ph-gear-fill', 'ph-bell-fill', 'ph-envelope-fill',
        'ph-star-fill', 'ph-heart-fill', 'ph-thumbs-up-fill', 'ph-chat-circle-fill',
        
        // Energy filled
        'ph-lightning-fill', 'ph-lightbulb-fill', 'ph-sun-fill', 'ph-moon-fill', 'ph-drop-fill',
        'ph-fire-fill', 'ph-leaf-fill', 'ph-battery-full-fill', 'ph-plug-fill',
        
        // Documents filled
        'ph-file-fill', 'ph-file-text-fill', 'ph-book-fill', 'ph-folder-fill', 'ph-bookmark-simple-fill',
        
        // Business filled
        'ph-chart-line-fill', 'ph-chart-bar-fill', 'ph-chart-pie-slice-fill', 'ph-briefcase-fill',
        
        // Status filled
        'ph-info-fill', 'ph-question-fill', 'ph-warning-fill', 'ph-check-circle-fill', 'ph-x-circle-fill',
        'ph-shield-fill', 'ph-lock-fill', 'ph-flag-fill'
    ]
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = eakcIconData;
}