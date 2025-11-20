#!/usr/bin/env bash
set -euo pipefail

if [ "${EUID}" -ne 0 ]; then
  echo "Run as root"
  exit 1
fi

OS_PKG=""
if command -v apt-get >/dev/null 2>&1; then
  OS_PKG="apt"
elif command -v yum >/dev/null 2>&1; then
  OS_PKG="yum"
fi

APP_DIR="$(pwd)"

prompt() {
  local p d
  p="$1"; d="${2:-}"
  read -r -p "$p" input
  if [ -z "${input:-}" ] && [ -n "$d" ]; then
    input="$d"
  fi
  echo "$input"
}

pkg_install() {
  if [ "$OS_PKG" = "apt" ]; then
    apt-get update -y
    DEBIAN_FRONTEND=noninteractive apt-get install -y "$@"
  elif [ "$OS_PKG" = "yum" ]; then
    yum install -y "$@"
  else
    echo "Unsupported OS"
    exit 1
  fi
}

install_prereqs_panel() {
  if [ "$OS_PKG" = "apt" ]; then
    pkg_install curl git nginx redis-server software-properties-common
    add-apt-repository -y ppa:ondrej/php || true
    apt-get update -y
    pkg_install php8.2 php8.2-fpm php8.2-cli php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-mysql
    pkg_install certbot python3-certbot-nginx
    if ! command -v composer >/dev/null 2>&1; then
      curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
      php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    fi
    systemctl enable --now php8.2-fpm nginx redis-server
  elif [ "$OS_PKG" = "yum" ]; then
    pkg_install curl git nginx redis php php-fpm php-cli php-mbstring php-xml php-gd php-curl php-zip php-mysqlnd certbot python3-certbot-nginx
    if ! command -v composer >/dev/null 2>&1; then
      curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
      php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    fi
    systemctl enable --now php-fpm nginx redis
  fi
}

panel_env_setup() {
  local app_url tz db_host db_port db_name db_user db_pass mail_mailer mail_host mail_port mail_user mail_pass mail_enc mail_from mail_name
  app_url="$(prompt "App URL: ")"
  tz="$(prompt "Timezone [UTC]: " "UTC")"
  db_host="$(prompt "DB Host [127.0.0.1]: " "127.0.0.1")"
  db_port="$(prompt "DB Port [3306]: " "3306")"
  db_name="$(prompt "DB Name [panel]: " "panel")"
  db_user="$(prompt "DB User [pterodactyl]: " "pterodactyl")"
  db_pass="$(prompt "DB Password: ")"
  mail_mailer="$(prompt "Mail Mailer [smtp]: " "smtp")"
  mail_host="$(prompt "Mail Host [127.0.0.1]: " "127.0.0.1")"
  mail_port="$(prompt "Mail Port [2525]: " "2525")"
  mail_user="$(prompt "Mail Username: ")"
  mail_pass="$(prompt "Mail Password: ")"
  mail_enc="$(prompt "Mail Encryption [true/false]: " "true")"
  mail_from="$(prompt "Mail From Address [noreply@example.com]: " "noreply@example.com")"
  mail_name="$(prompt "Mail From Name [Pterodactyl Panel]: " "Pterodactyl Panel")"

  if [ -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env" "$APP_DIR/.env.bak.$(date +%s)"
  elif [ -f "$APP_DIR/.env.example" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  else
    touch "$APP_DIR/.env"
  fi

  sed -i "s|^APP_URL=.*|APP_URL=$app_url|" "$APP_DIR/.env" || echo "APP_URL=$app_url" >> "$APP_DIR/.env"
  sed -i "s|^APP_ENV=.*|APP_ENV=production|" "$APP_DIR/.env" || echo "APP_ENV=production" >> "$APP_DIR/.env"
  sed -i "s|^APP_TIMEZONE=.*|APP_TIMEZONE=$tz|" "$APP_DIR/.env" || echo "APP_TIMEZONE=$tz" >> "$APP_DIR/.env"
  sed -i "s|^DB_HOST=.*|DB_HOST=$db_host|" "$APP_DIR/.env" || echo "DB_HOST=$db_host" >> "$APP_DIR/.env"
  sed -i "s|^DB_PORT=.*|DB_PORT=$db_port|" "$APP_DIR/.env" || echo "DB_PORT=$db_port" >> "$APP_DIR/.env"
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$db_name|" "$APP_DIR/.env" || echo "DB_DATABASE=$db_name" >> "$APP_DIR/.env"
  sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$db_user|" "$APP_DIR/.env" || echo "DB_USERNAME=$db_user" >> "$APP_DIR/.env"
  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$db_pass|" "$APP_DIR/.env" || echo "DB_PASSWORD=$db_pass" >> "$APP_DIR/.env"
  sed -i "s|^CACHE_DRIVER=.*|CACHE_DRIVER=redis|" "$APP_DIR/.env" || echo "CACHE_DRIVER=redis" >> "$APP_DIR/.env"
  sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=redis|" "$APP_DIR/.env" || echo "SESSION_DRIVER=redis" >> "$APP_DIR/.env"
  sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|" "$APP_DIR/.env" || echo "QUEUE_CONNECTION=redis" >> "$APP_DIR/.env"
  sed -i "s|^REDIS_HOST=.*|REDIS_HOST=127.0.0.1|" "$APP_DIR/.env" || echo "REDIS_HOST=127.0.0.1" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=$mail_mailer|" "$APP_DIR/.env" || echo "MAIL_MAILER=$mail_mailer" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_HOST=.*|MAIL_HOST=$mail_host|" "$APP_DIR/.env" || echo "MAIL_HOST=$mail_host" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_PORT=.*|MAIL_PORT=$mail_port|" "$APP_DIR/.env" || echo "MAIL_PORT=$mail_port" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_USERNAME=.*|MAIL_USERNAME=$mail_user|" "$APP_DIR/.env" || echo "MAIL_USERNAME=$mail_user" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=$mail_pass|" "$APP_DIR/.env" || echo "MAIL_PASSWORD=$mail_pass" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_ENCRYPTION=.*|MAIL_ENCRYPTION=$mail_enc|" "$APP_DIR/.env" || echo "MAIL_ENCRYPTION=$mail_enc" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=$mail_from|" "$APP_DIR/.env" || echo "MAIL_FROM_ADDRESS=$mail_from" >> "$APP_DIR/.env"
  sed -i "s|^MAIL_FROM_NAME=.*|MAIL_FROM_NAME=$mail_name|" "$APP_DIR/.env" || echo "MAIL_FROM_NAME=$mail_name" >> "$APP_DIR/.env"
}

create_nginx_panel_conf() {
  local domain php_upstream confdir
  domain="$(prompt "Panel domain: ")"
  php_upstream="$(prompt "PHP-FPM upstream [unix:/run/php/php8.2-fpm.sock or host:port]: " "unix:/run/php/php8.2-fpm.sock")"
  confdir="/etc/nginx/http.d"
  if [ ! -d "$confdir" ]; then
    confdir="/etc/nginx/conf.d"
  fi
  cat > "$confdir/panel.conf" <<EOF
server {
    listen 80;
    server_name $domain;
    root $APP_DIR/public;
    index index.php index.html index.htm;
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_pass $php_upstream;
    }
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 30d;
    }
}
EOF
  nginx -t
  systemctl reload nginx
  local ssl_email issue_ssl
  issue_ssl="$(prompt "Issue SSL with Let's Encrypt? [y/N]: " "N")"
  if [ "$issue_ssl" = "y" ] || [ "$issue_ssl" = "Y" ]; then
    ssl_email="$(prompt "Email for Let\'s Encrypt: ")"
    certbot --nginx -d "$domain" -m "$ssl_email" --agree-tos -n
    nginx -t
    systemctl reload nginx
  fi
}

install_panel() {
  install_prereqs_panel
  cd "$APP_DIR"
  composer install --no-dev --optimize-autoloader
  php artisan key:generate --force
  panel_env_setup
  php artisan migrate --seed --force
  chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" || true
  php artisan storage:link || true
  create_nginx_panel_conf
  echo "Panel installation completed"
}

install_wings() {
  if [ "$OS_PKG" = "apt" ]; then
    pkg_install curl jq
  elif [ "$OS_PKG" = "yum" ]; then
    pkg_install curl jq
  fi
  mkdir -p /etc/pterodactyl
  curl -L https://github.com/pterodactyl/wings/releases/latest/download/wings_linux_amd64 -o /usr/local/bin/wings
  chmod +x /usr/local/bin/wings
  local panel_url token node_id
  panel_url="$(prompt "Panel URL: ")"
  token="$(prompt "Node configuration token: ")"
  node_id="$(prompt "Node ID: ")"
  cd /etc/pterodactyl
  /usr/local/bin/wings configure --panel-url "$panel_url" --token "$token" --node "$node_id"
  cat > /etc/systemd/system/wings.service <<EOF
[Unit]
Description=Wings
After=network.target

[Service]
User=root
WorkingDirectory=/etc/pterodactyl
ExecStart=/usr/local/bin/wings
Restart=always
StartLimitBurst=3
StartLimitInterval=60s

[Install]
WantedBy=multi-user.target
EOF
  systemctl daemon-reload
  systemctl enable --now wings
  echo "Wings installation completed"
}

show_menu() {
  echo "Select an option"
  echo "0) Install Panel"
  echo "1) Install Wings"
  echo "2) Exit"
}

while true; do
  show_menu
  read -r -p "> " choice
  case "$choice" in
    0) install_panel ;;
    1) install_wings ;;
    2) exit 0 ;;
    *) echo "Invalid" ;;
  esac
done

