#!/bin/bash

# Fuwari Theme Build Script for OpenOlah
# This script builds the theme assets using Vite

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "Building Fuwari theme for OpenOlah..."

# Check if pnpm is installed
if ! command -v pnpm &> /dev/null; then
    echo "pnpm is not installed. Installing pnpm..."
    npm install -g pnpm
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    pnpm install
fi

# Build the theme
echo "Building theme assets..."
pnpm run build

echo "Fuwari theme build completed!"
echo "Assets are output to: $SCRIPT_DIR/templates/assets/dist/"
