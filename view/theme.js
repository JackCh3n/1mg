/**
 * 1mg 皮肤系统
 * 优先级: 访客手动选择(localStorage) > 系统明暗偏好(prefers-color-scheme) > 站点默认(后台设置)
 * - 任意带 data-mg-theme-toggle 的按钮: 明暗切换
 * - window.MGTheme 供其他脚本调用
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
    //空字符串 = 跟随系统(用户没有手动选过)
    if (typeof state.skin === 'undefined' || state.skin === null) state.skin = '';
    if (typeof state.accent === 'undefined') state.accent = root.getAttribute('data-default-accent') || '';

    var siteDefault = root.getAttribute('data-default-skin') || 'light';
    var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function effectiveSkin() {
        if (state.skin === 'dark' || state.skin === 'light') return state.skin;
        if (mq) return mq.matches ? 'dark' : 'light';
        return siteDefault;
    }

    function apply() {
        root.setAttribute('data-skin', effectiveSkin());
        if (state.accent && /^#[0-9a-fA-F]{6}$/.test(state.accent)) {
            root.style.setProperty('--accent', state.accent);
        } else {
            root.style.removeProperty('--accent');
        }
        document.querySelectorAll('[data-mg-theme-toggle]').forEach(function (el) {
            var dark = effectiveSkin() === 'dark';
            el.setAttribute('aria-label', dark ? '切换到明亮模式' : '切换到暗黑模式');
            var moon = el.querySelector('.icon-moon'), sun = el.querySelector('.icon-sun');
            if (moon && sun) { moon.style.display = dark ? '' : 'none'; sun.style.display = dark ? 'none' : ''; }
        });
    }

    window.MGTheme = {
        skin: effectiveSkin,
        manual: function () { return state.skin || ''; },
        setSkin: function (s) { state.skin = (s === 'dark') ? 'dark' : 'light'; save(state); apply(); },
        toggle: function () { this.setSkin(effectiveSkin() === 'dark' ? 'light' : 'dark'); },
        auto: function () { state.skin = ''; save(state); apply(); },
        accent: function () { return state.accent; },
        setAccent: function (a) { state.accent = a || ''; save(state); apply(); }
    };

    apply();

    //跟随系统: 用户未手动选择时,系统切换明暗即时生效
    if (mq && mq.addEventListener) {
        mq.addEventListener('change', function () { if (!state.skin) apply(); });
    }

    document.addEventListener('click', function (ev) {
        var t = ev.target.closest ? ev.target.closest('[data-mg-theme-toggle]') : null;
        if (t) { window.MGTheme.toggle(); }
    });
})();
