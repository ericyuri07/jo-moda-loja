# Como hospedar a Jô Modas na Oracle Cloud (grátis)

Tudo roda em **um servidor só**, com 2 containers:

| Container | O que faz |
|---|---|
| `app` | FrankenPHP (Caddy + PHP). Entrega o **site Flutter** em `/` e a **API Laravel** em `/api`. Cuida do HTTPS sozinho quando houver domínio. |
| `mysql` | Banco de dados. Não fica exposto na internet. |

As fotos dos produtos e o banco ficam em volumes do Docker, então não somem quando o container reinicia.

> **Importante:** configure o **domínio antes de cadastrar produtos de verdade**. A API salva a URL completa das fotos (ex.: `http://IP/api/uploads/...`), e trocar o endereço depois deixa as fotos antigas quebradas.

---

## 1. Criar a conta e o servidor

1. Crie a conta em **cloud.oracle.com** (plano *Always Free*).
   - Pede cartão só para verificação. Não ative o plano pago ("Upgrade to Pay As You Go") se quiser continuar grátis.
   - A **Home Region** não pode ser trocada depois. Escolha **Brazil East (São Paulo)** ou **Brazil Southeast (Vinhedo)**.
2. Gere uma chave SSH no Windows (PowerShell). Se já tiver uma, pule:
   ```powershell
   ssh-keygen -t ed25519
   ```
   Aperte Enter em tudo. A chave pública fica em `C:\Users\SEU_USUARIO\.ssh\id_ed25519.pub`.
3. No painel: **Compute → Instances → Create instance**
   - **Image:** Canonical **Ubuntu 24.04**
   - **Shape:** *Ampere* → `VM.Standard.A1.Flex` → **2 OCPU e 12 GB** (o grátis vai até 4 OCPU / 24 GB)
   - **Networking:** deixe criar a VCN e marque **Assign a public IPv4 address**
   - **SSH keys:** *Paste public keys* e cole o conteúdo do `id_ed25519.pub`
   - Se der "Out of capacity", tente outro *Availability Domain* ou tente de novo mais tarde.
4. Anote o **Public IP** da instância (ex.: `123.45.67.89`).

## 2. Liberar as portas 80 e 443

**No painel da Oracle:** Instance → *Subnet* → *Security Lists* → *Default Security List* → **Add Ingress Rules**:

| Source CIDR | Protocol | Destination Port |
|---|---|---|
| `0.0.0.0/0` | TCP | `80` |
| `0.0.0.0/0` | TCP | `443` |

**No servidor:** o Ubuntu da Oracle também tem um firewall próprio. Entre por SSH:

```powershell
ssh ubuntu@123.45.67.89
```

E rode:

```bash
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save
```

## 3. Instalar o Docker no servidor

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker ubuntu
exit
```

Entre de novo (`ssh ubuntu@IP`) para o grupo `docker` valer.

## 4. Levar o back-end para o servidor

O jeito mais simples é pelo GitHub:

```bash
git clone https://github.com/SEU_USUARIO/jo-modas-back-end.git
cd jo-modas-back-end
```

> Se o repositório for privado, o GitHub pede usuário e um **token** no lugar da senha (GitHub → Settings → Developer settings → Personal access tokens).

## 5. Configurar o `.env.production`

```bash
cp .env.production.example .env.production
echo "APP_KEY=base64:$(openssl rand -base64 32)"
echo "DB_PASSWORD=$(openssl rand -hex 16)"
echo "DB_ROOT_PASSWORD=$(openssl rand -hex 16)"
echo "ADMIN_REGISTER_CODE=$(openssl rand -hex 8)"
nano .env.production
```

Cole os valores gerados nos campos correspondentes e preencha:

- `SITE_ADDRESS=:80` (por enquanto, sem domínio)
- `APP_URL=http://123.45.67.89` (o IP do servidor)

Salve com `Ctrl+O`, Enter, `Ctrl+X`. **Guarde o `ADMIN_REGISTER_CODE`**: ele é pedido para criar a conta de administrador.

> As senhas do banco só valem na **primeira** vez que o MySQL sobe. Se mudar depois, o banco não aceita. Nesse caso, apague o volume (`dc down -v`), mas isso **apaga todos os dados**.

## 6. Gerar o site Flutter e enviar

**No Windows**, na pasta do back-end (PowerShell):

```powershell
cd \\wsl.localhost\Ubuntu\home\eric_yuri\jo-modas-back-end
.\deploy\build-front.ps1 -SiteUrl http://123.45.67.89
scp -r .\deploy\front\* ubuntu@123.45.67.89:~/jo-modas-back-end/deploy/front/
```

O script compila o projeto `Downloads\jo_modas` já apontando para `http://123.45.67.89/api`. Opções:

- `-Whatsapp 5542999999999` troca o número que recebe os pedidos.
- `-FrontPath C:\outra\pasta` usa outra pasta do Flutter.

## 7. Subir tudo

No servidor, crie um atalho para não digitar o comando longo toda vez:

```bash
echo "alias dc='docker compose -f docker-compose.prod.yml --env-file .env.production'" >> ~/.bashrc
source ~/.bashrc
```

Agora suba:

```bash
cd ~/jo-modas-back-end
dc up -d --build
dc logs -f app
```

A primeira vez demora alguns minutos (baixa as imagens e instala as dependências). Quando passar o `Aguardando o MySQL...` e as migrations, e começarem a aparecer as linhas em JSON do Caddy, aperte `Ctrl+C` e abra **http://123.45.67.89** no navegador.

O container roda as migrations sozinho toda vez que sobe.

**Criar o administrador:** no site, clique em *Entrar como ADM* → *Não tem conta ADM? Crie aqui* e use o `ADMIN_REGISTER_CODE`.

> Não rode `php artisan db:seed` em produção: os seeders criam produtos e usuários de teste.

## 8. Colocar o domínio (HTTPS)

1. Registre o domínio (ex.: `jomodas.com.br` no Registro.br).
2. Na zona DNS do domínio, crie um registro **A** apontando para o IP do servidor:
   - `@` → `123.45.67.89`
   - `www` → `123.45.67.89` (opcional)
3. Espere o DNS propagar (de minutos a algumas horas). Confira com:
   ```powershell
   nslookup jomodas.com.br
   ```
4. No servidor, edite o `.env.production`:
   ```
   SITE_ADDRESS=jomodas.com.br
   APP_URL=https://jomodas.com.br
   ```
   E rode `dc up -d`. O certificado HTTPS é emitido sozinho.
5. Gere o site de novo com o endereço novo e envie:
   ```powershell
   .\deploy\build-front.ps1 -SiteUrl https://jomodas.com.br
   scp -r .\deploy\front\* ubuntu@123.45.67.89:~/jo-modas-back-end/deploy/front/
   ```

## 9. App Android (APK)

Com o domínio e o HTTPS funcionando:

```powershell
cd $env:USERPROFILE\Downloads\jo_modas
flutter build apk --release --dart-define=API_URL=https://jomodas.com.br/api
```

O arquivo sai em `build\app\outputs\flutter-apk\app-release.apk`. O Android bloqueia API em `http://` por padrão, então o APK precisa do domínio com HTTPS.

## 10. Dia a dia

**Atualizar o back-end** depois de um `git push`:
```bash
cd ~/jo-modas-back-end && git pull && dc up -d --build
```

**Atualizar o site:** rode o `build-front.ps1` e o `scp` de novo. Não precisa reiniciar nada.

**Ver erros:**
```bash
dc logs --tail 100 app
dc exec app tail -n 50 storage/logs/laravel.log
```

**Backup** (banco + fotos), salvo em `~/backups`:
```bash
mkdir -p ~/backups
dc exec -T mysql sh -c 'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' | gzip > ~/backups/banco-$(date +%F).sql.gz
dc exec -T app tar czf - -C /app/storage app/public > ~/backups/fotos-$(date +%F).tar.gz
```

Copie os backups para o seu PC de vez em quando:
```powershell
scp ubuntu@123.45.67.89:~/backups/* .
```
