// (Listening for the events)
self.addEventListener('install', function (event) {
    // (Pushing the message)
    console.log('Service Worker :: Installed');
});

self.addEventListener('activate', function (event) {
    // (Pushing the message)
    console.log('Service Worker :: Activated');
});

self.addEventListener('fetch', function (event) {
    // (Pushing the message)
    console.log(`Service Worker :: Fetching '${ event.request.url }'`);
});