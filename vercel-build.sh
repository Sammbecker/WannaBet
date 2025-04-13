#!/bin/bash

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create necessary directories
mkdir -p public/vendor

# Copy vendor files to public directory
cp -r vendor public/

# Set proper permissions
chmod -R 755 public 