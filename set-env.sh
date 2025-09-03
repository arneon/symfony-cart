#!/usr/bin/env bash
set -euo pipefail

get_env() {
  file="$1" key="$2"
  awk -v k="$key" '
    /^[[:space:]]*#/ {next}
    $0 ~ "^[[:space:]]*(export[[:space:]]+)?"k"[[:space:]]*=" {
      line=$0
      sub(/^[[:space:]]*/, "", line)
      sub(/[[:space:]]*=[[:space:]]*/, "=", line)
      v = substr(line, index(line,"=")+1)

      # trim espacios
      sub(/^[[:space:]]+/, "", v); sub(/[[:space:]]+$/, "", v)

      # si está envuelto en comillas, quítalas
      if (v ~ /^".*"$/) { sub(/^"/,"",v); sub(/"$/,"",v) }
      else if (v ~ /^'\''.*'\''$/) { sub(/^'\''/,"",v); sub(/'\''$/,"",v) }

      # comentario inline (si no estaba citado)
      sub(/[[:space:]]#.*$/, "", v)

      print v; exit
    }
  ' "$file"
}

# Devuelve KEY=VALUE listo para .env (con comillas si hace falta)
fmt_kv() {
  local key="$1" value="$2"
  if printf '%s' "$value" | grep -q '[[:space:]#]'; then
    value=${value//\"/\\\"}
    printf '%s="%s"\n' "$key" "$value"
  else
    printf '%s=%s\n' "$key" "$value"
  fi
}

# Reemplaza o añade KEY=VALUE en archivo destino
update_kv_in_file() {
  local file="$1" key="$2" value="$3" newval
  newval=$(fmt_kv "$key" "$value")

  # Asegurar que el archivo exista
  [ -f "$file" ] || : > "$file"

  awk -v k="$key" -v nv="$newval" '
    BEGIN{done=0}
    /^[[:space:]]*#/ { print; next }                                 # comentarios intactos
    $0 ~ "^[[:space:]]*"k"[[:space:]]*=" { print nv; done=1; next }  # reemplazo
    { print }
    END{ if(!done) printf "%s\n", nv }                                # añadir al final si no existía
  ' "$file" > "$file.tmp" && mv "$file.tmp" "$file"
}

MYSQL_PORT="$(get_env .env.example MYSQL_PORT || true)"
              [ -n "${MYSQL_PORT:-}" ] || { echo "MYSQL_PORT no encontrada"; exit 1; }

REDIS_PORT="$(get_env .env.example REDIS_PORT || true)"
              [ -n "${REDIS_PORT:-}" ] || { echo "REDIS_PORT no encontrada"; exit 1; }

ELASTIC_PORT1="$(get_env .env.example ELASTIC_PORT1 || true)"
             [ -n "${ELASTIC_PORT1:-}" ] || { echo "ELASTIC_PORT1 no encontrada"; exit 1; }

update_kv_in_file "symfony/.env.example" "MYSQL_PORT" "$MYSQL_PORT"
update_kv_in_file "symfony/.env.example" "REDIS_PORT" "$REDIS_PORT"
update_kv_in_file "symfony/.env.test.example" "REDIS_PORT" "$REDIS_PORT"
update_kv_in_file "symfony/.env.example" "ELASTIC_PORT1" "$ELASTIC_PORT1"

cp ./.env.example ./.env
cp ./symfony/.env.example ./symfony/.env
cp ./symfony/.env.test.example ./symfony/.env.test
cp ./symfony/.env.test.local.example ./symfony/.env.test.local