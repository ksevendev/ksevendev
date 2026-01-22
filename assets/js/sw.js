const cacheName = 'kseven-cv1-2';
const assets = [
    '../../index.html',
    '../../offline.html',
    '../../signature.html',
    '../css/style.css',
    './script.js',
    '../img/favicon.png',
    '../img/favicon-192.png',
    '../img/favicon-512.png',
    '../img/screenshot-wide-1280.png',
    '../img/screenshot-mobile-1334.png'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(cacheName).then(cache => cache.addAll(assets)));
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => {
      return res || fetch(e.request).catch(() => caches.match('../../offline.html'));
    })
  );
});