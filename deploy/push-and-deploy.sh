#!/usr/bin/env bash
#
# Run from your Mac — uploads the project and runs ubuntu-install.sh on the server.
#
# Usage:
#   export MORIS_SSH=user@YOUR_SERVER_IP
#   bash deploy/push-and-deploy.sh
#
# Server must be Ubuntu 22.04/24.04 with SSH access and sudo for the user.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

MORIS_SSH="${MORIS_SSH:-}"
REMOTE_DIR="${MORIS_REMOTE_DIR:-/var/www/moris}"

if [[ -z "$MORIS_SSH" ]]; then
  echo "Set your server SSH target, e.g.:"
  echo "  export MORIS_SSH=ubuntu@123.45.67.89"
  exit 1
fi

echo "[push] Uploading to ${MORIS_SSH}:${REMOTE_DIR} ..."
ssh "$MORIS_SSH" "sudo mkdir -p '$REMOTE_DIR' && sudo chown -R \$(whoami):\$(whoami) '$REMOTE_DIR'"

rsync -avz --delete \
  --exclude 'includes/config.php' \
  --exclude 'includes/.installed' \
  --exclude 'assets/images/uploads/*' \
  --exclude '.git/' \
  --exclude 'deploy/ubuntu-env' \
  "$PROJECT_ROOT/" "${MORIS_SSH}:${REMOTE_DIR}/"

if [[ -f "$SCRIPT_DIR/ubuntu-env" ]]; then
  echo "[push] Uploading deploy/ubuntu-env (secrets) ..."
  scp "$SCRIPT_DIR/ubuntu-env" "${MORIS_SSH}:${REMOTE_DIR}/deploy/ubuntu-env"
else
  echo "[push] WARNING: deploy/ubuntu-env not found — create it on the server before install."
fi

echo "[push] Running ubuntu-install.sh on server ..."
ssh -t "$MORIS_SSH" "cd '$REMOTE_DIR' && sudo bash deploy/ubuntu-install.sh"

echo "[push] Finished. Open your domain in the browser."
