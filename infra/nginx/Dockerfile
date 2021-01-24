FROM node:14.2-alpine as node
FROM nginx:1.18-alpine
SHELL ["/bin/ash", "-oeux", "pipefail", "-c"]

RUN apk update && \
  apk add --update --no-cache --virtual=.build-dependencies g++

COPY --from=node /usr/local/bin /usr/local/bin

COPY --from=node /usr/local/lib /usr/local/lib

COPY ./default.conf /etc/nginx/conf.d/default.conf

WORKDIR /work   
