FROM golang:alpine

RUN apk add --update --no-cache --virtual .build-deps curl

WORKDIR /app 

RUN curl -fLo install.sh https://raw.githubusercontent.com/cosmtrek/air/master/install.sh \
    && chmod +x install.sh && sh install.sh && cp ./bin/air /bin/air

RUN apk del .build-deps

EXPOSE 9000

CMD air