const cacheName = 'kseven-cv-v1-0';
const assets = [
    './', 
    './index.html', 
    './style.css', 
    './script.js', 
    './photo.jpeg', 
    './favicon.jpg',
    './screenshot-wide.jpg', 
    './screenshot-mobile.jpg'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(cacheName).then(cache => cache.addAll(assets)));
});

self.addEventListener('fetch', e => {
  e.respondWith(caches.match(e.request).then(res => res || fetch(e.request)));
});