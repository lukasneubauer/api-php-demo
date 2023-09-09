FROM nginx:1.22-alpine

COPY docker/images/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/images/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/images/nginx/reload /usr/local/bin/reload

COPY public/robots.txt /var/www/html/public/robots.txt

WORKDIR /var/www/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
