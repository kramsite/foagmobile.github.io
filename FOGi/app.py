from flask import Flask, render_template
from flask_socketio import SocketIO, emit
import requests  # importante!

app = Flask(__name__)
app.config['SECRET_KEY'] = 'connectai-local'

# deixa o CORS liberado, evita dor de cabeça
socketio = SocketIO(app, cors_allowed_origins="*", async_mode="threading")


OLLAMA_URL = "http://localhost:11434/api/generate"
OLLAMA_MODEL = "llama3"  # confere no `ollama list` se o nome é esse

@app.route('/')
def index():
    # seu arquivo está em templates/FOGi.html
    return render_template('FOGi.html')

def gerar_resposta(mensagem: str) -> str:
    prompt = (
        "Você é a FOGi, uma IA que tem o proposito de ajudar com as duvidas escolares dos alunos. Você é do site FOAG"
        "Sempre seja amigavel e gentil, explique detalhadamente e sempre passe exercicios para fazer no final, respostas diretas mas não muito longas.\n\n"
        f"Usuário: {mensagem}\nConnectAI:"
    )

    payload = {
        "model": OLLAMA_MODEL,
        "prompt": prompt,
        "stream": False
    }

    try:
        # log pra ver no terminal o que está indo
        print(">>> Enviando para Ollama:", payload, flush=True)

        resp = requests.post(OLLAMA_URL, json=payload, timeout=60)
        resp.raise_for_status()
        data = resp.json()
        print("<<< Resposta Ollama:", data, flush=True)

        return data.get("response", "Desculpe, não consegui entender sua pergunta agora.")
    except Exception as e:
        print("ERRO NO OLLAMA:", e, flush=True)
        return f"Erro ao gerar resposta: {e}"

@socketio.on('message')
def handle_message(data):
    # data vem como { "message": "texto" }
    user_message = data.get('message', '')
    print("Mensagem recebida do front:", user_message, flush=True)

    resposta = gerar_resposta(user_message)
    emit('response', {'message': resposta})

if __name__ == '__main__':
    # roda em 127.0.0.1:5000
    socketio.run(app, debug=True)
