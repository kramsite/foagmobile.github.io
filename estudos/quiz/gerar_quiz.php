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
 * Por enquanto, o Quiz funciona localmente usando os flashcards do aluno.
 * Quando a OpenAI for configurada, substituiremos este arquivo pela chamada
 * ao modelo e retornaremos:
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
