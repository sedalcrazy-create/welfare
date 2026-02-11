#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Create standalone HTML guide with embedded images
"""

import base64
import re
from pathlib import Path

# Paths
PROJECT_DIR = Path(__file__).parent.parent
HTML_FILE = PROJECT_DIR / 'public' / 'user-guide.html'
SCREENSHOTS_DIR = PROJECT_DIR / 'public' / 'screenshots'
OUTPUT_FILE = PROJECT_DIR / 'user-guide-standalone.html'

def image_to_base64(image_path):
    """Convert image to base64 string"""
    with open(image_path, 'rb') as f:
        return base64.b64encode(f.read()).decode('utf-8')

def create_standalone_html():
    """Create standalone HTML with embedded images"""
    print('📖 Creating standalone user guide...')

    # Read original HTML
    with open(HTML_FILE, 'r', encoding='utf-8') as f:
        html_content = f.read()

    # Find all image references
    img_pattern = r'<img src="screenshots/(screenshot-\d+-[^"]+\.png)"'
    matches = re.findall(img_pattern, html_content)

    print(f'🖼️  Found {len(matches)} images to embed')

    # Replace each image with base64 data
    for img_filename in matches:
        img_path = SCREENSHOTS_DIR / img_filename

        if img_path.exists():
            print(f'   Converting {img_filename}...')
            base64_data = image_to_base64(img_path)

            # Replace src attribute
            old_src = f'src="screenshots/{img_filename}"'
            new_src = f'src="data:image/png;base64,{base64_data}"'
            html_content = html_content.replace(old_src, new_src)
        else:
            print(f'   ⚠️  Warning: {img_filename} not found')

    # Write standalone HTML
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        f.write(html_content)

    file_size_mb = OUTPUT_FILE.stat().st_size / (1024 * 1024)
    print(f'\n✅ Standalone guide created!')
    print(f'📄 File: {OUTPUT_FILE}')
    print(f'📦 Size: {file_size_mb:.2f} MB')
    print(f'\n💡 You can now open this file directly in any browser without needing the screenshots folder.')

if __name__ == '__main__':
    create_standalone_html()
