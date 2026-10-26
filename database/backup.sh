#!/usr/bin/env bash
# ==============================================================================
# InternFlow: Daily MySQL Database Backup Script (NFR-REL-02)
# Universiti Tenaga Nasional (UNITEN) - College of Computing & Informatics
# ==============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/../storage/backups"
mkdir -p "${BACKUP_DIR}"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/internflow_backup_${TIMESTAMP}.sql"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_NAME="${DB_NAME:-internflow}"

echo "[INFO] Starting InternFlow MySQL backup at ${TIMESTAMP}..."

mysqldump --host="${DB_HOST}" --port="${DB_PORT}" --user="${DB_USER}" \
    --databases "${DB_NAME}" --routines --triggers --single-transaction > "${BACKUP_FILE}"

echo "[SUCCESS] Backup completed: ${BACKUP_FILE}"

# Keep 30 days of retention
find "${BACKUP_DIR}" -type f -name "*.sql" -mtime +30 -delete
echo "[INFO] Retained backups up to 30 days."
