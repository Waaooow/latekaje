@auth
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@2.7.4/dist/echo.iife.js"></script>
<script>
(function () {
    var KEY = @json(config('broadcasting.connections.reverb.key'));
    var booted = false;

    function boot() {
        if (booted) return;
        if (!KEY) return;
        if (!window.Pusher || !window.Echo) {
            setTimeout(boot, 500);
            return;
        }
        booted = true;

        try {
            var echo = new Echo({
                broadcaster: 'reverb',
                key: KEY,
                wsHost: window.location.hostname,
                wsPort: 8446,
                wssPort: 8446,
                forceTLS: window.location.protocol === 'https:',
                enabledTransports: ['ws', 'wss'],
            });

            echo.channel('latekaje-lab').listen('.loan.activity', function (e) {
                try {
                    if (window.Livewire) {
                        Livewire.all().forEach(function (c) {
                            try { c.$refresh(); } catch (err) {}
                        });
                    }
                } catch (err) {}

                var msg = e.kind === 'return'
                    ? @json(__('loans.ws_toast_return')).replace(':borrower', e.borrower_name).replace(':code', e.itemCode)
                    : @json(__('loans.ws_toast_borrow')).replace(':borrower', e.borrower_name).replace(':group', e.group).replace(':code', e.itemCode);
                toast(msg);
            });
        } catch (err) {}
    }

    function toast(msg) {
        var box = document.getElementById('latekaje-ws-toasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'latekaje-ws-toasts';
            box.style.cssText = 'position:fixed;bottom:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:8px;max-width:min(360px,90vw);';
            document.body.appendChild(box);
        }
        var el = document.createElement('div');
        el.style.cssText = 'background:#111827;color:#fff;padding:10px 14px;border-radius:10px;font-size:13px;box-shadow:0 4px 14px rgba(0,0,0,.3);';
        el.textContent = msg;
        box.appendChild(el);
        setTimeout(function () { el.remove(); }, 6000);
    }

    if (document.readyState === 'complete') boot();
    else window.addEventListener('load', boot);
})();
</script>
@endauth
