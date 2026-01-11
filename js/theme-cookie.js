/**
 * Theme Manager - Light/Dark Mode with Cookies
 * Lưu theme preference vào cookie trình duyệt (không database)
 * AJAX - Toggle theme không reload page
 */

class ThemeCookieManager {
    constructor() {
        this.cookieName = 'theme-preference';
        this.cookieExpireDays = 365; // Cookie tồn tại 1 năm
        this.darkMode = 'dark';
        this.lightMode = 'light';
        this.init();
    }

    /**
     * Khởi tạo: Load theme từ cookie hoặc mặc định dark
     */
    init() {
        const savedTheme = this.getThemeFromCookie();
        this.applyTheme(savedTheme);
        this.setupToggleButton();
    }

    /**
     * Lấy theme từ cookie
     * @returns {string} 'dark' hoặc 'light'
     */
    getThemeFromCookie() {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${this.cookieName}=`);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return this.darkMode; // Mặc định dark
    }

    /**
     * Lưu theme vào cookie
     * @param {string} theme - 'dark' hoặc 'light'
     */
    setThemeCookie(theme) {
        const expires = new Date();
        expires.setTime(expires.getTime() + this.cookieExpireDays * 24 * 60 * 60 * 1000);
        document.cookie = `${this.cookieName}=${theme};expires=${expires.toUTCString()};path=/`;
    }

    /**
     * Apply theme vào HTML
     * - Dark mode: không thêm class
     * - Light mode: thêm class 'light-mode' vào html
     */
    applyTheme(theme) {
        const html = document.documentElement;
        
        if (theme === this.lightMode) {
            html.classList.add('light-mode');
        } else {
            html.classList.remove('light-mode');
        }
        
        // Update button text
        this.updateToggleButtonUI(theme);
    }

    /**
     * Toggle giữa light và dark mode
     * - Dùng onclick, không cần event listener phức tạp
     */
    toggleTheme() {
        const currentTheme = this.getThemeFromCookie();
        const newTheme = currentTheme === this.lightMode ? this.darkMode : this.lightMode;
        
        // Lưu cookie
        this.setThemeCookie(newTheme);
        
        // Apply theme
        this.applyTheme(newTheme);
        
        // Dispatch event để các component khác có thể lắng nghe
        window.dispatchEvent(new CustomEvent('themeToggled', { 
            detail: { theme: newTheme } 
        }));
    }

    /**
     * Setup toggle button listener
     */
    setupToggleButton() {
        const btn = document.getElementById('theme-toggle-btn');
        if (btn) {
            btn.addEventListener('click', () => this.toggleTheme());
        }
    }

    /**
     * Update toggle button UI text
     */
    updateToggleButtonUI(theme) {
        const btn = document.getElementById('theme-toggle-btn');
        if (!btn) return;
        
        if (theme === this.lightMode) {
            btn.textContent = '🌙';
            btn.title = 'Chuyển sang Dark Mode';
        } else {
            btn.textContent = '☀️';
            btn.title = 'Chuyển sang Light Mode';
        }
    }

    /**
     * Getter: Lấy theme hiện tại
     */
    getCurrentTheme() {
        return this.getThemeFromCookie();
    }
}

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeCookieManager();
});
