let audioCtx = null;
let sonidoActivo = true;

function activarAudio() {
    audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === "suspended") { audioCtx.resume(); }
}

function beep(freq, inicio, duracion, tipo, volumen) {
    if (!audioCtx || !sonidoActivo) { return; }
    const t = audioCtx.currentTime + inicio;
    const osc = audioCtx.createOscillator();
    const ganancia = audioCtx.createGain();
    osc.type = tipo || "square";
    osc.frequency.setValueAtTime(freq, t);
    ganancia.gain.setValueAtTime(volumen || 0.06, t);
    ganancia.gain.exponentialRampToValueAtTime(0.001, t + duracion);
    osc.connect(ganancia);
    ganancia.connect(audioCtx.destination);
    osc.start(t);
    osc.stop(t + duracion);
}

// ----- Ruleta -----
function sonidoInicio() {
    [300, 400, 520, 700, 900].forEach(function (f, i) { beep(f, i * 0.06, 0.07, "square", 0.07); });
}
function sonidoTic() { beep(900, 0, 0.04, "square", 0.05); }
function sonidoFin() {
    beep(110, 0, 0.25, "triangle", 0.2);
    [523, 659, 784, 1047].forEach(function (f, i) { beep(f, 0.12 + i * 0.11, 0.14, "square", 0.07); });
    beep(1047, 0.6, 0.35, "square", 0.07);
}

// ----- Preguntas -----
// Correcta: dos notas agudas tipo moneda ("ding-ding")
function sonidoCorrecto() {
    beep(988, 0, 0.09, "square", 0.07);
    beep(1319, 0.09, 0.4, "square", 0.07);
}
// Incorrecta: zumbido grave descendente ("uy")
function sonidoIncorrecto() {
    beep(220, 0, 0.18, "sawtooth", 0.09);
    beep(150, 0.18, 0.18, "sawtooth", 0.09);
    beep(90, 0.36, 0.45, "sawtooth", 0.1);
}
// Tic de alarma en los últimos segundos
function sonidoTiempo() { beep(1200, 0, 0.05, "square", 0.05); }
// Se acabó el tiempo
function sonidoTiempoAgotado() {
    beep(400, 0, 0.15, "square", 0.08);
    beep(300, 0.15, 0.15, "square", 0.08);
    beep(200, 0.3, 0.45, "square", 0.08);
}