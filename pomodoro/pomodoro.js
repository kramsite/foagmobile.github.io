// ===== Persistência (via servidor / pomodoro.json do usuário) =====
const SAVE_URL = window.POMODORO_SAVE_URL || 'salvar_pomodoro.php';

const state = (window.POMODORO_DATA && typeof window.POMODORO_DATA === 'object')
  ? window.POMODORO_DATA
  : {};

if (!Array.isArray(state.disciplines)) state.disciplines = ['Geral'];
if (!Array.isArray(state.sessions)) state.sessions = [];
if (!state.goals || typeof state.goals !== 'object') state.goals = {};

function save() {
  try {
    fetch(SAVE_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(state)
    }).catch(() => {});
  } catch (e) {}
}


// ===== Header actions =====
const logoutModal = document.getElementById('logout-modal');
const confirmLogout = document.getElementById('confirm-logout');
const cancelLogout = document.getElementById('cancel-logout');
const iconPerfil = document.getElementById('icon-perfil');
const iconSair = document.getElementById('icon-sair');

iconPerfil &&
  iconPerfil.addEventListener(
    'click',
    () => (location.href = '../perfil/perfil.php')
  );

iconSair &&
  iconSair.addEventListener('click', () => {
    if (logoutModal) logoutModal.style.display = 'flex';
  });

confirmLogout &&
  confirmLogout.addEventListener(
    'click',
    () => (location.href = '../login/index.php')
  );

cancelLogout &&
  cancelLogout.addEventListener('click', () => {
    if (logoutModal) logoutModal.style.display = 'none';
  });

logoutModal &&
  logoutModal.addEventListener('click', (e) => {
    if (e.target === logoutModal) logoutModal.style.display = 'none';
  });

// ===== Tabs (Timer/Cronômetro) =====
document.querySelectorAll('.tab-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    document
      .querySelectorAll('.tab-btn')
      .forEach((b) => b.classList.remove('active'));
    document
      .querySelectorAll('.tab-panel')
      .forEach((p) => p.classList.remove('active'));

    btn.classList.add('active');
    const tab = btn.getAttribute('data-tab');
    const panel = document.getElementById('tab-' + tab);
    panel && panel.classList.add('active');
  });
});

// ===== Disciplinas =====
const disciplineSel = document.getElementById('discipline');
const newDiscipline = document.getElementById('newDiscipline');
const addDisciplineBtn = document.getElementById('addDiscipline');
const goalDiscipline = document.getElementById('goalDiscipline');
const stopwatchDiscipline = document.getElementById('stopwatchDiscipline');

function fillSelect(selectEl, values) {
  if (!selectEl) return;
  while (selectEl.options.length) selectEl.remove(0);
  values.forEach((v) => selectEl.add(new Option(v, v)));
}

function refreshDisciplines() {
  if (!state.disciplines.includes('Geral')) state.disciplines.unshift('Geral');
  const ordered = ['Geral', ...state.disciplines.filter((d) => d !== 'Geral')];
  fillSelect(disciplineSel, ordered);
  fillSelect(goalDiscipline, ordered);
  fillSelect(stopwatchDiscipline, ordered);
}
refreshDisciplines();

addDisciplineBtn &&
  addDisciplineBtn.addEventListener('click', () => {
    const val = ((newDiscipline && newDiscipline.value) || '').trim();
    if (!val) return;
    if (!state.disciplines.includes(val)) {
      state.disciplines.push(val);
      save();
      refreshDisciplines();
      if (newDiscipline) newDiscipline.value = '';
    }
  });

// ===== Polyfill UUID =====
if (!(window.crypto && crypto.randomUUID)) {
  window.crypto = window.crypto || {};
  crypto.randomUUID = function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0,
        v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  };
}

// ===== Pomodoro =====
let mode = 'focus';
let cycle = 1;
let timer = null;
let endAt = null;
let totalMs = 0;
let remainingMs = 0;

const focusM = document.getElementById('focusM');
const shortM = document.getElementById('shortM');
const longM = document.getElementById('longM');
const everyCycles = document.getElementById('everyCycles');
const timerEl = document.getElementById('timer');
const modePill = document.getElementById('modePill');
const cyclePill = document.getElementById('cyclePill');
const progressBar = document.getElementById('timerProgress');
const ding = document.getElementById('ding');

function setMode(m) {
  mode = m;
  const mins =
    m === 'focus'
      ? +(focusM?.value || 25)
      : m === 'short'
      ? +(shortM?.value || 5)
      : +(longM?.value || 15);
  totalMs = mins * 60 * 1000;
  remainingMs = totalMs;
  endAt = null;
  renderTimer();
  if (modePill)
    modePill.innerHTML = `<i class="fa-solid fa-hourglass-half"></i> ${
      m === 'focus' ? 'Foco' : m === 'short' ? 'Pausa curta' : 'Pausa longa'
    }`;
}

function renderTimer() {
  if (!timerEl || !progressBar || !cyclePill) return;
  const mm = Math.floor(remainingMs / 60000)
    .toString()
    .padStart(2, '0');
  const ss = Math.floor((remainingMs % 60000) / 1000)
    .toString()
    .padStart(2, '0');
  timerEl.textContent = `${mm}:${ss}`;
  const pct = totalMs ? Math.max(0, 100 - Math.floor((remainingMs / totalMs) * 100)) : 0;
  progressBar.style.width = pct + '%';
  cyclePill.textContent = `Ciclo ${cycle}`;
  document.title = `${mm}:${ss} – FOAG`;
}

function tick() {
  const now = Date.now();
  remainingMs = Math.max(0, endAt - now);
  renderTimer();
  if (remainingMs <= 0) {
    clearInterval(timer);
    timer = null;
    completeCycle();
    try {
      ding && ding.play && ding.play();
    } catch (e) {}
  }
}

function start() {
  if (timer) return;
  if (!endAt) {
    endAt = Date.now() + remainingMs;
  }
  timer = setInterval(tick, 200);
}

function pause() {
  if (timer) {
    clearInterval(timer);
    timer = null;
    remainingMs = Math.max(0, endAt - Date.now());
    endAt = null;
    renderTimer();
  }
}

function reset() {
  pause();
  setMode(mode);
}

function completeCycle() {
  const mins = Math.round(totalMs / 60000);
  const discipline = disciplineSel ? disciplineSel.value : 'Geral';
  state.sessions.push({ ts: Date.now(), minutes: mins, mode, discipline });
  save();
  updateHistory();
  updateCharts();
  updateGoalsView();

  if (mode === 'focus') {
    const ec = +(everyCycles?.value || 4);
    cycle++;
    if ((cycle - 1) % ec === 0) {
      setMode('long');
    } else {
      setMode('short');
    }
  } else {
    setMode('focus');
  }
}

const startBtn = document.getElementById('startBtn');
const pauseBtn = document.getElementById('pauseBtn');
const resetBtn = document.getElementById('resetBtn');

startBtn && (startBtn.onclick = start);
pauseBtn && (pauseBtn.onclick = pause);
resetBtn && (resetBtn.onclick = reset);

setMode('focus');

// ===== Cronômetro =====
const swDisplay = document.getElementById('stopwatchDisplay');
const swStartBtn = document.getElementById('swStart');
const swPauseBtn = document.getElementById('swPause');
const swResetBtn = document.getElementById('swReset');
const swLapBtn = document.getElementById('swLap');
const swSaveBtn = document.getElementById('swSaveSession');
const lapsList = document.getElementById('lapsList');

let swRunning = false,
  swStartAt = null,
  swElapsed = 0,
  swTimer = null;
const swLaps = [];

function renderStopwatch() {
  if (!swDisplay) return;
  const total = swElapsed;
  const h = Math.floor(total / 3600000)
    .toString()
    .padStart(2, '0');
  const m = Math.floor((total % 3600000) / 60000)
    .toString()
    .padStart(2, '0');
  const s = Math.floor((total % 60000) / 1000)
    .toString()
    .padStart(2, '0');
  swDisplay.textContent = `${h}:${m}:${s}`;
}

function renderLaps() {
  if (!lapsList) return;
  lapsList.innerHTML = '';
  swLaps.forEach((ms, idx) => {
    const prev = idx === 0 ? 0 : swLaps[idx - 1];
    const lapDur = ms - prev;
    const fmt = (t) => {
      const hh = Math.floor(t / 3600000)
        .toString()
        .padStart(2, '0');
      const mm = Math.floor((t % 3600000) / 60000)
        .toString()
        .padStart(2, '0');
      const ss = Math.floor((t % 60000) / 1000)
        .toString()
        .padStart(2, '0');
      return `${hh}:${mm}:${ss}`;
    };
    const div = document.createElement('div');
    div.className = 'task';
    div.innerHTML = `<strong>Volta ${
      idx + 1
    }</strong><small style="color:#666">Tempo total: ${fmt(
      ms
    )} | Parcial: ${fmt(lapDur)}</small>`;
    lapsList.appendChild(div);
  });
}

function swTick() {
  swElapsed = Date.now() - swStartAt;
  renderStopwatch();
}

swStartBtn &&
  swStartBtn.addEventListener('click', () => {
    if (swRunning) return;
    swRunning = true;
    swStartAt = Date.now() - swElapsed;
    swTimer = setInterval(swTick, 200);
  });

swPauseBtn &&
  swPauseBtn.addEventListener('click', () => {
    if (!swRunning) return;
    swRunning = false;
    clearInterval(swTimer);
    swTimer = null;
    swTick();
  });

swResetBtn &&
  swResetBtn.addEventListener('click', () => {
    swRunning = false;
    clearInterval(swTimer);
    swTimer = null;
    swElapsed = 0;
    swLaps.length = 0;
    renderStopwatch();
    renderLaps();
  });

swLapBtn &&
  swLapBtn.addEventListener('click', () => {
    if (swRunning) {
      swLaps.push(swElapsed);
      renderLaps();
    }
  });

swSaveBtn &&
  swSaveBtn.addEventListener('click', () => {
    const minutes = Math.round(swElapsed / 60000);
    if (minutes <= 0) {
      alert(
        'Cronômetro zerado. Inicie e registre algum tempo antes de salvar.'
      );
      return;
    }
    const discipline =
      (stopwatchDiscipline && stopwatchDiscipline.value) || 'Geral';
    state.sessions.push({
      ts: Date.now(),
      minutes,
      mode: 'focus',
      discipline,
    });
    save();
    updateHistory();
    updateCharts();
    updateGoalsView();
    alert('Sessão salva no histórico!');
  });

renderStopwatch();

// ===== Metas Semanais =====
const saveGoalBtn = document.getElementById('saveGoal');
const goalHours = document.getElementById('goalHours');
const goalsList = document.getElementById('goalsList');

function getWeekRange(d = new Date()) {
  const dt = new Date(d);
  const day = (dt.getDay() + 6) % 7; // seg=0
  const monday = new Date(dt);
  monday.setDate(dt.getDate() - day);
  monday.setHours(0, 0, 0, 0);
  const sunday = new Date(monday);
  sunday.setDate(monday.getDate() + 6);
  sunday.setHours(23, 59, 59, 999);
  return { monday, sunday };
}

function minutesInWeekByDiscipline() {
  const { monday, sunday } = getWeekRange();
  const acc = {};
  for (const s of state.sessions) {
    if (s.mode !== 'focus') continue;
    const t = s.ts;
    if (t >= monday.getTime() && t <= sunday.getTime()) {
      acc[s.discipline] = (acc[s.discipline] || 0) + s.minutes;
    }
  }
  return acc;
}

function updateGoalsView() {
  if (!goalsList) return;
  goalsList.innerHTML = '';
  const minsMap = minutesInWeekByDiscipline();
  for (const d of state.disciplines) {
    const goalH = state.goals[d] || 0;
    if (!goalH) continue;
    const doneMin = minsMap[d] || 0;
    const goalMin = goalH * 60;
    const pct = Math.min(100, Math.floor((doneMin / goalMin) * 100));
    const wrap = document.createElement('div');
    wrap.innerHTML = `
      <div class="row between">
        <strong>${d}</strong>
        <span style="color:#666">${Math.round(
          doneMin / 60
        )}h / ${goalH}h</span>
      </div>
      <div class="progress mt"><span style="width:${pct}%"></span></div>
    `;
    goalsList.appendChild(wrap);
  }
}

saveGoalBtn &&
  saveGoalBtn.addEventListener('click', () => {
    const d = goalDiscipline && goalDiscipline.value;
    const h = +(goalHours && goalHours.value);
    if (!d || !h) return;
    state.goals[d] = h;
    save();
    if (goalHours) goalHours.value = '';
    updateGoalsView();
  });

// ===== Histórico + Export =====
const historyTableBody = document.querySelector('#historyTable tbody');

function updateHistory() {
  if (!historyTableBody) return;
  historyTableBody.innerHTML = '';
  const rows = [...state.sessions].sort((a, b) => b.ts - a.ts);
  for (const s of rows) {
    const tr = document.createElement('tr');
    const d = new Date(s.ts);
    tr.innerHTML = `<td>${d.toLocaleString(
      'pt-BR'
    )}</td><td>${s.discipline}</td><td>${
      s.mode === 'focus' ? 'Foco' : 'Pausa'
    }</td><td>${s.minutes}</td>`;
    historyTableBody.appendChild(tr);
  }
}

const clearHistoryBtn = document.getElementById('clearHistory');
const exportCsvBtn = document.getElementById('exportCsv');

clearHistoryBtn &&
  clearHistoryBtn.addEventListener('click', () => {
    if (!confirm('Tem certeza que deseja limpar o histórico?')) return;
    state.sessions = [];
    save();
    updateHistory();
    updateCharts();
    updateGoalsView();
  });

exportCsvBtn &&
  exportCsvBtn.addEventListener('click', () => {
    const header = ['data', 'disciplina', 'modo', 'minutos'];
    const lines = [header.join(',') + '\n'];
    for (const s of state.sessions) {
      const d = new Date(s.ts).toISOString();
      lines.push(
        [d, s.discipline, s.mode, s.minutes]
          .map((v) => `"${String(v).replace(/"/g, '""')}"`)
          .join(',') + '\n'
      );
    }
    const blob = new Blob([lines.join('')], {
      type: 'text/csv;charset=utf-8;',
    });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'foag_estudos.csv';
    a.click();
  });

// ===== Gráficos =====
const lineCtx = document.getElementById('lineChart');
const pieCtx = document.getElementById('pieChart');
let lineChart, pieChart;

function hoursLastNDays(n = 14) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const labels = [];
  const mins = [];
  for (let i = n - 1; i >= 0; i--) {
    const day = new Date(today);
    day.setDate(today.getDate() - i);
    const start = day.getTime();
    const end = start + 86400000 - 1;
    const m = state.sessions
      .filter(
        (s) => s.mode === 'focus' && s.ts >= start && s.ts <= end
      )
      .reduce((a, b) => a + b.minutes, 0);
    labels.push(
      day.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
      })
    );
    mins.push(Math.round((m / 60) * 100) / 100);
  }
  return { labels, hours: mins };
}

function distributionByDiscipline() {
  const by = {};
  for (const s of state.sessions) {
    if (s.mode === 'focus')
      by[s.discipline] = (by[s.discipline] || 0) + s.minutes;
  }
  const labels = Object.keys(by);
  const hours = labels.map((k) => Math.round((by[k] / 60) * 100) / 100);
  return { labels, hours };
}

function updateCharts() {
  if (typeof Chart === 'undefined' || !lineCtx || !pieCtx) return;
  const hl = hoursLastNDays(14);
  const dist = distributionByDiscipline();
  try {
    lineChart && lineChart.destroy();
    pieChart && pieChart.destroy();
  } catch (e) {}
  try {
    lineChart = new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: hl.labels,
        datasets: [
          {
            label: 'Horas por dia',
            data: hl.hours,
            tension: 0.3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
      },
    });

    pieChart = new Chart(pieCtx, {
      type: 'doughnut',
      data: {
        labels: dist.labels,
        datasets: [{ data: dist.hours }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
      },
    });
  } catch (e) {}
}

// ===== Init =====
updateHistory();
updateCharts();
updateGoalsView();
