function abrirModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('ativo');
    }, 10);
}

function fecharModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('ativo');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function abrirModalMaquina(id) {
    const modal = document.getElementById("modalMaquina_" + id);
    modal.style.display = "flex";
    setTimeout(() => modal.classList.add("ativo"), 10);
}

function fecharModalMaquina(id) {
    const modal = document.getElementById("modalMaquina_" + id);
    modal.classList.remove("ativo");
    setTimeout(() => modal.style.display = "none", 300);
}

function abrirModalExcluir(id) {
    document.getElementById("idExcluir").value = id;
    const modal = document.getElementById("modalExcluir");
    modal.style.display = "flex";
}

function fecharModalExcluir() {
    const modal = document.getElementById("modalExcluir");
    modal.style.display = "none";
}
// let startTime;
// let intervalo;

// function atualizarRelogio(idDoRelogio) {

//     const tempoSalvo = parseInt(localStorage.getItem('startTime'));
//     if (!tempoSalvo) return;

//     let agora = new Date();
//     let diferenca = agora - startTime;

//     let segundosTotais = Math.floor(diferenca / 1000);
//     let minutos = Math.floor(segundosTotais / 60);
//     let segundos = segundosTotais % 60;

//     let tempoFormatado =
//         (minutos < 10 ? '0' + minutos : minutos) + ':' +
//         (segundos < 10 ? '0' + segundos : segundos);

//     document.getElementById(idDoRelogio).innerText = tempoFormatado;
// }

// function iniciarContador(idDoRelogio, btnIniciar1, btnParar1) {


//     startTime = new Date();
//     localStorage.setItem('startTime', startTime.getTime());
//     localStorage.setItem('isRunning', 'true');
//     isContando = true;

//     atualizarRelogio(idDoRelogio);
//     intervalo = setInterval(() => atualizarRelogio(idDoRelogio), 1000);

//     document.getElementById(btnIniciar1).style.display = 'none';
//     document.getElementById(btnParar1).style.display = 'block';

// }

// function pararContador(btnIniciar1, btnParar1) {


//     clearInterval(intervalo);
//     localStorage.setItem('isRunning', 'false');
//     isContando = false;

//     document.getElementById(btnIniciar1).style.display = 'block';
//     document.getElementById(btnParar1).style.display = 'none';
// }

// window.onload = function () {


//     const tempoSalvo = localStorage.getItem('startTime');
//     const rodando = localStorage.getItem('isRunning') === 'true';

//     if (tempoSalvo) {
//         startTime = new Date(parseInt(tempoSalvo));

//         atualizarRelogio('idDoRelogio');

//         if (rodando) {
//             intervalo = setInterval(() => atualizarRelogio('idDoRelogio'), 1000);
//             document.getElementById('btnIniciar1').style.display = 'none';
//             document.getElementById('btnParar1').style.display = 'block';
//         } else {
//             document.getElementById('btnIniciar1').style.display = 'block';
//             document.getElementById('btnParar1').style.display = 'none';
//         }
//     } else {
//         document.getElementById('idDoRelogio').innerText = '00:00';
//         document.getElementById('btnIniciar1').style.display = 'block';
//         document.getElementById('btnParar1').style.display = 'none';
//     }
// };

const contadores = {}; // { [id]: { segundos, intervalo } }

function formatarTempo(segundos) {
    const h = Math.floor(segundos / 3600).toString().padStart(2, '0');
    const m = Math.floor((segundos % 3600) / 60).toString().padStart(2, '0');
    const s = (segundos % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function tick(id) {
    contadores[id].segundos++;
    document.getElementById(`relogio_${id}`).innerText = formatarTempo(contadores[id].segundos);
}

function ligarUI(id, rodando) {
    document.getElementById(`btnIniciar_${id}`).style.display = rodando ? 'none' : 'inline-block';
    document.getElementById(`btnParar_${id}`).style.display = rodando ? 'inline-block' : 'none';
}

async function iniciarContador(id) {

    const resp = await fetch('iniciar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_maquina=${id}`
    });
    const data = await resp.json();

    if (!contadores[id]) contadores[id] = { segundos: 0, intervalo: null };
    contadores[id].segundos = parseInt(data.total_segundos || 0, 10);

    if (contadores[id].intervalo) clearInterval(contadores[id].intervalo);
    contadores[id].intervalo = setInterval(() => tick(id), 1000);

    ligarUI(id, true);
}

async function pausarContador(id) {
    if (!contadores[id]) return;
    await fetch('pausar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_maquina=${id}&total_segundos=${contadores[id].segundos}`
    });

    if (contadores[id].intervalo) clearInterval(contadores[id].intervalo);
    contadores[id].intervalo = null;
    ligarUI(id, false);
}

async function finalizarMaquina(id) {

    const resp = await fetch('finalizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_maquina=${id}&total_segundos=${contadores[id]?.segundos || 0}`
    });
    const data = await resp.json();


    if (contadores[id]?.intervalo) clearInterval(contadores[id].intervalo);
    contadores[id] = { segundos: 0, intervalo: null };
    document.getElementById(`relogio_${id}`).innerText = '00:00:00';
    ligarUI(id, false);


    if (data?.status === 'ok' && data?.valor_total != null) {
        alert(`Tempo final: ${formatarTempo(data.total_segundos)}\nValor: R$ ${data.valor_total.toFixed(2)}`);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.contador').forEach(box => {
        const id = parseInt(box.dataset.id, 10);
        const status = box.dataset.status;
        const elapsed = parseInt(box.dataset.elapsed || '0', 10);

        contadores[id] = { segundos: elapsed, intervalo: null };
        document.getElementById(`relogio_${id}`).innerText = formatarTempo(elapsed);

        if (status === 'ocupada') {
            contadores[id].intervalo = setInterval(() => tick(id), 1000);
            ligarUI(id, true);
        } else {
            ligarUI(id, false);
        }
    });
});