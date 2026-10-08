<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['codigo_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não autenticado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
 * FOAG — endpoint reservado para a API de IA.
 *
 * A tela do Quiz já envia SOMENTE os conteúdos marcados pelo aluno.
 * O corpo recebido terá este formato:
 *
 * {
 *   "quantidade": 10,
 *   "dificuldade": "medium",
 *   "tipo": "mixed",
 *   "conteudos": [
 *     {
 *       "tipo": "anotacao",
 *       "id": 123,
 *       "texto": "conteúdo exato da anotação",
 *       "data": "08/10/2026",
 *       "materia": null
 *     },
 *     {
 *       "tipo": "flashcard",
 *       "id": "CARD_...",
 *       "baralho_id": "BAR_...",
 *       "baralho": "Citologia",
 *       "materia": "Biologia",
 *       "pergunta": "...",
 *       "resposta": "..."
 *     }
 *   ]
 * }
 *
 * Quando a OpenAI for conectada, este endpoint deve usar apenas esse array
 * `conteudos` para gerar as questões e retornar:
 *
 * {
 *   "sucesso": true,
 *   "questoes": [
 *     {
 *       "question": "...",
 *       "options": ["...", "...", "...", "..."],
 *       "correctIndex": 0,
 *       "correctText": "...",
 *       "explanation": "...",
 *       "topic": "...",
 *       "type": "multiple"
 *     }
 *   ]
 * }
 */

http_response_code(503);

echo json_encode([
    'sucesso' => false,
    'mensagem' => 'A API de IA ainda não foi conectada.'
], JSON_UNESCAPED_UNICODE);
