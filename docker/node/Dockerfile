ARG NODE_VERSION=24
ARG NODE_VARIANT=bookworm
ARG HOST_UID=1000
ARG HOST_GID=1000
FROM node:${NODE_VERSION}-${NODE_VARIANT}

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        bash-completion \
        zsh \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /usr/local/bin/yarn /usr/local/bin/yarnpkg \
    && npm install -g yarn

RUN groupmod -o -g "${HOST_GID}" node \
    && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" node \
    && mkdir -p /var/www/html/node_modules /var/www/app \
    && chown -R node:node /var/www/html /var/www/app

RUN printf '%s\n' \
    'autoload -Uz compinit' \
    'compinit' \
    > /etc/zsh/zshrc

WORKDIR /var/www/app
