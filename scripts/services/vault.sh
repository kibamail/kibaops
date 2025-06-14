#!/bin/bash

# ==============================================================================
#      HashiCorp Vault Setup Script for Workspace-Scoped EaaS
# ==============================================================================
#
# This script configures Vault for a Laravel application with separate read and
# write roles for enhanced security. It prepares Vault to be ready for
# application-driven reads and writes.
#
# Features:
#   - Creates 'kibaops-reads' AppRole for reading & decrypting secrets.
#   - Creates 'kibaops-writes' AppRole for creating/updating & encrypting secrets.
#   - KVv2 secrets engine for versioned secret storage.
#   - Transit secrets engine for Encryption-as-a-Service (EaaS).
#   - Policies are scoped to the 'secrets/workspaces/*' path.
#
# Prerequisites:
#   - HashiCorp Vault server is running and unsealed.
#   - `vault` and `jq` CLIs are installed and in the system's PATH.
#   - VAULT_ADDR and VAULT_TOKEN environment variables are set.
#     (e.g., export VAULT_ADDR='http://127.0.0.1:8200'; export VAULT_TOKEN='root')
#
# Required Environment Variables:
#   - VAULT_READ_SECRET_ID: Secret ID for read role (must be provided)
#   - VAULT_WRITE_SECRET_ID: Secret ID for write role (must be provided)
#   - VAULT_READ_ROLE_ID: Role ID for read role (must be provided)
#   - VAULT_WRITE_ROLE_ID: Role ID for write role (must be provided)
#
# ==============================================================================

set -e

if [ -z "$VAULT_READ_SECRET_ID" ]; then
    echo "Error: VAULT_READ_SECRET_ID environment variable is required"
    exit 1
fi

if [ -z "$VAULT_WRITE_SECRET_ID" ]; then
    echo "Error: VAULT_WRITE_SECRET_ID environment variable is required"
    exit 1
fi

echo "🚀 Starting Vault configuration for workspace-scoped secrets..."

# --- 1. Enable Necessary Secrets & Auth Engines ---
echo "Enabling KVv2 secrets engine at path 'secrets'..."
vault secrets enable -path=secrets kv-v2 || echo "KV 'secrets' engine already enabled."

echo "Enabling Transit secrets engine at path 'transit'..."
vault secrets enable -path=transit transit || echo "Transit engine already enabled."

echo "Enabling AppRole auth method..."
vault auth enable approle || echo "AppRole auth already enabled."


# --- 2. Create Granular Access Policies ---
READ_POLICY_NAME="kibaops-reads-policy"
WRITE_POLICY_NAME="kibaops-writes-policy"

echo "Creating read-only policy: $READ_POLICY_NAME"
vault policy write $READ_POLICY_NAME - <<EOF
# Grant read access to secrets within any workspace.
path "secrets/data/workspaces/*" {
  capabilities = ["read"]
}
# Grant permission to decrypt data using a per-workspace key.
path "transit/decrypt/*" {
  capabilities = ["update"]
}
EOF

echo "Creating write-only policy: $WRITE_POLICY_NAME"
vault policy write $WRITE_POLICY_NAME - <<EOF
# Grant write access to secrets within any workspace.
path "secrets/data/workspaces/*" {
  capabilities = ["create", "update"]
}
# Grant permission to create transit keys for new workspaces.
path "transit/keys/*" {
    capabilities = ["create", "update"]
}
# Grant permission to encrypt data.
path "transit/encrypt/*" {
  capabilities = ["update"]
}
EOF


# --- 3. Configure AppRoles for the Application ---
READ_APPROLE_NAME="kibaops-reads"
WRITE_APPROLE_NAME="kibaops-writes"

echo "Creating AppRole: $READ_APPROLE_NAME"
vault write auth/approle/role/$READ_APPROLE_NAME \
    role_id="$READ_APPROLE_NAME" \
    token_policies="$READ_POLICY_NAME" \
    token_ttl=1h \
    token_max_ttl=4h

echo "Creating AppRole: $WRITE_APPROLE_NAME"
vault write auth/approle/role/$WRITE_APPROLE_NAME \
    role_id="$WRITE_APPROLE_NAME" \
    token_policies="$WRITE_POLICY_NAME" \
    token_ttl=10m \
    token_max_ttl=30m


vault write auth/approle/role/$READ_APPROLE_NAME/custom-secret-id secret_id="$VAULT_READ_SECRET_ID" > /dev/null
vault write auth/approle/role/$WRITE_APPROLE_NAME/custom-secret-id secret_id="$VAULT_WRITE_SECRET_ID" > /dev/null

echo "✅ Vault configuration complete!"
p
