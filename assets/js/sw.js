const cacheName = 'kseven-cv-v1-0';
const offlinePage = './offline.html'; // Página de fallback
const assets = [
    './', 
    './index.html', 
    './style.css', 
    './script.js', 
    './photo.jpeg', 
    './favicon.jpg',
    './screenshot-wide.jpg', 
    './screenshot-mobile.jpg',
    offlinePage // Adicionado aos assets
];

// Instalação: Cacheia todos os recursos essenciais
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(cacheName).then(cache => {
      console.log('PWA: Cacheando arquivos e página offline');
      return cache.addAll(assets);
    })
  );
  self.skipWaiting();
});

// Ativação: Limpa caches antigos se você mudar o cacheName
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== cacheName).map(key => caches.delete(key))
      );
    })
  );
});

// Fetch: Tenta Cache -> Tenta Rede -> Se falhar, entrega Offline
self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(res => {
      return res || fetch(e.request).catch(() => {
        // Se a navegação falhar (ex: usuário clicou em link sem rede)
        if (e.request.mode === 'navigate') {
          return caches.match(offlinePage);
        }
      });
    })
  );
});