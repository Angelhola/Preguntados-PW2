
(function () {
    const dialogo = document.getElementById('cartu-dialogo');
    const botonCartu = document.getElementById('cartu-btn');
    if (!dialogo || !botonCartu) return;

    const frases = [
        '¡Hola! ¿Qué te llevás?',
        'Los potenciadores te salvan en las preguntas difíciles.',
        '¿Perdiste? Tranqui, te ofrezco una vida extra... pero solo una por partida.',
        'Sumá puntos respondiendo bien y volvé.',
        'Mirá todo lo que quieras, ¡no cobro por mirar!',
        'Un cambio de look siempre viene bien.'
    ];

    const sinAnimacion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let indice = 0;
    let temporizador = null;

    function decir(texto) {
        clearInterval(temporizador);

        if (sinAnimacion) {
            dialogo.textContent = texto;
            return;
        }

        dialogo.textContent = '';
        let letras = 0;
        temporizador = setInterval(function () {
            letras++;
            dialogo.textContent = texto.slice(0, letras);
            if (letras >= texto.length) clearInterval(temporizador);
        }, 25);
    }

    botonCartu.addEventListener('click', function () {
        indice = (indice + 1) % frases.length;
        decir(frases[indice]);
    });

    document.querySelectorAll('.item[data-frase]').forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            decir(item.dataset.frase);
        });
    });
})();