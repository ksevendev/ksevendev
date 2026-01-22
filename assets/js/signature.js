
    async function gerarPDF() {
        const elementoCV = document.getElementById('seu-template-cv');
        const canvas = await html2canvas(elementoCV);
        const imgData = canvas.toDataURL('image/png');
        
        const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
        pdf.addImage(imgData, 'PNG', 0, 0, 210, 297); // Tamanho A4
        pdf.save("Curriculo_Assinado.pdf");
    }

    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    // Configurações de traço
    ctx.strokeStyle = "#000";
    ctx.lineWidth = 2;

    // Iniciar desenho
    canvas.addEventListener('mousedown', () => drawing = true);
    canvas.addEventListener('mouseup', () => {
        drawing = false;
        ctx.beginPath();
    });

    canvas.addEventListener('mousemove', draw);

    function draw(event) {
        if (!drawing) return;
        
        // Ajusta coordenadas para o canvas
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
    }

    // Botão Limpar
    document.getElementById('clear-signature').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });