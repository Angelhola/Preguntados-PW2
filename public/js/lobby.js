document.addEventListener('DOMContentLoaded', () => {
    const globo = document.getElementById('lobby-globo');
    const cartu = document.getElementById('lobby-cartu');
    if (!globo || !cartu) return;

    const usuario = globo.dataset.usuario || 'JUGADOR';
    const carpeta = cartu.dataset.carpeta;
    const ruta = (cara) => `${carpeta}cartu-${cara}.svg`;

    const frases = [
        { texto: `¡HOLA, ${usuario}!\n¿LISTO PARA JUGAR?`,       cara: 'feliz' },
        { texto: '¡ELEGÍ UNA CATEGORÍA\nY DEMOSTRÁ LO QUE SABÉS!', cara: 'sorpresa' },
        { texto: 'PSST... PRONTO HABRÁ\nRANKING. ¡ENTRENÁ!',       cara: 'guino' },
        { texto: '¿TE ANIMÁS A\nUNA PARTIDA MÁS?',                 cara: 'pensando' }
    ];

    // precarga para que no parpadee al cambiar de cara
    frases.forEach(f => { new Image().src = ruta(f.cara); });

    let indice = 0;
    let timer = null;

    function decir(frase) {
        clearInterval(timer);
        cartu.src = ruta(frase.cara);
        globo.textContent = '';
        let letras = 0;
        timer = setInterval(() => {
            letras++;
            globo.textContent = frase.texto.slice(0, letras);
            if (letras >= frase.texto.length) clearInterval(timer);
        }, 40);
    }

    decir(frases[0]);

    setInterval(() => {
        indice = (indice + 1) % frases.length;
        decir(frases[indice]);
    }, 5000);
});