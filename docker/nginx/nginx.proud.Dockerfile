FROM nginx:alpine
COPY docker/nginx/default.prod.conf /etc/nginx/conf.d/default.conf
