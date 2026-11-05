{{-- ── VISTA PREVIA PDF (misma que upload_pdf) ───────────────────────────
     Requiere: $pdfUrl con la URL del PDF y los contenedores #pdf-pages-container
     y #pdf-loading presentes en la vista.
     ──────────────────────────────────────────────────────────────────── --}}
<script src="{{ asset('assets/vendor/js/pdf.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const url = @json($pdfUrl);
        const container = document.getElementById('pdf-pages-container');
        const loadingMsg = document.getElementById('pdf-loading');

        if (!container) {
            return;
        }

        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/vendor/js/pdf.worker.min.js') }}";

        // Recorta el espacio en blanco superior de cada página escaneada.
        function cropWhitespaceTop(sourceCanvas) {
            const ctx = sourceCanvas.getContext('2d', { willReadFrequently: true });
            const width = sourceCanvas.width;
            const height = sourceCanvas.height;
            const imgData = ctx.getImageData(0, 0, width, height).data;
            let cropY = 0;

            for (let y = 0; y < height; y++) {
                let pixelesOscuros = 0;
                for (let x = 0; x < width; x++) {
                    const i = (y * width + x) * 4;
                    if (imgData[i] < 220 && imgData[i + 1] < 220 && imgData[i + 2] < 220 && imgData[i + 3] > 10) {
                        pixelesOscuros++;
                    }
                }
                if (pixelesOscuros > 15) {
                    cropY = Math.max(0, y - 30);
                    break;
                }
            }

            if (cropY === 0) return sourceCanvas;

            const finalCanvas = document.createElement('canvas');
            finalCanvas.className = "w-full";
            finalCanvas.width = width;
            finalCanvas.height = height - cropY;
            finalCanvas.getContext('2d').drawImage(
                sourceCanvas,
                0, cropY, width, height - cropY,
                0, 0, width, height - cropY
            );
            return finalCanvas;
        }

        async function renderizarGaceta() {
            try {
                const pdf = await pdfjsLib.getDocument(url).promise;
                if (loadingMsg) loadingMsg.style.display = 'none';

                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const numPaginaReal = i + 1; // La portada HTML es la página 1.
                    const esPar = (numPaginaReal % 2 === 0);

                    const viewport = page.getViewport({ scale: 2.0 });
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.height = viewport.height;
                    tempCanvas.width = viewport.width;
                    await page.render({ canvasContext: tempCanvas.getContext('2d'), viewport }).promise;

                    const canvasRecortado = cropWhitespaceTop(tempCanvas);

                    const hojaDiv = document.createElement('div');
                    hojaDiv.className = "bg-white shadow-2xl relative text-black";
                    hojaDiv.style.cssText = "width:100%; max-width:850px; padding:3rem 4rem; min-height:1100px; margin-bottom:2rem;";

                    const headerDiv = document.createElement('div');
                    headerDiv.style.borderBottom = "4px solid black";
                    headerDiv.className = "flex justify-between items-end pb-1 mb-8 font-sans font-bold text-sm uppercase";

                    const textPagina = `<span class="text-[1.1rem]">PAGINA N° ${numPaginaReal}</span>`;
                    const textTitulo = `<span class="tracking-widest text-[1rem]">GACETA OFICIAL DEL ESTADO YARACUY</span>`;

                    const colIzq = document.createElement('div');
                    colIzq.className = "w-1/4 text-left";
                    colIzq.innerHTML = esPar ? textPagina : '';

                    const colCen = document.createElement('div');
                    colCen.className = "w-2/4 text-center";
                    colCen.innerHTML = textTitulo;

                    const colDer = document.createElement('div');
                    colDer.className = "w-1/4 text-right";
                    colDer.innerHTML = !esPar ? textPagina : '';

                    headerDiv.appendChild(colIzq);
                    headerDiv.appendChild(colCen);
                    headerDiv.appendChild(colDer);
                    hojaDiv.appendChild(headerDiv);
                    hojaDiv.appendChild(canvasRecortado);
                    container.appendChild(hojaDiv);
                }
            } catch (error) {
                console.error("Error cargando el PDF de la Gaceta:", error);
                if (loadingMsg) {
                    loadingMsg.style.display = '';
                    loadingMsg.innerHTML = '<span class="text-red-500 font-bold"><i class="fa-solid fa-circle-xmark mr-2"></i>Error al cargar el documento. Verifique el archivo.</span>';
                }
            }
        }

        renderizarGaceta();
    });
</script>
