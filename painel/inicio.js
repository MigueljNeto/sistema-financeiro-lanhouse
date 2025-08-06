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

let startTime;
let intervalo;

function atualizarRelogio(idDoRelogio) {

    const tempoSalvo = parseInt(localStorage.getItem('startTime'));
    if (!tempoSalvo) return;

    let agora = new Date();
    let diferenca = agora - startTime;

    let segundosTotais = Math.floor(diferenca / 1000);
    let minutos = Math.floor(segundosTotais / 60);
    let segundos = segundosTotais % 60;

    let tempoFormatado =
        (minutos < 10 ? '0' + minutos : minutos) + ':' +
        (segundos < 10 ? '0' + segundos : segundos);

    document.getElementById(idDoRelogio).innerText = tempoFormatado;
}

function iniciarContador(idDoRelogio, btnIniciar1, btnParar1) {


    startTime = new Date();
    localStorage.setItem('startTime', startTime.getTime());
    localStorage.setItem('isRunning', 'true');
    isContando = true;

    atualizarRelogio(idDoRelogio);
    intervalo = setInterval(() => atualizarRelogio(idDoRelogio), 1000);

    document.getElementById(btnIniciar1).style.display = 'none';
    document.getElementById(btnParar1).style.display = 'block';

}

function pararContador(btnIniciar1, btnParar1) {
   

    clearInterval(intervalo);
    localStorage.setItem('isRunning', 'false'); 
    isContando = false;

    document.getElementById(btnIniciar1).style.display = 'block';
    document.getElementById(btnParar1).style.display = 'none';
}

window.onload = function () {


    const tempoSalvo = localStorage.getItem('startTime');
    const rodando = localStorage.getItem('isRunning') === 'true';

    if (tempoSalvo) {
        startTime = new Date(parseInt(tempoSalvo));

        atualizarRelogio('idDoRelogio');

        if (rodando) {
            intervalo = setInterval(() => atualizarRelogio('idDoRelogio'), 1000);
            document.getElementById('btnIniciar1').style.display = 'none';
            document.getElementById('btnParar1').style.display = 'block';
        } else {
            document.getElementById('btnIniciar1').style.display = 'block';
            document.getElementById('btnParar1').style.display = 'none';
        }
    } else {
        document.getElementById('idDoRelogio').innerText = '00:00';
        document.getElementById('btnIniciar1').style.display = 'block';
        document.getElementById('btnParar1').style.display = 'none';
    }
};


