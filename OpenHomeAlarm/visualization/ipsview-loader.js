(function (global) {
    'use strict';

    const originResolver = typeof module !== 'undefined' && module.exports
        ? require('../../libs/helper/visualization/http-origin.js') : global.SymconHTTPOrigin;

    function dashboardURL(configuration, environment) {
        try {
            if (!configuration || typeof configuration.path !== 'string'
                || !/^\/hook\/openhomealarm\/[1-9][0-9]*$/.test(configuration.path)
                || typeof configuration.token !== 'string'
                || !/^[a-f0-9]{32}$/i.test(configuration.token)) {
                return null;
            }
            const origin = originResolver.resolve(environment);
            if (!origin) return null;
            const url = new URL(configuration.path, origin);
            if (url.origin !== origin || !/^https?:$/.test(url.protocol) || url.username || url.password) return null;
            url.searchParams.set('view', 'dashboard');
            url.searchParams.set('token', configuration.token);
            return url.href;
        } catch (_) { return null; }
    }

    function start(environment) {
        const document = environment.document;
        const frame = document.getElementById('oha-ipsview-dashboard');
        const message = document.getElementById('oha-ipsview-error');
        let configuration = {};
        try {
            configuration = JSON.parse(document.getElementById('oha-ipsview-config').textContent);
        } catch (_) { /* The visible error below remains authoritative. */ }
        const url = dashboardURL(configuration, environment);
        if (!url) {
            message.textContent = configuration.error || 'Die OpenHomeAlarm-Ansicht konnte nicht geladen werden.';
            message.hidden = false;
            return;
        }
        frame.title = configuration.title || 'OpenHomeAlarm';
        frame.src = url;
        frame.hidden = false;
    }

    if (typeof module !== 'undefined' && module.exports) module.exports = {dashboardURL, start};
    if (!global.document) return;
    if (global.document.readyState === 'loading') {
        global.document.addEventListener('DOMContentLoaded', () => start(global), {once: true});
    } else {
        start(global);
    }
}(typeof window === 'undefined' ? globalThis : window));
