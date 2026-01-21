    window.addEventListener('load', () => {
        document.getElementById('loader').style.display = 'none';
        document.body.style.opacity = '1';
    });

    function toggleMenu() { document.getElementById('mainMenu').classList.toggle('hidden'); }

    function setLang(lang) {
        document.body.className = 'lang-' + lang;
        document.querySelectorAll('#btn-pt, #btn-en').forEach(b => b.classList.remove('active'));
        document.getElementById('btn-' + lang).classList.add('active');
    }

    function filterCV(type, btn) {
        btn.parentNode.querySelectorAll('.btn-cv').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.job-card, .cert-card, .skill-list').forEach(el => {
            const elType = el.getAttribute('data-type') || "";
            if (type === 'all') { el.classList.remove('dim', 'highlight'); }
            else if (elType.includes(type)) { el.classList.remove('dim'); el.classList.add('highlight'); }
            else { el.classList.add('dim'); el.classList.remove('highlight'); }
        });
    }

    function generatePDF() {
        document.getElementById('mainMenu').classList.add('hidden');
        setTimeout(() => { window.print(); }, 300);
    }

    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('active'); });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(r => obs.observe(r));