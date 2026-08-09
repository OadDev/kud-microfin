#!/bin/bash
# Run on the Hostinger server (via SSH) by the "Set Android SHA-256
# Fingerprint" GitHub Actions workflow. Idempotent: updates the
# ANDROID_SHA256_FINGERPRINTS line in .env if it already exists, appends
# it otherwise. Needed for passkey/biometric login to work inside the
# Android app -- see mobile/README.md ("Biometric login").
set -e

cd "$1"

if [ ! -f .env ]; then
  echo "No .env found at $1 -- has the app been installed yet?" >&2
  exit 1
fi

if [ -z "$FINGERPRINT" ]; then
  echo "FINGERPRINT env var not set" >&2
  exit 1
fi

cp .env ".env.bak.$(date +%s)"

if grep -q '^ANDROID_SHA256_FINGERPRINTS=' .env; then
  sed -i "s#^ANDROID_SHA256_FINGERPRINTS=.*#ANDROID_SHA256_FINGERPRINTS=${FINGERPRINT}#" .env
else
  printf '\nANDROID_SHA256_FINGERPRINTS=%s\n' "$FINGERPRINT" >> .env
fi

grep '^ANDROID_SHA256_FINGERPRINTS=' .env
