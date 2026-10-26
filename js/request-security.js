/* Send the session CSRF token only to this DABS origin. */
(() => {
    const token = document.querySelector('meta[name="dabs-csrf"]')?.content;
    if (!token) return;
    const unsafe = method => !['GET', 'HEAD', 'OPTIONS'].includes(method.toUpperCase());
    const local = url => new URL(url, location.href).origin === location.origin;
    const fetchRequest = window.fetch;
    window.fetch = function (input, init = {}) {
        const request = input instanceof Request ? input : null;
        const method = init.method || request?.method || 'GET';
        if (unsafe(method) && local(request?.url || input)) {
            const headers = new Headers(init.headers || request?.headers);
            headers.set('X-DABS-CSRF', token);
            init = { ...init, headers };
        }
        return fetchRequest.call(this, input, init);
    };
    const open = XMLHttpRequest.prototype.open;
    const send = XMLHttpRequest.prototype.send;
    const requests = new WeakMap();
    XMLHttpRequest.prototype.open = function (method, url, ...args) {
        requests.set(this, unsafe(method) && local(url));
        return open.call(this, method, url, ...args);
    };
    XMLHttpRequest.prototype.send = function (...args) {
        if (requests.get(this)) this.setRequestHeader('X-DABS-CSRF', token);
        return send.apply(this, args);
    };
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !unsafe(form.method) || !local(form.action)) return;
        let field = form.querySelector('input[name="csrf_token"]');
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'csrf_token';
            form.append(field);
        }
        field.value = token;
    }, true);
})();
