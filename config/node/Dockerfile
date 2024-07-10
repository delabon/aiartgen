FROM node:18.15.0-slim

ARG UID=1000
ARG GID=1000

WORKDIR /code

RUN groupmod -g "${GID}" node && usermod -u "${UID}" -g "${GID}" node

USER node
