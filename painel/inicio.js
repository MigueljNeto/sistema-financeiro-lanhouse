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

const contadores = {}; // { [id]: { segundos, intervalo } }

// ------------------ RELÓGIO ------------------
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

// ------------------ CONTADOR ------------------
async function iniciarContador(id) {
    const resp = await fetch('contador.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=iniciar&id_maquina=${id}`
    });
    const data = await resp.json();

    if (!contadores[id]) contadores[id] = { segundos: 0, intervalo: null };
    contadores[id].segundos = parseInt(data.total_segundos || 0, 10);

    if (contadores[id].intervalo) clearInterval(contadores[id].intervalo);
    contadores[id].intervalo = setInterval(() => tick(id), 1000);

    ligarUI(id, true);
}

async function pararContador(id) {
    if (!contadores[id]) return;

    await fetch('contador.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=pausar&id_maquina=${id}&total_segundos=${contadores[id].segundos}`
    });

    if (contadores[id].intervalo) clearInterval(contadores[id].intervalo);
    contadores[id].intervalo = null;
    ligarUI(id, false);
}

async function finalizarMaquina(id) {
    const resp = await fetch('contador.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=finalizar&id_maquina=${id}&total_segundos=${contadores[id]?.segundos || 0}`
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

// ------------------ SERVIÇOS ------------------
async function adicionarServico(id, tipo) {
    const resp = await fetch('servicos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `acao=adicionar&id_maquina=${id}&tipo=${tipo}&quantidade=1`
    });

    const data = await resp.json();
    if (data?.status === 'ok') {
        document.getElementById(`${tipo}_${id}`).innerText = data[tipo];
    }
}
async function salvarServicos(event, id) {
    event.preventDefault();
    const form = document.getElementById(`form_servicos_${id}`);
    const formData = new FormData(form);

    const resp = await fetch('servicos.php', {
        method: 'POST',
        body: new URLSearchParams(formData) + `&acao=salvar&id_maquina=${id}`
    });

    const data = await resp.json();
    if (data?.status === 'ok') {
        document.getElementById(`impressoes_${id}`).innerText = data.impressoes;
        document.getElementById(`scanners_${id}`).innerText   = data.scanners;
    }
}

// ------------------ INICIALIZAÇÃO ------------------
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