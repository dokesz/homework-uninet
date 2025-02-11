FROM oven/bun:1.2.2-debian

WORKDIR /app

COPY app/package.* .

RUN bun add vite -g

RUN bun install

ENTRYPOINT [ "vite" ]
