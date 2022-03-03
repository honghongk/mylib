
# $email 만료 등의 알림 받을 이메일
# https 세팅할 도메인

certbot certonly --email $email -d $domain --keep --register --server https://acme-v02.api.letsencrypt.org/directory --preferred-challenges dns --manual --manual-public-ip-logging-ok