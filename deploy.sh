#!/bin/bash

# ShopService Deployment Script

IMAGE_NAME="shopservice-app"
STACK_NAME="shopservice"
COMPOSE_FILE="docker-compose.prod.yml"

echo "========================================"
echo "   Iniciando Deploy ShopService VPS"
echo "========================================"

# 1. Verifica se o Docker está instalado
if ! command -v docker &> /dev/null
then
    echo "[ERRO] Docker não encontrado. Instale o Docker primeiro."
    exit 1
fi

# 2. Verifica configuração de rede echonet
echo "[INFO] Verificando rede 'echonet'..."
if ! docker network inspect echonet >/dev/null 2>&1; then
    echo "[AVISO] Rede 'echonet' não encontrada!"
    echo "       Criando rede 'echonet' (overlay attachable)..."
    docker network create --driver overlay --attachable echonet
    echo "[OK] Rede criada."
else
    echo "[OK] Rede 'echonet' já existe."
fi

# 3. Build da Imagem
echo "[INFO] Construindo imagem Docker ($IMAGE_NAME)..."
docker build -t $IMAGE_NAME:latest .

if [ $? -ne 0 ]; then
    echo "[ERRO] Falha no build da imagem."
    exit 1
fi

# 4. Deploy da Stack
echo "[INFO] Implantando Stack '$STACK_NAME' via Docker Swarm..."

# Verifica se o arquivo compose existe
if [ ! -f "$COMPOSE_FILE" ]; then
    echo "[ERRO] Arquivo $COMPOSE_FILE não encontrado!"
    exit 1
fi

docker stack deploy -c $COMPOSE_FILE $STACK_NAME

if [ $? -ne 0 ]; then
    echo "[ERRO] Falha ao implantar a stack."
    exit 1
fi

echo "========================================"
echo "   Deploy Concluído com Sucesso!"
echo "========================================"
echo "Status do serviço:"
docker service ls | grep $STACK_NAME
echo ""
echo "Acesse: https://shopservice.echo.dev.br"
