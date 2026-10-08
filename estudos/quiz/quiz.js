(() => {
    'use strict';

    const materias = Array.isArray(window.MATERIAS_DATA?.materias)
        ? window.MATERIAS_DATA.materias
        : [];

    const baralhos = Array.isArray(window.FLASHCARDS_DATA?.baralhos)
        ? window.FLASHCARDS_DATA.baralhos
        : [];

    const serverNotes = Array.isArray(window.ANOTACOES_DATA)
        ? window.ANOTACOES_DATA
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
        locked: false,
        selectedNoteKeys: new Set(),
        selectedFlashcardKeys: new Set()
    };

    const difficultyLabels = {
        easy: 'Fácil',
        medium: 'Média',
        hard: 'Difícil',
        adaptive: 'Adaptativa'
    };

    function materiaName(materia) {
        return String(materia?.nome ?? materia?.materia ?? '').trim();
    }

    function normalize(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('pt-BR')
            .trim();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function showScreen(name) {
        Object.entries(screens).forEach(([key, screen]) => {
            screen.classList.toggle('active', key === name);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function toast(message, type = '') {
        const node = el('toast');
        if (!node) return;

        node.textContent = message;
        node.className = `toast ${type}`.trim();
        requestAnimationFrame(() => node.classList.add('show'));

        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => node.classList.remove('show'), 2800);
    }

    function setAlert(node, message, type = '') {
        if (!node) return;

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
            const key = normalize(item);
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    function getLocalNotes() {
        try {
            const parsed = JSON.parse(localStorage.getItem('foag_important_notes') || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('Não foi possível carregar as anotações locais.', error);
            return [];
        }
    }

    function buildNotes() {
        const merged = [...serverNotes, ...getLocalNotes()];
        const seen = new Set();
        const notes = [];

        merged.forEach((note, index) => {
            const text = String(note?.text ?? note?.texto ?? note?.conteudo ?? '').trim();
            if (!text) return;

            const rawId = note?.id ?? note?.timestamp ?? `note-${index}`;
            const signature = `${normalize(text)}::${String(note?.timestamp ?? note?.date ?? note?.data ?? '')}`;
            if (seen.has(signature)) return;
            seen.add(signature);

            notes.push({
                key: `note::${String(rawId)}::${index}`,
                id: rawId,
                text,
                date: String(note?.date ?? note?.data ?? ''),
                timestamp: Number(note?.timestamp ?? 0) || 0,
                materia: String(note?.materia ?? note?.subject ?? '').trim()
            });
        });

        return notes.sort((a, b) => b.timestamp - a.timestamp);
    }

    function buildFlashcards() {
        const cards = [];

        baralhos.forEach((deck, deckIndex) => {
            const deckCards = Array.isArray(deck?.cartoes) ? deck.cartoes : [];
            const deckId = String(deck?.id ?? `deck-${deckIndex}`);
            const deckName = String(deck?.nome ?? deck?.titulo ?? 'Flashcards').trim();
            const materia = String(deck?.materia ?? '').trim();

            deckCards.forEach((card, cardIndex) => {
                const pergunta = String(card?.pergunta ?? '').trim();
                const resposta = String(card?.resposta ?? '').trim();
                if (!pergunta || !resposta) return;

                const cardId = String(card?.id ?? `card-${cardIndex}`);
                cards.push({
                    key: `flash::${deckId}::${cardId}`,
                    id: cardId,
                    deckId,
                    deckName,
                    materia,
                    pergunta,
                    resposta
                });
            });
        });

        return cards;
    }

    const notes = buildNotes();
    const flashcards = buildFlashcards();

    function populateSubjects() {
        const select = el('quiz-subject');
        const reviewSelect = el('review-subject');
        const names = uniqueText(materias.map(materiaName)).sort((a, b) => a.localeCompare(b, 'pt-BR'));

        names.forEach((nome) => {
            [select, reviewSelect].forEach((target) => {
                if (!target) return;
                const option = document.createElement('option');
                option.value = nome;
                option.textContent = nome;
                target.appendChild(option);
            });
        });
    }

    function currentSearch() {
        return normalize(el('content-search')?.value ?? '');
    }

    function currentSubjectFilter() {
        return String(el('quiz-subject')?.value ?? '').trim();
    }

    function visibleNotes() {
        const search = currentSearch();
        const subject = normalize(currentSubjectFilter());

        return notes.filter((note) => {
            const matchesSearch = !search || normalize(`${note.text} ${note.date}`).includes(search);
            // Se a anotação tiver matéria vinculada no futuro, respeita o filtro.
            // Anotações antigas sem matéria continuam aparecendo para não ficarem inacessíveis.
            const matchesSubject = !subject || !note.materia || normalize(note.materia) === subject;
            return matchesSearch && matchesSubject;
        });
    }

    function visibleFlashcards() {
        const search = currentSearch();
        const subject = normalize(currentSubjectFilter());

        return flashcards.filter((card) => {
            const haystack = normalize(`${card.pergunta} ${card.resposta} ${card.deckName} ${card.materia}`);
            const matchesSearch = !search || haystack.includes(search);
            const matchesSubject = !subject || normalize(card.materia) === subject;
            return matchesSearch && matchesSubject;
        });
    }

    function truncate(text, max = 105) {
        const value = String(text ?? '').trim();
        return value.length > max ? `${value.slice(0, max - 1)}…` : value;
    }

    function renderNotes() {
        const list = el('notes-content-list');
        const empty = el('notes-empty');
        const items = visibleNotes();

        list.innerHTML = '';
        empty.hidden = items.length > 0;
        list.hidden = items.length === 0;

        items.forEach((note) => {
            const label = document.createElement('label');
            label.className = 'selectable-content-item';
            label.dataset.key = note.key;
            label.dataset.type = 'note';

            const checked = state.selectedNoteKeys.has(note.key);
            label.innerHTML = `
                <input class="content-checkbox note-checkbox" type="checkbox" ${checked ? 'checked' : ''}>
                <span class="content-checkmark"><i class="fa-solid fa-check"></i></span>
                <span class="content-item-body">
                    <span class="content-item-meta">
                        <span><i class="fa-regular fa-note-sticky"></i> Anotação</span>
                        ${note.date ? `<span>${escapeHtml(note.date)}</span>` : ''}
                    </span>
                    <strong>${escapeHtml(truncate(note.text, 125))}</strong>
                </span>
            `;
            list.appendChild(label);
        });

        updateBulkButtons();
    }

    function renderFlashcards() {
        const list = el('flashcards-content-list');
        const empty = el('flashcards-empty');
        const items = visibleFlashcards();

        list.innerHTML = '';
        empty.hidden = items.length > 0;
        list.hidden = items.length === 0;

        items.forEach((card) => {
            const label = document.createElement('label');
            label.className = 'selectable-content-item flashcard-content-item';
            label.dataset.key = card.key;
            label.dataset.type = 'flashcard';

            const checked = state.selectedFlashcardKeys.has(card.key);
            label.innerHTML = `
                <input class="content-checkbox flashcard-content-checkbox" type="checkbox" ${checked ? 'checked' : ''}>
                <span class="content-checkmark"><i class="fa-solid fa-check"></i></span>
                <span class="content-item-body">
                    <span class="content-item-meta">
                        <span><i class="fa-solid fa-layer-group"></i> ${escapeHtml(card.deckName)}</span>
                        ${card.materia ? `<span>${escapeHtml(card.materia)}</span>` : ''}
                    </span>
                    <strong>${escapeHtml(truncate(card.pergunta, 100))}</strong>
                    <small>${escapeHtml(truncate(card.resposta, 110))}</small>
                </span>
            `;
            list.appendChild(label);
        });

        updateBulkButtons();
    }

    function renderContentLists() {
        renderNotes();
        renderFlashcards();
        updateSelectionSummary();
    }

    function selectedNotes() {
        return notes.filter((note) => state.selectedNoteKeys.has(note.key));
    }

    function selectedFlashcards() {
        return flashcards.filter((card) => state.selectedFlashcardKeys.has(card.key));
    }

    function updateSelectionSummary() {
        const noteCount = state.selectedNoteKeys.size;
        const flashCount = state.selectedFlashcardKeys.size;
        const total = noteCount + flashCount;

        el('selected-content-count').textContent =
            `${total} ${total === 1 ? 'conteúdo selecionado' : 'conteúdos selecionados'}`;

        if (!total) {
            el('selected-content-detail').textContent = 'Escolha pelo menos um item abaixo.';
        } else {
            const parts = [];
            if (noteCount) parts.push(`${noteCount} ${noteCount === 1 ? 'anotação' : 'anotações'}`);
            if (flashCount) parts.push(`${flashCount} ${flashCount === 1 ? 'flashcard' : 'flashcards'}`);
            el('selected-content-detail').textContent = parts.join(' + ');
        }

        el('clear-content-selection').disabled = total === 0;
    }

    function updateBulkButtons() {
        const visibleNoteItems = visibleNotes();
        const allNotesSelected = visibleNoteItems.length > 0 && visibleNoteItems.every((n) => state.selectedNoteKeys.has(n.key));
        el('select-all-notes').textContent = allNotesSelected ? 'Desmarcar visíveis' : 'Selecionar visíveis';
        el('select-all-notes').disabled = visibleNoteItems.length === 0;

        const visibleCardItems = visibleFlashcards();
        const allCardsSelected = visibleCardItems.length > 0 && visibleCardItems.every((c) => state.selectedFlashcardKeys.has(c.key));
        el('select-all-flashcards-content').textContent = allCardsSelected ? 'Desmarcar visíveis' : 'Selecionar visíveis';
        el('select-all-flashcards-content').disabled = visibleCardItems.length === 0;
    }

    function toggleVisibleNotes() {
        const items = visibleNotes();
        const allSelected = items.length > 0 && items.every((n) => state.selectedNoteKeys.has(n.key));
        items.forEach((note) => {
            if (allSelected) state.selectedNoteKeys.delete(note.key);
            else state.selectedNoteKeys.add(note.key);
        });
        renderContentLists();
    }

    function toggleVisibleFlashcards() {
        const items = visibleFlashcards();
        const allSelected = items.length > 0 && items.every((c) => state.selectedFlashcardKeys.has(c.key));
        items.forEach((card) => {
            if (allSelected) state.selectedFlashcardKeys.delete(card.key);
            else state.selectedFlashcardKeys.add(card.key);
        });
        renderContentLists();
    }

    function clearSelection() {
        state.selectedNoteKeys.clear();
        state.selectedFlashcardKeys.clear();
        renderContentLists();
        setAlert(el('setup-warning'), '');
    }

    function getConfig() {
        const type = document.querySelector('input[name="quiz-type"]:checked')?.value ?? 'multiple';
        const selectedNotesData = selectedNotes();
        const selectedFlashcardsData = selectedFlashcards();

        return {
            filterSubject: currentSubjectFilter(),
            selectedNotes: selectedNotesData,
            selectedFlashcards: selectedFlashcardsData,
            count: Number(el('quiz-count').value || 10),
            difficulty: el('quiz-difficulty').value,
            type,
            totalContents: selectedNotesData.length + selectedFlashcardsData.length
        };
    }

    function validateConfig(config) {
        if (!config.totalContents) {
            setAlert(el('setup-warning'), 'Selecione pelo menos uma anotação ou um flashcard para montar o quiz.', 'error');
            return false;
        }

        setAlert(el('setup-warning'), '');
        return true;
    }

    async function tryGenerateWithAI(config) {
        try {
            const payload = {
                quantidade: config.count,
                dificuldade: config.difficulty,
                tipo: config.type,
                conteudos: [
                    ...config.selectedNotes.map((note) => ({
                        tipo: 'anotacao',
                        id: note.id,
                        texto: note.text,
                        data: note.date,
                        materia: note.materia || null
                    })),
                    ...config.selectedFlashcards.map((card) => ({
                        tipo: 'flashcard',
                        id: card.id,
                        baralho_id: card.deckId,
                        baralho: card.deckName,
                        materia: card.materia,
                        pergunta: card.pergunta,
                        resposta: card.resposta
                    }))
                ]
            };

            const response = await fetch(window.QUIZ_CONFIG.aiEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!response.ok) return null;

            const data = await response.json();
            if (!data?.sucesso || !Array.isArray(data?.questoes) || !data.questoes.length) return null;
            return data.questoes;
        } catch {
            return null;
        }
    }

    function createMultipleQuestion(card, pool, difficulty) {
        const correct = card.resposta;
        const distractors = uniqueText(
            shuffle(pool.filter((other) => other.key !== card.key).map((other) => other.resposta))
        )
            .filter((text) => normalize(text) !== normalize(correct))
            .slice(0, 3);

        const generic = [
            'Nenhuma das alternativas anteriores',
            'Essa informação não corresponde ao conteúdo selecionado',
            'A afirmação apresentada está incorreta',
            'Não é possível concluir isso com o material selecionado'
        ];

        while (distractors.length < 3) {
            const next = generic.find((item) => !distractors.includes(item) && normalize(item) !== normalize(correct));
            if (!next) break;
            distractors.push(next);
        }

        const options = shuffle([correct, ...distractors]).slice(0, 4);
        return {
            id: `q_${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`,
            type: 'multiple',
            question: card.pergunta,
            options,
            correctIndex: options.indexOf(correct),
            correctText: correct,
            explanation: `Resposta esperada: ${correct}`,
            topic: card.deckName || card.materia || 'Flashcard selecionado',
            source: 'flashcard',
            difficulty
        };
    }

    function createTrueFalseQuestion(card, pool, difficulty, forceFalse = null) {
        const otherAnswers = pool
            .filter((other) => other.key !== card.key)
            .map((other) => other.resposta)
            .filter(Boolean);

        const makeFalse = forceFalse === null ? Math.random() > 0.5 : forceFalse;
        const canBeFalse = makeFalse && otherAnswers.length > 0;
        const statementAnswer = canBeFalse ? shuffle(otherAnswers)[0] : card.resposta;

        return {
            id: `q_${crypto.randomUUID?.() ?? Math.random().toString(36).slice(2)}`,
            type: 'truefalse',
            question: `A afirmação abaixo está correta?\n${card.pergunta} — ${statementAnswer}`,
            options: ['Verdadeiro', 'Falso'],
            correctIndex: canBeFalse ? 1 : 0,
            correctText: card.resposta,
            explanation: canBeFalse
                ? `Falso. A resposta correta para esse flashcard é: ${card.resposta}`
                : `Verdadeiro. ${card.resposta}`,
            topic: card.deckName || card.materia || 'Flashcard selecionado',
            source: 'flashcard',
            difficulty
        };
    }

    async function generateQuestions(config) {
        // Quando a API estiver ativa, ela recebe exatamente os itens marcados e pode usar
        // anotações + flashcards, inclusive misturados.
        const aiQuestions = await tryGenerateWithAI(config);
        if (aiQuestions?.length) return aiQuestions.slice(0, config.count);

        // Sem API, não fingimos ter lido uma anotação: se alguma anotação foi escolhida,
        // interrompemos e avisamos claramente.
        if (config.selectedNotes.length) {
            throw new Error(
                'As anotações selecionadas já estão prontas para entrar no quiz, mas precisam da API de IA. Por enquanto, gere o quiz somente com os flashcards selecionados ou conecte a API.'
            );
        }

        const pool = config.selectedFlashcards;
        if (!pool.length) {
            throw new Error('Selecione pelo menos um flashcard para gerar o quiz sem a API.');
        }

        const shuffled = shuffle(pool);
        const questions = [];

        for (let i = 0; i < config.count; i++) {
            const card = shuffled[i % shuffled.length];
            let targetType = config.type;
            if (targetType === 'mixed') targetType = i % 2 === 0 ? 'multiple' : 'truefalse';

            questions.push(
                targetType === 'truefalse'
                    ? createTrueFalseQuestion(card, pool, config.difficulty, i % 2 === 1)
                    : createMultipleQuestion(card, pool, config.difficulty)
            );
        }

        return questions;
    }

    function normalizeQuestion(raw, index) {
        const options = Array.isArray(raw?.options)
            ? raw.options.map(String)
            : Array.isArray(raw?.alternativas)
                ? raw.alternativas.map(String)
                : [];

        let correctIndex = Number.isInteger(raw?.correctIndex)
            ? raw.correctIndex
            : Number.isInteger(raw?.correta)
                ? raw.correta
                : 0;

        if (correctIndex < 0 || correctIndex >= options.length) correctIndex = 0;

        return {
            id: raw?.id ?? `ai_${index}_${Date.now()}`,
            type: raw?.type ?? raw?.tipo ?? 'multiple',
            question: String(raw?.question ?? raw?.pergunta ?? `Questão ${index + 1}`),
            options,
            correctIndex,
            correctText: String(raw?.correctText ?? raw?.resposta ?? options[correctIndex] ?? ''),
            explanation: String(raw?.explanation ?? raw?.explicacao ?? `Resposta correta: ${options[correctIndex] ?? ''}`),
            topic: String(raw?.topic ?? raw?.topico ?? 'Conteúdo selecionado'),
            difficulty: raw?.difficulty ?? state.config?.difficulty ?? 'medium',
            source: raw?.source ?? 'ai'
        };
    }

    async function startQuiz() {
        const config = getConfig();
        if (!validateConfig(config)) return;

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

            if (!state.questions.length) throw new Error('Não foi possível criar questões com os conteúdos selecionados.');

            el('quiz-subject-badge').textContent =
                `${config.totalContents} ${config.totalContents === 1 ? 'conteúdo' : 'conteúdos'}`;
            el('quiz-title').textContent = 'Quiz de revisão personalizada';

            showScreen('quiz');
            renderQuestion();
        } catch (error) {
            setAlert(el('setup-warning'), error?.message || 'Não foi possível gerar o quiz.', 'error');
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
        el('difficulty-badge').textContent = difficultyLabels[question.difficulty] || difficultyLabels[state.config?.difficulty] || 'Média';

        const typeLabel = question.type === 'truefalse'
            ? ['fa-toggle-on', 'Verdadeiro ou falso']
            : ['fa-list-check', 'Múltipla escolha'];
        el('question-type-label').innerHTML = `<i class="fa-solid ${typeLabel[0]}"></i>${typeLabel[1]}`;

        el('question-text').textContent = question.question;
        const list = el('answers-list');
        list.innerHTML = '';
        const letters = ['A', 'B', 'C', 'D', 'E'];

        question.options.forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'answer-option';
            button.dataset.index = String(index);
            const marker = question.type === 'truefalse' ? (index === 0 ? 'V' : 'F') : (letters[index] ?? String(index + 1));
            button.innerHTML = `<span class="answer-letter">${marker}</span><span>${escapeHtml(option)}</span>`;
            button.addEventListener('click', () => selectAnswer(index));
            list.appendChild(button);
        });

        el('answer-feedback').hidden = true;
        el('answer-feedback').className = 'answer-feedback';
        el('next-question').disabled = true;
        el('next-question').innerHTML = currentNumber === total
            ? 'Ver resultado <i class="fa-solid fa-flag-checkered"></i>'
            : 'Próxima questão <i class="fa-solid fa-arrow-right"></i>';
    }

    function selectAnswer(index) {
        if (state.locked) return;

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

        [...document.querySelectorAll('.answer-option')].forEach((button) => {
            const buttonIndex = Number(button.dataset.index);
            button.disabled = true;
            if (buttonIndex === question.correctIndex) button.classList.add('correct');
            if (buttonIndex === index && !correct) button.classList.add('wrong');
        });

        const feedback = el('answer-feedback');
        feedback.hidden = false;
        feedback.className = `answer-feedback ${correct ? 'correct' : 'wrong'}`;
        el('feedback-icon').innerHTML = correct
            ? '<i class="fa-solid fa-check"></i>'
            : '<i class="fa-solid fa-xmark"></i>';
        el('feedback-title').textContent = correct ? 'Resposta correta!' : 'Quase! Essa não é a resposta correta.';
        el('feedback-text').textContent = question.explanation;
        el('next-question').disabled = false;
    }

    function nextQuestion() {
        if (!state.locked) return;
        state.current += 1;
        if (state.current >= state.questions.length) finishQuiz();
        else renderQuestion();
    }

    function getWrongAnswers() {
        return state.answers.filter((answer) => answer && !answer.correct);
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
        let description = 'Cada tentativa ajuda a descobrir quais pontos merecem mais atenção.';
        if (percent >= 90) {
            message = 'Excelente revisão!';
            description = 'Você dominou muito bem os conteúdos escolhidos.';
        } else if (percent >= 70) {
            message = 'Muito bom!';
            description = 'Seu desempenho foi bom. Vale revisar os poucos pontos em que teve dificuldade.';
        } else if (percent >= 50) {
            message = 'Bom começo!';
            description = 'Você já acertou boa parte. Agora foque exatamente nos erros.';
        }

        el('result-message').textContent = message;
        el('result-description').textContent = description;

        const wrongAnswers = getWrongAnswers();
        el('review-summary-text').textContent = wrongAnswers.length
            ? `Você teve dificuldade em ${wrongAnswers.length} ${wrongAnswers.length === 1 ? 'questão' : 'questões'}.`
            : 'Você acertou todas as questões desta revisão.';

        const topics = uniqueText(wrongAnswers.map((answer) => answer.question.topic || 'Conteúdo selecionado'));
        const topicsNode = el('wrong-topics');
        topicsNode.innerHTML = '';
        (topics.length ? topics : ['Conteúdo dominado']).forEach((topic) => {
            const chip = document.createElement('span');
            chip.className = 'topic-chip';
            chip.textContent = topic;
            topicsNode.appendChild(chip);
        });

        el('retry-wrong').disabled = wrongAnswers.length === 0;
        el('create-review-flashcards').disabled = wrongAnswers.length === 0;
        showScreen('result');
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
        el('quiz-title').textContent = 'Revisão dos erros';
        showScreen('quiz');
        renderQuestion();
    }

    function buildReviewCards() {
        return getWrongAnswers().map((answer, index) => ({
            tempId: `review_${index}_${Date.now()}`,
            pergunta: answer.question.question,
            resposta: answer.question.correctText || answer.question.options[answer.question.correctIndex] || '',
            explicacao: answer.question.explanation || '',
            topico: answer.question.topic || 'Revisão do quiz'
        }));
    }

    function suggestedReviewSubject() {
        if (state.config?.filterSubject) return state.config.filterSubject;
        const subjects = uniqueText(state.config?.selectedFlashcards?.map((card) => card.materia).filter(Boolean) ?? []);
        return subjects.length === 1 ? subjects[0] : '';
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
                <input class="review-card-checkbox" type="checkbox" value="${index}" checked>
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
        el('review-subject').value = suggestedReviewSubject();
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
        el('flashcard-selection-count').textContent = `${selected} ${selected === 1 ? 'selecionado' : 'selecionados'}`;
        el('select-all-flashcards').checked = checks.length > 0 && selected === checks.length;
        el('save-review-flashcards').disabled = selected === 0;
    }

    async function saveReviewFlashcards() {
        const allCards = JSON.parse(el('flashcard-modal').dataset.cards || '[]');
        const selectedIndexes = [...document.querySelectorAll('.review-card-checkbox:checked')]
            .map((check) => Number(check.value));
        const selectedCards = selectedIndexes.map((index) => allCards[index]).filter(Boolean);
        const targetSubject = el('review-subject').value.trim();

        if (!targetSubject) {
            setAlert(el('flashcard-save-feedback'), 'Escolha em qual matéria deseja salvar o baralho de revisão.', 'error');
            return;
        }
        if (!selectedCards.length) {
            setAlert(el('flashcard-save-feedback'), 'Selecione pelo menos um flashcard.', 'error');
            return;
        }

        const button = el('save-review-flashcards');
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando...';

        try {
            const response = await fetch(window.QUIZ_CONFIG.saveReviewUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ materia: targetSubject, cards: selectedCards })
            });
            const data = await response.json().catch(() => null);
            if (!response.ok || !data?.sucesso) throw new Error(data?.mensagem || 'Não foi possível salvar os flashcards.');

            setAlert(el('flashcard-save-feedback'), `${selectedCards.length} flashcard(s) salvo(s) com sucesso.`, 'success');
            toast('Flashcards de revisão criados!', 'success');
            setTimeout(closeFlashcardModal, 850);
        } catch (error) {
            setAlert(el('flashcard-save-feedback'), error?.message || 'Não foi possível salvar os flashcards.', 'error');
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
        renderContentLists();
        showScreen('setup');
    }

    function exitQuiz() {
        if (window.confirm('Deseja sair deste quiz? Seu progresso desta tentativa será perdido.')) resetQuiz();
    }

    function bindContentEvents() {
        el('content-search').addEventListener('input', renderContentLists);
        el('quiz-subject').addEventListener('change', renderContentLists);
        el('select-all-notes').addEventListener('click', toggleVisibleNotes);
        el('select-all-flashcards-content').addEventListener('click', toggleVisibleFlashcards);
        el('clear-content-selection').addEventListener('click', clearSelection);

        el('notes-content-list').addEventListener('change', (event) => {
            const input = event.target.closest('.note-checkbox');
            if (!input) return;
            const item = input.closest('.selectable-content-item');
            if (input.checked) state.selectedNoteKeys.add(item.dataset.key);
            else state.selectedNoteKeys.delete(item.dataset.key);
            updateSelectionSummary();
            updateBulkButtons();
        });

        el('flashcards-content-list').addEventListener('change', (event) => {
            const input = event.target.closest('.flashcard-content-checkbox');
            if (!input) return;
            const item = input.closest('.selectable-content-item');
            if (input.checked) state.selectedFlashcardKeys.add(item.dataset.key);
            else state.selectedFlashcardKeys.delete(item.dataset.key);
            updateSelectionSummary();
            updateBulkButtons();
        });
    }

    function bindEvents() {
        el('icon-perfil')?.addEventListener('click', () => { window.location.href = '../../perfil/perfil.php'; });
        el('icon-configuracoes')?.addEventListener('click', () => { window.location.href = '../../configuracoes/configuracoes.php'; });
        el('icon-sair')?.addEventListener('click', () => {
            el('logout-modal')?.classList.add('open');
            el('logout-modal')?.setAttribute('aria-hidden', 'false');
        });
        el('cancel-logout')?.addEventListener('click', () => {
            el('logout-modal')?.classList.remove('open');
            el('logout-modal')?.setAttribute('aria-hidden', 'true');
        });
        el('confirm-logout')?.addEventListener('click', () => { window.location.href = '../../login/logout.php'; });
        el('logout-modal')?.addEventListener('click', (event) => {
            if (event.target === el('logout-modal')) {
                el('logout-modal').classList.remove('open');
                el('logout-modal').setAttribute('aria-hidden', 'true');
            }
        });

        bindContentEvents();
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
            if (event.target === el('flashcard-modal')) closeFlashcardModal();
        });
        el('select-all-flashcards').addEventListener('change', (event) => {
            document.querySelectorAll('.review-card-checkbox').forEach((check) => { check.checked = event.target.checked; });
            updateSelectionCount();
        });
        el('flashcard-preview-list').addEventListener('change', (event) => {
            if (event.target.matches('.review-card-checkbox')) updateSelectionCount();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (el('flashcard-modal').classList.contains('open')) closeFlashcardModal();
            if (el('logout-modal')?.classList.contains('open')) {
                el('logout-modal').classList.remove('open');
                el('logout-modal').setAttribute('aria-hidden', 'true');
            }
        });
    }

    populateSubjects();
    renderContentLists();
    bindEvents();
})();
