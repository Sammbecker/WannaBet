#!/bin/bash

# Install Netlify CLI if not already installed
if ! command -v netlify &> /dev/null; then
    echo "Installing Netlify CLI..."
    npm install -g netlify-cli
fi

# Login to Netlify (if not already logged in)
netlify login

# Deploy the site
echo "Deploying to Netlify..."
netlify deploy --prod

echo "Deployment complete! Your site should be live at the URL provided above." 