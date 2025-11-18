# WordPress Bootstrap Claude - Installation Guide

Complete installation guide for the Translation Bridge™ CLI system.

## Table of Contents

- [System Requirements](#system-requirements)
- [Installation Methods](#installation-methods)
- [PHP Installation](#php-installation)
- [WordPress Theme Installation](#wordpress-theme-installation)
- [CLI Setup](#cli-setup)
- [Verification](#verification)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)

---

## System Requirements

### Minimum Requirements

- **Operating System**: macOS, Linux, or Windows (with WSL)
- **PHP**: 7.4 or higher
- **Memory**: 256MB RAM available
- **Disk Space**: 50MB for theme files
- **WordPress**: 5.8 or higher (for WordPress integration)

### Required PHP Extensions

- `php-mbstring` - Multi-byte string handling
- `php-json` - JSON encoding/decoding
- `php-curl` - For Claude API (optional)

### Optional

- **Claude API Key** - For direct AI editing (Mode B)
- **Git** - For version control
- **Composer** - For dependency management (future)

---

## Installation Methods

### Method 1: WordPress Theme Installation (Recommended)

Install as a WordPress theme for full integration.

### Method 2: Standalone CLI Installation

Use the CLI without WordPress.

---

## PHP Installation

### macOS

**Using Homebrew:**

```bash
# Install Homebrew (if not installed)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP
brew install php

# Verify installation
php --version
# Should show PHP 7.4 or higher
```

**Check Extensions:**

```bash
php -m | grep mbstring
php -m | grep json
php -m | grep curl
```

### Ubuntu/Debian Linux

```bash
# Update package list
sudo apt-get update

# Install PHP and required extensions
sudo apt-get install -y php php-cli php-mbstring php-json php-curl

# Verify installation
php --version
```

### Windows (WSL)

1. Install Windows Subsystem for Linux (WSL)
2. Follow Ubuntu installation steps above

### Verify PHP Installation

```bash
# Check PHP version (must be 7.4+)
php --version

# Check extensions
php -m

# Test PHP
php -r "echo 'PHP is working!\n';"
```

---

## WordPress Theme Installation

### Step 1: Download Theme

```bash
# Clone from GitHub
git clone https://github.com/coryhubbell/wordpress-bootstrap-claude.git

# Or download ZIP and extract
```

### Step 2: Install in WordPress

**Option A: Via WordPress Admin**

1. Navigate to `Appearance → Themes → Add New`
2. Click `Upload Theme`
3. Choose the ZIP file
4. Click `Install Now`
5. Click `Activate`

**Option B: Manual Installation**

```bash
# Copy to WordPress themes directory
cp -r wordpress-bootstrap-claude /path/to/wordpress/wp-content/themes/

# Set permissions
chmod -R 755 /path/to/wordpress/wp-content/themes/wordpress-bootstrap-claude
```

### Step 3: Activate Theme

1. Log in to WordPress admin
2. Go to `Appearance → Themes`
3. Find "WordPress Bootstrap Claude"
4. Click `Activate`

### Step 4: Verify WordPress Installation

Navigate to `WordPress Admin → WPBC Translation`

You should see:
- System Status showing all checks passing
- Available frameworks listed
- CLI commands documentation

---

## CLI Setup

### Step 1: Make CLI Executable

```bash
# Navigate to theme directory
cd /path/to/wordpress/wp-content/themes/wordpress-bootstrap-claude

# Make wpbc executable
chmod +x wpbc

# Test CLI
./wpbc --version
```

**Expected Output:**
```
WPBC - WordPress Bootstrap Claude
Version: 3.1.0
Translation Bridge™ - Universal Framework Translator

Supported Frameworks: 7
Translation Pairs: 30
```

### Step 2: Add to PATH (Optional)

Add wpbc to your system PATH for global access.

**macOS/Linux (Bash):**

```bash
# Add to ~/.bashrc or ~/.bash_profile
echo 'export PATH="/path/to/wordpress-bootstrap-claude:$PATH"' >> ~/.bashrc

# Reload shell
source ~/.bashrc

# Test from anywhere
wpbc --version
```

**macOS/Linux (Zsh):**

```bash
# Add to ~/.zshrc
echo 'export PATH="/path/to/wordpress-bootstrap-claude:$PATH"' >> ~/.zshrc

# Reload shell
source ~/.zshrc

# Test from anywhere
wpbc --version
```

**Alternative: Create Symlink**

```bash
# Create symlink in /usr/local/bin
sudo ln -s /path/to/wordpress-bootstrap-claude/wpbc /usr/local/bin/wpbc

# Test
wpbc --version
```

### Step 3: Create Logs Directory

```bash
# In theme directory
mkdir -p logs
chmod 755 logs
```

---

## Standalone CLI Installation

Use wpbc without WordPress.

### Step 1: Clone Repository

```bash
git clone https://github.com/coryhubbell/wordpress-bootstrap-claude.git
cd wordpress-bootstrap-claude
```

### Step 2: Make Executable

```bash
chmod +x wpbc
```

### Step 3: Test CLI

```bash
./wpbc --version
./wpbc list-frameworks
```

### Step 4: Add to PATH

Follow PATH setup from [CLI Setup Step 2](#step-2-add-to-path-optional)

---

## Verification

### Verify CLI Installation

```bash
# Show version
wpbc --version

# List frameworks
wpbc list-frameworks

# Show help
wpbc --help
```

### Verify PHP Requirements

```bash
# Check PHP version
php --version
# Must show 7.4 or higher

# Check required extensions
php -m | grep -E "(mbstring|json|curl)"
```

### Verify File Permissions

```bash
# CLI should be executable
ls -l wpbc
# Should show: -rwxr-xr-x ... wpbc

# Logs directory should be writable
ls -ld logs
# Should show: drwxr-xr-x ... logs
```

### Verify Translation Bridge

```bash
# Check core files exist
ls -l translation-bridge/core/
# Should show:
# - class-translator.php
# - class-parser-factory.php
# - class-converter-factory.php

# Check all parsers exist
ls -l translation-bridge/parsers/
# Should show 7 parser files

# Check all converters exist
ls -l translation-bridge/converters/
# Should show 7 converter files
```

---

## Configuration

### Optional: Claude API Key

For future direct AI editing (Mode B).

**Set Environment Variable:**

```bash
# Temporary (current session)
export CLAUDE_API_KEY="sk-ant-..."

# Permanent (add to shell profile)
echo 'export CLAUDE_API_KEY="sk-ant-..."' >> ~/.bashrc
source ~/.bashrc
```

**Or via WordPress:**

1. Go to `WordPress Admin → WPBC Translation → Settings`
2. Enter Claude API Key
3. Save Settings

### Optional: Default Settings

Create a config file for default options.

```bash
# In theme directory
cat > wpbc.config.json <<EOF
{
  "default_source": "bootstrap",
  "default_target": "divi",
  "output_dir": "./translations",
  "enable_logging": true,
  "log_level": "info"
}
EOF
```

---

## Troubleshooting

### Issue: PHP Not Found

**Error:**
```
env: php: No such file or directory
```

**Solution:**

```bash
# Find PHP location
which php

# Update shebang in wpbc
# Edit first line to match PHP location
# Example: #!/usr/local/bin/php
```

### Issue: Permission Denied

**Error:**
```
Permission denied: ./wpbc
```

**Solution:**

```bash
chmod +x wpbc
```

### Issue: Class Not Found

**Error:**
```
Fatal error: Class 'Translator' not found
```

**Solution:**

```bash
# Verify Translation Bridge files exist
ls -l translation-bridge/core/class-translator.php

# Check file permissions
chmod -R 755 translation-bridge/
```

### Issue: Can't Create Logs

**Error:**
```
Failed to create directory: logs
```

**Solution:**

```bash
# Create logs directory manually
mkdir -p logs
chmod 755 logs

# Or run as sudo (not recommended)
sudo mkdir logs
sudo chown $(whoami) logs
```

### Issue: Memory Exhausted

**Error:**
```
Fatal error: Allowed memory size exhausted
```

**Solution:**

```bash
# Increase PHP memory limit
php -d memory_limit=512M wpbc translate ...

# Or edit php.ini
# Find php.ini: php --ini
# Set: memory_limit = 512M
```

### Issue: WordPress Theme Not Activating

**Error:**
Theme activation fails in WordPress admin

**Solution:**

1. Check WordPress version (5.8+ required)
2. Check PHP version (7.4+ required)
3. Verify all theme files present:
   ```bash
   ls style.css functions.php index.php
   ```
4. Check error logs:
   ```bash
   tail -f /path/to/wordpress/wp-content/debug.log
   ```

---

## Post-Installation Steps

### 1. Run Test Translation

```bash
# Create a simple test file
echo '<div class="container"><h1>Test</h1></div>' > test.html

# Test translation
wpbc translate bootstrap divi test.html

# Verify output
cat test-divi.txt
```

### 2. Check Logs

```bash
# View recent logs
tail -f logs/wpbc-$(date +%Y-%m-%d).log
```

### 3. Read Documentation

```bash
# View CLI guide
cat CLI_GUIDE.md

# View quick start
cat QUICK_START.md
```

### 4. Try Examples

```bash
# If examples directory exists
wpbc translate bootstrap divi examples/hero.html
```

---

## Updating

### Update via Git

```bash
cd /path/to/wordpress-bootstrap-claude
git pull origin main
chmod +x wpbc
```

### Update WordPress Theme

1. Deactivate theme
2. Delete old version
3. Install new version
4. Reactivate theme

---

## Uninstallation

### Remove WordPress Theme

1. Activate a different theme
2. Go to `Appearance → Themes`
3. Delete "WordPress Bootstrap Claude"

### Remove CLI

```bash
# Remove from PATH (if added)
# Edit ~/.bashrc or ~/.zshrc and remove PATH line

# Remove symlink (if created)
sudo rm /usr/local/bin/wpbc

# Delete directory
rm -rf /path/to/wordpress-bootstrap-claude
```

---

## Next Steps

After installation:

1. **Read the CLI Guide**: `CLI_GUIDE.md`
2. **Try Quick Start**: `QUICK_START.md`
3. **Explore Examples**: `/examples` directory
4. **Check Documentation**: `/docs` directory
5. **Run Test Translations**: Try all 90 translation pairs

---

## Support

- **Issues**: https://github.com/coryhubbell/wordpress-bootstrap-claude/issues
- **Documentation**: `/docs` directory
- **Examples**: `/examples` directory

---

## System Requirements Checklist

Before using wpbc, verify:

- [ ] PHP 7.4+ installed (`php --version`)
- [ ] Required PHP extensions installed (`php -m`)
- [ ] wpbc executable (`./wpbc --version`)
- [ ] Translation Bridge files present (`ls translation-bridge/`)
- [ ] Logs directory exists and is writable (`ls -ld logs/`)
- [ ] CLI commands working (`wpbc list-frameworks`)

---

## Quick Reference

```bash
# Installation
git clone https://github.com/coryhubbell/wordpress-bootstrap-claude.git
cd wordpress-bootstrap-claude
chmod +x wpbc

# Verification
./wpbc --version
./wpbc list-frameworks

# First Translation
echo '<div class="container"><h1>Hello</h1></div>' > test.html
./wpbc translate bootstrap divi test.html

# Add to PATH
export PATH="$(pwd):$PATH"
wpbc --version

# Done!
```

---

**Ready to translate!** 🎉

Run `wpbc --help` to get started.
