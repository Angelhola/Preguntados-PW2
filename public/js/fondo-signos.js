document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('fondo-signos');
    if (!contenedor) return;

    const CANTIDAD = 9;
    const COLORES = ['#00e5ff', '#ff1f6b', '#ffe600', '#8a5cff'];

    // "?" en pixel art, 5 columnas x 7 filas
    const mapa = [
        '.###.',
        '#...#',
        '....#',
        '...#.',
        '..#..',
        '.....',
        '..#..'
    ];

    function crearSigno(color) {
        let rects = '';
        mapa.forEach((fila, y) => {
            [...fila].forEach((celda, x) => {
                if (celda === '#') {
                    rects += `<rect x="${x}" y="${y}" width="1.02" height="1.02" fill="${color}"/>`;
                }
            });
        });
        return `<svg viewBox="0 0 5 7" shape-rendering="crispEdges">${rects}</svg>`;
    }

    for (let i = 0; i < CANTIDAD; i++) {
        const signo = document.createElement('div');
        const duracion = 7 + Math.random() * 5;

        signo.className = 'signo';
        signo.style.left = (4 + Math.random() * 92) + '%';
        signo.style.width = (14 + Math.random() * 14) + 'px';
        signo.style.animationDuration = duracion + 's';
        signo.style.animationDelay = (-Math.random() * duracion) + 's';
        signo.innerHTML = crearSigno(COLORES[i % COLORES.length]);

        contenedor.appendChild(signo);
    }
});