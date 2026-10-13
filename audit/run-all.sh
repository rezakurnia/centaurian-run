#!/usr/bin/env bash
# Full audit: capture reference + target (+ copy-parity run), then compare. Usage: bash run-all.sh [viewport ...]
set -e
cd "$(dirname "$0")"
node capture.mjs reference "$@" > cap-ref.log 2>&1 &
REF=$!
node capture.mjs target "$@"
wait $REF; cat cap-ref.log
PARITY=1 node capture.mjs target "$@" 
node compare.mjs
node compare.mjs --parity
