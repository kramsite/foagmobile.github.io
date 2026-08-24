<?php
session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

if (
    empty(
        $_SESSION[
            'codigo_usuario'
        ]
    )
) {
    http_response_code(401);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Usuário não autenticado.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if (
    $_SERVER[
        'REQUEST_METHOD'
    ] !== 'POST'
) {
    http_response_code(405);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Método não permitido.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$entrada =
    json_decode(
        file_get_contents(
            'php://input'
        ),
        true
    );

if (!is_array($entrada)) {
    http_response_code(400);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Dados inválidos.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$codigoUsuario =
    $_SESSION[
        'codigo_usuario'
    ];

$pastaUsuario =
    __DIR__ .
    '/../json/usuarios/' .
    $codigoUsuario;

if (!is_dir($pastaUsuario)) {
    http_response_code(404);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Pasta do usuário não encontrada.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

$diasPermitidos = [
    'vermelho',
    'amarelo',
    'sem-aula',
    'roxo'
];

$dias = [];

if (
    isset($entrada['dias']) &&
    is_array($entrada['dias'])
) {
    foreach (
        $entrada['dias']
        as $data => $status
    ) {
        if (
            !is_string($data) ||
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $data
            ) ||
            !in_array(
                $status,
                $diasPermitidos,
                true
            )
        ) {
            continue;
        }

        $dias[$data] =
            $status;
    }
}

$metas = [];

if (
    isset($entrada['metas']) &&
    is_array($entrada['metas'])
) {
    foreach (
        $entrada['metas']
        as $chave => $valor
    ) {
        if (
            !is_string($chave)
        ) {
            continue;
        }

        $meta =
            max(
                0,
                min(
                    100,
                    (int)$valor
                )
            );

        $metas[$chave] =
            $meta;
    }
}

$configuracoes = [];

if (
    isset(
        $entrada[
            'configuracoes'
        ]
    ) &&
    is_array(
        $entrada[
            'configuracoes'
        ]
    )
) {
    foreach (
        $entrada[
            'configuracoes'
        ]
        as $ano => $config
    ) {
        if (
            !preg_match(
                '/^\d{4}$/',
                (string)$ano
            ) ||
            !is_array($config)
        ) {
            continue;
        }

        $validarData =
            function ($valor) {
                return (
                    is_string($valor) &&
                    preg_match(
                        '/^\d{4}-\d{2}-\d{2}$/',
                        $valor
                    )
                )
                    ? $valor
                    : '';
            };

        $configuracoes[
            (string)$ano
        ] = [
            'meta_anual' =>
                max(
                    0,
                    min(
                        100,
                        (int)(
                            $config[
                                'meta_anual'
                            ] ??
                            80
                        )
                    )
                ),

            'inicio_ano_letivo' =>
                $validarData(
                    $config[
                        'inicio_ano_letivo'
                    ] ??
                    ''
                ),

            'fim_ano_letivo' =>
                $validarData(
                    $config[
                        'fim_ano_letivo'
                    ] ??
                    ''
                ),

            'inicio_ferias_meio' =>
                $validarData(
                    $config[
                        'inicio_ferias_meio'
                    ] ??
                    ''
                ),

            'fim_ferias_meio' =>
                $validarData(
                    $config[
                        'fim_ferias_meio'
                    ] ??
                    ''
                )
        ];
    }
}

$dados = [
    'dias' =>
        $dias,

    'metas' =>
        $metas,

    'configuracoes' =>
        $configuracoes
];

$arquivo =
    $pastaUsuario .
    '/calendario.json';

$salvou =
    file_put_contents(
        $arquivo,
        json_encode(
            $dados,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );

if ($salvou === false) {
    http_response_code(500);

    echo json_encode(
        [
            'sucesso' => false,
            'mensagem' =>
                'Não foi possível salvar o calendário.'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

echo json_encode(
    [
        'sucesso' => true
    ],
    JSON_UNESCAPED_UNICODE
);