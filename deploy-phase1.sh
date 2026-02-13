#!/bin/bash

# Phase 1 Deployment Script
# Deploy user guide and take screenshots

SERVER="root@37.152.174.87"
PROJECT_PATH="/var/www/welfare"

echo "🚀 Starting Phase 1 deployment..."
echo ""

# 1. Upload user guide
echo "📄 Step 1/4: Uploading user guide..."
scp user-guide-standalone.html $SERVER:$PROJECT_PATH/public/
if [ $? -eq 0 ]; then
    echo "✅ User guide uploaded successfully"
else
    echo "❌ Failed to upload user guide"
    exit 1
fi
echo ""

# 2. Upload screenshot script
echo "📸 Step 2/4: Uploading screenshot script..."
scp scripts/take-phase1-screenshots.js $SERVER:$PROJECT_PATH/scripts/
if [ $? -eq 0 ]; then
    echo "✅ Screenshot script uploaded"
else
    echo "❌ Failed to upload screenshot script"
    exit 1
fi
echo ""

# 3. Update package.json
echo "📦 Step 3/4: Updating package.json..."
scp package.json $SERVER:$PROJECT_PATH/
if [ $? -eq 0 ]; then
    echo "✅ package.json updated"
else
    echo "❌ Failed to update package.json"
    exit 1
fi
echo ""

# 4. Run screenshot script on server
echo "🎬 Step 4/4: Taking Phase 1 screenshots on server..."
ssh $SERVER << 'EOF'
  cd /var/www/welfare

  # Check if Playwright is installed
  if ! npm list @playwright/test > /dev/null 2>&1; then
    echo "📥 Installing Playwright..."
    npm install @playwright/test@^1.40.0
    npx playwright install chromium
  fi

  # Run screenshot script
  echo "📸 Capturing screenshots..."
  npm run screenshots:phase1

  # List results
  echo ""
  echo "📋 Screenshots captured:"
  ls -lh public/screenshots/phase1/
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Phase 1 deployment completed successfully!"
else
    echo ""
    echo "⚠️  Deployment completed but screenshot capture may have errors"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 User Guide URL:"
echo "   https://ria.jafamhis.ir/welfare/user-guide-standalone.html"
echo ""
echo "📸 Screenshots location:"
echo "   $PROJECT_PATH/public/screenshots/phase1/"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 All done!"
