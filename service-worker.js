self.addEventListener('install', (event) => {
    event.waitUntil(
      caches.open('your-app-cache').then((cache) => {
        return cache.addAll([
          '/',
          'index.php',
          '/header.php',
          '/footer.php',
          '/footer.css',
          '/index.css',
          '/header.css',
          '/main.js',
          '/default.css',
          'assets/images/sys-logo.png',
          'assets/images/sys-logo.png'
        ]);
      })
    );
  });
  
  self.addEventListener('fetch', (event) => {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        return cachedResponse || fetch(event.request);
      })
    );
  });
  