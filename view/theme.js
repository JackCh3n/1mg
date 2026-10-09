/**
 * 1mg 皮肤系统
 * - 站点默认皮肤/强调色: 由 <html data-default-skin data-default-accent> 提供(后台设置)
 * - 访客自己的选择: localStorage 本地记忆, 优先于站点默认
 * - 任意带 data-mg-theme-toggle 的按钮: 明暗切换
 * - 任意带 data-mg-accent 的元素: 设置自定义强调色
 */
(function () {
    var STORE = 'mg_theme';

    function read() {
        try { return JSON.parse(localStorage.getItem(STORE) || '{}') || {}; }
        catch (e) { return {}; }
    }
    function save(t) {
        try { localStorage.setItem(STORE, JSON.stringify(t)); } catch (e) { }
    }

    var root = document.documentElement;
    var state = read();
    if (!state.skin) state.skin = root.getAttribute('data-default-skin') || 'light';
    if (typeof state.accent === 'undefined') state.accent = root.getAttribute('data-default-accent') || '';

    function apply() {
        root.setAttribute('data-skin', state.skin === 'dark' ? 'dark' : 'light');
        if (state.accent && /^#[0-9a-fA-F]{6}$/.test(state.accent)) {
            root.style.setProperty('--accent', state.accent);
        } else {
            root.style.removeProperty('--accent');
        }
        document.querySelectorAll('[data-mg-theme-toggle]').forEach(function (el) {
            var dark = state.skin === 'dark';
            el.setAttribute('aria-label', dark ? '切换到明亮模式' : '切换到暗黑模式');
            var moon = el.querySelector('.icon-moon'), sun = el.querySelector('.icon-sun');
            if (moon && sun) { moon.style.display = dark ? '' : 'none'; sun.style.display = dark ? 'none' : ''; }
        });
    }

    window.MGTheme = {
        skin: function () { return state.skin; },
        setSkin: function (s) { state.skin = (s === 'dark') ? 'dark' : 'light'; save(state); apply(); },
        toggle: function () { this.setSkin(state.skin === 'dark' ? 'light' : 'dark'); },
        accent: function () { return state.accent; },
        setAccent: function (a) { state.accent = a || ''; save(state); apply(); }
    };

    apply();

    document.addEventListener('click', function (ev) {
        var t = ev.target.closest ? ev.target.closest('[data-mg-theme-toggle]') : null;
        if (t) { window.MGTheme.toggle(); }
    });
})();
