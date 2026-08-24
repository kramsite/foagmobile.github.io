// calend.js — FOAG
// Calendário + Agenda + Frequência + Projeção anual

document.addEventListener('DOMContentLoaded', () => {

  // =====================================================
  // DADOS
  // =====================================================

  const agendaData =
    window.CAL_AGENDA_DATA || {
      notas: [],
      tarefas: [],
      nao_esquecer: []
    };

  const rawCalendData =
    window.CAL_CALEND_DATA || {};


  // =====================================================
  // GARANTIR QUE O JSON VIRE OBJETO E NÃO ARRAY
  // =====================================================

  function garantirObjeto(valor) {

    if (
      valor &&
      typeof valor === 'object' &&
      !Array.isArray(valor)
    ) {
      return valor;
    }

    return {};
  }


  const calendData = {

    dias:
      garantirObjeto(
        rawCalendData.dias
      ),

    metas:
      garantirObjeto(
        rawCalendData.metas
      ),

    configuracoes:
      garantirObjeto(
        rawCalendData.configuracoes
      )

  };


  const AGENDA_SAVE_URL =
    window.CAL_AGENDA_SAVE_URL ||
    '../bloco/salvar_agenda.php';

  const HORARIO_API_URL =
    window.CAL_HORARIO_URL ||
    '../horario/horario_api.php';

  const CALEND_SAVE_URL =
    window.CAL_CALEND_SAVE_URL ||
    'salvar_calendario.php';

  const ANO_ATUAL =
    Number(
      window.CAL_ANO ||
      new Date().getFullYear()
    );

  const NOMES_MESES = [
    'Janeiro',
    'Fevereiro',
    'Março',
    'Abril',
    'Maio',
    'Junho',
    'Julho',
    'Agosto',
    'Setembro',
    'Outubro',
    'Novembro',
    'Dezembro'
  ];


  // =====================================================
  // UTILIDADES
  // =====================================================

  function clamp(
    numero,
    minimo,
    maximo
  ) {

    return Math.max(
      minimo,
      Math.min(
        maximo,
        numero
      )
    );

  }


  function hojeIso() {

    const agora =
      new Date();

    return (
      `${agora.getFullYear()}-` +
      `${String(
        agora.getMonth() + 1
      ).padStart(2, '0')}-` +
      `${String(
        agora.getDate()
      ).padStart(2, '0')}`
    );

  }


  function dataIsoValida(valor) {

    return (
      typeof valor === 'string' &&
      /^\d{4}-\d{2}-\d{2}$/.test(valor)
    );

  }


  function diaSemanaIso(iso) {

    return new Date(
      iso + 'T00:00:00'
    ).getDay();

  }


  function ehFimDeSemana(iso) {

    const dia =
      diaSemanaIso(iso);

    return (
      dia === 0 ||
      dia === 6
    );

  }


  function isoEntre(
    iso,
    inicio,
    fim
  ) {

    return (
      dataIsoValida(inicio) &&
      dataIsoValida(fim) &&
      iso >= inicio &&
      iso <= fim
    );

  }


  function escapeHtml(valor) {

    const div =
      document.createElement('div');

    div.textContent =
      String(valor ?? '');

    return div.innerHTML;

  }


  function formataDataBR(iso) {

    const [
      ano,
      mes,
      dia
    ] =
      iso
        .split('-')
        .map(Number);

    return (
      `${String(dia).padStart(2, '0')}/` +
      `${String(mes).padStart(2, '0')}/` +
      `${ano}`
    );

  }


  // =====================================================
  // CONFIGURAÇÃO ANUAL
  // =====================================================

  function obterConfigAno(
    ano = ANO_ATUAL
  ) {

    const chave =
      String(ano);


    if (
      !calendData.configuracoes ||
      typeof calendData.configuracoes !== 'object' ||
      Array.isArray(
        calendData.configuracoes
      )
    ) {

      calendData.configuracoes = {};

    }


    if (
      !calendData.configuracoes[chave] ||
      typeof calendData.configuracoes[chave] !== 'object' ||
      Array.isArray(
        calendData.configuracoes[chave]
      )
    ) {

      calendData.configuracoes[chave] = {};

    }


    const config =
      calendData.configuracoes[chave];


    if (
      !Number.isFinite(
        Number(
          config.meta_anual
        )
      )
    ) {

      config.meta_anual =
        80;

    }


    config.inicio_ano_letivo =
      dataIsoValida(
        config.inicio_ano_letivo
      )
        ? config.inicio_ano_letivo
        : '';


    config.fim_ano_letivo =
      dataIsoValida(
        config.fim_ano_letivo
      )
        ? config.fim_ano_letivo
        : '';


    config.inicio_ferias_meio =
      dataIsoValida(
        config.inicio_ferias_meio
      )
        ? config.inicio_ferias_meio
        : '';


    config.fim_ferias_meio =
      dataIsoValida(
        config.fim_ferias_meio
      )
        ? config.fim_ferias_meio
        : '';


    return config;

  }


  function periodoLetivoPermite(
    iso,
    config
  ) {

    if (
      config.inicio_ano_letivo &&
      iso < config.inicio_ano_letivo
    ) {
      return false;
    }


    if (
      config.fim_ano_letivo &&
      iso > config.fim_ano_letivo
    ) {
      return false;
    }


    if (
      isoEntre(
        iso,
        config.inicio_ferias_meio,
        config.fim_ferias_meio
      )
    ) {
      return false;
    }


    return true;

  }


  function validarConfigAno(config) {

    if (
      config.inicio_ano_letivo &&
      config.fim_ano_letivo &&
      config.inicio_ano_letivo >
        config.fim_ano_letivo
    ) {

      return (
        'O início do ano letivo não pode ser depois do final.'
      );

    }


    if (
      config.inicio_ferias_meio &&
      config.fim_ferias_meio &&
      config.inicio_ferias_meio >
        config.fim_ferias_meio
    ) {

      return (
        'O início das férias não pode ser depois do final.'
      );

    }


    return '';

  }


  // =====================================================
  // SALVAR CALENDÁRIO NO JSON
  // =====================================================

  async function salvarCalendarioServidor() {

    try {

      console.log(
        'Enviando calendário:',
        calendData
      );


      console.log(
        'JSON enviado:',
        JSON.stringify(
          calendData
        )
      );


      const resposta =
        await fetch(
          CALEND_SAVE_URL,
          {
            method: 'POST',

            credentials:
              'same-origin',

            headers: {
              'Content-Type':
                'application/json'
            },

            body:
              JSON.stringify(
                calendData
              )
          }
        );


      const retorno =
        await resposta
          .json()
          .catch(
            () => null
          );


      if (
        !resposta.ok
      ) {

        throw new Error(
          retorno?.mensagem ||
          `HTTP ${resposta.status}`
        );

      }


      if (
        retorno &&
        retorno.sucesso === false
      ) {

        throw new Error(
          retorno.mensagem ||
          'Erro ao salvar calendário.'
        );

      }


      console.log(
        'Calendário salvo com sucesso.'
      );


      return true;


    } catch (erro) {

      console.error(
        'Erro ao salvar calendário:',
        erro
      );


      return false;

    }

  }


  // =====================================================
  // SALVAR AGENDA
  // =====================================================

  async function salvarAgendaServidor() {

    try {

      await fetch(
        AGENDA_SAVE_URL,
        {
          method: 'POST',

          credentials:
            'same-origin',

          headers: {
            'Content-Type':
              'application/json'
          },

          body:
            JSON.stringify(
              agendaData
            )
        }
      );


    } catch (erro) {

      console.error(
        'Erro ao salvar agenda:',
        erro
      );

    }

  }


  // =====================================================
  // ELEMENTOS DO PAINEL ANUAL
  // =====================================================

  const inputMetaAnual =
    document.getElementById(
      'meta-anual'
    );

  const inputInicioAno =
    document.getElementById(
      'inicio-ano-letivo'
    );

  const inputFimAno =
    document.getElementById(
      'fim-ano-letivo'
    );

  const inputInicioFerias =
    document.getElementById(
      'inicio-ferias-meio'
    );

  const inputFimFerias =
    document.getElementById(
      'fim-ferias-meio'
    );

  const erroConfig =
    document.getElementById(
      'freq-config-erro'
    );


  // =====================================================
  // PREENCHER CONFIGURAÇÃO
  // =====================================================

  function preencherConfigAno() {

    const config =
      obterConfigAno();


    if (inputMetaAnual) {

      inputMetaAnual.value =
        String(
          clamp(
            Number(
              config.meta_anual
            ),
            0,
            100
          )
        );

    }


    if (inputInicioAno) {

      inputInicioAno.value =
        config.inicio_ano_letivo;

    }


    if (inputFimAno) {

      inputFimAno.value =
        config.fim_ano_letivo;

    }


    if (inputInicioFerias) {

      inputInicioFerias.value =
        config.inicio_ferias_meio;

    }


    if (inputFimFerias) {

      inputFimFerias.value =
        config.fim_ferias_meio;

    }

  }


  // =====================================================
  // ATUALIZAR CONFIGURAÇÃO
  // =====================================================

  async function atualizarConfigAno() {

    const config =
      obterConfigAno();


    config.meta_anual =
      clamp(
        Number(
          inputMetaAnual?.value ||
          80
        ),
        0,
        100
      );


    config.inicio_ano_letivo =
      inputInicioAno?.value ||
      '';


    config.fim_ano_letivo =
      inputFimAno?.value ||
      '';


    config.inicio_ferias_meio =
      inputInicioFerias?.value ||
      '';


    config.fim_ferias_meio =
      inputFimFerias?.value ||
      '';


    const erro =
      validarConfigAno(
        config
      );


    if (erroConfig) {

      erroConfig.textContent =
        erro;

    }


    if (erro) {
      return;
    }


    const salvou =
      await salvarCalendarioServidor();


    if (!salvou) {

      alert(
        'Não foi possível salvar as configurações do calendário.'
      );

      return;

    }


    atualizarTudo();

  }


  [
    inputMetaAnual,
    inputInicioAno,
    inputFimAno,
    inputInicioFerias,
    inputFimFerias
  ].forEach(
    input => {

      input?.addEventListener(
        'change',
        atualizarConfigAno
      );

    }
  );


  // =====================================================
  // TAREFAS
  // =====================================================

  function tarefasDoDia(iso) {

    const lista =
      Array.isArray(
        agendaData.tarefas
      )
        ? agendaData.tarefas
        : [];


    return lista.filter(
      tarefa =>
        tarefa.data === iso &&
        tarefa.texto &&
        tarefa.texto.trim() !== ''
    );

  }


  function marcarDiasComTarefa() {

    if (
      !Array.isArray(
        agendaData.tarefas
      )
    ) {
      return;
    }


    agendaData.tarefas.forEach(
      tarefa => {

        const iso =
          tarefa.data;


        if (!iso) {
          return;
        }


        const dia =
          document.querySelector(
            `.calendario .dia[data-date="${iso}"]`
          );


        if (dia) {

          dia.classList.add(
            'has-tarefa'
          );

        }

      }
    );

  }


  function salvarTextoDoDiaNaAgenda(
    iso,
    texto
  ) {

    if (
      !Array.isArray(
        agendaData.tarefas
      )
    ) {

      agendaData.tarefas = [];

    }


    agendaData.tarefas =
      agendaData.tarefas.filter(
        tarefa =>
          !(
            tarefa.data === iso &&
            tarefa.origem ===
              'calendario'
          )
      );


    const textoLimpo =
      String(
        texto || ''
      ).trim();


    if (textoLimpo) {

      agendaData.tarefas.push({
        texto:
          textoLimpo,

        data:
          iso,

        origem:
          'calendario'
      });

    }


    const dia =
      document.querySelector(
        `.calendario .dia[data-date="${iso}"]`
      );


    if (dia) {

      dia.classList.toggle(
        'has-tarefa',
        Boolean(
          textoLimpo
        )
      );


      atualizarDots(
        dia
      );

    }


    salvarAgendaServidor();

  }


  // =====================================================
  // BUSCAR HORÁRIOS
  // =====================================================

  async function buscarHorarios(iso) {

    if (!HORARIO_API_URL) {
      return [];
    }


    try {

      const resposta =
        await fetch(
          `${HORARIO_API_URL}?data=${encodeURIComponent(iso)}`
        );


      if (!resposta.ok) {

        throw new Error(
          `HTTP ${resposta.status}`
        );

      }


      const json =
        await resposta.json();


      if (
        !json ||
        !json.html
      ) {

        return [];

      }


      const diaSemana =
        diaSemanaIso(
          iso
        );


      const mapaColuna = {
        1: 1,
        2: 2,
        3: 3,
        4: 4,
        5: 5
      };


      const coluna =
        mapaColuna[
          diaSemana
        ];


      if (!coluna) {
        return [];
      }


      const parser =
        new DOMParser();


      const documento =
        parser.parseFromString(
          `<table>${json.html}</table>`,
          'text/html'
        );


      const materias =
        new Set();


      documento
        .querySelectorAll('tr')
        .forEach(
          linha => {

            const colunas =
              linha.querySelectorAll(
                'td'
              );


            if (
              colunas.length >
              coluna
            ) {

              const texto =
                colunas[
                  coluna
                ].textContent.trim();


              if (texto) {

                materias.add(
                  texto
                );

              }

            }

          }
        );


      return Array.from(
        materias
      );


    } catch (erro) {

      console.error(
        'Erro ao buscar horários:',
        erro
      );


      return [];

    }

  }


  // =====================================================
  // STATUS DO DIA
  // =====================================================

  function statusDia(dia) {

    if (
      dia.classList.contains(
        'vermelho'
      )
    ) {
      return 'vermelho';
    }


    if (
      dia.classList.contains(
        'amarelo'
      )
    ) {
      return 'amarelo';
    }


    if (
      dia.classList.contains(
        'sem-aula'
      )
    ) {
      return 'sem-aula';
    }


    if (
      dia.classList.contains(
        'roxo'
      )
    ) {
      return 'roxo';
    }


    return null;

  }


  // =====================================================
  // DIA LETIVO
  // =====================================================

  function diaContaComoLetivo(
    dia,
    config,
    incluirFuturo = false
  ) {

    const iso =
      dia.getAttribute(
        'data-date'
      );


    if (!iso) {
      return false;
    }


    if (
      ehFimDeSemana(iso) ||
      dia.classList.contains(
        'feriado'
      ) ||
      dia.classList.contains(
        'sem-aula'
      ) ||
      !periodoLetivoPermite(
        iso,
        config
      )
    ) {

      return false;

    }


    if (
      !incluirFuturo &&
      iso > hojeIso()
    ) {

      return false;

    }


    return true;

  }


  // =====================================================
  // PRESENÇA AUTOMÁTICA
  // =====================================================

  function ehPresencaAutomatica(
    dia
  ) {

    const mes =
      dia.closest(
        '.mes'
      );


    if (!mes) {
      return false;
    }


    const config =
      obterConfigAno(
        Number(
          mes.dataset.ano
        )
      );


    if (
      !diaContaComoLetivo(
        dia,
        config,
        false
      )
    ) {

      return false;

    }


    return (
      !dia.classList.contains(
        'vermelho'
      ) &&
      !dia.classList.contains(
        'amarelo'
      )
    );

  }


  // =====================================================
  // PERÍODO LETIVO
  // =====================================================

  function aplicarMarcacoesPeriodo() {

    const config =
      obterConfigAno();


    document
      .querySelectorAll(
        '.calendario .dia[data-date]'
      )
      .forEach(
        dia => {

          const iso =
            dia.getAttribute(
              'data-date'
            );


          dia.classList.remove(
            'ferias-meio-ano',
            'fora-periodo-letivo'
          );


          dia
            .querySelectorAll(
              '.periodo-badge'
            )
            .forEach(
              badge =>
                badge.remove()
            );


          if (
            config.inicio_ano_letivo &&
            iso <
              config.inicio_ano_letivo
          ) {

            dia.classList.add(
              'fora-periodo-letivo'
            );

          }


          if (
            config.fim_ano_letivo &&
            iso >
              config.fim_ano_letivo
          ) {

            dia.classList.add(
              'fora-periodo-letivo'
            );

          }


          if (
            isoEntre(
              iso,
              config.inicio_ferias_meio,
              config.fim_ferias_meio
            )
          ) {

            dia.classList.add(
              'ferias-meio-ano'
            );

          }


          const marcacoes = [
            {
              data:
                config.inicio_ano_letivo,

              texto:
                'Início'
            },

            {
              data:
                config.fim_ano_letivo,

              texto:
                'Fim'
            },

            {
              data:
                config.inicio_ferias_meio,

              texto:
                'Férias'
            },

            {
              data:
                config.fim_ferias_meio,

              texto:
                'Fim férias'
            }
          ];


          marcacoes.forEach(
            item => {

              if (
                item.data &&
                iso === item.data
              ) {

                const badge =
                  document.createElement(
                    'span'
                  );


                badge.className =
                  'periodo-badge';


                badge.textContent =
                  item.texto;


                dia.appendChild(
                  badge
                );

              }

            }
          );

        }
      );

  }


  // =====================================================
  // DOTS
  // =====================================================

  function criaDot(tipo) {

    const dot =
      document.createElement(
        'span'
      );

    dot.className =
      `dot ${tipo}`;

    return dot;

  }


  function atualizarDots(dia) {

    const dots =
      dia.querySelector(
        '.dots'
      );


    if (!dots) {
      return;
    }


    dots.innerHTML =
      '';


    dia.classList.remove(
      'presenca-automatica'
    );


    if (
      ehPresencaAutomatica(
        dia
      )
    ) {

      dots.appendChild(
        criaDot(
          'presenca'
        )
      );


      dia.classList.add(
        'presenca-automatica'
      );

    }


    if (
      dia.classList.contains(
        'vermelho'
      )
    ) {

      dots.appendChild(
        criaDot(
          'vermelho'
        )
      );

    }


    if (
      dia.classList.contains(
        'amarelo'
      )
    ) {

      dots.appendChild(
        criaDot(
          'amarelo'
        )
      );

    }


    if (
      dia.classList.contains(
        'sem-aula'
      )
    ) {

      dots.appendChild(
        criaDot(
          'semaula'
        )
      );

    }


    if (
      dia.classList.contains(
        'roxo'
      )
    ) {

      dots.appendChild(
        criaDot(
          'roxo'
        )
      );

    }


    if (
      dia.classList.contains(
        'has-tarefa'
      )
    ) {

      dots.appendChild(
        criaDot(
          'tarefa'
        )
      );

    }

  }


  // =====================================================
  // DESTACAR HOJE
  // =====================================================

  function destacarHoje() {

    const hoje =
      hojeIso();


    document
      .querySelectorAll(
        '.calendario .dia[data-date]'
      )
      .forEach(
        dia => {

          const iso =
            dia.getAttribute(
              'data-date'
            );


          const ehHoje =
            iso === hoje;


          dia.classList.toggle(
            'dia-hoje',
            ehHoje
          );


          const badge =
            dia.querySelector(
              '.hoje-badge'
            );


          if (
            ehHoje &&
            !badge
          ) {

            const novo =
              document.createElement(
                'span'
              );


            novo.className =
              'hoje-badge';


            novo.textContent =
              'Hoje';


            dia.appendChild(
              novo
            );

          }


          if (
            !ehHoje &&
            badge
          ) {

            badge.remove();

          }

        }
      );

  }


  // =====================================================
  // DIAS RESTANTES
  // =====================================================

  function diasRestantesPeriodo(
    dias,
    config
  ) {

    const hoje =
      hojeIso();


    return dias.filter(
      dia => {

        const iso =
          dia.getAttribute(
            'data-date'
          );


        return (
          iso &&
          iso > hoje &&
          diaContaComoLetivo(
            dia,
            config,
            true
          )
        );

      }
    ).length;

  }


  // =====================================================
  // FALTAS QUE AINDA PODE TER
  // =====================================================

  function calcularFaltasPossiveis(
    presencas,
    totalAtual,
    diasRestantes,
    meta
  ) {

    const alvo =
      clamp(
        Number(meta),
        0,
        100
      ) / 100;


    if (alvo <= 0) {
      return null;
    }


    const totalFinal =
      totalAtual +
      diasRestantes;


    const presencasFinais =
      presencas +
      diasRestantes;


    const maxFaltas =
      Math.floor(
        presencasFinais -
        alvo *
        totalFinal +
        0.000000001
      );


    return Math.max(
      0,
      maxFaltas
    );

  }


  // =====================================================
  // MÉTRICAS DO MÊS
  // =====================================================

  function calcularMetricasMesDados(
    mes
  ) {

    const config =
      obterConfigAno(
        Number(
          mes.dataset.ano
        )
      );


    const dias = [
      ...mes.querySelectorAll(
        '.dia[data-date]'
      )
    ];


    let presencas = 0;
    let faltas = 0;
    let atestados = 0;
    let semAula = 0;
    let provas = 0;
    let totalDiasLetivos = 0;


    dias.forEach(
      dia => {

        if (
          dia.classList.contains(
            'sem-aula'
          )
        ) {
          semAula++;
        }


        if (
          dia.classList.contains(
            'roxo'
          )
        ) {
          provas++;
        }


        if (
          !diaContaComoLetivo(
            dia,
            config,
            false
          )
        ) {
          return;
        }


        totalDiasLetivos++;


        if (
          dia.classList.contains(
            'vermelho'
          )
        ) {

          faltas++;
          return;

        }


        if (
          dia.classList.contains(
            'amarelo'
          )
        ) {

          atestados++;
          return;

        }


        presencas++;

      }
    );


    const percentual =
      totalDiasLetivos > 0
        ? Math.round(
            (
              presencas /
              totalDiasLetivos
            ) *
            100
          )
        : 0;


    const diasRestantes =
      diasRestantesPeriodo(
        dias,
        config
      );


    return {
      presencas,
      faltas,
      atestados,
      semAula,
      provas,
      totalDiasLetivos,
      percentual,
      diasRestantes
    };

  }


  // =====================================================
  // TEXTO DA META
  // =====================================================

  function textoDiferencaMeta(
    atual,
    meta
  ) {

    const diferenca =
      Math.round(
        (
          atual -
          meta
        ) *
        10
      ) / 10;


    if (diferenca > 0) {

      return (
        `Você está ${diferenca} pontos percentuais acima da meta.`
      );

    }


    if (diferenca < 0) {

      return (
        `Você está ${Math.abs(diferenca)} pontos percentuais abaixo da meta.`
      );

    }


    return (
      'Você está exatamente na meta.'
    );

  }


  // =====================================================
  // RECALCULAR MÊS
  // =====================================================

  function recalcularMetricasDoMes(
    mes
  ) {

    const dados =
      calcularMetricasMesDados(
        mes
      );


    const metaInput =
      mes.querySelector(
        '.meta-presenca'
      );


    const meta =
      clamp(
        Number(
          metaInput?.value ||
          80
        ),
        0,
        100
      );


    const campos = {
      '.count-presenca':
        dados.presencas,

      '.count-falta':
        dados.faltas,

      '.count-atestado':
        dados.atestados,

      '.count-semaula':
        dados.semAula,

      '.count-prova':
        dados.provas
    };


    Object.entries(
      campos
    ).forEach(
      ([seletor, valor]) => {

        const elemento =
          mes.querySelector(
            seletor
          );


        if (elemento) {

          elemento.textContent =
            String(valor);

        }

      }
    );


    const barra =
      mes.querySelector(
        '.progress-bar'
      );


    if (barra) {

      barra.style.width =
        `${Math.min(
          100,
          dados.percentual
        )}%`;

    }


    const label =
      mes.querySelector(
        '.label-presenca'
      );


    if (label) {

      label.textContent =
        `${dados.percentual}%`;

    }


    const statusMeta =
      mes.querySelector(
        '.meta-status-mes'
      );


    if (statusMeta) {

      statusMeta.textContent =
        (
          `Meta: ${meta}% · ` +
          `Atual: ${dados.percentual}% · ` +
          textoDiferencaMeta(
            dados.percentual,
            meta
          )
        );

    }


    const faltasRestantes =
      mes.querySelector(
        '.faltas-restantes-mes'
      );


    if (faltasRestantes) {

      const quantidade =
        calcularFaltasPossiveis(
          dados.presencas,
          dados.totalDiasLetivos,
          dados.diasRestantes,
          meta
        );


      faltasRestantes.textContent =
        quantidade === null
          ? 'Meta 0%: não há limite calculado de faltas.'
          : (
              `Você ainda pode ter até ${quantidade} ` +
              `${quantidade === 1 ? 'falta' : 'faltas'} ` +
              `mantendo pelo menos ${meta}%.`
            );

    }


    return dados;

  }


  // =====================================================
  // CLASSIFICAÇÃO ANUAL
  // =====================================================

  function classificacaoRisco(
    percentual
  ) {

    if (
      percentual > 85
    ) {

      return {
        texto:
          'Frequência ótima',

        classe:
          'otima'
      };

    }


    if (
      percentual >= 75
    ) {

      return {
        texto:
          'Atenção',

        classe:
          'atencao'
      };

    }


    return {
      texto:
        'Risco de reprovação por frequência',

      classe:
        'risco'
    };

  }


  // =====================================================
  // RESUMO + PROJEÇÃO ANUAL
  // =====================================================

  function atualizarResumoAnual() {

    const meses = [
      ...document.querySelectorAll(
        '.mes'
      )
    ];


    let presencas = 0;
    let faltas = 0;
    let atestados = 0;
    let totalDiasLetivos = 0;
    let diasRestantes = 0;

    const porMes = [];


    meses.forEach(
      mes => {

        const dados =
          recalcularMetricasDoMes(
            mes
          );


        presencas +=
          dados.presencas;

        faltas +=
          dados.faltas;

        atestados +=
          dados.atestados;

        totalDiasLetivos +=
          dados.totalDiasLetivos;

        diasRestantes +=
          dados.diasRestantes;


        porMes.push({
          mes:
            Number(
              mes.dataset.mes
            ),

          ...dados
        });

      }
    );


    const percentual =
      totalDiasLetivos > 0
        ? Math.round(
            (
              presencas /
              totalDiasLetivos
            ) *
            100
          )
        : 0;


    const mesesComDados =
      porMes.filter(
        item =>
          item.totalDiasLetivos > 0
      );


    const melhorMes =
      mesesComDados.length
        ? [...mesesComDados]
            .sort(
              (a, b) =>
                b.percentual -
                a.percentual
            )[0]
        : null;


    const mesMaisFaltas =
      mesesComDados.length
        ? [...mesesComDados]
            .sort(
              (a, b) =>
                b.faltas -
                a.faltas
            )[0]
        : null;


    const config =
      obterConfigAno();


    const meta =
      clamp(
        Number(
          config.meta_anual ||
          80
        ),
        0,
        100
      );


    const periodoConfigurado =
      Boolean(
        config.inicio_ano_letivo &&
        config.fim_ano_letivo
      );


    const faltasPossiveis =
      periodoConfigurado
        ? calcularFaltasPossiveis(
            presencas,
            totalDiasLetivos,
            diasRestantes,
            meta
          )
        : null;


    // =====================================================
    // PROJEÇÃO FINAL SEM NOVAS FALTAS
    // =====================================================

    const totalFinalPrevisto =
      totalDiasLetivos +
      diasRestantes;


    const presencasFinais =
      presencas +
      diasRestantes;


    const projecaoFinal =
      periodoConfigurado &&
      totalFinalPrevisto > 0
        ? Math.round(
            (
              presencasFinais /
              totalFinalPrevisto
            ) *
            100
          )
        : 0;


    const risco =
      totalDiasLetivos > 0
        ? classificacaoRisco(
            percentual
          )
        : {
            texto:
              periodoConfigurado
                ? 'Sem dias letivos contabilizados'
                : 'Configure o período letivo',

            classe:
              ''
          };


    const mapaTexto = {

      'freq-anual-percentual':
        `${percentual}%`,

      'freq-anual-faltas':
        String(faltas),

      'freq-anual-atestados':
        String(atestados),

      'freq-melhor-mes':
        melhorMes
          ? (
              `${NOMES_MESES[
                melhorMes.mes - 1
              ]} · ${melhorMes.percentual}%`
            )
          : '—',

      'freq-mes-mais-faltas':
        mesMaisFaltas
          ? (
              `${NOMES_MESES[
                mesMaisFaltas.mes - 1
              ]} · ${mesMaisFaltas.faltas}`
            )
          : '—',

      'freq-meta-resumo':
        `Meta: ${meta}% · Atual: ${percentual}%`,

      'freq-meta-percent':
        `${percentual}%`,

      'proj-dias-passados':
        periodoConfigurado
          ? String(
              totalDiasLetivos
            )
          : '—',

      'proj-dias-restantes':
        periodoConfigurado
          ? String(
              diasRestantes
            )
          : '—',

      'proj-presencas':
        periodoConfigurado
          ? String(
              presencas
            )
          : '—',

      'proj-final-sem-faltas':
        periodoConfigurado
          ? `${projecaoFinal}%`
          : '—',

      'freq-diferenca-meta':
        periodoConfigurado
          ? textoDiferencaMeta(
              percentual,
              meta
            )
          : (
              'Defina o início e o final do ano letivo para calcular a meta anual.'
            ),

      'freq-faltas-restantes':
        !periodoConfigurado
          ? (
              'As faltas restantes serão calculadas depois que o período letivo for definido.'
            )
          : (
              faltasPossiveis === null
                ? (
                    'Meta 0%: não há limite calculado de faltas.'
                  )
                : (
                    `Você ainda pode ter até ${faltasPossiveis} ` +
                    `${faltasPossiveis === 1 ? 'falta' : 'faltas'} ` +
                    `mantendo pelo menos ${meta}% de frequência.`
                  )
            )

    };


    Object.entries(
      mapaTexto
    ).forEach(
      ([id, texto]) => {

        const elemento =
          document.getElementById(
            id
          );


        if (elemento) {

          elemento.textContent =
            texto;

        }

      }
    );


    const barra =
      document.getElementById(
        'freq-progress-fill'
      );


    if (barra) {

      barra.style.width =
        `${Math.min(
          100,
          percentual
        )}%`;

    }


    const badge =
      document.getElementById(
        'freq-risco'
      );


    if (badge) {

      badge.textContent =
        risco.texto;


      badge.classList.remove(
        'otima',
        'atencao',
        'risco'
      );


      if (risco.classe) {

        badge.classList.add(
          risco.classe
        );

      }

    }


    // =====================================================
    // AVISO DA PROJEÇÃO
    // =====================================================

    const aviso =
      document.getElementById(
        'freq-projecao-aviso'
      );


    if (aviso) {

      const texto =
        aviso.querySelector(
          'span'
        );


      aviso.classList.remove(
        'neutro',
        'seguro',
        'atencao',
        'critico'
      );


      let classe =
        'neutro';

      let mensagem =
        'Configure o período letivo para gerar a projeção.';


      if (periodoConfigurado) {

        if (
          totalDiasLetivos === 0
        ) {

          mensagem =
            'O período letivo está configurado, mas ainda não há dias contabilizados.';

        }

        else if (
          percentual < meta &&
          projecaoFinal >= meta
        ) {

          classe =
            'atencao';

          mensagem =
            (
              `Sua frequência atual está abaixo da meta de ${meta}%, ` +
              `mas ainda pode se recuperar. ` +
              `Sem novas faltas, a projeção final é ${projecaoFinal}%.`
            );

        }

        else if (
          percentual < meta
        ) {

          classe =
            'critico';

          mensagem =
            (
              `Sua frequência está abaixo da meta de ${meta}%. ` +
              `Mesmo sem novas faltas, a projeção final é ${projecaoFinal}%.`
            );

        }

        else if (
          faltasPossiveis === 0
        ) {

          classe =
            'critico';

          mensagem =
            (
              `Você está dentro da meta de ${meta}%, ` +
              `mas não possui margem para novas faltas.`
            );

        }

        else if (
          faltasPossiveis !== null &&
          faltasPossiveis <= 2
        ) {

          classe =
            'atencao';

          mensagem =
            (
              `Atenção: você só pode ter mais ${faltasPossiveis} ` +
              `${faltasPossiveis === 1 ? 'falta' : 'faltas'} ` +
              `e manter pelo menos ${meta}%.`
            );

        }

        else {

          classe =
            'seguro';

          mensagem =
            (
              `Você está dentro da meta. ` +
              `Ainda pode ter até ${faltasPossiveis} faltas ` +
              `e manter pelo menos ${meta}% de frequência.`
            );

        }

      }


      aviso.classList.add(
        classe
      );


      if (texto) {

        texto.textContent =
          mensagem;

      }

    }

  }


  // =====================================================
  // FECHAR MÊS
  // =====================================================

  function fecharMes(mes) {

    if (!mes) {
      return;
    }


    mes.classList.remove(
      'expanded'
    );


    mes.__corSelecionada =
      null;


    mes
      .__atualizarBotoesCor
      ?.();


    const fechar =
      mes.querySelector(
        '.fechar-btn'
      );


    if (fechar) {

      fechar.style.display =
        'none';

    }


    if (
      !document.querySelector(
        '.mes.expanded'
      )
    ) {

      document.body
        .classList
        .remove(
          'no-scroll'
        );


      document
        .getElementById(
          'cal-backdrop'
        )
        ?.classList
        .remove(
          'ativo'
        );

    }

  }


  // =====================================================
  // ABRIR MÊS
  // =====================================================

  document
    .querySelectorAll(
      '.mes'
    )
    .forEach(
      mes => {

        mes.addEventListener(
          'click',
          () => {

            const aberto =
              document.querySelector(
                '.mes.expanded'
              );


            if (
              aberto &&
              aberto !== mes
            ) {
              return;
            }


            if (
              mes.classList.contains(
                'expanded'
              )
            ) {
              return;
            }


            mes.classList.add(
              'expanded'
            );


            document.body
              .classList
              .add(
                'no-scroll'
              );


            document
              .getElementById(
                'cal-backdrop'
              )
              ?.classList
              .add(
                'ativo'
              );


            let fechar =
              mes.querySelector(
                '.fechar-btn'
              );


            if (!fechar) {

              fechar =
                document.createElement(
                  'button'
                );


              fechar.type =
                'button';


              fechar.textContent =
                '×';


              fechar.className =
                'fechar-btn';


              fechar.addEventListener(
                'click',
                evento => {

                  evento.stopPropagation();


                  fecharMes(
                    mes
                  );

                }
              );


              mes.appendChild(
                fechar
              );

            }


            fechar.style.display =
              'flex';

          }
        );

      }
    );


  // =====================================================
  // SELEÇÃO DE STATUS
  // =====================================================

  document
    .querySelectorAll(
      '.mes'
    )
    .forEach(
      mes => {

        mes.__corSelecionada =
          null;


        const botoes =
          mes.querySelectorAll(
            '.btn-cor'
          );


        function atualizarBotoes() {

          botoes.forEach(
            botao => {

              const ativo =
                botao.dataset.cor ===
                mes.__corSelecionada;


              botao.classList.toggle(
                'selecionado',
                ativo
              );


              botao.style.outline =
                ativo
                  ? '3px solid #555'
                  : 'none';


              botao.style.transform =
                ativo
                  ? 'scale(1.3)'
                  : 'scale(1)';

            }
          );

        }


        mes.__atualizarBotoesCor =
          atualizarBotoes;


        botoes.forEach(
          botao => {

            botao.addEventListener(
              'click',
              evento => {

                evento.stopPropagation();


                const cor =
                  botao.dataset.cor;


                mes.__corSelecionada =
                  mes.__corSelecionada ===
                  cor
                    ? null
                    : cor;


                atualizarBotoes();

              }
            );

          }
        );

      }
    );


  // =====================================================
  // MARCAR DIA + SALVAR NO JSON
  // =====================================================

  document
    .querySelectorAll(
      '.mes'
    )
    .forEach(
      mes => {

        mes.addEventListener(
          'click',
          async evento => {

            if (
              !mes.classList.contains(
                'expanded'
              ) ||
              !mes.__corSelecionada
            ) {
              return;
            }


            const dia =
              evento.target.closest?.(
                '.dia'
              );


            if (
              !dia ||
              dia.classList.contains(
                'header-dia'
              ) ||
              !dia.getAttribute(
                'data-date'
              )
            ) {
              return;
            }


            evento.stopPropagation();


            const iso =
              dia.getAttribute(
                'data-date'
              );


            const cor =
              mes.__corSelecionada;


            // FERIADO

            if (
              dia.classList.contains(
                'feriado'
              )
            ) {

              alert(
                'Este dia é feriado automático e não pode ser alterado.'
              );

              return;

            }


            // NÃO PERMITE FALTA OU ATESTADO NO FUTURO

            if (
              iso > hojeIso() &&
              (
                cor === 'vermelho' ||
                cor === 'amarelo'
              )
            ) {

              alert(
                'Não é possível marcar falta ou atestado em uma data futura.'
              );

              return;

            }


            dia.classList.remove(
              'vermelho',
              'amarelo',
              'sem-aula',
              'roxo'
            );


            if (
              cor !== 'limpar'
            ) {

              dia.classList.add(
                cor
              );

            }


            const status =
              statusDia(
                dia
              );


            // ==========================================
            // GARANTIR QUE DIAS SEJA {}
            // ==========================================

            if (
              !calendData.dias ||
              typeof calendData.dias !== 'object' ||
              Array.isArray(
                calendData.dias
              )
            ) {

              calendData.dias = {};

            }


            // ==========================================
            // GRAVAR A DATA
            // ==========================================

            if (status) {

              calendData.dias[
                iso
              ] = status;

            } else {

              delete calendData.dias[
                iso
              ];

            }


            console.log(
              'Data marcada:',
              iso
            );


            console.log(
              'Status:',
              status
            );


            console.log(
              'Dias que serão salvos:',
              calendData.dias
            );


            const salvou =
              await salvarCalendarioServidor();


            if (!salvou) {

              alert(
                'Não foi possível salvar esta alteração no calendário.'
              );

              return;

            }


            atualizarTudo();

          }
        );

      }
    );


  // =====================================================
  // META MENSAL
  // =====================================================

  document
    .querySelectorAll(
      '.mes .meta-presenca'
    )
    .forEach(
      input => {

        input.addEventListener(
          'change',
          async evento => {

            const mes =
              evento.target.closest(
                '.mes'
              );


            if (!mes) {
              return;
            }


            if (
              !calendData.metas ||
              typeof calendData.metas !== 'object' ||
              Array.isArray(
                calendData.metas
              )
            ) {

              calendData.metas = {};

            }


            const chave =
              `${mes.dataset.ano}-${mes.dataset.mes}`;


            calendData.metas[
              chave
            ] =
              clamp(
                Number(
                  evento.target.value ||
                  0
                ),
                0,
                100
              );


            const salvou =
              await salvarCalendarioServidor();


            if (!salvou) {

              alert(
                'Não foi possível salvar a meta mensal.'
              );

              return;

            }


            atualizarTudo();

          }
        );

      }
    );


  // =====================================================
  // MINI AGENDA — DUPLO CLIQUE
  // =====================================================

  document
    .querySelectorAll(
      '.mes .dia[data-date]'
    )
    .forEach(
      dia => {

        dia.addEventListener(
          'dblclick',
          evento => {

            const mes =
              dia.closest(
                '.mes'
              );


            if (
              !mes ||
              !mes.classList.contains(
                'expanded'
              )
            ) {
              return;
            }


            const box =
              mes.querySelector(
                '.mini-agenda'
              );


            const dataEl =
              box?.querySelector(
                '.agenda-data'
              );


            const notasEl =
              box?.querySelector(
                '.agenda-notas'
              );


            if (
              !box ||
              !dataEl ||
              !notasEl
            ) {
              return;
            }


            const iso =
              dia.getAttribute(
                'data-date'
              );


            box.dataset.date =
              iso;


            dataEl.textContent =
              formataDataBR(
                iso
              );


            const tarefa =
              tarefasDoDia(
                iso
              ).find(
                item =>
                  item.origem ===
                  'calendario'
              );


            notasEl.value =
              tarefa?.texto ||
              '';


            const resumo =
              box.querySelector(
                '.agenda-resumo'
              );


            const editor =
              box.querySelector(
                '.agenda-editor'
              );


            if (resumo) {

              resumo.style.display =
                'none';

            }


            if (editor) {

              editor.style.display =
                'block';

            }


            box.classList.add(
              'aberto'
            );


            notasEl.focus();


            evento.stopPropagation();

          }
        );

      }
    );


  // =====================================================
  // FECHAR MINI AGENDA
  // =====================================================

  document
    .querySelectorAll(
      '.agenda-fechar'
    )
    .forEach(
      botao => {

        botao.addEventListener(
          'click',
          evento => {

            evento.preventDefault();
            evento.stopPropagation();


            botao
              .closest('.mes')
              ?.querySelector(
                '.mini-agenda'
              )
              ?.classList
              .remove(
                'aberto'
              );

          }
        );

      }
    );


  // =====================================================
  // SALVAR MINI AGENDA
  // =====================================================

  document
    .querySelectorAll(
      '.agenda-salvar'
    )
    .forEach(
      botao => {

        botao.addEventListener(
          'click',
          evento => {

            evento.preventDefault();
            evento.stopPropagation();


            const mes =
              botao.closest(
                '.mes'
              );


            const box =
              mes?.querySelector(
                '.mini-agenda'
              );


            const textarea =
              box?.querySelector(
                '.agenda-notas'
              );


            const iso =
              box?.dataset.date;


            if (
              !iso ||
              !textarea
            ) {
              return;
            }


            salvarTextoDoDiaNaAgenda(
              iso,
              textarea.value
            );


            box.classList.remove(
              'aberto'
            );

          }
        );

      }
    );


  // =====================================================
  // CLIQUE SIMPLES NO DIA
  // =====================================================

  document
    .querySelectorAll(
      '.mes .dia[data-date]'
    )
    .forEach(
      dia => {

        dia.addEventListener(
          'click',
          evento => {

            const mes =
              dia.closest(
                '.mes'
              );


            if (
              !mes ||
              !mes.classList.contains(
                'expanded'
              ) ||
              mes.__corSelecionada
            ) {
              return;
            }


            evento.stopPropagation();


            const iso =
              dia.getAttribute(
                'data-date'
              );


            const mini =
              mes.querySelector(
                '.mini-agenda'
              );


            const dataEl =
              mini?.querySelector(
                '.agenda-data'
              );


            const resumo =
              mini?.querySelector(
                '.agenda-resumo'
              );


            const editor =
              mini?.querySelector(
                '.agenda-editor'
              );


            const notas =
              mini?.querySelector(
                '.agenda-notas'
              );


            const btnVer =
              mini?.querySelector(
                '.btn-ver-tarefas'
              );


            const btnNova =
              mini?.querySelector(
                '.btn-nova-tarefa'
              );


            const btnHorarios =
              mini?.querySelector(
                '.btn-ver-horarios'
              );


            if (
              !mini ||
              !dataEl ||
              !resumo ||
              !editor ||
              !notas ||
              !btnVer ||
              !btnNova ||
              !btnHorarios
            ) {
              return;
            }


            mini.dataset.date =
              iso;


            dataEl.textContent =
              formataDataBR(
                iso
              );


            // VER TAREFAS

            btnVer.onclick =
              () => {

                const tarefas =
                  tarefasDoDia(
                    iso
                  );


                resumo.style.display =
                  'block';


                editor.style.display =
                  'none';


                if (
                  !tarefas.length
                ) {

                  resumo.innerHTML = `
                    <p class="agenda-resumo-vazio">
                      Nenhuma tarefa cadastrada para este dia.
                    </p>
                  `;

                  return;

                }


                resumo.innerHTML = `
                  <div class="agenda-bloco">

                    <strong>
                      Tarefas do dia
                    </strong>

                    <ul>
                      ${
                        tarefas
                          .map(
                            tarefa =>
                              `<li>${escapeHtml(
                                tarefa.texto
                              )}</li>`
                          )
                          .join('')
                      }
                    </ul>

                  </div>
                `;

              };


            // NOVA TAREFA

            btnNova.onclick =
              () => {

                const tarefa =
                  tarefasDoDia(
                    iso
                  ).find(
                    item =>
                      item.origem ===
                      'calendario'
                  );


                notas.value =
                  tarefa?.texto ||
                  '';


                resumo.style.display =
                  'none';


                editor.style.display =
                  'block';


                notas.focus();

              };


            // HORÁRIOS

            btnHorarios.onclick =
              async () => {

                resumo.style.display =
                  'block';


                editor.style.display =
                  'none';


                resumo.innerHTML =
                  '<p>Carregando horários...</p>';


                const horarios =
                  await buscarHorarios(
                    iso
                  );


                if (
                  !horarios.length
                ) {

                  resumo.innerHTML = `
                    <p class="agenda-resumo-vazio">
                      Nenhum horário cadastrado para este dia.
                    </p>
                  `;

                  return;

                }


                resumo.innerHTML = `
                  <div class="agenda-bloco">

                    <strong>
                      Horários do dia
                    </strong>

                    <p>
                      ${
                        horarios
                          .map(
                            escapeHtml
                          )
                          .join(', ')
                      }
                    </p>

                  </div>
                `;

              };


            if (
              tarefasDoDia(
                iso
              ).length
            ) {

              btnVer.click();

            } else {

              btnNova.click();

            }


            mini.classList.add(
              'aberto'
            );

          }
        );

      }
    );


  // =====================================================
  // IMPRIMIR
  // =====================================================

  document
    .querySelectorAll(
      '.btn-imprimir'
    )
    .forEach(
      botao => {

        botao.addEventListener(
          'click',
          evento => {

            evento.preventDefault();
            evento.stopPropagation();

            window.print();

          }
        );

      }
    );


  // =====================================================
  // EXPORTAR PNG
  // =====================================================

  document
    .querySelectorAll(
      '.btn-exportar-png'
    )
    .forEach(
      botao => {

        botao.addEventListener(
          'click',
          async evento => {

            evento.preventDefault();
            evento.stopPropagation();


            const mes =
              botao.closest(
                '.mes'
              );


            const bloco =
              mes?.querySelector(
                '.calendario-mes'
              );


            if (
              !bloco ||
              typeof html2canvas !==
                'function'
            ) {
              return;
            }


            const numeroMes =
              Number(
                mes.dataset.mes
              );


            const canvas =
              await html2canvas(
                bloco,
                {
                  useCORS: true,

                  backgroundColor:
                    '#ffffff',

                  scale: 2
                }
              );


            const link =
              document.createElement(
                'a'
              );


            link.download =
              (
                `Calendario_` +
                `${NOMES_MESES[
                  numeroMes - 1
                ]}_` +
                `${mes.dataset.ano}.png`
              );


            link.href =
              canvas.toDataURL(
                'image/png'
              );


            link.click();

          }
        );

      }
    );


  // =====================================================
  // SELETOR DE ANO
  // =====================================================

  document
    .querySelectorAll(
      '.anoSelect'
    )
    .forEach(
      select => {

        for (
          let ano =
            ANO_ATUAL - 4;

          ano <=
            ANO_ATUAL + 4;

          ano++
        ) {

          const option =
            document.createElement(
              'option'
            );


          option.value =
            String(ano);


          option.textContent =
            String(ano);


          option.selected =
            ano ===
            ANO_ATUAL;


          select.appendChild(
            option
          );

        }


        select.addEventListener(
          'change',
          () => {

            const url =
              new URL(
                location.href
              );


            url.searchParams.set(
              'ano',
              select.value
            );


            location.href =
              url.toString();

          }
        );

      }
    );


  // =====================================================
  // PERFIL / LOGOUT
  // =====================================================

  const perfilIcon =
    document.getElementById(
      'icon-perfil'
    );

  const logoutModal =
    document.getElementById(
      'logout-modal'
    );

  const iconSair =
    document.getElementById(
      'icon-sair'
    );

  const confirmLogout =
    document.getElementById(
      'confirm-logout'
    );

  const cancelLogout =
    document.getElementById(
      'cancel-logout'
    );


  perfilIcon?.addEventListener(
    'click',
    () => {

      window.location.href =
        '../perfil/perfil.php';

    }
  );


  iconSair?.addEventListener(
    'click',
    () => {

      if (logoutModal) {

        logoutModal.style.display =
          'flex';

      }

    }
  );


  confirmLogout?.addEventListener(
    'click',
    () => {

      window.location.href =
        '../login/logout.php';

    }
  );


  cancelLogout?.addEventListener(
    'click',
    () => {

      if (logoutModal) {

        logoutModal.style.display =
          'none';

      }

    }
  );


  logoutModal?.addEventListener(
    'click',
    evento => {

      if (
        evento.target ===
        logoutModal
      ) {

        logoutModal.style.display =
          'none';

      }

    }
  );


  // =====================================================
  // PAINEL ANUAL EXPANSÍVEL
  // =====================================================

  function configurarPainelAnualExpansivel() {

    const painel =
      document.getElementById(
        'frequencia-anual'
      );


    if (!painel) {
      return;
    }


    const topo =
      painel.querySelector(
        '.freq-anual-topo'
      );


    if (!topo) {
      return;
    }


    if (
      topo.querySelector(
        '.btn-expandir-frequencia'
      )
    ) {
      return;
    }


    painel.classList.add(
      'frequencia-recolhida'
    );


    const botao =
      document.createElement(
        'button'
      );


    botao.type =
      'button';


    botao.className =
      'btn-expandir-frequencia';


    botao.setAttribute(
      'aria-expanded',
      'false'
    );


    botao.setAttribute(
      'aria-label',
      'Expandir frequência anual'
    );


    botao.title =
      'Expandir frequência anual';


    botao.innerHTML = `
      <i class="fa-solid fa-chevron-down"></i>
    `;


    topo.appendChild(
      botao
    );


    botao.addEventListener(
      'click',
      evento => {

        evento.preventDefault();
        evento.stopPropagation();


        const vaiAbrir =
          painel.classList.contains(
            'frequencia-recolhida'
          );


        painel.classList.toggle(
          'frequencia-recolhida'
        );


        botao.setAttribute(
          'aria-expanded',
          vaiAbrir
            ? 'true'
            : 'false'
        );


        botao.setAttribute(
          'aria-label',
          vaiAbrir
            ? 'Recolher frequência anual'
            : 'Expandir frequência anual'
        );


        botao.title =
          vaiAbrir
            ? 'Recolher frequência anual'
            : 'Expandir frequência anual';

      }
    );

  }


  // =====================================================
  // ATUALIZAÇÃO GERAL
  // =====================================================

  function atualizarTudo() {

    aplicarMarcacoesPeriodo();

    destacarHoje();


    document
      .querySelectorAll(
        '.calendario .dia[data-date]'
      )
      .forEach(
        atualizarDots
      );


    atualizarResumoAnual();

  }


  // =====================================================
  // INICIALIZAÇÃO
  // =====================================================

  configurarPainelAnualExpansivel();


  preencherConfigAno();


  // ==========================================
  // CARREGAR FALTAS / ATESTADOS / ETC SALVOS
  // ==========================================

  document
    .querySelectorAll(
      '.calendario .dia[data-date]'
    )
    .forEach(
      dia => {

        const iso =
          dia.getAttribute(
            'data-date'
          );


        const status =
          calendData.dias[
            iso
          ];


        if (status) {

          dia.classList.add(
            status
          );

        }

      }
    );


  // ==========================================
  // CARREGAR TAREFAS
  // ==========================================

  marcarDiasComTarefa();


  // ==========================================
  // CARREGAR METAS MENSAIS
  // ==========================================

  document
    .querySelectorAll(
      '.mes'
    )
    .forEach(
      mes => {

        const chave =
          `${mes.dataset.ano}-${mes.dataset.mes}`;


        const meta =
          calendData.metas[
            chave
          ];


        const input =
          mes.querySelector(
            '.meta-presenca'
          );


        if (
          input &&
          meta != null
        ) {

          input.value =
            String(
              meta
            );

        }

      }
    );


  // ==========================================
  // CALCULAR TUDO
  // ==========================================

  atualizarTudo();

});