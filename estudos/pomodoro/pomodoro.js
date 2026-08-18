// ==========================================
// PERSISTÊNCIA DO POMODORO
// ==========================================

const SAVE_URL =
  window.POMODORO_SAVE_URL ||
  'salvar_pomodoro.php';

const state =
  window.POMODORO_DATA &&
  typeof window.POMODORO_DATA === 'object'
    ? window.POMODORO_DATA
    : {};


// ==========================================
// NORMALIZAR DADOS
// ==========================================

if (!Array.isArray(state.sessions)) {
  state.sessions = [];
}

// IMPORTANTE:
// array [] não funciona corretamente para
// propriedades como goals["Matemática"].
if (
  !state.goals ||
  typeof state.goals !== 'object' ||
  Array.isArray(state.goals)
) {
  state.goals = {};
}


// ==========================================
// MATÉRIAS VINDAS DE materias.json
// ==========================================

const materiasData =
  window.MATERIAS_DATA &&
  typeof window.MATERIAS_DATA === 'object'
    ? window.MATERIAS_DATA
    : {
        materias: []
      };

const materias =
  Array.isArray(materiasData.materias)
    ? materiasData.materias
    : [];


// ==========================================
// LISTA DE MATÉRIAS
// ==========================================

const nomesMaterias = materias
  .map((materia) => {
    return String(materia.nome || '').trim();
  })
  .filter((nome) => {
    return nome !== '';
  });


// Remove matérias duplicadas
const materiasUnicas = [
  ...new Set(nomesMaterias)
];


// Geral continua disponível
state.disciplines = [
  'Geral',
  ...materiasUnicas.filter(
    (materia) => materia !== 'Geral'
  )
];


// ==========================================
// SALVAR POMODORO
// ==========================================

function save() {

  try {

    fetch(SAVE_URL, {
      method: 'POST',

      credentials: 'same-origin',

      headers: {
        'Content-Type': 'application/json'
      },

      body: JSON.stringify(state)
    })
      .then((response) => {

        if (!response.ok) {
          throw new Error(
            'Erro ao salvar dados do Pomodoro.'
          );
        }

        return response;
      })
      .catch((error) => {

        console.error(
          'Erro ao salvar Pomodoro:',
          error
        );

      });

  } catch (error) {

    console.error(
      'Erro ao salvar dados:',
      error
    );

  }
}


// ==========================================
// CABEÇALHO
// ==========================================

const logoutModal =
  document.getElementById('logout-modal');

const confirmLogout =
  document.getElementById('confirm-logout');

const cancelLogout =
  document.getElementById('cancel-logout');

const iconPerfil =
  document.getElementById('icon-perfil');

const iconSair =
  document.getElementById('icon-sair');

const iconConfiguracoes =
  document.getElementById('icon-configuracoes');


// PERFIL
if (iconPerfil) {

  iconPerfil.addEventListener(
    'click',
    () => {

      window.location.href =
        '../../perfil/perfil.php';

    }
  );

}


// CONFIGURAÇÕES
if (iconConfiguracoes) {

  iconConfiguracoes.addEventListener(
    'click',
    () => {

      window.location.href =
        '../../configuracoes/configuracoes.php';

    }
  );

}


// ABRIR LOGOUT
if (iconSair) {

  iconSair.addEventListener(
    'click',
    () => {

      if (logoutModal) {
        logoutModal.style.display = 'flex';
      }

    }
  );

}


// CONFIRMAR LOGOUT
if (confirmLogout) {

  confirmLogout.addEventListener(
    'click',
    () => {

      window.location.href =
        '../../login/logout.php';

    }
  );

}


// CANCELAR LOGOUT
if (cancelLogout) {

  cancelLogout.addEventListener(
    'click',
    () => {

      if (logoutModal) {
        logoutModal.style.display = 'none';
      }

    }
  );

}


// FECHAR CLICANDO FORA
if (logoutModal) {

  logoutModal.addEventListener(
    'click',
    (event) => {

      if (event.target === logoutModal) {
        logoutModal.style.display = 'none';
      }

    }
  );

}


// ==========================================
// ABAS TIMER / CRONÔMETRO
// ==========================================

document
  .querySelectorAll('.tab-btn')
  .forEach((btn) => {

    btn.addEventListener(
      'click',
      () => {

        document
          .querySelectorAll('.tab-btn')
          .forEach((button) => {

            button.classList.remove(
              'active'
            );

          });


        document
          .querySelectorAll('.tab-panel')
          .forEach((panel) => {

            panel.classList.remove(
              'active'
            );

          });


        btn.classList.add('active');


        const tab =
          btn.getAttribute(
            'data-tab'
          );


        const panel =
          document.getElementById(
            'tab-' + tab
          );


        if (panel) {
          panel.classList.add('active');
        }

      }
    );

  });


// ==========================================
// SELECTS DE MATÉRIA
// ==========================================

const disciplineSel =
  document.getElementById('discipline');

const goalDiscipline =
  document.getElementById('goalDiscipline');

const stopwatchDiscipline =
  document.getElementById(
    'stopwatchDiscipline'
  );


// ==========================================
// PREENCHER SELECT
// ==========================================

function fillSelect(
  selectEl,
  values
) {

  if (!selectEl) {
    return;
  }


  selectEl.innerHTML = '';


  values.forEach((value) => {

    const option =
      document.createElement('option');

    option.value = value;
    option.textContent = value;

    selectEl.appendChild(option);

  });

}


// ==========================================
// CARREGAR MATÉRIAS NOS SELECTS
// ==========================================

function refreshDisciplines() {

  fillSelect(
    disciplineSel,
    state.disciplines
  );

  fillSelect(
    goalDiscipline,
    state.disciplines
  );

  fillSelect(
    stopwatchDiscipline,
    state.disciplines
  );

}


refreshDisciplines();

// ===== Polyfill UUID =====
if (!(window.crypto && crypto.randomUUID)) {
  window.crypto = window.crypto || {};

  crypto.randomUUID = function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'
      .replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;

        const v =
          c === 'x'
            ? r
            : (r & 0x3) | 0x8;

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

const focusM =
  document.getElementById('focusM');

const shortM =
  document.getElementById('shortM');

const longM =
  document.getElementById('longM');

const everyCycles =
  document.getElementById('everyCycles');

const timerEl =
  document.getElementById('timer');

const modePill =
  document.getElementById('modePill');

const cyclePill =
  document.getElementById('cyclePill');

const progressBar =
  document.getElementById('timerProgress');

const ding =
  document.getElementById('ding');

function setMode(newMode) {
  mode = newMode;

  const mins =
    newMode === 'focus'
      ? +(focusM?.value || 25)
      : newMode === 'short'
        ? +(shortM?.value || 5)
        : +(longM?.value || 15);

  totalMs = mins * 60 * 1000;
  remainingMs = totalMs;
  endAt = null;

  renderTimer();

  if (modePill) {
    modePill.innerHTML = `
      <i class="fa-solid fa-hourglass-half"></i>
      ${
        newMode === 'focus'
          ? 'Foco'
          : newMode === 'short'
            ? 'Pausa curta'
            : 'Pausa longa'
      }
    `;
  }
}

function renderTimer() {
  if (!timerEl || !progressBar || !cyclePill) {
    return;
  }

  const mm =
    Math.floor(remainingMs / 60000)
      .toString()
      .padStart(2, '0');

  const ss =
    Math.floor((remainingMs % 60000) / 1000)
      .toString()
      .padStart(2, '0');

  timerEl.textContent = `${mm}:${ss}`;

  const pct = totalMs
    ? Math.max(
        0,
        100 -
          Math.floor(
            (remainingMs / totalMs) * 100
          )
      )
    : 0;

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
      if (ding && ding.play) {
        ding.play();
      }
    } catch (e) {
      console.error('Erro ao reproduzir som:', e);
    }
  }
}

function start() {
  if (timer) {
    return;
  }

  if (!endAt) {
    endAt = Date.now() + remainingMs;
  }

  timer = setInterval(tick, 200);
}

function pause() {
  if (timer) {
    clearInterval(timer);

    timer = null;

    remainingMs = Math.max(
      0,
      endAt - Date.now()
    );

    endAt = null;

    renderTimer();
  }
}

function reset() {
  pause();
  setMode(mode);
}

function completeCycle() {
  const mins =
    Math.round(totalMs / 60000);

  const discipline =
    disciplineSel
      ? disciplineSel.value
      : 'Geral';

  state.sessions.push({
    ts: Date.now(),
    minutes: mins,
    mode: mode,
    discipline: discipline
  });

  save();
  updateHistory();
  updateCharts();
  updateGoalsView();

  if (mode === 'focus') {
    const ec =
      +(everyCycles?.value || 4);

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

const startBtn =
  document.getElementById('startBtn');

const pauseBtn =
  document.getElementById('pauseBtn');

const resetBtn =
  document.getElementById('resetBtn');

if (startBtn) {
  startBtn.onclick = start;
}

if (pauseBtn) {
  pauseBtn.onclick = pause;
}

if (resetBtn) {
  resetBtn.onclick = reset;
}

setMode('focus');


// ===== Cronômetro =====
const swDisplay =
  document.getElementById('stopwatchDisplay');

const swStartBtn =
  document.getElementById('swStart');

const swPauseBtn =
  document.getElementById('swPause');

const swResetBtn =
  document.getElementById('swReset');

const swLapBtn =
  document.getElementById('swLap');

const swSaveBtn =
  document.getElementById('swSaveSession');

const lapsList =
  document.getElementById('lapsList');

let swRunning = false;
let swStartAt = null;
let swElapsed = 0;
let swTimer = null;

const swLaps = [];

function renderStopwatch() {
  if (!swDisplay) {
    return;
  }

  const total = swElapsed;

  const h =
    Math.floor(total / 3600000)
      .toString()
      .padStart(2, '0');

  const m =
    Math.floor(
      (total % 3600000) / 60000
    )
      .toString()
      .padStart(2, '0');

  const s =
    Math.floor(
      (total % 60000) / 1000
    )
      .toString()
      .padStart(2, '0');

  swDisplay.textContent = `${h}:${m}:${s}`;
}

function renderLaps() {
  if (!lapsList) {
    return;
  }

  lapsList.innerHTML = '';

  swLaps.forEach((ms, idx) => {
    const prev =
      idx === 0
        ? 0
        : swLaps[idx - 1];

    const lapDur = ms - prev;

    const formatarTempo = (tempo) => {
      const hh =
        Math.floor(tempo / 3600000)
          .toString()
          .padStart(2, '0');

      const mm =
        Math.floor(
          (tempo % 3600000) / 60000
        )
          .toString()
          .padStart(2, '0');

      const ss =
        Math.floor(
          (tempo % 60000) / 1000
        )
          .toString()
          .padStart(2, '0');

      return `${hh}:${mm}:${ss}`;
    };

    const div =
      document.createElement('div');

    div.className = 'task';

    div.innerHTML = `
      <strong>Volta ${idx + 1}</strong>

      <small style="color:#666">
        Tempo total: ${formatarTempo(ms)}
        |
        Parcial: ${formatarTempo(lapDur)}
      </small>
    `;

    lapsList.appendChild(div);
  });
}

function swTick() {
  swElapsed =
    Date.now() - swStartAt;

  renderStopwatch();
}

if (swStartBtn) {
  swStartBtn.addEventListener('click', () => {
    if (swRunning) {
      return;
    }

    swRunning = true;

    swStartAt =
      Date.now() - swElapsed;

    swTimer =
      setInterval(swTick, 200);
  });
}

if (swPauseBtn) {
  swPauseBtn.addEventListener('click', () => {
    if (!swRunning) {
      return;
    }

    swRunning = false;

    clearInterval(swTimer);

    swTimer = null;

    swTick();
  });
}

if (swResetBtn) {
  swResetBtn.addEventListener('click', () => {
    swRunning = false;

    clearInterval(swTimer);

    swTimer = null;
    swElapsed = 0;

    swLaps.length = 0;

    renderStopwatch();
    renderLaps();
  });
}

if (swLapBtn) {
  swLapBtn.addEventListener('click', () => {
    if (swRunning) {
      swLaps.push(swElapsed);
      renderLaps();
    }
  });
}

if (swSaveBtn) {
  swSaveBtn.addEventListener('click', () => {
    const minutes =
      Math.round(swElapsed / 60000);

    if (minutes <= 0) {
      alert(
        'Cronômetro zerado. Inicie e registre algum tempo antes de salvar.'
      );

      return;
    }

    const discipline =
      stopwatchDiscipline
        ? stopwatchDiscipline.value
        : 'Geral';

    state.sessions.push({
      ts: Date.now(),
      minutes: minutes,
      mode: 'focus',
      discipline: discipline
    });

    save();
    updateHistory();
    updateCharts();
    updateGoalsView();

    alert('Sessão salva no histórico!');
  });
}

renderStopwatch();

// ==========================================
// MATÉRIAS ESTUDADAS RECENTEMENTE
// ==========================================

const recentStudiesList =
  document.getElementById('recentStudiesList');


function formatRecentDate(timestamp) {
  const date = new Date(timestamp);
  const now = new Date();

  const todayStart = new Date(
    now.getFullYear(),
    now.getMonth(),
    now.getDate()
  );

  const studyStart = new Date(
    date.getFullYear(),
    date.getMonth(),
    date.getDate()
  );

  const diffDays =
    Math.round(
      (todayStart - studyStart) / 86400000
    );


  const time = date.toLocaleTimeString(
    'pt-BR',
    {
      hour: '2-digit',
      minute: '2-digit'
    }
  );


  if (diffDays === 0) {
    return `Hoje às ${time}`;
  }


  if (diffDays === 1) {
    return `Ontem às ${time}`;
  }


  return `${date.toLocaleDateString(
    'pt-BR',
    {
      day: '2-digit',
      month: '2-digit'
    }
  )} às ${time}`;
}


function getRecentStudies() {

  const orderedSessions = [...state.sessions]
    .filter((session) => {
      return (
        session.mode === 'focus' &&
        session.discipline &&
        session.discipline !== 'Geral'
      );
    })
    .sort((a, b) => {
      return b.ts - a.ts;
    });


  const recentes = [];
  const materiasUsadas = new Set();


  for (const session of orderedSessions) {

    const materia =
      String(session.discipline).trim();


    if (
      !materia ||
      materiasUsadas.has(materia)
    ) {
      continue;
    }


    materiasUsadas.add(materia);

    recentes.push(session);


    if (recentes.length >= 5) {
      break;
    }
  }


  return recentes;
}


function updateRecentStudies() {

  if (!recentStudiesList) {
    return;
  }


  recentStudiesList.innerHTML = '';


  const recentes =
    getRecentStudies();


  // Nenhuma matéria estudada ainda
  if (recentes.length === 0) {

    const empty =
      document.createElement('div');

    empty.className =
      'recent-empty';


    const icon =
      document.createElement('i');

    icon.className =
      'fa-regular fa-clock';


    const text =
      document.createElement('span');

    text.textContent =
      'Suas matérias estudadas recentemente aparecerão aqui.';


    empty.appendChild(icon);
    empty.appendChild(text);

    recentStudiesList.appendChild(empty);

    return;
  }


  recentes.forEach((session) => {

    const item =
      document.createElement('div');

    item.className =
      'recent-study-item';


    // ÍCONE
    const iconBox =
      document.createElement('div');

    iconBox.className =
      'recent-study-icon';


    const icon =
      document.createElement('i');

    icon.className =
      'fa-solid fa-book-open';


    iconBox.appendChild(icon);


    // INFORMAÇÕES
    const info =
      document.createElement('div');

    info.className =
      'recent-study-info';


    const name =
      document.createElement('span');

    name.className =
      'recent-study-name';

    name.textContent =
      session.discipline;


    const meta =
      document.createElement('span');

    meta.className =
      'recent-study-meta';

    meta.textContent =
      formatRecentDate(session.ts);


    info.appendChild(name);
    info.appendChild(meta);


    // DURAÇÃO
    const duration =
      document.createElement('span');

    duration.className =
      'recent-study-duration';

    duration.textContent =
      `${session.minutes} min`;


    item.appendChild(iconBox);
    item.appendChild(info);
    item.appendChild(duration);


    recentStudiesList.appendChild(item);

  });
}

// ===== Metas Semanais =====
const saveGoalBtn =
  document.getElementById('saveGoal');

const goalHours =
  document.getElementById('goalHours');

const goalsList =
  document.getElementById('goalsList');

function getWeekRange(d = new Date()) {
  const dt = new Date(d);

  const day =
    (dt.getDay() + 6) % 7;

  const monday = new Date(dt);

  monday.setDate(
    dt.getDate() - day
  );

  monday.setHours(0, 0, 0, 0);

  const sunday = new Date(monday);

  sunday.setDate(
    monday.getDate() + 6
  );

  sunday.setHours(
    23,
    59,
    59,
    999
  );

  return {
    monday,
    sunday
  };
}

function minutesInWeekByDiscipline() {
  const {
    monday,
    sunday
  } = getWeekRange();

  const acc = {};

  for (const session of state.sessions) {
    if (session.mode !== 'focus') {
      continue;
    }

    const time = session.ts;

    if (
      time >= monday.getTime() &&
      time <= sunday.getTime()
    ) {
      acc[session.discipline] =
        (acc[session.discipline] || 0) +
        session.minutes;
    }
  }

  return acc;
}

function updateGoalsView() {
  if (!goalsList) {
    return;
  }

  goalsList.innerHTML = '';

  const minsMap =
    minutesInWeekByDiscipline();

  for (const discipline of state.disciplines) {
    const goalH =
      state.goals[discipline] || 0;

    if (!goalH) {
      continue;
    }

    const doneMin =
      minsMap[discipline] || 0;

    const goalMin =
      goalH * 60;

    const pct =
      Math.min(
        100,
        Math.floor(
          (doneMin / goalMin) * 100
        )
      );

    const wrap =
      document.createElement('div');

    wrap.innerHTML = `
      <div class="row between">
        <strong>${discipline}</strong>

        <span style="color:#666">
          ${Math.round(doneMin / 60)}h /
          ${goalH}h
        </span>
      </div>

      <div class="progress mt">
        <span style="width:${pct}%"></span>
      </div>
    `;

    goalsList.appendChild(wrap);
  }
}

if (saveGoalBtn) {
  saveGoalBtn.addEventListener('click', () => {
    const discipline =
      goalDiscipline &&
      goalDiscipline.value;

    const hours =
      +(goalHours && goalHours.value);

    if (!discipline || !hours) {
      return;
    }

    state.goals[discipline] = hours;

    save();

    if (goalHours) {
      goalHours.value = '';
    }

    updateGoalsView();
  });
}


// ===== Histórico + Export =====
const historyTableBody =
  document.querySelector(
    '#historyTable tbody'
  );

function updateHistory() {
  if (!historyTableBody) {
    return;
  }

  historyTableBody.innerHTML = '';

  const rows =
    [...state.sessions]
      .sort((a, b) => b.ts - a.ts);

  for (const session of rows) {
    const tr =
      document.createElement('tr');

    const date =
      new Date(session.ts);

    tr.innerHTML = `
      <td>
        ${date.toLocaleString('pt-BR')}
      </td>

      <td>
        ${session.discipline}
      </td>

      <td>
        ${
          session.mode === 'focus'
            ? 'Foco'
            : 'Pausa'
        }
      </td>

      <td>
        ${session.minutes}
      </td>
    `;

    historyTableBody.appendChild(tr);
  }
}

const clearHistoryBtn =
  document.getElementById('clearHistory');

const exportCsvBtn =
  document.getElementById('exportCsv');

if (clearHistoryBtn) {
  clearHistoryBtn.addEventListener(
    'click',
    () => {
      if (
        !confirm(
          'Tem certeza que deseja limpar o histórico?'
        )
      ) {
        return;
      }

      state.sessions = [];

      save();
      updateHistory();
      updateCharts();
      updateGoalsView();
    }
  );
}

if (exportCsvBtn) {
  exportCsvBtn.addEventListener(
    'click',
    () => {
      const header = [
        'data',
        'disciplina',
        'modo',
        'minutos'
      ];

      const lines = [
        header.join(',') + '\n'
      ];

      for (const session of state.sessions) {
        const date =
          new Date(
            session.ts
          ).toISOString();

        lines.push(
          [
            date,
            session.discipline,
            session.mode,
            session.minutes
          ]
            .map((value) => {
              return `"${String(value)
                .replace(/"/g, '""')}"`;
            })
            .join(',') + '\n'
        );
      }

      const blob = new Blob(
        [lines.join('')],
        {
          type:
            'text/csv;charset=utf-8;'
        }
      );

      const link =
        document.createElement('a');

      link.href =
        URL.createObjectURL(blob);

      link.download =
        'foag_estudos.csv';

      link.click();

      URL.revokeObjectURL(link.href);
    }
  );
}


// ===== Gráficos =====
const lineCtx =
  document.getElementById('lineChart');

const pieCtx =
  document.getElementById('pieChart');

let lineChart;
let pieChart;

function hoursLastNDays(n = 14) {
  const today = new Date();

  today.setHours(0, 0, 0, 0);

  const labels = [];
  const mins = [];

  for (let i = n - 1; i >= 0; i--) {
    const day =
      new Date(today);

    day.setDate(
      today.getDate() - i
    );

    const start =
      day.getTime();

    const end =
      start + 86400000 - 1;

    const minutes =
      state.sessions
        .filter((session) => {
          return (
            session.mode === 'focus' &&
            session.ts >= start &&
            session.ts <= end
          );
        })
        .reduce(
          (total, session) => {
            return (
              total +
              session.minutes
            );
          },
          0
        );

    labels.push(
      day.toLocaleDateString(
        'pt-BR',
        {
          day: '2-digit',
          month: '2-digit'
        }
      )
    );

    mins.push(
      Math.round(
        (minutes / 60) * 100
      ) / 100
    );
  }

  return {
    labels,
    hours: mins
  };
}

function distributionByDiscipline() {
  const by = {};

  for (const session of state.sessions) {
    if (session.mode === 'focus') {
      by[session.discipline] =
        (by[session.discipline] || 0) +
        session.minutes;
    }
  }

  const labels =
    Object.keys(by);

  const hours =
    labels.map((key) => {
      return (
        Math.round(
          (by[key] / 60) * 100
        ) / 100
      );
    });

  return {
    labels,
    hours
  };
}

function updateCharts() {
  if (
    typeof Chart === 'undefined' ||
    !lineCtx ||
    !pieCtx
  ) {
    return;
  }

  const hl =
    hoursLastNDays(14);

  const dist =
    distributionByDiscipline();

  try {
    if (lineChart) {
      lineChart.destroy();
    }

    if (pieChart) {
      pieChart.destroy();
    }
  } catch (e) {
    console.error(
      'Erro ao destruir gráficos:',
      e
    );
  }

  try {
    lineChart =
      new Chart(lineCtx, {
        type: 'line',

        data: {
          labels: hl.labels,

          datasets: [
            {
              label: 'Horas por dia',
              data: hl.hours,
              tension: 0.3
            }
          ]
        },

        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: {
              display: false
            }
          },

          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

    pieChart =
      new Chart(pieCtx, {
        type: 'doughnut',

        data: {
          labels: dist.labels,

          datasets: [
            {
              data: dist.hours
            }
          ]
        },

        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
  } catch (e) {
    console.error(
      'Erro ao criar gráficos:',
      e
    );
  }
}


// ===== Inicialização =====
updateHistory();
updateCharts();
updateGoalsView();