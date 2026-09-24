// PayMe PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((registration) => {
                // Service worker registered successfully
            })
            .catch((error) => {
                console.warn('PayMe PWA SW registration skipped:', error);
            });
    });
}
