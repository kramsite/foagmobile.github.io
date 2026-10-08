(() => {
    'use strict';

    const materias = Array.isArray(window.MATERIAS_DATA?.materias)
        ? window.MATERIAS_DATA.materias
        : [];

    const baralhos = Array.isArray(window.FLASHCARDS_DATA?.baralhos)
        ? window.FLASHCARDS_DATA.baralhos
        : [];

    const el = (id) => document.getElementById(id);

    const screens = {
        setup: el('screen-setup'),
        quiz: el('screen-quiz'),
        result: el('screen-result')
    };

    const state = {
        config: null,
        questions: [],
        current: 0,
        answers: [],
        selectedAnswer: null,
        locked: false
    };

    const difficultyLabels = {
        easy: 'Fácil',
        medium: 'Média',
        hard: 'Difícil',
        adaptive: 'Adaptativa'
    };

    function showScreen(name) {
        Object.entries(screens).forEach(([key, screen]) => {
            screen.classList.toggle('active', key === name);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function toast(message, type = '') {
        const node = el('toast');
        node.textContent = message;
        node.className = `toast ${type}`.trim();
        requestAnimationFrame(() => node.classList.add('show'));

        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => {
            node.classList.remove('show');
        }, 2800);
    }

    function setAlert(node, message, type = '') {
        if (!message) {
            node.hidden = true;
            node.textContent = '';
            node.className = 'inline-alert';
            return;
        }

        node.hidden = false;
        node.textContent = message;
        node.className = `inline-alert ${type}`.trim();
    }

    function shuffle(items) {
        const arr = [...items];

        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }

        return arr;
    }

    function uniqueText(items) {
        const seen = new Set();

        return items.filter((item) => {
            const key = String(item ?? '').trim().toLocaleLowerCase('pt-BR');

            if (!key || seen.has(key)) {
                return false;
            }

            seen.add(key);
            return true;
        });
    }

    function materiaName(materia) {
        return String(
            materia?.nome ??
            materia?.materia ??
            ''
        ).trim();
    }

    function populateSubjects() {
        const select = el('quiz-subject');

        materias.forEach((materia) => {
            const nome = materiaName(materia);

            if (!nome) {
                return;
            }

            const option = document.createElement('option');
            option.value = nome;
            option.textContent = nome;
            select.appendChild(option);
        });

        if (!materias.length) {
            el('subject-hint').textContent =
                'Você ainda não cadastrou matérias. Cadastre uma matéria em Estudos primeiro.';
        }
    }

    function getConfig() {
        const subject = el('quiz-subject').value.trim();

        const sources = [
            el('source-notes').checked ? 'notes' : null,
            el('source-flashcards').checked ? 'flashcards' : null
        ].filter(Boolean);

        const type = document.querySelector('input[name="quiz-type"]:checked')?.value ?? 'multiple';

        return {
            subject,
            sources,
            count: Number(el('quiz-count').value || 10),
            difficulty: el('quiz-difficulty').value,
            type
        };
    }

    function validateConfig(config) {
        const warning = el('setup-warning');

        if (!config.subject) {
            setAlert(warning, 'Escolha uma matéria para gerar o quiz.', 'error');
            return false;
        }

        if (!config.sources.length) {
            setAlert(warning, 'Selecione pelo menos uma fonte de conteúdo.', 'error');
            return false;
        }

        setAlert(warning, '');
        return true;
    }

    function cardsForSubject(subject) {
        const normalized = subject.toLocaleLowerCase('pt-BR');

        return baralhos
            .filter((deck) => {
                return String(deck?.materia ?? '')
                    .trim()
                    .toLocaleLowerCase('pt-BR') === normalized;
            })
            .flatMap((deck) => {
                const cards = Array.isArray(deck?.cartoes) ? deck.cartoes : [];

                return cards.map((card) => ({
                    ...card,
                    deckName: deck?.nome ?? 'Flashcards'
                }));
            })
            .filter((card) => {
                return String(card?.pergunta ?? '').trim() &&
                    String(card?.resposta ?? '').trim();
            });
    }

    function createMultipleQuestion(card, allCards, difficulty) {
        const correct = String(card.resposta).trim();

        const distractors = uniqueText(
            shuffle(
                allCards
                    .filter((other) => other !== card)
                    .map((other) => String(other.resposta ?? '').trim())
            )
        )
            .filter((text) => text.toLocaleLowerCase('pt-BR') !== correct.toLocaleLowerCase('pt-BR'))
            .slice(0, 3);

        const genericDistractors = [
            'Nenhuma das alternativas anteriores',
            'Esse conceito não se aplica ao conteúdo',
            'A afirmação está parcialmente incorreta',
            'Não é possível concluir com essas informações'
        ];

        while (distractors.length < 3) {
            const fallback = genericDistractors.find(
                (item) =>
                    !distractors.includes(item) &&
                    item.toLocaleLowerCase('pt-BR') !== correct.toLocaleLowerCase('pt-BR')
            );

            if (!fallback) {
                break;
            }

            distractors.push(fallback);
        }

        const options = shuffle([correct, ...distractors]).slice(0, 4);
        const correctIndex = options.indexOf(correct);

        return {
            id: `q_${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`,
            type: 'multiple',
            question: String(card.pergunta).trim(),
            options,
            correctIndex,
            correctText: correct,
            explanation: `Resposta esperada: ${correct}`,
            topic: String(card.deckName || 'Revisão'),
            source: 'flashcard',
            difficulty
        };
    }

    function createTrueFalseQuestion(card, allCards, difficulty, forceFalse = null) {
        const correct = String(card.resposta).trim();
        const otherAnswers = allCards
            .filter((other) => other !== card)
            .map((other) => String(other.resposta ?? '').trim())
            .filter(Boolean);

        const makeFalse = forceFalse === null
            ? Math.random() > 0.5
            : forceFalse;

        const statementAnswer =
            makeFalse && otherAnswers.length
                ? shuffle(otherAnswers)[0]
                : correct;

        const statement =
            `${String(card.pergunta).trim()} — ${statementAnswer}`;

        return {
            id: `q_${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`,
            type: 'truefalse',
            question: `A afirmação abaixo está correta?\n${statement}`,
            options: ['Verdadeiro', 'Falso'],
            correctIndex: makeFalse && otherAnswers.length ? 1 : 0,
            correctText: correct,
            explanation: makeFalse && otherAnswers.length
                ? `Falso. Para essa pergunta, a resposta correta é: ${correct}`
                : `Verdadeiro. ${correct}`,
            topic: String(card.deckName || 'Revisão'),
            source: 'flashcard',
            difficulty
        };
    }

    function fallbackDemoQuestions(config) {
        const subject = config.subject || 'sua matéria';

        const demo = [
            {
                type: 'multiple',
                question: `Qual alternativa melhor representa um conceito importante de ${subject}?`,
                options: [
                    'O conceito principal estudado nas suas anotações',
                    'Uma informação sem relação com o conteúdo',
                    'Uma conclusão que contradiz o material',
                    'Nenhuma das alternativas'
                ],
                correctIndex: 0,
                correctText: 'O conceito principal estudado nas suas anotações',
                explanation: 'Esta é uma questão demonstrativa. Quando a API for conectada, ela será criada a partir do conteúdo real.',
                topic: subject
            },
            {
                type: 'truefalse',
                question: `O FOAG poderá usar suas anotações de ${subject} para criar perguntas personalizadas.`,
                options: ['Verdadeiro', 'Falso'],
                correctIndex: 0,
                correctText: 'Verdadeiro',
                explanation: 'Sim. Essa etapa será feita pela API de IA quando ela for conectada.',
                topic: subject
            },
            {
                type: 'multiple',
                question: `Para uma revisão eficiente de ${subject}, qual estratégia o FOAG vai priorizar?`,
                options: [
                    'Revisar também os conteúdos que você errou',
                    'Mostrar apenas as respostas corretas',
                    'Ignorar seu desempenho',
                    'Usar questões sem relação com o material'
                ],
                correctIndex: 0,
                correctText: 'Revisar também os conteúdos que você errou',
                explanation: 'Os erros são usados para criar flashcards e novos ciclos de revisão.',
                topic: subject
            },
            {
                type: 'multiple',
                question: `O que acontece ao final do quiz de ${subject}?`,
                options: [
                    'Você pode criar flashcards com os erros',
                    'O resultado é apagado imediatamente',
                    'A matéria é excluída',
                    'O quiz fecha sem mostrar o desempenho'
                ],
                correctIndex: 0,
                correctText: 'Você pode criar flashcards com os erros',
                explanation: 'Essa é a principal integração desta página com a área de Flashcards.',
                topic: subject
            },
            {
                type: 'truefalse',
                question: 'Os flashcards de revisão podem ser selecionados antes de serem salvos.',
                options: ['Verdadeiro', 'Falso'],
                correctIndex: 0,
                correctText: 'Verdadeiro',
                explanation: 'Você escolhe quais cartões quer manter antes de salvá-los.',
                topic: subject
            }
        ];

        return Array.from({ length: config.count }, (_, index) => {
            const base = demo[index % demo.length];

            return {
                ...base,
                id: `demo_${index}_${Date.now()}`,
                difficulty: config.difficulty,
                source: 'demo'
            };
        });
    }

    async function tryGenerateWithAI(config) {
        /*
         * Esta função já está pronta para receber o endpoint da OpenAI depois.
         * Se gerar_quiz.php ainda não estiver conectado, ela retorna null e
         * o FOAG usa o modo local com flashcards.
         */
        try {
            const response = await fetch(window.QUIZ_CONFIG.aiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(config)
            });

            if (!response.ok) {
                return null;
            }

            const data = await response.json();

            if (!data?.sucesso || !Array.isArray(data?.questoes) || !data.questoes.length) {
                return null;
            }

            return data.questoes;
        } catch {
            return null;
        }
    }

    async function generateQuestions(config) {
        const useNotes = config.sources.includes('notes');
        const useFlashcards = config.sources.includes('flashcards');

        if (useNotes) {
            const aiQuestions = await tryGenerateWithAI(config);

            if (aiQuestions?.length) {
                return aiQuestions.slice(0, config.count);
            }
        }

        if (useFlashcards) {
            const cards = cardsForSubject(config.subject);

            if (cards.length) {
                const shuffledCards = shuffle(cards);
                const questions = [];

                for (let i = 0; i < config.count; i++) {
                    const card = shuffledCards[i % shuffledCards.length];

                    let targetType = config.type;

                    if (targetType === 'mixed') {
                        targetType = i % 2 === 0 ? 'multiple' : 'truefalse';
                    }

                    if (targetType === 'truefalse') {
                        questions.push(
                            createTrueFalseQuestion(
                                card,
                                cards,
                                config.difficulty,
                                i % 2 === 1
                            )
                        );
                    } else {
                        questions.push(
                            createMultipleQuestion(
                                card,
                                cards,
                                config.difficulty
                            )
                        );
                    }
                }

                if (useNotes) {
                    toast('A API ainda não está conectada; este quiz foi criado com seus flashcards.', '');
                }

                return questions;
            }
        }

        if (useNotes && !useFlashcards) {
            throw new Error(
                'A geração usando anotações será ativada quando conectarmos a API do ChatGPT. Por enquanto, selecione Flashcards.'
            );
        }

        throw new Error(
            'Essa matéria ainda não possui flashcards suficientes para gerar o quiz.'
        );
    }

    function normalizeQuestion(raw, index) {
        const options = Array.isArray(raw?.options)
            ? raw.options.map((x) => String(x))
            : Array.isArray(raw?.alternativas)
                ? raw.alternativas.map((x) => String(x))
                : [];

        let correctIndex = Number.isInteger(raw?.correctIndex)
            ? raw.correctIndex
            : Number.isInteger(raw?.correta)
                ? raw.correta
                : 0;

        if (correctIndex < 0 || correctIndex >= options.length) {
            correctIndex = 0;
        }

        return {
            id: raw?.id ?? `ai_${index}_${Date.now()}`,
            type: raw?.type ?? raw?.tipo ?? 'multiple',
            question: String(raw?.question ?? raw?.pergunta ?? `Questão ${index + 1}`),
            options,
            correctIndex,
            correctText: String(
                raw?.correctText ??
                raw?.resposta ??
                options[correctIndex] ??
                ''
            ),
            explanation: String(
                raw?.explanation ??
                raw?.explicacao ??
                `Resposta correta: ${options[correctIndex] ?? ''}`
            ),
            topic: String(raw?.topic ?? raw?.topico ?? state.config?.subject ?? 'Revisão'),
            difficulty: raw?.difficulty ?? state.config?.difficulty ?? 'medium',
            source: raw?.source ?? 'ai'
        };
    }

    async function startQuiz() {
        const config = getConfig();

        if (!validateConfig(config)) {
            return;
        }

        const button = el('generate-quiz');
        const original = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Preparando quiz...';

        try {
            const generated = await generateQuestions(config);

            state.config = config;
            state.questions = generated.map(normalizeQuestion).filter((q) => q.options.length >= 2);
            state.current = 0;
            state.answers = [];
            state.selectedAnswer = null;
            state.locked = false;

            if (!state.questions.length) {
                throw new Error('Não foi possível criar questões com esse conteúdo.');
            }

            el('quiz-subject-badge').textContent = config.subject;
            el('quiz-title').textContent = `Quiz de ${config.subject}`;

            showScreen('quiz');
            renderQuestion();
        } catch (error) {
            setAlert(
                el('setup-warning'),
                error?.message || 'Não foi possível gerar o quiz.',
                'error'
            );
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    function renderQuestion() {
        const question = state.questions[state.current];

        if (!question) {
            finishQuiz();
            return;
        }

        state.selectedAnswer = null;
        state.locked = false;

        const currentNumber = state.current + 1;
        const total = state.questions.length;
        const percent = Math.round((currentNumber / total) * 100);

        el('question-counter').textContent = `Questão ${currentNumber} de ${total}`;
        el('progress-percent').textContent = `${percent}%`;
        el('progress-bar').style.width = `${percent}%`;

        el('difficulty-badge').textContent =
            difficultyLabels[question.difficulty] ||
            difficultyLabels[state.config?.difficulty] ||
            'Média';

        const typeLabel = question.type === 'truefalse'
            ? ['fa-toggle-on', 'Verdadeiro ou falso']
            : ['fa-list-check', 'Múltipla escolha'];

        el('question-type-label').innerHTML =
            `<i class="fa-solid ${typeLabel[0]}"></i>${typeLabel[1]}`;

        el('question-text').textContent = question.question;

        const list = el('answers-list');
        list.innerHTML = '';

        const letters = ['A', 'B', 'C', 'D', 'E'];

        question.options.forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'answer-option';
            button.dataset.index = String(index);

            const marker = question.type === 'truefalse'
                ? (index === 0 ? 'V' : 'F')
                : (letters[index] ?? String(index + 1));

            button.innerHTML = `
                <span class="answer-letter">${marker}</span>
                <span>${escapeHtml(option)}</span>
            `;

            button.addEventListener('click', () => selectAnswer(index));
            list.appendChild(button);
        });

        el('answer-feedback').hidden = true;
        el('answer-feedback').className = 'answer-feedback';
        el('next-question').disabled = true;
        el('next-question').innerHTML =
            currentNumber === total
                ? 'Ver resultado <i class="fa-solid fa-flag-checkered"></i>'
                : 'Próxima questão <i class="fa-solid fa-arrow-right"></i>';
    }

    function selectAnswer(index) {
        if (state.locked) {
            return;
        }

        const question = state.questions[state.current];
        const correct = index === question.correctIndex;

        state.locked = true;
        state.selectedAnswer = index;

        state.answers[state.current] = {
            questionId: question.id,
            selectedIndex: index,
            correctIndex: question.correctIndex,
            correct,
            question
        };

        const buttons = [...document.querySelectorAll('.answer-option')];

        buttons.forEach((button) => {
            const buttonIndex = Number(button.dataset.index);
            button.disabled = true;

            if (buttonIndex === question.correctIndex) {
                button.classList.add('correct');
            }

            if (buttonIndex === index && !correct) {
                button.classList.add('wrong');
            }
        });

        const feedback = el('answer-feedback');
        feedback.hidden = false;
        feedback.className = `answer-feedback ${correct ? 'correct' : 'wrong'}`;

        el('feedback-icon').innerHTML = correct
            ? '<i class="fa-solid fa-check"></i>'
            : '<i class="fa-solid fa-xmark"></i>';

        el('feedback-title').textContent = correct
            ? 'Resposta correta!'
            : 'Quase! Essa não é a resposta correta.';

        el('feedback-text').textContent = question.explanation;
        el('next-question').disabled = false;
    }

    function nextQuestion() {
        if (!state.locked) {
            return;
        }

        state.current += 1;

        if (state.current >= state.questions.length) {
            finishQuiz();
        } else {
            renderQuestion();
        }
    }

    function finishQuiz() {
        const total = state.questions.length;
        const correct = state.answers.filter((answer) => answer?.correct).length;
        const wrong = total - correct;
        const percent = total ? Math.round((correct / total) * 100) : 0;

        el('result-percent').textContent = `${percent}%`;
        el('result-ring').style.setProperty('--score', String(percent));
        el('correct-count').textContent = String(correct);
        el('wrong-count').textContent = String(wrong);
        el('total-count').textContent = String(total);

        let message = 'Continue revisando!';
        let description = 'Cada tentativa ajuda você a identificar melhor os conteúdos que precisam de atenção.';

        if (percent >= 90) {
            message = 'Excelente revisão!';
            description = 'Você dominou muito bem este conteúdo. Continue praticando para manter o ritmo.';
        } else if (percent >= 70) {
            message = 'Muito bom!';
            description = 'Seu desempenho foi bom. Revise os poucos pontos em que você teve dificuldade.';
        } else if (percent >= 50) {
            message = 'Bom começo!';
            description = 'Você já acertou boa parte. Agora vale focar nos erros para consolidar o conteúdo.';
        }

        el('result-message').textContent = message;
        el('result-description').textContent = description;

        const wrongAnswers = getWrongAnswers();

        el('review-summary-text').textContent = wrongAnswers.length
            ? `Você teve dificuldade em ${wrongAnswers.length} ${wrongAnswers.length === 1 ? 'questão' : 'questões'}.`
            : 'Você acertou todas! Não há erros obrigatórios para revisar.';

        const topics = uniqueText(
            wrongAnswers.map((answer) => answer.question.topic || state.config.subject)
        );

        const topicsNode = el('wrong-topics');
        topicsNode.innerHTML = '';

        if (topics.length) {
            topics.forEach((topic) => {
                const chip = document.createElement('span');
                chip.className = 'topic-chip';
                chip.textContent = topic;
                topicsNode.appendChild(chip);
            });
        } else {
            const chip = document.createElement('span');
            chip.className = 'topic-chip';
            chip.textContent = 'Conteúdo dominado';
            topicsNode.appendChild(chip);
        }

        el('retry-wrong').disabled = wrongAnswers.length === 0;
        el('create-review-flashcards').disabled = wrongAnswers.length === 0;

        showScreen('result');
    }

    function getWrongAnswers() {
        return state.answers.filter((answer) => answer && !answer.correct);
    }

    function retryWrong() {
        const wrong = getWrongAnswers();

        if (!wrong.length) {
            toast('Você não tem questões erradas para refazer.', 'success');
            return;
        }

        state.questions = wrong.map((answer) => ({
            ...answer.question,
            id: `retry_${answer.question.id}_${Date.now()}`
        }));

        state.current = 0;
        state.answers = [];
        state.selectedAnswer = null;
        state.locked = false;

        el('quiz-title').textContent = `Revisão dos erros — ${state.config.subject}`;

        showScreen('quiz');
        renderQuestion();
    }

    function buildReviewCards() {
        return getWrongAnswers().map((answer, index) => {
            const question = answer.question;

            return {
                tempId: `review_${index}_${Date.now()}`,
                pergunta: question.question,
                resposta: question.correctText || question.options[question.correctIndex] || '',
                explicacao: question.explanation || '',
                topico: question.topic || state.config.subject
            };
        });
    }

    function openFlashcardModal() {
        const cards = buildReviewCards();

        if (!cards.length) {
            toast('Você não errou nenhuma questão neste quiz.', 'success');
            return;
        }

        const list = el('flashcard-preview-list');
        list.innerHTML = '';

        cards.forEach((card, index) => {
            const item = document.createElement('label');
            item.className = 'flashcard-preview';
            item.innerHTML = `
                <input
                    class="review-card-checkbox"
                    type="checkbox"
                    value="${index}"
                    checked
                >
                <div>
                    <span class="side-label">Frente</span>
                    <strong>${escapeHtml(card.pergunta)}</strong>
                    <span class="side-label" style="margin-top:9px; display:block;">Verso</span>
                    <p>${escapeHtml(card.resposta)}</p>
                </div>
            `;

            list.appendChild(item);
        });

        el('flashcard-modal').dataset.cards = JSON.stringify(cards);
        el('select-all-flashcards').checked = true;
        updateSelectionCount();
        setAlert(el('flashcard-save-feedback'), '');

        el('flashcard-modal').classList.add('open');
        el('flashcard-modal').setAttribute('aria-hidden', 'false');
    }

    function closeFlashcardModal() {
        el('flashcard-modal').classList.remove('open');
        el('flashcard-modal').setAttribute('aria-hidden', 'true');
    }

    function updateSelectionCount() {
        const checks = [...document.querySelectorAll('.review-card-checkbox')];
        const selected = checks.filter((check) => check.checked).length;

        el('flashcard-selection-count').textContent =
            `${selected} ${selected === 1 ? 'selecionado' : 'selecionados'}`;

        el('select-all-flashcards').checked =
            checks.length > 0 && selected === checks.length;

        el('save-review-flashcards').disabled = selected === 0;
    }

    async function saveReviewFlashcards() {
        const allCards = JSON.parse(el('flashcard-modal').dataset.cards || '[]');
        const selectedIndexes = [...document.querySelectorAll('.review-card-checkbox:checked')]
            .map((check) => Number(check.value));

        const selectedCards = selectedIndexes
            .map((index) => allCards[index])
            .filter(Boolean);

        if (!selectedCards.length) {
            setAlert(
                el('flashcard-save-feedback'),
                'Selecione pelo menos um flashcard.',
                'error'
            );
            return;
        }

        const button = el('save-review-flashcards');
        const original = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';

        try {
            const response = await fetch(window.QUIZ_CONFIG.saveReviewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    materia: state.config.subject,
                    cards: selectedCards
                })
            });

            const data = await response.json().catch(() => null);

            if (!response.ok || !data?.sucesso) {
                throw new Error(data?.mensagem || 'Não foi possível salvar os flashcards.');
            }

            setAlert(
                el('flashcard-save-feedback'),
                `${selectedCards.length} flashcard(s) salvo(s) com sucesso.`,
                'success'
            );

            toast('Flashcards de revisão criados!', 'success');

            setTimeout(() => {
                closeFlashcardModal();
            }, 850);
        } catch (error) {
            setAlert(
                el('flashcard-save-feedback'),
                error?.message || 'Não foi possível salvar os flashcards.',
                'error'
            );
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    function resetQuiz() {
        state.config = null;
        state.questions = [];
        state.current = 0;
        state.answers = [];
        state.selectedAnswer = null;
        state.locked = false;

        showScreen('setup');
    }

    function exitQuiz() {
        const shouldExit = window.confirm(
            'Deseja sair deste quiz? Seu progresso desta tentativa será perdido.'
        );

        if (shouldExit) {
            resetQuiz();
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function bindEvents() {
        el('icon-perfil')?.addEventListener('click', () => {
            window.location.href = '../../perfil/perfil.php';
        });

        el('icon-configuracoes')?.addEventListener('click', () => {
            window.location.href = '../../configuracoes/configuracoes.php';
        });

        el('icon-sair')?.addEventListener('click', () => {
            el('logout-modal')?.classList.add('open');
            el('logout-modal')?.setAttribute('aria-hidden', 'false');
        });

        el('cancel-logout')?.addEventListener('click', () => {
            el('logout-modal')?.classList.remove('open');
            el('logout-modal')?.setAttribute('aria-hidden', 'true');
        });

        el('confirm-logout')?.addEventListener('click', () => {
            window.location.href = '../../login/logout.php';
        });

        el('logout-modal')?.addEventListener('click', (event) => {
            if (event.target === el('logout-modal')) {
                el('logout-modal').classList.remove('open');
                el('logout-modal').setAttribute('aria-hidden', 'true');
            }
        });
        el('generate-quiz').addEventListener('click', startQuiz);
        el('next-question').addEventListener('click', nextQuestion);
        el('exit-quiz').addEventListener('click', exitQuiz);
        el('new-quiz').addEventListener('click', resetQuiz);
        el('retry-wrong').addEventListener('click', retryWrong);

        el('create-review-flashcards').addEventListener('click', openFlashcardModal);
        el('close-flashcard-modal').addEventListener('click', closeFlashcardModal);
        el('cancel-flashcards').addEventListener('click', closeFlashcardModal);
        el('save-review-flashcards').addEventListener('click', saveReviewFlashcards);

        el('flashcard-modal').addEventListener('click', (event) => {
            if (event.target === el('flashcard-modal')) {
                closeFlashcardModal();
            }
        });

        el('select-all-flashcards').addEventListener('change', (event) => {
            document.querySelectorAll('.review-card-checkbox')
                .forEach((check) => {
                    check.checked = event.target.checked;
                });

            updateSelectionCount();
        });

        el('flashcard-preview-list').addEventListener('change', (event) => {
            if (event.target.matches('.review-card-checkbox')) {
                updateSelectionCount();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (el('flashcard-modal').classList.contains('open')) {
                closeFlashcardModal();
            }

            if (el('logout-modal')?.classList.contains('open')) {
                el('logout-modal').classList.remove('open');
                el('logout-modal').setAttribute('aria-hidden', 'true');
            }
        });
    }

    populateSubjects();
    bindEvents();
})();
